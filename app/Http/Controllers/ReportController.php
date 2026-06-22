<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\PartyGroup;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use App\Models\Party;
use App\Models\Category;
use App\Services\Reports\ItemReportByPartyService;
use Illuminate\Support\Carbon;
use Symfony\Component\Process\Process;

class ReportController extends Controller
{
    public function __construct(
        private readonly ItemReportByPartyService $itemReportByPartyService
    ) {
    }

    // ─── COLUMN MAP (verified from phpMyAdmin screenshots) ────────────────────
    // sales        : invoice_date, total_amount, payment_type, discount_pct,
    //                discount_rs, tax_amount, tax_pct, received_amount, balance,
    //                status, type, bill_number, party_id, grand_total
    // purchases    : bill_date, total_amount, payment_type, discount_pct,
    //                discount_rs, tax_amount, tax_pct, paid_amount, balance,
    //                status, type, bill_number, party_id, grand_total
    // expenses     : expense_date (NOT "date"), total_amount (NOT "amount"),
    //                payment_type, expense_category_id, expense_no, party
    // payment_ins  : date, amount, payment_type, party_id, reference_no
    // payment_outs : date, amount, payment_type, party_id, reference_no
    // ─────────────────────────────────────────────────────────────────────────

    public function index()
    {
        $categories = Category::all();
        $parties    = Party::all();
        $partyGroups = PartyGroup::orderBy('name')->get(['id', 'name']);
        $brokers    = Broker::orderBy('name')->get(['id', 'name', 'phone']);
        $items      = DB::table('items')->orderBy('name')->get();

        // Stock Summary
        $stockSummary = DB::table('items')
            ->select(
                'id', 'name', 'category_id',
                'sale_price', 'purchase_price',
                DB::raw('opening_qty as stock_qty'),
                DB::raw('opening_qty * purchase_price as stock_value')
            )->get();

        $stockSummaryTotals = [
            'qty'   => $stockSummary->sum('stock_qty'),
            'value' => $stockSummary->sum('stock_value'),
        ];

        // Low Stock
        $lowStock = DB::table('items')
            ->select(
                'id', 'name', 'category_id',
                DB::raw('opening_qty as stock_qty'),
                DB::raw('min_stock as min_stock_qty'),
                DB::raw('opening_qty * purchase_price as stock_value')
            )
            ->whereRaw('opening_qty <= min_stock')
            ->get();

        // Stock Detail
        $stockDetail = DB::table('items')
            ->select(
                'id', 'name', 'category_id',
                DB::raw('opening_qty as beginning_qty'),
                DB::raw('0 as qty_in'),
                DB::raw('0 as qty_out'),
                DB::raw('0 as purchase_amount'),
                DB::raw('0 as sale_amount'),
                DB::raw('opening_qty as closing_qty')
            )->get();

        $stockDetailTotals = [
            'beginning_qty'   => $stockDetail->sum('beginning_qty'),
            'qty_in'          => 0,
            'qty_out'         => 0,
            'purchase_amount' => 0,
            'sale_amount'     => 0,
            'closing_qty'     => $stockDetail->sum('closing_qty'),
        ];

        // Item Wise P&L
        $itemWisePnL = DB::table('items')
            ->select(
                'name',
                DB::raw('0 as sale'),
                DB::raw('0 as cr_note'),
                DB::raw('0 as purchase'),
                DB::raw('0 as dr_note'),
                DB::raw('opening_qty * purchase_price as opening_stock'),
                DB::raw('opening_qty * purchase_price as closing_stock'),
                DB::raw('0 as tax_receivable'),
                DB::raw('0 as tax_payable'),
                DB::raw('0 as mfg_cost'),
                DB::raw('0 as consumption_cost'),
                DB::raw('0 as net_profit')
            )->get();

        $itemWisePnLTotal = 0;

        $stockSummaryByCat = DB::table('items')
            ->join('categories', 'categories.id', '=', 'items.category_id')
            ->select(
                'categories.name as category_name',
                DB::raw('SUM(opening_qty) as stock_qty'),
                DB::raw('SUM(opening_qty * purchase_price) as stock_value')
            )
            ->groupBy('categories.id', 'categories.name')
            ->get();

        $partyReport        = collect();
        $partyReportTotals  = ['sale_qty' => 0, 'sale_amount' => 0, 'purchase_qty' => 0, 'purchase_amount' => 0];
        $itemReportByParty  = collect();
        $itemReportByPartyTotals = [
            'sale_quantity' => 0,
            'sale_amount' => 0,
            'purchase_quantity' => 0,
            'purchase_amount' => 0,
        ];
        $itemCategoryPnL    = collect();
        $salePurchaseByCat  = collect();
        $itemWiseDiscountData = $this->buildItemWiseDiscountReport(new Request([
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->endOfMonth()->toDateString(),
        ]));
        $itemWiseDiscount   = $itemWiseDiscountData['items'];
        $iwdTotals          = $itemWiseDiscountData['totals'];
        $itemDetail         = collect();
        $firms              = collect();

        return view('dashboard.report', compact(
            'categories', 'parties', 'partyGroups', 'brokers', 'items',
            'stockSummary', 'stockSummaryTotals',
            'lowStock', 'stockDetail', 'stockDetailTotals',
            'itemWisePnL', 'itemWisePnLTotal',
            'stockSummaryByCat', 'partyReport', 'partyReportTotals',
            'itemReportByParty', 'itemReportByPartyTotals',
            'itemCategoryPnL', 'salePurchaseByCat',
            'itemWiseDiscount', 'iwdTotals', 'itemDetail', 'firms'
        ));
    }

    public function unreceivedInvoicePdf(Request $request)
    {
        $sales = $this->getUnreceivedSalesForPdf($request);

        $viewData = [
            'sales' => $sales,
            'generatedAt' => now(),
            'filters' => [
                'from' => $request->input('from'),
                'to' => $request->input('to'),
                'party_id' => $request->input('party_id'),
                'party_name' => $request->input('party_name'),
                'broker_id' => $request->input('broker_id'),
                'city' => $request->input('city'),
            ],
        ];

        $htmlDirectory = storage_path('app/unreceived-invoice-pdf');
        File::ensureDirectoryExists($htmlDirectory);

        $htmlPath = $htmlDirectory . DIRECTORY_SEPARATOR . 'unreceived-' . uniqid() . '.html';
        $pdfPath = $htmlDirectory . DIRECTORY_SEPARATOR . 'unreceived-' . uniqid() . '.pdf';

        File::put($htmlPath, view('dashboard.reports.pdf.unreceived-invoices', $viewData)->render());

        $chromePath = $this->resolveChromeExecutable();
        abort_unless($chromePath !== null, 500, 'Chrome/Edge executable not found for PDF generation.');

        $process = new Process([
            $chromePath,
            '--headless=new',
            '--disable-gpu',
            '--disable-extensions',
            '--disable-sync',
            '--no-pdf-header-footer',
            '--run-all-compositor-stages-before-draw',
            '--virtual-time-budget=1200',
            '--print-to-pdf=' . $pdfPath,
            'file:///' . str_replace('\\', '/', $htmlPath),
        ]);

        $process->setTimeout(60);
        $process->run();

        File::delete($htmlPath);

        if (! $process->isSuccessful() || ! File::exists($pdfPath)) {
            File::delete($pdfPath);
            abort(500, 'Unreceived invoice PDF generation failed.');
        }

        return response()->download(
            $pdfPath,
            'agarri-list-' . now()->format('Ymd-His') . '.pdf'
        )->deleteFileAfterSend(true);
    }

    // ─── HELPER: parse date range ─────────────────────────────────────────────
    private function dateRange(Request $request): array
    {
        $from = $request->filled('from') ? $request->input('from') : '2000-01-01';
        $to   = $request->filled('to') ? $request->input('to') : now()->toDateString();
        return [$from, $to];
    }

    // ─── HELPER: safe float format ────────────────────────────────────────────
    private function fmt($val): float
    {
        return round((float) ($val ?? 0), 2);
    }

    private function getUnreceivedSalesForPdf(Request $request)
    {
        $today = now()->startOfDay();
        $hasDealDaysColumn = Schema::hasColumn('sales', 'deal_days');

        return Sale::with(['party', 'broker', 'items'])
            ->where('balance', '>', 0)
            ->where('type', 'invoice')
            ->when($request->filled('from'), function ($query) use ($request) {
                $query->whereDate('invoice_date', '>=', $request->input('from'));
            })
            ->when($request->filled('to'), function ($query) use ($request) {
                $query->whereDate('invoice_date', '<=', $request->input('to'));
            })
            ->when($request->filled('party_id'), function ($query) use ($request) {
                $query->where('party_id', $request->input('party_id'));
            })
            ->when($request->filled('party_name'), function ($query) use ($request) {
                $query->whereHas('party', function ($partyQuery) use ($request) {
                    $partyQuery->where('name', 'like', '%' . trim((string) $request->input('party_name')) . '%');
                });
            })
            ->when($request->filled('broker_id'), function ($query) use ($request) {
                $query->where('broker_id', $request->input('broker_id'));
            })
            ->when($request->filled('city'), function ($query) use ($request) {
                $query->whereHas('party', function ($partyQuery) use ($request) {
                    $partyQuery->where('city', 'like', '%' . trim((string) $request->input('city')) . '%');
                });
            })
            ->orderBy('due_date')
            ->orderBy('party_id')
            ->get()
            ->filter(function ($sale) {
                return $sale->party !== null;
            })
            ->groupBy(function ($sale) {
                return trim((string) ($sale->party?->city ?: 'بغیر شہر'));
            })
            ->map(function ($citySales, $cityName) use ($today, $hasDealDaysColumn) {
                return [
                    'city_name' => $cityName,
                    'parties' => $citySales->groupBy(function ($sale) {
                        return $sale->party_id ?: ('sale-' . $sale->id);
                    })->map(function ($partySales) use ($today, $hasDealDaysColumn) {
                        $firstSale = $partySales->first();
                        $party = $firstSale->party;

                        $rows = $partySales->map(function ($sale) use ($today, $hasDealDaysColumn) {
                            $saleDate = $sale->invoice_date ?: $sale->order_date;
                            $dueDate = $sale->due_date;

                            $dealDays = null;
                            if ($hasDealDaysColumn && !is_null($sale->deal_days)) {
                                $dealDays = (int) $sale->deal_days;
                            } elseif ($saleDate && $dueDate) {
                                $dealDays = $saleDate->diffInDays($dueDate);
                            }

                            $lateDays = 0;
                            if ($dueDate && $today->gt($dueDate->copy()->startOfDay())) {
                                $lateDays = $today->diffInDays($dueDate->copy()->startOfDay());
                            }

                            if ($lateDays === 0) {
                                $toneLabel = 'Soft';
                                $toneClass = 'soft';
                            } elseif ($lateDays <= 5) {
                                $toneLabel = 'Normal';
                                $toneClass = 'normal';
                            } elseif ($lateDays <= 15) {
                                $toneLabel = 'Strict';
                                $toneClass = 'strict';
                            } else {
                                $toneLabel = 'Very Strict';
                                $toneClass = 'very-strict';
                            }

                            return [
                                'bill_number' => $sale->bill_number ?: ('#' . $sale->id),
                                'broker_name' => $sale->broker?->name ?: '-',
                                'broker_phone' => $sale->broker?->phone ?: '-',
                                'items' => $sale->items->map(function ($item) {
                                    $rate = (float) ($item->unit_price ?? 0);
                                    if ($rate <= 0 && (float) ($item->quantity ?? 0) > 0 && (float) ($item->amount ?? 0) > 0) {
                                        $rate = (float) $item->amount / (float) $item->quantity;
                                    }

                                    return [
                                        'name' => (string) ($item->item_name ?: 'Item'),
                                        'rate' => $rate,
                                    ];
                                })->values()->all(),
                                'sale_date' => $saleDate
                                    ? $saleDate->format('d/m/Y')
                                    : '-',
                                'due_date' => $dueDate
                                    ? $dueDate->format('d/m/Y')
                                    : '-',
                                'deal_days' => $dealDays,
                                'late_days' => max(0, (int) $lateDays),
                                'tone_label' => $toneLabel,
                                'tone_class' => $toneClass,
                                'grand_total' => (float) ($sale->grand_total ?? 0),
                                'received_amount' => (float) ($sale->received_amount ?? 0),
                                'balance' => (float) ($sale->balance ?? 0),
                            ];
                        })->values();

                        return [
                            'party_name' => $party?->name ?: 'N/A',
                            'party_phone' => $party?->phone ?: '-',
                            'party_whatsapp' => $party?->phone_number_2 ?: '-',
                            'party_ptcl' => $party?->ptcl_number ?: '-',
                            'party_address' => $party?->address ?: '-',
                            'rows' => $rows,
                            'total_balance' => $rows->sum('balance'),
                        ];
                    })->values(),
                ];
            })->values();
    }

    private function resolveChromeExecutable(): ?string
    {
        $candidates = [
            'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
            'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    // ============================================================
    // 1. PARTY STATEMENT
    // ============================================================
    public function partyStatement(Request $request, $partyId = null)
    {
        $partyId = $partyId ?: $request->input('party_id');
        $party = Party::findOrFail($partyId);
        [$from, $to] = $this->dateRange($request);

        $rows = collect();
        $signedOpening = (float) ($party->opening_balance ?? 0);
        if (strtolower((string) $party->transaction_type) === 'pay') {
            $signedOpening *= -1;
        }

        $rows->push((object) [
            'date' => $from,
            'type' => strtolower((string) $party->transaction_type) === 'pay' ? 'Payable Opening Balance' : 'Receivable Opening Balance',
            'reference' => 'OB-' . $party->id,
            'payment_type' => '-',
            'debit' => $signedOpening > 0 ? $signedOpening : 0,
            'credit' => $signedOpening < 0 ? abs($signedOpening) : 0,
            'source' => 'opening',
        ]);

        // Sales — invoice_date, total_amount
        if (Schema::hasTable('sales')) {
            $rows = $rows->merge(
                DB::table('sales')
                    ->where('party_id', $partyId)
                    ->whereBetween('invoice_date', [$from, $to])
                    ->select(
                        'id as source_id',
                        'invoice_date as date',
                        DB::raw("'Sale' as type"),
                        'bill_number as reference',
                        DB::raw("COALESCE(payment_type, 'Cash') as payment_type"),
                        DB::raw('COALESCE(grand_total, total_amount, 0) as debit'),
                        DB::raw('0 as credit'),
                        DB::raw("'sale' as source")
                    )->get()
            );
        }

        // Purchases — bill_date, total_amount
        if (Schema::hasTable('purchases')) {
            $purchasePaymentTypeSelect = Schema::hasColumn('purchases', 'payment_type')
                ? DB::raw("COALESCE(payment_type, 'Cash') as payment_type")
                : DB::raw("'Cash' as payment_type");

            $rows = $rows->merge(
                DB::table('purchases')
                    ->where('party_id', $partyId)
                    ->whereBetween('bill_date', [$from, $to])
                    ->select(
                        'id as source_id',
                        'bill_date as date',
                        DB::raw("'Purchase' as type"),
                        'bill_number as reference',
                        $purchasePaymentTypeSelect,
                        DB::raw('0 as debit'),
                        DB::raw('COALESCE(grand_total, total_amount, 0) as credit'),
                        DB::raw("'purchase' as source")
                    )->get()
            );
        }

        // Payment Ins — date, amount, payment_type
        if (Schema::hasTable('payment_ins')) {
            $rows = $rows->merge(
                DB::table('payment_ins')
                    ->where('party_id', $partyId)
                    ->whereBetween('date', [$from, $to])
                    ->select(
                        'id as source_id',
                        'date',
                        DB::raw("'Payment In' as type"),
                        'reference_no as reference',
                        DB::raw("COALESCE(payment_type, 'Cash') as payment_type"),
                        DB::raw('0 as debit'),
                        'amount as credit',
                        DB::raw("'payment_in' as source")
                    )->get()
            );
        }

        // Payment Outs — date, amount, payment_type
        if (Schema::hasTable('payment_outs')) {
            $rows = $rows->merge(
                DB::table('payment_outs')
                    ->where('party_id', $partyId)
                    ->whereBetween('date', [$from, $to])
                    ->select(
                        'id as source_id',
                        'date',
                        DB::raw("'Payment Out' as type"),
                        'reference_no as reference',
                        DB::raw("COALESCE(payment_type, 'Cash') as payment_type"),
                        'amount as debit',
                        DB::raw('0 as credit'),
                        DB::raw("'payment_out' as source")
                    )->get()
            );
        }

        $rows = $rows->sortBy([
            ['date', 'asc'],
            ['reference', 'asc'],
        ])->values();

        $running        = 0.0;
        $totalDebit     = 0.0;
        $totalCredit    = 0.0;
        $totalSale      = 0.0;
        $totalPurchase  = 0.0;
        $totalMoneyIn   = 0.0;
        $totalMoneyOut  = 0.0;

        $transactions = $rows->map(function ($r) use (&$running, &$totalDebit, &$totalCredit, &$totalSale, &$totalPurchase, &$totalMoneyIn, &$totalMoneyOut) {
            $debit  = (float) ($r->debit ?? 0);
            $credit = (float) ($r->credit ?? 0);
            $running     += ($debit - $credit);
            $totalDebit  += $debit;
            $totalCredit += $credit;

            if (($r->source ?? '') === 'sale') {
                $totalSale += $debit;
            }
            if (($r->source ?? '') === 'purchase') {
                $totalPurchase += $credit;
            }
            if (($r->source ?? '') === 'payment_in') {
                $totalMoneyIn += $credit;
            }
            if (($r->source ?? '') === 'payment_out') {
                $totalMoneyOut += $debit;
            }

            $receivable = $running > 0 ? $running : 0;
            $payable = $running < 0 ? abs($running) : 0;
            $txnBalance = $debit > 0 ? $debit - $credit : $credit - $debit;

            return [
                'date'            => $r->date,
                'type'            => $r->type,
                'reference'       => $r->reference ?? '-',
                'payment_type'    => $r->payment_type ?? '-',
                'source'          => $r->source ?? '',
                'source_id'       => $r->source_id ?? null,
                'edit_url'        => ($r->source ?? '') === 'sale' && !empty($r->source_id)
                    ? route('sale.edit', ['sale' => $r->source_id])
                    : null,
                'preview_url'     => ($r->source ?? '') === 'sale' && !empty($r->source_id)
                    ? route('invoice', ['sale_id' => $r->source_id])
                    : null,
                'total'           => $this->fmt(max($debit, $credit)),
                'received_paid'   => $this->fmt($credit > 0 ? $credit : $debit),
                'txn_balance'     => $this->fmt(abs($txnBalance)),
                'receivable_balance' => $this->fmt($receivable),
                'payable_balance' => $this->fmt($payable),
                'debit'           => $this->fmt($debit),
                'credit'          => $this->fmt($credit),
                'running_balance' => $this->fmt(abs($running)),
                'running_balance_label' => $running < 0 ? 'Cr' : 'Dr',
            ];
        })->values()->toArray();

        return response()->json([
            'success'          => true,
            'party'            => [
                'id' => $party->id,
                'name' => $party->name,
            ],
            'transactions'     => $transactions,
            'opening_balance'  => $this->fmt(abs($signedOpening)),
            'opening_balance_label' => $signedOpening < 0 ? 'Cr' : 'Dr',
            'closing_balance'  => $this->fmt(abs($running)),
            'closing_balance_label' => $running < 0 ? 'Cr' : 'Dr',
            'total_debit'      => $this->fmt($totalDebit),
            'total_credit'     => $this->fmt($totalCredit),
            'total_sale'       => $this->fmt($totalSale),
            'total_purchase'   => $this->fmt($totalPurchase),
            'total_money_in'   => $this->fmt($totalMoneyIn),
            'total_money_out'  => $this->fmt($totalMoneyOut),
            'total_receivable' => $this->fmt($running > 0 ? $running : 0),
            'total_payable'    => $this->fmt($running < 0 ? abs($running) : 0),
        ]);
    }

    // ============================================================
    // 2. ALL PARTIES
    // ============================================================
    public function allParties(Request $request)
    {
        $query = Party::query();

        if ($request->filled('party_group')) {
            $group = trim((string) $request->party_group);
            if ($group !== '') {
                if (strtolower($group) === 'ungrouped') {
                    $query->where(function ($subQuery) {
                        $subQuery->whereNull('party_group')
                            ->orWhere('party_group', '');
                    });
                } else {
                    $query->where('party_group', $group);
                }
            }
        }

        if ($request->filled('party_id')) {
            $query->where('id', $request->party_id);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('type')) {
            if ($request->type === 'receivable') {
                $query->where('current_balance', '>', 0);
            } elseif ($request->type === 'payable') {
                $query->where('current_balance', '<', 0);
            }
        }

        $parties = $query->get()->map(function ($p) {
            $balance = (float) ($p->current_balance ?? 0);
            return [
                'id'                   => $p->id,
                'name'                 => $p->name,
                'email'                => $p->email,
                'phone'                => $p->phone,
                'party_group'          => $p->party_group ?: 'Ungrouped',
                'receivable_balance'   => $balance > 0 ? $balance : 0,
                'payable_balance'      => $balance < 0 ? abs($balance) : 0,
                'credit_limit_enabled' => $p->credit_limit_enabled,
                'credit_limit_amount'  => $p->credit_limit_amount,
            ];
        });

        return response()->json(['success' => true, 'parties' => $parties]);
    }

    // ============================================================
    // 3. PARTY REPORT BY ITEMS
    // ============================================================
    public function partyReportByItems(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        if (!Schema::hasTable('sales') || !Schema::hasTable('sale_items')) {
            return response()->json(['success' => true, 'rows' => []]);
        }

        $itemId   = $request->input('item', $request->input('item_id'));
        $category = $request->input('category');
        $search   = trim((string) $request->input('search', ''));

        $saleRows = DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->join('parties as p', 'p.id', '=', 's.party_id')
            ->leftJoin('items as it', 'it.id', '=', 'si.item_id')
            ->whereBetween('s.invoice_date', [$from, $to])
            ->select(
                'p.id as party_id',
                'p.name as party_name',
                'si.item_id as item_id',
                DB::raw("COALESCE(NULLIF(si.item_name, ''), it.name, '-') as item_name"),
                DB::raw("COALESCE(NULLIF(si.item_category, ''), 'Uncategorized') as category_name"),
                DB::raw('SUM(si.quantity) as sale_qty'),
                DB::raw('SUM(si.amount) as sale_amount')
            )
            ->when($itemId, fn ($query) => $query->where('si.item_id', $itemId))
            ->when($category, fn ($query) => $query->where('it.category_id', $category))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $like = '%' . $search . '%';
                    $sub->where('p.name', 'like', $like)
                        ->orWhere('si.item_name', 'like', $like)
                        ->orWhere('si.item_category', 'like', $like)
                        ->orWhere('it.name', 'like', $like);
                });
            })
            ->groupBy('p.id', 'p.name', 'si.item_id', 'si.item_name', 'si.item_category', 'it.name')
            ->get();

        $purchaseRows = collect();
        if (Schema::hasTable('purchase_items')) {
            $purchaseRows = DB::table('purchase_items as pi')
                ->join('purchases as pu', 'pu.id', '=', 'pi.purchase_id')
                ->join('parties as p', 'p.id', '=', 'pu.party_id')
                ->leftJoin('items as it', 'it.id', '=', 'pi.item_id')
                ->whereBetween('pu.bill_date', [$from, $to])
                ->select(
                    'p.id as party_id',
                    'p.name as party_name',
                    'pi.item_id as item_id',
                    DB::raw("COALESCE(NULLIF(pi.item_name, ''), it.name, '-') as item_name"),
                    DB::raw("COALESCE(NULLIF(pi.item_category, ''), 'Uncategorized') as category_name"),
                    DB::raw('SUM(pi.quantity) as purchase_qty'),
                    DB::raw('SUM(pi.amount) as purchase_amount')
                )
                ->when($itemId, fn ($query) => $query->where('pi.item_id', $itemId))
                ->when($category, fn ($query) => $query->where('it.category_id', $category))
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($sub) use ($search) {
                        $like = '%' . $search . '%';
                        $sub->where('p.name', 'like', $like)
                        ->orWhere('pi.item_name', 'like', $like)
                        ->orWhere('pi.item_category', 'like', $like)
                        ->orWhere('it.name', 'like', $like);
                });
            })
                ->groupBy('p.id', 'p.name', 'pi.item_id', 'pi.item_name', 'pi.item_category', 'it.name')
                ->get();
        }

        $saleMap = $saleRows->keyBy(fn ($row) => implode('|', [
            $row->party_id ?? 0,
            $row->category_name ?? '',
            $row->item_id ?? 0,
        ]));

        $purchaseMap = $purchaseRows->keyBy(fn ($row) => implode('|', [
            $row->party_id ?? 0,
            $row->category_name ?? '',
            $row->item_id ?? 0,
        ]));

        $rowKeys = $saleMap->keys()->merge($purchaseMap->keys())->unique()->values();
        $rows = $rowKeys->map(function ($key) use ($saleMap, $purchaseMap) {
            $s = $saleMap->get($key);
            $p = $purchaseMap->get($key);
            $source = $s ?: $p;

            return [
                'party_id'        => $source->party_id ?? null,
                'party_name'      => $source->party_name ?? '-',
                'category_name'   => $source->category_name ?? 'Uncategorized',
                'item_name'       => $source->item_name ?? '-',
                'sale_qty'        => $s ? (int) $s->sale_qty : 0,
                'sale_amount'     => $this->fmt($s ? $s->sale_amount : 0),
                'purchase_qty'    => $p ? (int) $p->purchase_qty : 0,
                'purchase_amount' => $this->fmt($p ? $p->purchase_amount : 0),
            ];
        })->sortBy(function ($row) {
            return sprintf(
                '%s|%s|%s',
                strtolower((string) ($row['party_name'] ?? '')),
                strtolower((string) ($row['category_name'] ?? '')),
                strtolower((string) ($row['item_name'] ?? ''))
            );
        })->values();

        return response()->json(['success' => true, 'rows' => $rows]);
    }

    // ============================================================
    // 4. SALE PURCHASE BY PARTY
    // ============================================================
    public function salePurchaseByParty(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $parties = Party::all();

        $saleMap = collect();
        if (Schema::hasTable('sales')) {
            $saleMap = DB::table('sales')
                ->whereBetween('invoice_date', [$from, $to])
                ->select('party_id', DB::raw('SUM(total_amount) as sale_amount'))
                ->groupBy('party_id')
                ->get()->keyBy('party_id');
        }

        $purchaseMap = collect();
        if (Schema::hasTable('purchases')) {
            $purchaseMap = DB::table('purchases')
                ->whereBetween('bill_date', [$from, $to])
                ->select('party_id', DB::raw('SUM(total_amount) as purchase_amount'))
                ->groupBy('party_id')
                ->get()->keyBy('party_id');
        }

        $rows = $parties->map(function ($p) use ($saleMap, $purchaseMap) {
            $s  = $saleMap->get($p->id);
            $pu = $purchaseMap->get($p->id);
            return [
                'party_id'        => $p->id,
                'party_name'      => $p->name,
                'sale_amount'     => $this->fmt($s  ? $s->sale_amount     : 0),
                'purchase_amount' => $this->fmt($pu ? $pu->purchase_amount : 0),
            ];
        })->filter(fn($r) => $r['sale_amount'] > 0 || $r['purchase_amount'] > 0)->values();

        return response()->json(['success' => true, 'rows' => $rows]);
    }

    // ============================================================
    // 5. SALE PURCHASE BY PARTY GROUP
    // ============================================================
    public function salePurchaseByPartyGroup(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $parties = Party::all();

        $saleMap = collect();
        if (Schema::hasTable('sales')) {
            $saleMap = DB::table('sales')
                ->whereBetween('invoice_date', [$from, $to])
                ->select('party_id', DB::raw('SUM(total_amount) as sale_amount'))
                ->groupBy('party_id')
                ->get()->keyBy('party_id');
        }

        $purchaseMap = collect();
        if (Schema::hasTable('purchases')) {
            $purchaseMap = DB::table('purchases')
                ->whereBetween('bill_date', [$from, $to])
                ->select('party_id', DB::raw('SUM(total_amount) as purchase_amount'))
                ->groupBy('party_id')
                ->get()->keyBy('party_id');
        }

        $groups = [];
        foreach ($parties as $p) {
            $group = $p->party_group ?: 'Ungrouped';
            if (!isset($groups[$group])) {
                $groups[$group] = ['sale_amount' => 0, 'purchase_amount' => 0];
            }
            $s  = $saleMap->get($p->id);
            $pu = $purchaseMap->get($p->id);
            $groups[$group]['sale_amount']     += $s  ? (float) $s->sale_amount     : 0;
            $groups[$group]['purchase_amount'] += $pu ? (float) $pu->purchase_amount : 0;
        }

        $rows = collect($groups)->map(function ($vals, $group) {
            return [
                'party_group'     => $group,
                'sale_amount'     => $this->fmt($vals['sale_amount']),
                'purchase_amount' => $this->fmt($vals['purchase_amount']),
            ];
        })->values();

        return response()->json(['success' => true, 'rows' => $rows]);
    }

    // ============================================================
    // 6. ITEM REPORT BY PARTY
    // ============================================================
    public function itemReportByParty(Request $request)
    {
        $data = $this->itemReportByPartyService->report([
            'from' => $request->input('from', now()->startOfMonth()->toDateString()),
            'to' => $request->input('to', now()->endOfMonth()->toDateString()),
            'party_id' => $request->filled('party_id') ? (int) $request->input('party_id') : null,
        ]);

        return response()->json([
            'success' => true,
            'rows' => $data['rows']->values()->all(),
            'totals' => $data['totals'],
        ]);
    }

    // ============================================================
    // 6. SALE REPORT
    // ============================================================
    public function saleReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $firms = Schema::hasTable('parties')
            ? DB::table('parties')->select('id', 'name')->whereNotNull('name')->orderBy('name')->get()
            : collect();

        $stores = Schema::hasTable('warehouses')
            ? DB::table('warehouses')
                ->select('id', 'name')
                ->when(Schema::hasColumn('warehouses', 'is_active'), fn ($query) => $query->where('is_active', true))
                ->whereNotNull('name')
                ->orderBy('name')
                ->get()
            : collect();

        if (!Schema::hasTable('sales')) {
            return response()->json([
                'success' => true, 'transactions' => [],
                'total_amount' => 0, 'total_received' => 0,
                'total_balance' => 0, 'growth_pct' => 0,
                'firms' => $firms,
                'stores' => $stores,
            ]);
        }

        $hasSaleDetails = Schema::hasTable('sales_details')
            && Schema::hasColumn('sales_details', 'sale_id')
            && Schema::hasColumn('sales_details', 'warehouse_id');
        $hasWarehouses = $hasSaleDetails && Schema::hasTable('warehouses');

        $query = DB::table('sales as s')
            ->leftJoin('parties as p', 'p.id', '=', 's.party_id')
            ->when($hasSaleDetails, fn ($query) => $query->leftJoin('sales_details as sd', 'sd.sale_id', '=', 's.id'))
            ->when($hasWarehouses, fn ($query) => $query->leftJoin('warehouses as w', 'w.id', '=', 'sd.warehouse_id'))
            ->whereBetween('s.invoice_date', [$from, $to]);

        $query->select(
                's.id',
                's.bill_number',
                's.invoice_date',
                's.total_amount',
                's.grand_total',
                DB::raw("COALESCE(s.payment_type, 'Cash') as payment_type"),
                DB::raw('COALESCE(s.received_amount, 0) as received_paid'),
                DB::raw('COALESCE(s.balance, 0) as balance_due'),
                'p.name as party_name',
                'p.phone as party_phone',
                's.status',
                's.description',
                's.order_date'
            );

        if ($hasSaleDetails) {
            $query->addSelect('sd.warehouse_id');
        } else {
            $query->addSelect(DB::raw('NULL as warehouse_id'));
        }

        if ($hasWarehouses) {
            $query->addSelect('w.name as warehouse_name');
        } else {
            $query->addSelect(DB::raw('NULL as warehouse_name'));
        }

        $query
            ->orderByDesc('s.invoice_date')
            ->orderByDesc('s.id');

        if ($request->filled('party')) $query->where('s.party_id', $request->party);
        if ($request->filled('type'))  $query->where('s.type', $request->type);
        if ($request->filled('warehouse') && $hasSaleDetails) {
            $query->where('sd.warehouse_id', $request->warehouse);
        }

        $rows = $query->get();

        $totalAmount   = $rows->sum('total_amount');
        $totalReceived = $rows->sum('received_paid');
        $totalBalance  = $rows->sum('balance_due');

        $fromDate = new \DateTime($from);
        $toDate   = new \DateTime($to);
        $diffDays = $fromDate->diff($toDate)->days + 1;
        $prevFrom = (clone $fromDate)->modify("-{$diffDays} days")->format('Y-m-d');
        $prevTo   = (clone $fromDate)->modify('-1 day')->format('Y-m-d');

        $prevQuery = DB::table('sales as s')
            ->when($hasSaleDetails, fn ($query) => $query->leftJoin('sales_details as sd', 'sd.sale_id', '=', 's.id'))
            ->whereBetween('s.invoice_date', [$prevFrom, $prevTo]);

        if ($request->filled('party')) $prevQuery->where('s.party_id', $request->party);
        if ($request->filled('type')) $prevQuery->where('s.type', $request->type);
        if ($request->filled('warehouse') && $hasSaleDetails) {
            $prevQuery->where('sd.warehouse_id', $request->warehouse);
        }

        $prevTotal = $prevQuery->sum('s.total_amount');

        $growthPct = 0;
        if ($prevTotal > 0) {
            $growthPct = round((($totalAmount - $prevTotal) / $prevTotal) * 100, 1);
        } elseif ($totalAmount > 0) {
            $growthPct = 100;
        }

        return response()->json([
            'success'        => true,
            'transactions'   => $rows->toArray(),
            'total_amount'   => $this->fmt($totalAmount),
            'total_received' => $this->fmt($totalReceived),
            'total_balance'  => $this->fmt($totalBalance),
            'growth_pct'     => $growthPct,
            'period'         => ['from' => $from, 'to' => $to],
            'firms'          => $firms,
            'stores'         => $stores,
        ]);
    }

    // ============================================================
    // 7. PURCHASE REPORT
    // ============================================================
    public function purchaseReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $firms = Schema::hasTable('parties')
            ? DB::table('parties')->select('id', 'name')->whereNotNull('name')->orderBy('name')->get()
            : collect();

        if (!Schema::hasTable('purchases')) {
            return response()->json([
                'success'       => true,
                'transactions'  => [],
                'total_amount'  => 0,
                'total_paid'    => 0,
                'total_balance' => 0,
                'period'        => ['from' => $from, 'to' => $to],
                'firms'         => $firms,
            ]);
        }

        $hasTypeColumn = Schema::hasColumn('purchases', 'type');
        $hasStatusColumn = Schema::hasColumn('purchases', 'status');
        $hasPurchasePayments = Schema::hasTable('purchase_payments')
            && Schema::hasColumn('purchase_payments', 'purchase_id')
            && Schema::hasColumn('purchase_payments', 'payment_type');
        $paymentTypeSelect = $hasPurchasePayments
            ? "COALESCE(pp.payment_type, 'Cash') as payment_type"
            : "'Cash' as payment_type";
        $statusSelect = $hasStatusColumn ? 'pu.status' : DB::raw('NULL as status');

        $query = DB::table('purchases as pu')
            ->leftJoin('parties as p', 'p.id', '=', 'pu.party_id')
            ->when($hasPurchasePayments, function ($query) {
                $query->leftJoinSub(
                    DB::table('purchase_payments')
                        ->select('purchase_id', DB::raw('MIN(payment_type) as payment_type'))
                        ->groupBy('purchase_id'),
                    'pp',
                    'pp.purchase_id',
                    '=',
                    'pu.id'
                );
            })
            ->whereBetween('pu.bill_date', [$from, $to])
            ->when($hasTypeColumn, fn ($query) => $query->where('pu.type', 'purchase_bill'))
            ->select(
                'pu.id',
                'pu.bill_number',
                'pu.bill_date',
                'pu.total_amount',
                'pu.grand_total',
                DB::raw($paymentTypeSelect),
                DB::raw('COALESCE(pu.paid_amount, 0) as paid_amount'),
                DB::raw('COALESCE(pu.balance, 0) as balance_due'),
                DB::raw('COALESCE(pu.party_name, p.name) as party_name'),
                DB::raw('COALESCE(pu.phone, p.phone) as party_phone'),
                'pu.description',
                $statusSelect
            )
            ->orderByDesc('pu.bill_date')
            ->orderByDesc('pu.id');

        if ($request->filled('party')) $query->where('pu.party_id', $request->party);

        $rows = $query->get();

        return response()->json([
            'success'       => true,
            'transactions'  => $rows->toArray(),
            'total_amount'  => $this->fmt($rows->sum(fn ($row) => (float) ($row->grand_total ?: $row->total_amount))),
            'total_paid'    => $this->fmt($rows->sum('paid_amount')),
            'total_balance' => $this->fmt($rows->sum('balance_due')),
            'period'        => ['from' => $from, 'to' => $to],
            'firms'         => $firms,
        ]);
    }

    // ============================================================
    // 8. ALL TRANSACTIONS
    // ============================================================

        // if (Schema::hasTable('purchases')) {
        //     $rows = $rows->merge(
        //         DB::table('purchases as pu')
        //             ->leftJoin('parties as p', 'p.id', '=', 'pu.party_id')
        //             ->whereBetween('pu.bill_date', [$from, $to])
        //             ->select(
        //                 'pu.id',
        //                 DB::raw("'Purchase' as type"),
        //                 'pu.bill_number as reference',
        //                 'pu.bill_date as date',
        //                 'p.name as party_name',
        //                 'pu.total_amount as amount',
        //                 DB::raw("COALESCE(pu.payment_type, 'Cash') as payment_type")
        //             )->get()
        //     );
        // }
    public function allTransactions(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $rows = collect();

        if (Schema::hasTable('sales')) {
            $rows = $rows->merge(
                DB::table('sales as s')
                    ->leftJoin('parties as p', 'p.id', '=', 's.party_id')
                    ->whereBetween('s.invoice_date', [$from, $to])
                    ->select(
                        's.id',
                        DB::raw("'Sale' as type"),
                        's.bill_number as reference',
                        's.invoice_date as date',
                        'p.name as party_name',
                        's.total_amount as amount',
                        DB::raw("COALESCE(s.payment_type, 'Cash') as payment_type")
                    )->get()
            );
        }



        // return $rows;
        if (Schema::hasTable('payment_ins')) {
            $rows = $rows->merge(
                DB::table('payment_ins as pi')
                    ->leftJoin('parties as p', 'p.id', '=', 'pi.party_id')
                    ->whereBetween('pi.date', [$from, $to])
                    ->select(
                        'pi.id',
                        DB::raw("'Payment In' as type"),
                        'pi.reference_no as reference',
                        'pi.date as date',
                        'p.name as party_name',
                        'pi.amount as amount',
                        'pi.payment_type'
                    )->get()
            );
        }

        if (Schema::hasTable('payment_outs')) {
            $rows = $rows->merge(
                DB::table('payment_outs as po')
                    ->leftJoin('parties as p', 'p.id', '=', 'po.party_id')
                    ->whereBetween('po.date', [$from, $to])
                    ->select(
                        'po.id',
                        DB::raw("'Payment Out' as type"),
                        'po.reference_no as reference',
                        'po.date as date',
                        'p.name as party_name',
                        'po.amount as amount',
                        'po.payment_type'
                    )->get()
            );
        }

        // expenses: expense_date, total_amount, payment_type
        if (Schema::hasTable('expenses')) {
            $rows = $rows->merge(
                DB::table('expenses as e')
                    ->whereBetween('e.expense_date', [$from, $to])
                    ->select(
                        'e.id',
                        DB::raw("'Expense' as type"),
                        DB::raw("COALESCE(e.expense_no, '') as reference"),
                        'e.expense_date as date',
                        DB::raw("COALESCE(e.party, '') as party_name"),
                        'e.total_amount as amount',
                        DB::raw("COALESCE(e.payment_type, 'Cash') as payment_type")
                    )->get()
            );
        }

        $rows = $rows->sortByDesc('date')->values();

        return response()->json([
            'success'      => true,
            'transactions' => $rows->toArray(),
            'total_amount' => $this->fmt($rows->sum('amount')),
            'period'       => ['from' => $from, 'to' => $to],
        ]);
    }

    public function dayBook(Request $request){
        //    [$from, $to] = $this->dateRange($request);

        $date=Carbon::today();
        $rows = collect();

        if (Schema::hasTable('sales')) {
            $rows = $rows->merge(
                DB::table('sales as s')
                    ->leftJoin('parties as p', 'p.id', '=', 's.party_id')
                    ->where('s.invoice_date', $date)
                    ->select(
                        's.id',
                        DB::raw("'Sale' as type"),
                        's.bill_number as reference',
                        's.invoice_date as date',
                        'p.name as party_name',
                        's.total_amount as amount',
                        DB::raw("COALESCE(s.payment_type, 'Cash') as payment_type")
                    )->get()
            );
        }



        // return $rows;
        if (Schema::hasTable('payment_ins')) {
            $rows = $rows->merge(
                DB::table('payment_ins as pi')
                    ->leftJoin('parties as p', 'p.id', '=', 'pi.party_id')
                    ->where('pi.date', $date)
                    ->select(
                        'pi.id',
                        DB::raw("'Payment In' as type"),
                        'pi.reference_no as reference',
                        'pi.date as date',
                        'p.name as party_name',
                        'pi.amount as amount',
                        'pi.payment_type'
                    )->get()
            );
        }

        if (Schema::hasTable('payment_outs')) {
            $rows = $rows->merge(
                DB::table('payment_outs as po')
                    ->leftJoin('parties as p', 'p.id', '=', 'po.party_id')
                    ->where('po.date', $date)
                    ->select(
                        'po.id',
                        DB::raw("'Payment Out' as type"),
                        'po.reference_no as reference',
                        'po.date as date',
                        'p.name as party_name',
                        'po.amount as amount',
                        'po.payment_type'
                    )->get()
            );
        }

        // expenses: expense_date, total_amount, payment_type
        if (Schema::hasTable('expenses')) {
            $rows = $rows->merge(
                DB::table('expenses as e')
                    ->where('e.expense_date', $date)
                    ->select(
                        'e.id',
                        DB::raw("'Expense' as type"),
                        DB::raw("COALESCE(e.expense_no, '') as reference"),
                        'e.expense_date as date',
                        DB::raw("COALESCE(e.party, '') as party_name"),
                        'e.total_amount as amount',
                        DB::raw("COALESCE(e.payment_type, 'Cash') as payment_type")
                    )->get()
            );
        }

        $rows = $rows->sortByDesc('date')->values();

        return response()->json([
            'success'      => true,
            'transactions' => $rows->toArray(),
            'total_amount' => $this->fmt($rows->sum('amount')),
            'period'       => ['from' => $date],
        ]);

    }
    // ============================================================
    // 9. CASH FLOW
    // ─ sales        → payment_type column EXISTS ✓
    // ─ purchases    → payment_type column EXISTS ✓
    // ─ payment_ins  → payment_type column EXISTS ✓
    // ─ payment_outs → payment_type column EXISTS ✓
    // ─ expenses     → expense_date / total_amount / payment_type ✓
    // ============================================================
    public function cashFlow(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $cashIn  = 0;
        $cashOut = 0;
        $rows    = collect();

        // Sales cash-in
        if (Schema::hasTable('sales')) {
            $sale = DB::table('sales')
                ->whereBetween('invoice_date', [$from, $to])
                ->where('payment_type', 'Cash')
                ->select(
                    DB::raw("'Sale' as category"),
                    DB::raw('SUM(total_amount) as amount'),
                    DB::raw("'in' as flow")
                )
                ->groupBy(DB::raw("'Sale'"))
                ->first();
            if ($sale && $sale->amount) { $cashIn += $sale->amount; $rows->push($sale); }
        }

        // Payment-ins cash-in
        if (Schema::hasTable('payment_ins')) {
            $payIn = DB::table('payment_ins')
                ->whereBetween('date', [$from, $to])
                ->where('payment_type', 'Cash')
                ->select(
                    DB::raw("'Payment In' as category"),
                    DB::raw('SUM(amount) as amount'),
                    DB::raw("'in' as flow")
                )
                ->groupBy(DB::raw("'Payment In'"))
                ->first();
            if ($payIn && $payIn->amount) { $cashIn += $payIn->amount; $rows->push($payIn); }
        }

        // Purchases cash-out
        // if (Schema::hasTable('purchases')) {
        //     $purchase = DB::table('purchases')
        //         ->whereBetween('bill_date', [$from, $to])
        //         ->where('payment_type', 'Cash')
        //         ->select(
        //             DB::raw("'Purchase' as category"),
        //             DB::raw('SUM(total_amount) as amount'),
        //             DB::raw("'out' as flow")
        //         )
        //         ->groupBy(DB::raw("'Purchase'"))
        //         ->first();
        //     if ($purchase && $purchase->amount) { $cashOut += $purchase->amount; $rows->push($purchase); }
        // }

        // Payment-outs cash-out
        if (Schema::hasTable('payment_outs')) {
            $payOut = DB::table('payment_outs')
                ->whereBetween('date', [$from, $to])
                ->where('payment_type', 'Cash')
                ->select(
                    DB::raw("'Payment Out' as category"),
                    DB::raw('SUM(amount) as amount'),
                    DB::raw("'out' as flow")
                )
                ->groupBy(DB::raw("'Payment Out'"))
                ->first();
            if ($payOut && $payOut->amount) { $cashOut += $payOut->amount; $rows->push($payOut); }
        }

        // Expenses cash-out — uses expense_date and total_amount (verified)
        if (Schema::hasTable('expenses')) {
            $expense = DB::table('expenses')
                ->whereBetween('expense_date', [$from, $to])
                ->where('payment_type', 'Cash')
                ->select(
                    DB::raw("'Expense' as category"),
                    DB::raw('SUM(total_amount) as amount'),
                    DB::raw("'out' as flow")
                )
                ->groupBy(DB::raw("'Expense'"))
                ->first();
            if ($expense && $expense->amount) { $cashOut += $expense->amount; $rows->push($expense); }
        }

        return response()->json([
            'success'   => true,
            'rows'      => $rows->toArray(),
            'total_in'  => $this->fmt($cashIn),
            'total_out' => $this->fmt($cashOut),
            'net_flow'  => $this->fmt($cashIn - $cashOut),
            'period'    => ['from' => $from, 'to' => $to],
        ]);
    }

    public function cashFlowExport(Request $request)
    {
        return $this->cashFlow($request);
    }

    // ============================================================
    // 10. PROFIT AND LOSS
    // ============================================================
    public function profitAndLoss(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $totalSales           = 0;
        $totalPurchases       = 0;
        $totalExpenses        = 0;
        $totalSaleReturns     = 0;
        $totalPurchaseReturns = 0;

        if (Schema::hasTable('sales')) {
            $totalSales = DB::table('sales')
                ->whereBetween('invoice_date', [$from, $to])
                ->sum('total_amount');
        }

        if (Schema::hasTable('purchases')) {
            $totalPurchases = DB::table('purchases')
                ->whereBetween('bill_date', [$from, $to])
                ->sum('total_amount');
        }

        // expenses: expense_date, total_amount
        if (Schema::hasTable('expenses')) {
            $totalExpenses = DB::table('expenses')
                ->whereBetween('expense_date', [$from, $to])
                ->sum('total_amount');
        }

        if (Schema::hasTable('sale_returns')) {
            $totalSaleReturns = DB::table('sale_returns')
                ->whereBetween('date', [$from, $to])
                ->sum('total_amount');
        }

        if (Schema::hasTable('purchase_returns')) {
            $totalPurchaseReturns = DB::table('purchase_returns')
                ->whereBetween('date', [$from, $to])
                ->sum('total_amount');
        }

        $grossProfit = $totalSales - $totalSaleReturns - $totalPurchases + $totalPurchaseReturns;
        $netProfit   = $grossProfit - $totalExpenses;

        return response()->json([
            'success'                => true,
            'total_sales'            => $this->fmt($totalSales),
            'total_purchases'        => $this->fmt($totalPurchases),
            'total_expenses'         => $this->fmt($totalExpenses),
            'total_sale_returns'     => $this->fmt($totalSaleReturns),
            'total_purchase_returns' => $this->fmt($totalPurchaseReturns),
            'gross_profit'           => $this->fmt($grossProfit),
            'net_profit'             => $this->fmt($netProfit),
            'period'                 => ['from' => $from, 'to' => $to],
        ]);
    }

    public function profitAndLossExport(Request $request)
    {
        return $this->profitAndLoss($request);
    }

    // ============================================================
    // 10A. BALANCE SHEET
    // ============================================================
    public function balanceSheet(Request $request)
    {
        $asOn = $request->filled('to') ? $request->input('to') : now()->toDateString();
        $from = $request->filled('from') ? $request->input('from') : now()->startOfYear()->toDateString();

        $partyBalances = $this->partyBalancesAsOn($asOn);
        $sundryDebtors = $partyBalances->where('balance', '>', 0)->sum('balance');
        $sundryCreditors = abs($partyBalances->where('balance', '<', 0)->sum('balance'));

        $bankBalances = $this->bankBalancesAsOn($asOn);
        $cashAccounts = $bankBalances->where('is_cash', true)->sum('balance');
        $bankAccounts = $bankBalances->where('is_cash', false)->sum('balance');

        $inputDuties = $this->sumColumnAsOn('purchases', 'tax_amount', 'bill_date', $asOn);
        $outwardDuties = $this->sumColumnAsOn('sales', 'tax_amount', 'invoice_date', $asOn);
        $inventoryValue = $this->inventoryValueAsOn($asOn);
        $fixedAssets = $this->fixedAssetsValueAsOn($asOn);
        $longTermLiabilities = $this->sumColumnAsOn('loan_accounts', 'opening_balance', 'as_of_date', $asOn);
        $netIncome = $this->netIncomeAsOn($from, $asOn);

        $currentAssets = $sundryDebtors + $inputDuties + $bankAccounts + $cashAccounts + $inventoryValue;
        $assetsTotal = $fixedAssets + $currentAssets;

        $currentLiabilities = $sundryCreditors + $outwardDuties;
        $reservesSurplus = $netIncome;
        $ownerEquity = $assetsTotal - $longTermLiabilities - $currentLiabilities - $reservesSurplus;
        $capitalAccount = $ownerEquity + $reservesSurplus;
        $equitiesLiabilitiesTotal = $capitalAccount + $longTermLiabilities + $currentLiabilities;
        $mismatch = round($assetsTotal - $equitiesLiabilitiesTotal, 2);

        return response()->json([
            'success' => true,
            'period' => [
                'from' => $from,
                'to' => $asOn,
                'as_on_label' => Carbon::parse($asOn)->format('M d, Y'),
            ],
            'tree' => [
                'equities_liabilities' => [
                    $this->balanceNode('capital_account', 'Capital Account', $capitalAccount, [
                        $this->balanceNode('owners_equity', "Owner's Equity", $ownerEquity),
                        $this->balanceNode('reserves_surplus', 'Reserves & Surplus', $reservesSurplus, [
                            $this->balanceNode('net_income', 'Net Income (Profit)', $netIncome),
                            $this->balanceNode('retained_earnings', 'Retained Earnings', 0),
                            $this->balanceNode('revaluation_reserve', 'Revaluation Reserve', 0),
                        ]),
                    ]),
                    $this->balanceNode('long_term_liabilities', 'Long-term Liabilities', $longTermLiabilities),
                    $this->balanceNode('current_liabilities', 'Current Liabilities', $currentLiabilities, [
                        $this->balanceNode('sundry_creditors', 'Sundry Creditors', $sundryCreditors, $this->partyNodes($partyBalances, false)),
                        $this->balanceNode('outward_duties_taxes', 'Outward Duties & Taxes', $outwardDuties),
                        $this->balanceNode('other_current_liabilities', 'Other Current Liabilities', 0),
                    ]),
                    $this->balanceNode('other_liabilities', 'Other Liabilities', 0),
                ],
                'assets' => [
                    $this->balanceNode('fixed_assets', 'Fixed Assets', $fixedAssets),
                    $this->balanceNode('non_current_assets', 'Non Current Assets', 0),
                    $this->balanceNode('current_assets', 'Current Assets', $currentAssets, [
                        $this->balanceNode('sundry_debtors', 'Sundry Debtors', $sundryDebtors, $this->partyNodes($partyBalances, true)),
                        $this->balanceNode('input_duties_taxes', 'Input Duties & Taxes', $inputDuties),
                        $this->balanceNode('bank_accounts', 'Bank Accounts', $bankAccounts, $this->bankNodes($bankBalances, false)),
                        $this->balanceNode('cash_accounts', 'Cash Accounts', $cashAccounts, $this->bankNodes($bankBalances, true)),
                        $this->balanceNode('other_current_assets', 'Other Current Assets', $inventoryValue, [
                            $this->balanceNode('inventory_stock', 'Inventory / Stock in Hand', $inventoryValue),
                        ]),
                    ]),
                    $this->balanceNode('other_assets', 'Other Assets', 0),
                ],
            ],
            'totals' => [
                'assets' => $this->fmt($assetsTotal),
                'equities_liabilities' => $this->fmt($equitiesLiabilitiesTotal),
                'mismatch' => $this->fmt($mismatch),
                'is_balanced' => abs($mismatch) < 0.01,
            ],
        ]);
    }

    public function balanceSheetExport(Request $request)
    {
        return $this->balanceSheet($request);
    }

    private function balanceNode(string $id, string $label, $amount, array $children = []): array
    {
        return [
            'id' => $id,
            'label' => $label,
            'amount' => $this->fmt($amount),
            'children' => $children,
        ];
    }

    private function partyBalancesAsOn(string $asOn)
    {
        if (!Schema::hasTable('parties')) {
            return collect();
        }

        if (Schema::hasTable('transactions')
            && Schema::hasColumn('transactions', 'party_id')
            && Schema::hasColumn('transactions', 'date')) {
            $rows = DB::table('parties as p')
                ->leftJoin('transactions as t', function ($join) use ($asOn) {
                    $join->on('t.party_id', '=', 'p.id')
                        ->whereDate('t.date', '<=', $asOn);
                })
                ->select(
                    'p.id',
                    'p.name',
                    'p.opening_balance',
                    'p.transaction_type',
                    'p.current_balance',
                    DB::raw('COALESCE(SUM(t.debit), 0) as debit_total'),
                    DB::raw('COALESCE(SUM(t.credit), 0) as credit_total')
                )
                ->groupBy('p.id', 'p.name', 'p.opening_balance', 'p.transaction_type', 'p.current_balance')
                ->get();

            return $rows->map(function ($row) {
                $opening = (float) ($row->opening_balance ?? 0);
                if (strtolower((string) ($row->transaction_type ?? '')) === 'pay') {
                    $opening *= -1;
                }

                $balance = $opening + (float) $row->debit_total - (float) $row->credit_total;

                return (object) [
                    'id' => $row->id,
                    'name' => $row->name,
                    'balance' => $this->fmt($balance),
                ];
            });
        }

        return DB::table('parties')
            ->select('id', 'name', DB::raw('COALESCE(current_balance, opening_balance, 0) as balance'))
            ->get()
            ->map(fn ($row) => (object) [
                'id' => $row->id,
                'name' => $row->name,
                'balance' => $this->fmt($row->balance),
            ]);
    }

    private function partyNodes($partyBalances, bool $debtors): array
    {
        return $partyBalances
            ->filter(fn ($party) => $debtors ? $party->balance > 0 : $party->balance < 0)
            ->sortByDesc(fn ($party) => abs($party->balance))
            ->take(25)
            ->map(fn ($party) => $this->balanceNode(
                'party_' . $party->id,
                $party->name ?: 'Unnamed Party',
                abs($party->balance)
            ))
            ->values()
            ->all();
    }

    private function bankBalancesAsOn(string $asOn)
    {
        if (!Schema::hasTable('bank_accounts')) {
            return collect();
        }

        $accounts = DB::table('bank_accounts')
            ->select('id', 'display_name', 'bank_name', 'type', 'opening_balance', 'as_of_date')
            ->get();

        return $accounts->map(function ($account) use ($asOn) {
            $opening = Carbon::parse($account->as_of_date ?? '1900-01-01')->toDateString() <= $asOn
                ? (float) ($account->opening_balance ?? 0)
                : 0;

            $in = 0;
            $out = 0;
            if (Schema::hasTable('bank_transactions')) {
                $in = DB::table('bank_transactions')
                    ->where('to_bank_account_id', $account->id)
                    ->whereDate('transaction_date', '<=', $asOn)
                    ->sum('amount');

                $out = DB::table('bank_transactions')
                    ->where('from_bank_account_id', $account->id)
                    ->whereDate('transaction_date', '<=', $asOn)
                    ->sum('amount');
            }

            return (object) [
                'id' => $account->id,
                'name' => $account->display_name ?: ($account->bank_name ?: 'Bank Account'),
                'is_cash' => strtolower((string) $account->type) === 'cash',
                'balance' => $this->fmt($opening + $in - $out),
            ];
        });
    }

    private function bankNodes($bankBalances, bool $cash): array
    {
        return $bankBalances
            ->where('is_cash', $cash)
            ->filter(fn ($account) => abs($account->balance) > 0.009)
            ->map(fn ($account) => $this->balanceNode(
                'bank_' . $account->id,
                $account->name,
                $account->balance
            ))
            ->values()
            ->all();
    }

    private function sumColumnAsOn(string $table, string $amountColumn, string $dateColumn, string $asOn): float
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $amountColumn)) {
            return 0;
        }

        $query = DB::table($table);
        if (Schema::hasColumn($table, $dateColumn)) {
            $query->whereDate($dateColumn, '<=', $asOn);
        }

        return $this->fmt($query->sum($amountColumn));
    }

    private function inventoryValueAsOn(string $asOn): float
    {
        if (!Schema::hasTable('items')) {
            return 0;
        }

        $value = DB::table('items')
            ->when(Schema::hasColumn('items', 'type'), function ($query) {
                $query->whereRaw("LOWER(COALESCE(type, '')) NOT LIKE ?", ['%fixed%']);
            })
            ->selectRaw('SUM(COALESCE(opening_qty, 0) * COALESCE(purchase_price, 0)) as value')
            ->value('value');

        return $this->fmt($value);
    }

    private function fixedAssetsValueAsOn(string $asOn): float
    {
        if (!Schema::hasTable('items') || !Schema::hasColumn('items', 'type')) {
            return 0;
        }

        $value = DB::table('items')
            ->whereRaw("LOWER(COALESCE(type, '')) LIKE ?", ['%fixed%'])
            ->selectRaw('SUM(COALESCE(opening_qty, 0) * COALESCE(purchase_price, 0)) as value')
            ->value('value');

        return $this->fmt($value);
    }

    private function netIncomeAsOn(string $from, string $asOn): float
    {
        $sales = $this->sumColumnBetween('sales', 'total_amount', 'invoice_date', $from, $asOn);
        $purchases = $this->sumColumnBetween('purchases', 'total_amount', 'bill_date', $from, $asOn);
        $expenses = $this->sumColumnBetween('expenses', 'total_amount', 'expense_date', $from, $asOn);
        $saleReturns = $this->sumColumnBetween('sale_returns', 'total_amount', 'date', $from, $asOn);
        $purchaseReturns = $this->sumColumnBetween('purchase_returns', 'total_amount', 'date', $from, $asOn);

        return $this->fmt($sales - $saleReturns - $purchases + $purchaseReturns - $expenses);
    }

    private function sumColumnBetween(string $table, string $amountColumn, string $dateColumn, string $from, string $to): float
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $amountColumn)) {
            return 0;
        }

        $query = DB::table($table);
        if (Schema::hasColumn($table, $dateColumn)) {
            $query->whereBetween($dateColumn, [$from, $to]);
        }

        return $this->fmt($query->sum($amountColumn));
    }

    // ============================================================
    // 11. BILL WISE PROFIT
    // ============================================================
    public function billWiseProfit(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        if (!Schema::hasTable('sales') || !Schema::hasTable('sale_items')) {
            return response()->json(['success' => true, 'rows' => [], 'total_profit' => 0]);
        }

        $rows = DB::table('sales as s')
            ->leftJoin('parties as p', 'p.id', '=', 's.party_id')
            ->leftJoin('sale_items as si', 'si.sale_id', '=', 's.id')
            ->leftJoin('items as i', 'i.id', '=', 'si.item_id')
            ->whereBetween('s.invoice_date', [$from, $to])
            ->select(
                's.id',
                's.bill_number',
                's.invoice_date',
                'p.name as party_name',
                DB::raw('SUM(si.quantity * si.price) as sale_amount'),
                DB::raw('SUM(si.quantity * COALESCE(i.purchase_price, 0)) as cost_amount'),
                DB::raw('SUM(si.quantity * si.price) - SUM(si.quantity * COALESCE(i.purchase_price, 0)) as profit')
            )
            ->groupBy('s.id', 's.bill_number', 's.invoice_date', 'p.name')
            ->orderByDesc('s.invoice_date')
            ->get();

        return response()->json([
            'success'      => true,
            'rows'         => $rows->toArray(),
            'total_sale'   => $this->fmt($rows->sum('sale_amount')),
            'total_cost'   => $this->fmt($rows->sum('cost_amount')),
            'total_profit' => $this->fmt($rows->sum('profit')),
            'period'       => ['from' => $from, 'to' => $to],
        ]);
    }

    public function billWiseProfitExport(Request $request)
    {
        return $this->billWiseProfit($request);
    }

    public function billWiseProfitItems(Request $request, $id)
    {
        if (!Schema::hasTable('sale_items')) {
            return response()->json(['success' => true, 'items' => []]);
        }

        $items = DB::table('sale_items as si')
            ->leftJoin('items as i', 'i.id', '=', 'si.item_id')
            ->where('si.sale_id', $id)
            ->select(
                'i.name as item_name',
                'si.quantity',
                'si.price as sale_price',
                DB::raw('COALESCE(i.purchase_price, 0) as purchase_price'),
                DB::raw('si.quantity * si.price as sale_amount'),
                DB::raw('si.quantity * COALESCE(i.purchase_price, 0) as cost_amount'),
                DB::raw('(si.quantity * si.price) - (si.quantity * COALESCE(i.purchase_price, 0)) as profit')
            )
            ->get();

        return response()->json(['success' => true, 'items' => $items->toArray()]);
    }

    // ============================================================
    // 12. BANK STATEMENT
    // ============================================================
    public function bankStatement(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $bankId = (int) $request->input('bank_id');

        if (!$bankId || !Schema::hasTable('bank_accounts')) {
            return response()->json([
                'success' => true,
                'transactions' => [],
                'total_withdrawal' => 0,
                'total_deposit' => 0,
                'opening_balance' => 0,
                'final_balance' => 0,
                'period' => ['from' => $from, 'to' => $to],
            ]);
        }

        $bank = DB::table('bank_accounts')->where('id', $bankId)->first();

        if (!$bank) {
            return response()->json(['success' => false, 'message' => 'Bank account not found.'], 404);
        }

        $allEntries = $this->bankStatementEntries($bankId);
        $ledgerNet = $allEntries->sum(fn ($entry) => (float) $entry['deposit_amount'] - (float) $entry['withdrawal_amount']);
        $baseOpeningBalance = (float) ($bank->opening_balance ?? 0) - $ledgerNet;
        $openingBalance = $this->fmt(
            $baseOpeningBalance +
            $allEntries
                ->filter(fn ($entry) => $entry['date'] && $entry['date'] < $from)
                ->sum(fn ($entry) => (float) $entry['deposit_amount'] - (float) $entry['withdrawal_amount'])
        );

        $balance = $openingBalance;
        $rows = $allEntries
            ->filter(fn ($entry) => $entry['date'] >= $from && $entry['date'] <= $to)
            ->sortBy([
                ['date', 'asc'],
                ['sort_id', 'asc'],
                ['source', 'asc'],
            ])
            ->values()
            ->map(function ($entry) use (&$balance) {
                $balance += (float) $entry['deposit_amount'] - (float) $entry['withdrawal_amount'];
                $entry['balance_amount'] = $this->fmt($balance);
                unset($entry['sort_id']);

                return $entry;
            });

        $totalDeposit = $this->fmt($rows->sum('deposit_amount'));
        $totalWithdrawal = $this->fmt($rows->sum('withdrawal_amount'));

        return response()->json([
            'success'          => true,
            'bank'             => [
                'id' => $bank->id,
                'name' => $bank->display_name ?? $bank->bank_name ?? 'Bank Account',
                'account_number' => $bank->account_number ?? null,
            ],
            'transactions'     => $rows->toArray(),
            'rows'             => $rows->toArray(),
            'total_deposit'    => $totalDeposit,
            'total_withdrawal' => $totalWithdrawal,
            'opening_balance'  => $openingBalance,
            'final_balance'    => $this->fmt($balance),
            'net'              => $this->fmt($totalDeposit - $totalWithdrawal),
            'period'           => ['from' => $from, 'to' => $to],
        ]);
    }

    public function bankStatementExport(Request $request)
    {
        return $this->bankStatement($request);
    }

    private function bankStatementEntries(int $bankId)
    {
        $entries = collect();

        if (Schema::hasTable('bank_transactions')) {
            $transferTypes = [
                'bank_to_bank',
                'bank_transfer_out',
                'bank_transfer_in',
                'bank_to_cash',
                'cash_to_bank',
                'adjust_balance',
                'bank_adjust_in',
                'bank_adjust_out',
                'opening_balance',
                'cheque_deposit',
                'sale_payment',
                'sale_payment_out',
                'payment_in',
                'payment_out',
                'cash_in',
                'cash_out',
                'loan_more',
                'loan_adjustment',
                'emi_pay',
            ];

            $entries = $entries->merge(
                DB::table('bank_transactions')
                    ->where(function ($query) use ($bankId) {
                        $query->where('from_bank_account_id', $bankId)
                            ->orWhere('to_bank_account_id', $bankId);
                    })
                    ->whereIn('type', $transferTypes)
                    ->select(
                        'id',
                        'from_bank_account_id',
                        'to_bank_account_id',
                        'type',
                        'amount',
                        'transaction_date',
                        'reference_type',
                        'description',
                        'created_at'
                    )
                    ->get()
                    ->map(function ($row) use ($bankId) {
                        $amount = (float) ($row->amount ?? 0);
                        $date = $row->transaction_date ?: substr((string) ($row->created_at ?? ''), 0, 10);
                        $depositTypes = ['opening_balance', 'bank_transfer_in', 'cash_to_bank', 'bank_adjust_in', 'cheque_deposit', 'sale_payment', 'payment_in', 'cash_in', 'loan_more', 'loan_adjustment'];
                        $withdrawalTypes = ['bank_transfer_out', 'bank_to_cash', 'bank_adjust_out', 'sale_payment_out', 'payment_out', 'cash_out', 'emi_pay'];
                        $isDeposit = in_array($row->type, $depositTypes, true)
                            || (!in_array($row->type, $withdrawalTypes, true) && (int) $row->to_bank_account_id === $bankId);
                        $typeLabel = ucwords(str_replace('_', ' ', (string) $row->type));

                        return [
                            'date' => (string) $date,
                            'description' => trim(($row->description ?: $typeLabel) . ($row->reference_type ? ' | ' . $row->reference_type : '')),
                            'withdrawal_amount' => $isDeposit ? 0 : $amount,
                            'deposit_amount' => $isDeposit ? $amount : 0,
                            'balance_amount' => 0,
                            'source' => 'bank_transaction',
                            'source_id' => (int) $row->id,
                            'sort_id' => (int) $row->id,
                        ];
                    })
            );
        }

        if (Schema::hasTable('payment_ins')) {
            $entries = $entries->merge(
                DB::table('payment_ins as pi')
                    ->leftJoin('parties as p', 'p.id', '=', 'pi.party_id')
                    ->where('pi.bank_account_id', $bankId)
                    ->select(
                        'pi.id',
                        'pi.amount',
                        'pi.date',
                        'pi.receipt_no',
                        'pi.created_at',
                        'p.name as party_name'
                    )
                    ->get()
                    ->map(fn ($row) => [
                        'date' => (string) ($row->date ?: substr((string) ($row->created_at ?? ''), 0, 10)),
                        'description' => trim('Payment In' . ($row->receipt_no ? ' #' . $row->receipt_no : '') . ' | ' . ($row->party_name ?? 'Party')),
                        'withdrawal_amount' => 0,
                        'deposit_amount' => (float) ($row->amount ?? 0),
                        'balance_amount' => 0,
                        'source' => 'payment_in',
                        'source_id' => (int) $row->id,
                        'sort_id' => (int) $row->id,
                    ])
            );
        }

        if (Schema::hasTable('sale_payments') && Schema::hasTable('sales')) {
            $entries = $entries->merge(
                DB::table('sale_payments as sp')
                    ->leftJoin('sales as s', 's.id', '=', 'sp.sale_id')
                    ->leftJoin('parties as p', 'p.id', '=', 's.party_id')
                    ->where('sp.bank_account_id', $bankId)
                    ->select(
                        'sp.id',
                        'sp.amount',
                        'sp.direction',
                        'sp.created_at',
                        's.type as sale_type',
                        's.bill_number',
                        's.invoice_date',
                        'p.name as party_name'
                    )
                    ->get()
                    ->map(function ($row) {
                        $isOut = ($row->direction ?? 'payment_in') === 'payment_out' || ($row->sale_type ?? null) === 'sale_return';
                        $amount = (float) ($row->amount ?? 0);

                        return [
                            'date' => (string) ($row->invoice_date ?: substr((string) ($row->created_at ?? ''), 0, 10)),
                            'description' => trim(($isOut ? 'Sale Payment Out' : 'Sale Payment') . ($row->bill_number ? ' #' . $row->bill_number : '') . ' | ' . ($row->party_name ?? 'Party')),
                            'withdrawal_amount' => $isOut ? $amount : 0,
                            'deposit_amount' => $isOut ? 0 : $amount,
                            'balance_amount' => 0,
                            'source' => 'sale_payment',
                            'source_id' => (int) $row->id,
                            'sort_id' => (int) $row->id,
                        ];
                    })
            );
        }

        if (Schema::hasTable('purchase_payments') && Schema::hasTable('purchases')) {
            $entries = $entries->merge(
                DB::table('purchase_payments as pp')
                    ->leftJoin('purchases as pu', 'pu.id', '=', 'pp.purchase_id')
                    ->leftJoin('parties as p', 'p.id', '=', 'pu.party_id')
                    ->where('pp.bank_account_id', $bankId)
                    ->select(
                        'pp.id',
                        'pp.amount',
                        'pp.created_at',
                        'pu.type as purchase_type',
                        'pu.bill_number',
                        'pu.bill_date',
                        'p.name as party_name'
                    )
                    ->get()
                    ->map(function ($row) {
                        $isDeposit = ($row->purchase_type ?? null) === 'purchase_return';
                        $amount = (float) ($row->amount ?? 0);

                        return [
                            'date' => (string) ($row->bill_date ?: substr((string) ($row->created_at ?? ''), 0, 10)),
                            'description' => trim(($isDeposit ? 'Purchase Return' : 'Payment Out') . ($row->bill_number ? ' #' . $row->bill_number : '') . ' | ' . ($row->party_name ?? 'Party')),
                            'withdrawal_amount' => $isDeposit ? 0 : $amount,
                            'deposit_amount' => $isDeposit ? $amount : 0,
                            'balance_amount' => 0,
                            'source' => 'purchase_payment',
                            'source_id' => (int) $row->id,
                            'sort_id' => (int) $row->id,
                        ];
                    })
            );
        }

        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'bank_account_id')) {
            $entries = $entries->merge(
                DB::table('expenses as e')
                    ->leftJoin('expense_categories as ec', 'ec.id', '=', 'e.expense_category_id')
                    ->where('e.bank_account_id', $bankId)
                    ->select(
                        'e.id',
                        'e.expense_date',
                        'e.expense_no',
                        'e.total_amount',
                        'e.party',
                        'e.created_at',
                        'ec.name as category_name'
                    )
                    ->get()
                    ->map(fn ($row) => [
                        'date' => (string) ($row->expense_date ?: substr((string) ($row->created_at ?? ''), 0, 10)),
                        'description' => trim('Expense' . ($row->expense_no ? ' #' . $row->expense_no : '') . ' | ' . ($row->category_name ?? $row->party ?? 'Expense')),
                        'withdrawal_amount' => (float) ($row->total_amount ?? 0),
                        'deposit_amount' => 0,
                        'balance_amount' => 0,
                        'source' => 'expense',
                        'source_id' => (int) $row->id,
                        'sort_id' => (int) $row->id,
                    ])
            );
        }

        return $entries->filter(fn ($entry) => filled($entry['date']))->values();
    }

    // ============================================================
    // 13. DISCOUNT REPORT
    // ─ sales uses discount_rs (discount_pct & discount_rs exist, no plain "discount")
    // ─ purchases same
    // ============================================================
    public function discountReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        // Sale discounts grouped by party — use discount_rs
        $saleDiscounts = collect();
        if (Schema::hasTable('sales')) {
            $saleDiscounts = DB::table('sales as s')
                ->leftJoin('parties as p', 'p.id', '=', 's.party_id')
                ->whereBetween('s.invoice_date', [$from, $to])
                ->where('s.discount_rs', '>', 0)
                ->select(
                    DB::raw('COALESCE(p.id, 0) as party_id'),
                    DB::raw("COALESCE(p.name, 'Walk-in') as party_name"),
                    DB::raw('SUM(s.discount_rs) as sale_discount')
                )
                ->groupBy('p.id', 'p.name')
                ->get()
                ->keyBy('party_id');
        }

        if (Schema::hasTable('sales') && Schema::hasTable('sale_items') && Schema::hasColumn('sale_items', 'discount')) {
            $saleItemDiscounts = DB::table('sale_items as si')
                ->join('sales as s', 's.id', '=', 'si.sale_id')
                ->leftJoin('parties as p', 'p.id', '=', 's.party_id')
                ->whereBetween('s.invoice_date', [$from, $to])
                ->where('si.discount', '>', 0)
                ->select(
                    DB::raw('COALESCE(p.id, 0) as party_id'),
                    DB::raw("COALESCE(p.name, 'Walk-in') as party_name"),
                    DB::raw('SUM(COALESCE(si.discount, 0)) as sale_discount')
                )
                ->groupBy('p.id', 'p.name')
                ->get();

            $saleDiscounts = $saleDiscounts->values()
                ->merge($saleItemDiscounts)
                ->groupBy('party_id')
                ->map(function ($rows) {
                    $first = $rows->first();

                    return (object) [
                        'party_id' => $first->party_id,
                        'party_name' => $first->party_name,
                        'sale_discount' => $rows->sum('sale_discount'),
                    ];
                });
        }

        // Purchase discounts grouped by party — use discount_rs
        $purchaseDiscounts = collect();
        if (Schema::hasTable('purchases')) {
            $purchaseDiscounts = DB::table('purchases as pu')
                ->leftJoin('parties as p', 'p.id', '=', 'pu.party_id')
                ->whereBetween('pu.bill_date', [$from, $to])
                ->where('pu.discount_rs', '>', 0)
                ->select(
                    DB::raw('COALESCE(p.id, 0) as party_id'),
                    DB::raw("COALESCE(p.name, 'Walk-in') as party_name"),
                    DB::raw('SUM(pu.discount_rs) as purchase_discount')
                )
                ->groupBy('p.id', 'p.name')
                ->get()
                ->keyBy('party_id');
        }

        if (Schema::hasTable('purchases') && Schema::hasTable('purchase_items') && Schema::hasColumn('purchase_items', 'discount')) {
            $purchaseItemDiscounts = DB::table('purchase_items as pi')
                ->join('purchases as pu', 'pu.id', '=', 'pi.purchase_id')
                ->leftJoin('parties as p', 'p.id', '=', 'pu.party_id')
                ->whereBetween('pu.bill_date', [$from, $to])
                ->where('pi.discount', '>', 0)
                ->select(
                    DB::raw('COALESCE(p.id, 0) as party_id'),
                    DB::raw("COALESCE(p.name, 'Walk-in') as party_name"),
                    DB::raw('SUM(COALESCE(pi.discount, 0)) as purchase_discount')
                )
                ->groupBy('p.id', 'p.name')
                ->get();

            $purchaseDiscounts = $purchaseDiscounts->values()
                ->merge($purchaseItemDiscounts)
                ->groupBy('party_id')
                ->map(function ($rows) {
                    $first = $rows->first();

                    return (object) [
                        'party_id' => $first->party_id,
                        'party_name' => $first->party_name,
                        'purchase_discount' => $rows->sum('purchase_discount'),
                    ];
                });
        }

        $allPartyIds = $saleDiscounts->keys()->merge($purchaseDiscounts->keys())->unique();

        $rows = $allPartyIds->map(function ($id) use ($saleDiscounts, $purchaseDiscounts) {
            $s  = $saleDiscounts->get($id);
            $pu = $purchaseDiscounts->get($id);
            $sd = $s  ? (float) $s->sale_discount     : 0;
            $pd = $pu ? (float) $pu->purchase_discount : 0;
            return [
                'party_name'        => $s ? $s->party_name : ($pu ? $pu->party_name : '-'),
                'sale_discount'     => $this->fmt($sd),
                'purchase_discount' => $this->fmt($pd),
                'total_discount'    => $this->fmt($sd + $pd),
            ];
        })->sortByDesc('total_discount')->values();

        return response()->json([
            'success'                 => true,
            'rows'                    => $rows->toArray(),
            'total_sale_discount'     => $this->fmt($rows->sum('sale_discount')),
            'total_purchase_discount' => $this->fmt($rows->sum('purchase_discount')),
            'total_discount'          => $this->fmt($rows->sum('total_discount')),
            'period'                  => ['from' => $from, 'to' => $to],
        ]);
    }

    public function discountReportExport(Request $request)
    {
        return $this->discountReport($request);
    }

    // ============================================================
    // 14. ITEM WISE DISCOUNT
    // ============================================================
    public function itemWiseDiscount(Request $request)
    {
        $data = $this->buildItemWiseDiscountReport($request);

        return response()->json([
            'success'        => true,
            'items'          => $data['items']->values()->all(),
            'rows'           => $data['items']->values()->all(),
            'totals'         => $data['totals'],
            'total_discount' => $data['totals']['total_disc'],
            'period'         => $data['period'],
        ]);
    }

    private function buildItemWiseDiscountReport(Request $request): array
    {
        [$from, $to] = $this->dateRange($request);

        $empty = [
            'items' => collect(),
            'totals' => ['total_sale' => 0, 'total_disc' => 0],
            'period' => ['from' => $from, 'to' => $to],
        ];

        if (!Schema::hasTable('sale_items') || !Schema::hasTable('sales')) {
            return $empty;
        }

        $hasItemsTable = Schema::hasTable('items');
        $hasPartiesTable = Schema::hasTable('parties');
        $hasItemId = Schema::hasColumn('sale_items', 'item_id');
        $hasItemName = Schema::hasColumn('sale_items', 'item_name');
        $hasItemCategory = Schema::hasColumn('sale_items', 'item_category');
        $hasPartyId = Schema::hasColumn('sales', 'party_id');
        $hasPartyName = Schema::hasColumn('sales', 'party_name');

        $itemNameExpr = $hasItemsTable && $hasItemId
            ? ($hasItemName ? 'COALESCE(i.name, si.item_name)' : 'i.name')
            : ($hasItemName ? 'si.item_name' : "'Unknown Item'");

        $itemIdExpr = $hasItemsTable && $hasItemId ? 'COALESCE(i.id, 0)' : '0';
        $groupColumns = [DB::raw($itemIdExpr), DB::raw($itemNameExpr)];

        $query = DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->when($hasItemsTable && $hasItemId, fn ($query) => $query->leftJoin('items as i', 'i.id', '=', 'si.item_id'))
            ->when($hasPartiesTable && $hasPartyId, fn ($query) => $query->leftJoin('parties as p', 'p.id', '=', 's.party_id'))
            ->whereBetween('s.invoice_date', [$from, $to]);

        if ($request->filled('item_id') && $hasItemId) {
            $query->where('si.item_id', $request->input('item_id'));
        } elseif ($request->filled('item_name')) {
            $query->where(DB::raw($itemNameExpr), 'like', '%' . $request->input('item_name') . '%');
        }

        if ($request->filled('category_id')) {
            $categoryId = $request->input('category_id');
            $categoryName = Schema::hasTable('categories')
                ? DB::table('categories')->where('id', $categoryId)->value('name')
                : null;

            $query->where(function ($query) use ($categoryId, $categoryName, $hasItemsTable, $hasItemCategory) {
                if ($hasItemsTable) {
                    $query->where('i.category_id', $categoryId);
                }

                if ($hasItemCategory && $categoryName) {
                    $hasItemsTable
                        ? $query->orWhere('si.item_category', $categoryName)
                        : $query->where('si.item_category', $categoryName);
                }
            });
        }

        if ($request->filled('party_id') && $hasPartyId) {
            $query->where('s.party_id', $request->input('party_id'));
        } elseif ($request->filled('party_name')) {
            $partyNameExpr = $hasPartiesTable && $hasPartyId
                ? ($hasPartyName ? 'COALESCE(p.name, s.party_name)' : 'p.name')
                : ($hasPartyName ? 's.party_name' : "''");

            $query->where(DB::raw($partyNameExpr), 'like', '%' . $request->input('party_name') . '%');
        }

        $items = $query
            ->select(
                DB::raw($itemIdExpr . ' as id'),
                DB::raw($itemNameExpr . ' as name'),
                DB::raw('SUM(COALESCE(si.quantity, 0)) as total_qty_sold'),
                DB::raw('SUM(COALESCE(si.amount, 0)) as total_sale_amount'),
                DB::raw('SUM(COALESCE(si.discount, 0)) as total_disc_amount'),
                DB::raw('CASE WHEN SUM(COALESCE(si.amount, 0)) > 0 THEN (SUM(COALESCE(si.discount, 0)) / SUM(COALESCE(si.amount, 0))) * 100 ELSE 0 END as avg_disc_percent')
            )
            ->groupBy(...$groupColumns)
            ->orderByDesc('total_disc_amount')
            ->get();

        return [
            'items' => $items,
            'totals' => [
                'total_sale' => $this->fmt($items->sum('total_sale_amount')),
                'total_disc' => $this->fmt($items->sum('total_disc_amount')),
            ],
            'period' => ['from' => $from, 'to' => $to],
        ];
    }

    // ============================================================
    // 15. TAX REPORT
    // ─ sales: tax_amount, tax_pct ✓
    // ─ purchases: tax_amount, tax_pct ✓
    // ============================================================
    public function taxReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $saleTax     = 0;
        $purchaseTax = 0;
        $rows        = collect();

        if (Schema::hasTable('sales')) {
            $salesTaxRows = DB::table('sales as s')
                ->leftJoin('parties as p', 'p.id', '=', 's.party_id')
                ->whereBetween('s.invoice_date', [$from, $to])
                ->where('s.tax_amount', '>', 0)
                ->select(
                    's.bill_number',
                    's.invoice_date as date',
                    'p.name as party_name',
                    DB::raw("'Sale' as type"),
                    's.total_amount',
                    's.tax_amount',
                    DB::raw('COALESCE(s.tax_pct, 0) as tax_rate')
                )
                ->get();
            $saleTax = $salesTaxRows->sum('tax_amount');
            $rows    = $rows->merge($salesTaxRows);
        }

        if (Schema::hasTable('purchases')) {
            $purchaseTaxRows = DB::table('purchases as pu')
                ->leftJoin('parties as p', 'p.id', '=', 'pu.party_id')
                ->whereBetween('pu.bill_date', [$from, $to])
                ->where('pu.tax_amount', '>', 0)
                ->select(
                    'pu.bill_number',
                    'pu.bill_date as date',
                    'p.name as party_name',
                    DB::raw("'Purchase' as type"),
                    'pu.total_amount',
                    'pu.tax_amount',
                    DB::raw('COALESCE(pu.tax_pct, 0) as tax_rate')
                )
                ->get();
            $purchaseTax = $purchaseTaxRows->sum('tax_amount');
            $rows        = $rows->merge($purchaseTaxRows);
        }

        $rows = $rows->sortByDesc('date')->values();

        return response()->json([
            'success'      => true,
            'rows'         => $rows->toArray(),
            'sale_tax'     => $this->fmt($saleTax),
            'purchase_tax' => $this->fmt($purchaseTax),
            'net_tax'      => $this->fmt($saleTax - $purchaseTax),
            'period'       => ['from' => $from, 'to' => $to],
        ]);
    }

    public function taxReportExport(Request $request)
    {
        return $this->taxReport($request);
    }

    // ============================================================
    // 16. TAX RATE REPORT
    // ============================================================
    public function taxRateReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $rows = collect();

        if (Schema::hasTable('sales')) {
            $rows = $rows->merge(
                DB::table('sales')
                    ->whereBetween('invoice_date', [$from, $to])
                    ->whereNotNull('tax_pct')
                    ->where('tax_pct', '>', 0)
                    ->select(
                        'tax_pct as tax_rate',
                        DB::raw('SUM(tax_amount) as tax_amount'),
                        DB::raw('SUM(total_amount) as taxable_amount'),
                        DB::raw("'Sale' as type")
                    )
                    ->groupBy('tax_pct')
                    ->get()
            );
        }

        if (Schema::hasTable('purchases')) {
            $rows = $rows->merge(
                DB::table('purchases')
                    ->whereBetween('bill_date', [$from, $to])
                    ->whereNotNull('tax_pct')
                    ->where('tax_pct', '>', 0)
                    ->select(
                        'tax_pct as tax_rate',
                        DB::raw('SUM(tax_amount) as tax_amount'),
                        DB::raw('SUM(total_amount) as taxable_amount'),
                        DB::raw("'Purchase' as type")
                    )
                    ->groupBy('tax_pct')
                    ->get()
            );
        }

        return response()->json([
            'success'   => true,
            'rows'      => $rows->toArray(),
            'total_tax' => $this->fmt($rows->sum('tax_amount')),
            'period'    => ['from' => $from, 'to' => $to],
        ]);
    }

    public function taxRateReportExport(Request $request)
    {
        return $this->taxRateReport($request);
    }

    // ============================================================
    // 17. EXPENSE REPORT
    // ─ expenses: expense_date, total_amount, payment_type,
    //             expense_category_id, expense_no, party
    // ============================================================
    public function expenseReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        if (!Schema::hasTable('expenses')) {
            return response()->json(['success' => true, 'rows' => [], 'total_amount' => 0]);
        }

        $rows = DB::table('expenses as e')
            ->leftJoin('expense_categories as ec', 'ec.id', '=', 'e.expense_category_id')
            ->whereBetween('e.expense_date', [$from, $to])
            ->select(
                'e.id',
                'e.expense_date as date',
                'e.expense_no',
                'e.total_amount as amount',
                DB::raw("COALESCE(e.payment_type, 'Cash') as payment_type"),
                'e.party',
                'ec.name as category_name'
            )
            ->orderByDesc('e.expense_date')
            ->get();

        return response()->json([
            'success'      => true,
            'rows'         => $rows->toArray(),
            'total_amount' => $this->fmt($rows->sum('amount')),
            'period'       => ['from' => $from, 'to' => $to],
        ]);
    }

    public function expenseReportExport(Request $request)
    {
        return $this->expenseReport($request);
    }

    // ============================================================
    // 18. EXPENSE CATEGORY REPORT
    // ============================================================
    public function expenseCategoryReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        if (!Schema::hasTable('expenses')) {
            return response()->json(['success' => true, 'rows' => [], 'total_amount' => 0]);
        }

        $rows = DB::table('expenses as e')
            ->leftJoin('expense_categories as ec', 'ec.id', '=', 'e.expense_category_id')
            ->whereBetween('e.expense_date', [$from, $to])
            ->select(
                DB::raw("COALESCE(ec.name, 'Uncategorized') as category_name"),
                DB::raw('COUNT(e.id) as count'),
                DB::raw('SUM(e.total_amount) as total_amount')
            )
            ->groupBy('ec.id', 'ec.name')
            ->orderByDesc('total_amount')
            ->get();

        return response()->json([
            'success'      => true,
            'rows'         => $rows->toArray(),
            'total_amount' => $this->fmt($rows->sum('total_amount')),
            'period'       => ['from' => $from, 'to' => $to],
        ]);
    }

    public function expenseCategoryReportExport(Request $request)
    {
        return $this->expenseCategoryReport($request);
    }

    // ============================================================
    // 19. EXPENSE ITEM REPORT
    // ============================================================
    public function expenseItemReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        if (!Schema::hasTable('expense_items')) {
            return response()->json(['success' => true, 'rows' => [], 'total_amount' => 0]);
        }

        $rows = DB::table('expense_items as ei')
            ->leftJoin('expenses as e', 'e.id', '=', 'ei.expense_id')
            ->whereBetween('e.expense_date', [$from, $to])
            ->select(
                'ei.name as item_name',
                DB::raw('COUNT(ei.id) as count'),
                DB::raw('SUM(ei.amount) as total_amount')
            )
            ->groupBy('ei.name')
            ->orderByDesc('total_amount')
            ->get();

        return response()->json([
            'success'      => true,
            'rows'         => $rows->toArray(),
            'total_amount' => $this->fmt($rows->sum('total_amount')),
            'period'       => ['from' => $from, 'to' => $to],
        ]);
    }

    public function expenseItemReportExport(Request $request)
    {
        return $this->expenseItemReport($request);
    }

    // ============================================================
    // 20. SALE ORDER REPORT
    // ─ sales.type enum includes 'sale_order' ✓
    // ============================================================
    public function saleOrder(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        if (!Schema::hasTable('sales')) {
            return response()->json(['success' => true, 'rows' => [], 'total_amount' => 0]);
        }

        $dateExpression = DB::raw('DATE(COALESCE(s.order_date, s.invoice_date, s.created_at))');

        $query = DB::table('sales as s')
            ->leftJoin('parties as p', 'p.id', '=', 's.party_id')
            ->whereBetween($dateExpression, [$from, $to])
            ->where('s.type', 'sale_order')
            ->select(
                's.id',
                's.bill_number',
                's.reference_bill_number',
                DB::raw('DATE(COALESCE(s.order_date, s.invoice_date, s.created_at)) as date'),
                's.order_date',
                's.due_date',
                's.description',
                DB::raw("COALESCE(p.name, s.party_name, 'Walk-in') as party_name"),
                's.total_amount',
                's.grand_total',
                's.balance',
                's.status',
                DB::raw("COALESCE(s.payment_type, 'Cash') as payment_type")
            )
            ->orderByDesc($dateExpression)
            ->orderByDesc('s.id');

        if ($request->filled('party')) {
            $party = trim((string) $request->party);
            $query->where(function ($partyQuery) use ($party) {
                $partyQuery->where('p.name', 'like', '%' . $party . '%')
                    ->orWhere('s.party_name', 'like', '%' . $party . '%')
                    ->orWhere('s.party_id', $party);
            });
        }
        if ($request->filled('status')) {
            $query->where('s.status', $request->status);
        }

        $rows = $query->get();
        $itemsBySale = collect();

        if ($rows->isNotEmpty() && Schema::hasTable('sale_items')) {
            $itemsBySale = DB::table('sale_items as si')
                ->whereIn('si.sale_id', $rows->pluck('id'))
                ->select(
                    'si.sale_id',
                    'si.item_name',
                    'si.quantity',
                    'si.unit_price',
                    'si.amount',
                    'si.discount'
                )
                ->orderBy('si.id')
                ->get()
                ->groupBy('sale_id');
        }

        $rows = $rows->map(function ($row) use ($itemsBySale) {
            $row->items = ($itemsBySale->get($row->id) ?? collect())->map(function ($item) {
                return [
                    'item_name' => $item->item_name ?: 'Item',
                    'quantity' => $this->fmt($item->quantity ?? 0),
                    'unit_price' => $this->fmt($item->unit_price ?? 0),
                    'discount' => $this->fmt($item->discount ?? 0),
                    'amount' => $this->fmt($item->amount ?? 0),
                ];
            })->values()->toArray();

            return $row;
        });

        return response()->json([
            'success'      => true,
            'rows'         => $rows->toArray(),
            'total_amount' => $this->fmt($rows->sum('grand_total')),
            'total_orders' => $rows->count(),
            'open_orders'  => $rows->filter(fn ($row) => in_array(strtolower((string) $row->status), ['pending', 'confirmed', 'open'], true))->count(),
            'period'       => ['from' => $from, 'to' => $to],
        ]);
    }

    // ============================================================
    // 21. SALE ORDER ITEMS
    // ============================================================
    public function saleOrderItems(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        if (!Schema::hasTable('sales') || !Schema::hasTable('sale_items')) {
            return response()->json(['success' => true, 'rows' => [], 'total_amount' => 0, 'total_qty' => 0]);
        }

        $dateExpression = DB::raw('DATE(COALESCE(s.order_date, s.invoice_date, s.created_at))');

        $query = DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->leftJoin('items as i', 'i.id', '=', 'si.item_id')
            ->leftJoin('parties as p', 'p.id', '=', 's.party_id')
            ->whereBetween($dateExpression, [$from, $to])
            ->where('s.type', 'sale_order')
            ->select(
                's.id as sale_id',
                's.bill_number',
                DB::raw('DATE(COALESCE(s.order_date, s.invoice_date, s.created_at)) as date'),
                DB::raw("COALESCE(p.name, s.party_name, 'Walk-in') as party_name"),
                DB::raw("COALESCE(i.name, si.item_name, 'Item') as item_name"),
                'si.quantity',
                'si.unit',
                'si.unit_price',
                DB::raw('COALESCE(si.amount, si.quantity * si.unit_price) as amount'),
                's.status',
                's.type'
            )
            ->orderByDesc($dateExpression)
            ->orderByDesc('s.id');

        if ($request->filled('item')) {
            $item = trim((string) $request->item);
            $query->where(function ($itemQuery) use ($item) {
                $itemQuery->where('i.name', 'like', '%' . $item . '%')
                    ->orWhere('si.item_name', 'like', '%' . $item . '%')
                    ->orWhere('si.item_id', $item);
            });
        }
        if ($request->filled('party')) {
            $party = trim((string) $request->party);
            $query->where(function ($partyQuery) use ($party) {
                $partyQuery->where('p.name', 'like', '%' . $party . '%')
                    ->orWhere('s.party_name', 'like', '%' . $party . '%')
                    ->orWhere('s.party_id', $party);
            });
        }
        if ($request->filled('status')) {
            $query->where('s.status', $request->status);
        }

        $rows = $query->get();

        return response()->json([
            'success'      => true,
            'rows'         => $rows->toArray(),
            'total_amount' => $this->fmt($rows->sum('amount')),
            'total_qty'    => $rows->sum('quantity'),
            'period'       => ['from' => $from, 'to' => $to],
        ]);
    }

    // ============================================================
    // 22. STOCK SUMMARY BY CATEGORY
    // ============================================================
    public function stockSummaryByCategory(Request $request)
    {
        $rows = DB::table('items')
            ->join('categories', 'categories.id', '=', 'items.category_id')
            ->select(
                'categories.name as category_name',
                DB::raw('SUM(opening_qty) as stock_qty'),
                DB::raw('SUM(opening_qty * purchase_price) as stock_value')
            )
            ->groupBy('categories.id', 'categories.name')
            ->get();

        return response()->json([
            'success'     => true,
            'rows'        => $rows->toArray(),
            'total_qty'   => $this->fmt($rows->sum('stock_qty')),
            'total_value' => $this->fmt($rows->sum('stock_value')),
        ]);
    }

    // ============================================================
    // 23. LOW STOCK
    // ============================================================
    public function lowStock(Request $request)
    {
        $rows = DB::table('items')
            ->select(
                'id', 'name', 'category_id',
                DB::raw('opening_qty as stock_qty'),
                DB::raw('min_stock as min_stock_qty'),
                DB::raw('opening_qty * purchase_price as stock_value')
            )
            ->whereRaw('opening_qty <= min_stock')
            ->get();

        return response()->json(['success' => true, 'rows' => $rows->toArray()]);
    }

    // ============================================================
    // 24. ITEM DETAIL
    // ============================================================
    public function itemDetail(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $itemId = $request->input('item_id');

        if (! $itemId || ! Schema::hasTable('items')) {
            return response()->json(['success' => true, 'rows' => []]);
        }

        $item = DB::table('items')->where('id', $itemId)->first(['id', 'name', 'opening_qty']);
        if (! $item) {
            return response()->json(['success' => true, 'rows' => []]);
        }

        $openingQty = $this->fmt($item->opening_qty ?? 0);
        $priorSaleQty = 0;
        $priorPurchaseQty = 0;
        $saleRows = collect();
        $purchaseRows = collect();

        if (Schema::hasTable('sales') && Schema::hasTable('sale_items') && Schema::hasColumn('sale_items', 'item_id')) {
            $priorSaleQty = DB::table('sale_items as si')
                ->join('sales as s', 's.id', '=', 'si.sale_id')
                ->where('si.item_id', $item->id)
                ->whereIn('s.type', ['invoice', 'pos'])
                ->whereDate('s.invoice_date', '<', $from)
                ->sum(DB::raw('COALESCE(si.quantity, 0)'));

            $saleRows = DB::table('sale_items as si')
                ->join('sales as s', 's.id', '=', 'si.sale_id')
                ->where('si.item_id', $item->id)
                ->whereIn('s.type', ['invoice', 'pos'])
                ->whereDate('s.invoice_date', '>=', $from)
                ->whereDate('s.invoice_date', '<=', $to)
                ->select(
                    DB::raw('DATE(s.invoice_date) as date'),
                    DB::raw('SUM(COALESCE(si.quantity, 0)) as sale_qty')
                )
                ->groupBy(DB::raw('DATE(s.invoice_date)'))
                ->get()
                ->keyBy('date');
        }

        if (Schema::hasTable('purchases') && Schema::hasTable('purchase_items') && Schema::hasColumn('purchase_items', 'item_id')) {
            $priorPurchaseQty = DB::table('purchase_items as pi')
                ->join('purchases as pu', 'pu.id', '=', 'pi.purchase_id')
                ->where('pi.item_id', $item->id)
                ->where('pu.type', 'purchase_bill')
                ->whereDate('pu.bill_date', '<', $from)
                ->sum(DB::raw('COALESCE(pi.quantity, 0)'));

            $purchaseRows = DB::table('purchase_items as pi')
                ->join('purchases as pu', 'pu.id', '=', 'pi.purchase_id')
                ->where('pi.item_id', $item->id)
                ->where('pu.type', 'purchase_bill')
                ->whereDate('pu.bill_date', '>=', $from)
                ->whereDate('pu.bill_date', '<=', $to)
                ->select(
                    DB::raw('DATE(pu.bill_date) as date'),
                    DB::raw('SUM(COALESCE(pi.quantity, 0)) as purchase_qty')
                )
                ->groupBy(DB::raw('DATE(pu.bill_date)'))
                ->get()
                ->keyBy('date');
        }

        $dates = $saleRows->keys()
            ->merge($purchaseRows->keys())
            ->unique()
            ->sort()
            ->values();

        $closingQty = $this->fmt($openingQty + $priorPurchaseQty - $priorSaleQty);
        $rows = $dates->map(function ($date) use ($saleRows, $purchaseRows, &$closingQty) {
            $saleQty = $this->fmt($saleRows->get($date)->sale_qty ?? 0);
            $purchaseQty = $this->fmt($purchaseRows->get($date)->purchase_qty ?? 0);
            $adjustmentQty = 0;
            $closingQty = $this->fmt($closingQty + $purchaseQty + $adjustmentQty - $saleQty);

            return [
                'date' => Carbon::parse($date)->format('d/m/Y'),
                'sale_qty' => $saleQty,
                'purchase_qty' => $purchaseQty,
                'adjustment_qty' => $adjustmentQty,
                'closing_qty' => $closingQty,
                'active' => ($saleQty + $purchaseQty + $adjustmentQty) > 0,
            ];
        });

        return response()->json([
            'success' => true,
            'rows' => $rows->values()->toArray(),
            'item' => ['id' => $item->id, 'name' => $item->name],
        ]);
    }

    // ============================================================
    // 25. ITEM CATEGORY WISE P&L
    // ============================================================
    public function itemCategoryPnL(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $rows = collect();

        if (Schema::hasTable('sale_items') && Schema::hasTable('sales')) {
            $rows = DB::table('sale_items as si')
                ->join('sales as s', 's.id', '=', 'si.sale_id')
                ->join('items as i', 'i.id', '=', 'si.item_id')
                ->leftJoin('categories as c', 'c.id', '=', 'i.category_id')
                ->whereBetween('s.invoice_date', [$from, $to])
                ->select(
                    DB::raw("COALESCE(c.name, 'Uncategorized') as category_name"),
                    DB::raw('SUM(si.quantity * si.price) as sale_amount'),
                    DB::raw('SUM(si.quantity * COALESCE(i.purchase_price, 0)) as cost_amount'),
                    DB::raw('SUM(si.quantity * si.price) - SUM(si.quantity * COALESCE(i.purchase_price, 0)) as profit')
                )
                ->groupBy('c.id', 'c.name')
                ->get();
        }

        return response()->json([
            'success'      => true,
            'rows'         => $rows->toArray(),
            'total_profit' => $this->fmt($rows->sum('profit')),
            'period'       => ['from' => $from, 'to' => $to],
        ]);
    }

    // ============================================================
    // DEBUG — helpful while testing
    // ============================================================
    public function debugSaleTypes()
    {
        $types = DB::table('sales')
            ->select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get();

        $samples = DB::table('sales')
            ->select('id', 'bill_number', 'type', 'status', 'invoice_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return response()->json([
            'all_types' => $types,
            'samples'   => $samples,
        ]);
    }
}
