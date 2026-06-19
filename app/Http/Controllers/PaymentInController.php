<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Party;
use App\Models\Broker;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\PaymentIn;
use App\Models\PaymentInLink;
use App\Models\SalePayment;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PaymentInController extends Controller
{
    public function index(Request $request)
    {
        return view('dashboard.sales.payement-in', [
            'parties'      => Party::all(),
            'brokers'      => Broker::query()->where('is_active', true)->orderBy('name')->get(),
            'items'        => Item::query()->where('is_active', true)->orderBy('name')->get(),
            'bankAccounts' => BankAccount::active()->get(),
            'paymentIns'   => PaymentIn::with(['party', 'bankAccount'])->latest()->get(),
            'nextEntryNo'  => (Transaction::max('id') ?? 0) + 1,
            'editPaymentIn' => $request->filled('edit_payment_in')
                ? PaymentIn::with(['party', 'bankAccount', 'links.sale'])->find($request->integer('edit_payment_in'))
                : null,
            'duplicatePaymentIn' => $request->filled('duplicate_payment_in')
                ? PaymentIn::with(['party', 'bankAccount', 'links.sale'])->find($request->integer('duplicate_payment_in'))
                : null,
        ]);
    }

    public function linkableSales(Request $request, Party $party)
    {
        $paymentInId = $request->integer('payment_in_id');
        $existingLinks = collect();

        if ($paymentInId > 0) {
            $existingLinks = PaymentInLink::query()
                ->selectRaw('sale_id, SUM(linked_amount) as linked_amount')
                ->where('payment_in_id', $paymentInId)
                ->groupBy('sale_id')
                ->pluck('linked_amount', 'sale_id');
        }

        $sales = Sale::query()
            ->where('party_id', $party->id)
            ->where(function ($query) {
                $query->whereIn('type', ['invoice', 'pos', 'sale', 'sales'])
                    ->orWhereNull('type');
            })
            ->where(function ($query) use ($existingLinks) {
                $query->whereRaw('(COALESCE(balance, 0) > 0 OR (COALESCE(grand_total, total_amount, 0) - COALESCE(received_amount, 0)) > 0)');

                if ($existingLinks->isNotEmpty()) {
                    $query->orWhereIn('id', $existingLinks->keys());
                }
            })
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->get()
            ->map(function (Sale $sale) use ($existingLinks) {
                $existingAmount = (float) ($existingLinks[$sale->id] ?? 0);
                $currentBalance = (float) ($sale->balance ?? max(0, (float) ($sale->grand_total ?? 0) - (float) ($sale->received_amount ?? 0)));
                $availableBalance = $currentBalance + $existingAmount;

                return [
                    'sale_id' => $sale->id,
                    'date' => optional($sale->invoice_date)->format('d/m/Y') ?: '-',
                    'type' => strtoupper($sale->type === 'pos' ? 'POS' : 'Sale'),
                    'ref_no' => $sale->bill_number ?: ('INV-' . $sale->id),
                    'total' => round((float) ($sale->grand_total ?? $sale->total_amount ?? 0), 2),
                    'balance' => round($availableBalance, 2),
                    'linked_amount' => round($existingAmount, 2),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'party' => [
                'id' => $party->id,
                'name' => $party->name,
            ],
            'rows' => $sales,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'party_id'                   => 'nullable|exists:parties,id',
            'entity_type'                => 'required|string|in:party,broker,item',
            'entity_id'                  => 'nullable|integer',
            'entity_name'                => 'nullable|string|max:255',
            'payments'                   => 'required|array|min:1',
            'payments.*.type'            => 'required|string',
            'payments.*.amount'          => 'required|numeric|min:1',
            'payments.*.bank_account_id' => 'nullable|exists:bank_accounts,id',
            'date'                       => 'required|date',
            'reference_no'               => 'nullable|string',
            'receipt_no'                 => 'nullable|string',
            'description'                => 'nullable|string',
            'attachments'                => 'nullable|array',
            'attachments.*'              => 'nullable|image|max:5120',
            'linked_rows'                => 'nullable|array',
            'linked_rows.*.sale_id'      => 'required|exists:sales,id',
            'linked_rows.*.amount'       => 'required|numeric|min:0.01',
        ]);

        try {
            $entityType = strtolower((string) $request->input('entity_type', 'party'));
            $entityId = (int) $request->input('entity_id', 0);
            $entityName = trim((string) $request->input('entity_name', ''));
            $party = $entityType === 'party' && $request->filled('party_id')
                ? Party::findOrFail($request->party_id)
                : null;
            $broker = $entityType === 'broker' && $entityId > 0 ? Broker::findOrFail($entityId) : null;
            $item = $entityType === 'item' && $entityId > 0 ? Item::findOrFail($entityId) : null;
            $displayName = $party?->name ?: ($broker?->name ?: ($item?->name ?: ($entityName !== '' ? $entityName : 'Payment In')));
            $savedPayments = collect();

            DB::transaction(function () use ($request, $party, $entityType, $entityId, $displayName, &$savedPayments) {
                $this->validateLinkedRows($request, collect());
                $storedAttachments = $this->storePaymentInAttachments($request);

                foreach ($request->payments as $pay) {
                    $paymentType = strtolower((string) ($pay['type'] ?? ''));
                    $isCash = $paymentType === 'cash';
                    $cashAccount = $isCash ? BankAccount::cashAccount() : null;
                    $bankAccountId = $isCash ? $cashAccount->id : ($pay['bank_account_id'] ?? null);

                    $paymentIn = PaymentIn::create([
                        'party_id'        => $party?->id,
                        'entity_type'     => $entityType,
                        'entity_id'       => $entityType === 'party' ? $party?->id : $entityId,
                        'entity_name'     => $displayName,
                        'bank_account_id' => $bankAccountId,
                        'amount'          => $pay['amount'],
                        'payment_type'    => $paymentType ?: ($pay['type'] ?? null),
                        'reference_no'    => $request->reference_no ?? null,
                        'receipt_no'      => $request->receipt_no ?? null,
                        'date'            => $request->date,
                        'description'     => $request->description ?? null,
                        'attachments'     => $storedAttachments ?: null,
                    ]);

                    $savedPayments->push($paymentIn);

                    if ($party) {
                        Transaction::create([
                            'party_id'        => $party->id,
                            'type'            => 'receive',
                            'number'          => $request->receipt_no ?? null,
                            'date'            => $request->date,
                            'total'           => $pay['amount'],
                            'paid_amount'     => $pay['amount'],
                            'debit'           => $pay['amount'],
                            'status'          => 'receive',
                            'description'     => trim('Payment In'
                                . (($request->reference_no ?? null) ? ' | Ref: ' . $request->reference_no : '')
                                . (($request->receipt_no ?? null) ? ' | Receipt: ' . $request->receipt_no : '')
                            ),
                        ]);

                        $party->opening_balance = (float) ($party->opening_balance ?? 0) - (float) $pay['amount'];
                        $party->save();
                    }

                    if ($isCash && $cashAccount) {
                        $cashAccount->opening_balance = (float) ($cashAccount->opening_balance ?? 0) + (float) $pay['amount'];
                        $cashAccount->save();

                        BankTransaction::create([
                            'from_bank_account_id' => $cashAccount->id,
                            'to_bank_account_id' => null,
                            'type' => 'cash_in',
                            'amount' => (float) $pay['amount'],
                            'transaction_date' => $request->date ?? now()->toDateString(),
                            'reference_type' => 'payment_in',
                            'reference_id' => $paymentIn->id,
                            'description' => 'Cash received from payment in',
                        ]);
                    } elseif (!empty($bankAccountId)) {
                        $bank = BankAccount::findOrFail($bankAccountId);
                        $bank->opening_balance = (float) ($bank->opening_balance ?? 0) + (float) $pay['amount'];
                        $bank->save();
                    }
                }

                if ($party && collect($request->input('linked_rows', []))->isEmpty()) {
                    $this->autoAllocatePaymentInsToSales($savedPayments, $party);
                } else {
                    $this->attachLinkedRows($savedPayments, $request->input('linked_rows', []));
                }
            });

            $latestPayment = $savedPayments->last();

            return response()->json([
                'success' => true,
                'message' => 'Payment record ho gaya!',
                'redirect_url' => ($latestPayment && $party)
                    ? route('invoice', ['payment_in' => $latestPayment->id])
                    : route('payment-in'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function edit(PaymentIn $paymentIn)
    {
        return redirect()->route('payment-in', ['edit_payment_in' => $paymentIn->id]);
    }

    public function duplicate(PaymentIn $paymentIn)
    {
        return redirect()->route('payment-in', ['duplicate_payment_in' => $paymentIn->id]);
    }

    public function update(Request $request, PaymentIn $paymentIn)
    {
        $request->validate([
            'party_id'                   => 'nullable|exists:parties,id',
            'entity_type'                => 'required|string|in:party,broker,item',
            'entity_id'                  => 'nullable|integer',
            'entity_name'                => 'nullable|string|max:255',
            'payments'                   => 'required|array|min:1|max:1',
            'payments.*.type'            => 'required|string',
            'payments.*.amount'          => 'required|numeric|min:1',
            'payments.*.bank_account_id' => 'nullable|exists:bank_accounts,id',
            'date'                       => 'required|date',
            'reference_no'               => 'nullable|string',
            'receipt_no'                 => 'nullable|string',
            'description'                => 'nullable|string',
            'attachments'                => 'nullable|array',
            'attachments.*'              => 'nullable|image|max:5120',
            'linked_rows'                => 'nullable|array',
            'linked_rows.*.sale_id'      => 'required|exists:sales,id',
            'linked_rows.*.amount'       => 'required|numeric|min:0.01',
        ]);

        try {
            DB::transaction(function () use ($request, $paymentIn) {
                $entityType = strtolower((string) $request->input('entity_type', 'party'));
                $entityId = (int) $request->input('entity_id', 0);
                $entityName = trim((string) $request->input('entity_name', ''));
                $newParty = $entityType === 'party' && $request->filled('party_id') ? Party::find($request->party_id) : null;
                $newBroker = $entityType === 'broker' && $entityId > 0 ? Broker::find($entityId) : null;
                $newItem = $entityType === 'item' && $entityId > 0 ? Item::find($entityId) : null;
                $displayName = $newParty?->name ?: ($newBroker?->name ?: ($newItem?->name ?: ($entityName !== '' ? $entityName : 'Payment In')));

                $oldAmount = (float) ($paymentIn->amount ?? 0);
                $oldType = strtolower((string) ($paymentIn->payment_type ?? ''));
                $newPayment = $request->payments[0];
                $newAmount = (float) ($newPayment['amount'] ?? 0);
                $existingLinks = $paymentIn->links()->get();
                $newType = strtolower((string) ($newPayment['type'] ?? ''));
                $newIsCash = $newType === 'cash';
                $existingAttachments = is_array($paymentIn->attachments ?? null) ? $paymentIn->attachments : [];
                $newAttachments = $this->storePaymentInAttachments($request);
                $mergedAttachments = array_values(array_filter(array_merge($existingAttachments, $newAttachments)));

                $oldParty = $paymentIn->party_id ? Party::find($paymentIn->party_id) : null;
                if ($oldParty) {
                    $oldParty->opening_balance = (float) ($oldParty->opening_balance ?? 0) + $oldAmount;
                    $oldParty->save();
                }

                if ($oldType === 'cash') {
                    $cashAccount = BankAccount::cashAccount();
                    $cashAccount->opening_balance = (float) ($cashAccount->opening_balance ?? 0) - $oldAmount;
                    $cashAccount->save();
                } elseif ($paymentIn->bank_account_id) {
                    $oldBank = BankAccount::find($paymentIn->bank_account_id);
                    if ($oldBank) {
                        $oldBank->opening_balance = (float) ($oldBank->opening_balance ?? 0) - $oldAmount;
                        $oldBank->save();
                    }
                }

                $this->rollbackLinkedRows($existingLinks);
                SalePayment::query()->where('payment_in_id', $paymentIn->id)->delete();
                $cashAccount = $newIsCash ? BankAccount::cashAccount() : null;
                $bankAccountId = $newIsCash ? $cashAccount->id : ($newPayment['bank_account_id'] ?? null);

                $paymentIn->update([
                    'party_id'        => $newParty?->id,
                    'entity_type'     => $entityType,
                    'entity_id'       => $entityType === 'party' ? $newParty?->id : $entityId,
                    'entity_name'     => $displayName,
                    'bank_account_id' => $bankAccountId,
                    'amount'          => $newAmount,
                    'payment_type'    => $newType ?: ($newPayment['type'] ?? null),
                    'reference_no'    => $request->reference_no ?? null,
                    'receipt_no'      => $request->receipt_no ?? null,
                    'date'            => $request->date,
                    'description'     => $request->description ?? null,
                    'attachments'     => $mergedAttachments ?: null,
                ]);

                if ($newParty) {
                    $newParty->opening_balance = (float) ($newParty->opening_balance ?? 0) - $newAmount;
                    $newParty->save();
                }

                if ($newIsCash && $cashAccount) {
                    $cashAccount->opening_balance = (float) ($cashAccount->opening_balance ?? 0) + $newAmount;
                    $cashAccount->save();
                } elseif ($paymentIn->bank_account_id) {
                    $newBank = BankAccount::find($paymentIn->bank_account_id);
                    if ($newBank) {
                        $newBank->opening_balance = (float) ($newBank->opening_balance ?? 0) + $newAmount;
                        $newBank->save();
                    }
                }

                $transaction = $this->findMatchingTransaction($paymentIn, $oldAmount);
                if ($transaction) {
                    $transaction->update([
                        'party_id'        => $request->party_id,
                        'number'          => $request->receipt_no ?? null,
                        'date'            => $request->date,
                        'total'           => $newAmount,
                        'paid_amount'     => $newAmount,
                        'debit'           => $newAmount,
                        'credit'          => 0,
                        'status'          => 'receive',
                        'description'     => trim('Payment In'
                            . (($request->reference_no ?? null) ? ' | Ref: ' . $request->reference_no : '')
                            . (($request->receipt_no ?? null) ? ' | Receipt: ' . $request->receipt_no : '')
                        ),
                    ]);
                }

                $this->validateLinkedRows($request, $existingLinks);
                if ($newParty && collect($request->input('linked_rows', []))->isEmpty()) {
                    $this->autoAllocatePaymentInsToSales(collect([$paymentIn->fresh()]), $newParty);
                } else {
                    $this->attachLinkedRows(collect([$paymentIn->fresh()]), $request->input('linked_rows', []));
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Payment updated successfully!',
                'redirect_url' => route('payment-in'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(PaymentIn $paymentIn)
    {
        try {
            DB::transaction(function () use ($paymentIn) {
                $amount = (float) ($paymentIn->amount ?? 0);
                $existingLinks = $paymentIn->links()->get();
                $paymentType = strtolower((string) ($paymentIn->payment_type ?? ''));
                $party = Party::find($paymentIn->party_id);
                if ($party) {
                    $party->opening_balance = (float) ($party->opening_balance ?? 0) + $amount;
                    $party->save();
                }

                if ($paymentType === 'cash') {
                    $cashAccount = BankAccount::cashAccount();
                    $cashAccount->opening_balance = (float) ($cashAccount->opening_balance ?? 0) - $amount;
                    $cashAccount->save();
                } elseif ($paymentIn->bank_account_id) {
                    $bank = BankAccount::find($paymentIn->bank_account_id);
                    if ($bank) {
                        $bank->opening_balance = (float) ($bank->opening_balance ?? 0) - $amount;
                        $bank->save();
                    }
                }

                $transaction = $this->findMatchingTransaction($paymentIn, $amount);
                if ($transaction) {
                    $transaction->delete();
                }

                $this->rollbackLinkedRows($existingLinks);
                SalePayment::query()->where('payment_in_id', $paymentIn->id)->delete();
                $paymentIn->delete();
            });

            return redirect()->route('payment-in')->with('success', 'Payment deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('payment-in')->with('error', $e->getMessage());
        }
    }

    public function print(PaymentIn $paymentIn)
    {
        $paymentIn->loadMissing(['party', 'bankAccount', 'links.sale']);
        $linkedSale = $this->resolvePaymentInThemeSale($paymentIn);
        if ($linkedSale) {
            return redirect()->route('sale.invoice-pdf', [
                'sale' => $linkedSale->id,
                'download' => 1,
            ]);
        }

        $themeDefaults = $this->resolvePaymentInThemeConfig($linkedSale);
        $themeConfig = $this->resolveInvoiceThemeConfig(
            $themeDefaults['mode'],
            $themeDefaults[$themeDefaults['mode'] === 'thermal' ? 'thermalThemeId' : 'regularThemeId']
        );

        return view('themes.sales_invoice_pdf_document', [
            'invoicePreviewData' => $this->mapPaymentInToThemePreviewData($paymentIn),
            'themeConfig' => $themeConfig,
            'accent' => $themeDefaults['accent'],
            'accent2' => $themeDefaults['accent2'],
            'autoPrint' => true,
        ]);
    }

    public function pdf(PaymentIn $paymentIn)
    {
        $paymentIn->loadMissing(['party', 'bankAccount', 'links.sale']);
        $linkedSale = $this->resolvePaymentInThemeSale($paymentIn);
        if ($linkedSale) {
            return redirect()->route('sale.invoice-pdf', [
                'sale' => $linkedSale->id,
                'download' => 1,
            ]);
        }

        $themeDefaults = $this->resolvePaymentInThemeConfig($linkedSale);
        $themeConfig = $this->resolveInvoiceThemeConfig(
            $themeDefaults['mode'],
            $themeDefaults[$themeDefaults['mode'] === 'thermal' ? 'thermalThemeId' : 'regularThemeId']
        );
        $downloadName = 'payment-in-' . ($paymentIn->receipt_no ?: $paymentIn->id) . '.pdf';

        $pdf = Pdf::loadView('themes.sales_invoice_pdf_document', [
            'invoicePreviewData' => $this->mapPaymentInToThemePreviewData($paymentIn),
            'themeConfig' => $themeConfig,
            'accent' => $themeDefaults['accent'],
            'accent2' => $themeDefaults['accent2'],
        ])->setPaper('a4', 'portrait');

        return $pdf->download($downloadName);
    }

    private function findMatchingTransaction(PaymentIn $paymentIn, float $amount): ?Transaction
    {
        return Transaction::query()
            ->where('party_id', $paymentIn->party_id)
            ->where('type', 'receive')
            ->where('total', $amount)
            ->where('date', $paymentIn->date)
            ->where('status', 'receive')
            ->latest('id')
            ->first();
    }

    private function storePaymentInAttachments(Request $request): array
    {
        $paths = [];
        foreach ((array) $request->file('attachments', []) as $file) {
            if ($file && $file->isValid()) {
                $paths[] = $file->store('payment-ins/attachments', 'public');
            }
        }

        return array_values(array_filter($paths));
    }

    private function resolvePaymentInThemeSale(PaymentIn $paymentIn): ?Sale
    {
        $paymentIn->loadMissing(['links.sale', 'party']);

        $linkedSale = $paymentIn->links
            ->map(fn ($link) => $link->sale)
            ->filter()
            ->sortByDesc(fn ($sale) => $sale?->created_at?->timestamp ?? $sale?->id ?? 0)
            ->first();

        if ($linkedSale instanceof Sale) {
            return $linkedSale;
        }

        $salePayment = SalePayment::query()
            ->with('sale')
            ->where('payment_in_id', $paymentIn->id)
            ->latest('id')
            ->first();

        if ($salePayment?->sale instanceof Sale) {
            return $salePayment->sale;
        }

        if ($paymentIn->party_id) {
            return Sale::query()
                ->where('party_id', $paymentIn->party_id)
                ->where(function ($query) {
                    $query->whereIn('type', ['invoice', 'pos', 'sale', 'sales'])
                        ->orWhereNull('type');
                })
                ->latest('id')
                ->first();
        }

        return null;
    }

    private function resolveFallbackPaymentInSale(Party $party): ?Sale
    {
        return Sale::query()
            ->where('party_id', $party->id)
            ->where(function ($query) {
                $query->whereIn('type', ['invoice', 'pos', 'sale', 'sales'])
                    ->orWhereNull('type');
            })
            ->where(function ($query) {
                $query->whereNull('balance')->orWhere('balance', '>', 0);
            })
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->first()
            ?: Sale::query()
                ->where('party_id', $party->id)
                ->where(function ($query) {
                    $query->whereIn('type', ['invoice', 'pos', 'sale', 'sales'])
                        ->orWhereNull('type');
                })
                ->orderByDesc('invoice_date')
                ->orderByDesc('id')
                ->first();
    }

    private function resolvePaymentInThemeConfig(?Sale $sale): array
    {
        $stored = is_array($sale?->invoice_theme ?? null) ? $sale->invoice_theme : [];
        $mode = strtolower((string) ($stored['mode'] ?? 'regular'));
        $mode = $mode === 'thermal' ? 'thermal' : 'regular';

        $regularThemeId = (int) ($stored['regularThemeId'] ?? ($stored['theme_id'] ?? 1));
        $thermalThemeId = (int) ($stored['thermalThemeId'] ?? ($stored['theme_id'] ?? 1));

        return [
            'mode' => $mode,
            'regularThemeId' => $regularThemeId,
            'thermalThemeId' => $thermalThemeId,
            'accent' => (string) ($stored['accent'] ?? '#1f4e79'),
            'accent2' => (string) ($stored['accent2'] ?? '#ff981f'),
        ];
    }

    private function autoAllocatePaymentInsToSales(Collection $paymentIns, Party $party): void
    {
        $sales = Sale::query()
            ->where('party_id', $party->id)
            ->where(function ($query) {
                $query->whereIn('type', ['invoice', 'pos', 'sale', 'sales'])
                    ->orWhereNull('type');
            })
            ->where(function ($query) {
                $query->whereNull('balance')->orWhere('balance', '>', 0);
            })
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->get()
            ->values();

        if ($sales->isEmpty()) {
            return;
        }

        $saleIndex = 0;

        foreach ($paymentIns as $paymentIn) {
            $remaining = round((float) ($paymentIn->amount ?? 0), 2);
            if ($remaining <= 0.001) {
                continue;
            }

            while ($remaining > 0.001 && $saleIndex < $sales->count()) {
                $sale = $sales[$saleIndex];
                $saleGrandTotal = (float) ($sale->grand_total ?? $sale->total_amount ?? 0);
                $saleReceived = (float) ($sale->received_amount ?? 0);
                $saleBalance = max(0, round($saleGrandTotal - $saleReceived, 2));

                if ($saleBalance <= 0.001) {
                    $saleIndex++;
                    continue;
                }

                $allocate = min($remaining, $saleBalance);
                if ($allocate <= 0.001) {
                    break;
                }

                SalePayment::create([
                    'sale_id' => $sale->id,
                    'payment_in_id' => $paymentIn->id,
                    'payment_type' => $paymentIn->payment_type ?? 'payment_in',
                    'direction' => 'payment_in',
                    'bank_account_id' => $paymentIn->bank_account_id,
                    'amount' => $allocate,
                    'reference' => $paymentIn->receipt_no
                        ?: ($paymentIn->reference_no ?? ('payment_in:' . $paymentIn->id)),
                ]);

                $this->applySaleLinkDelta($sale, $allocate);
                $remaining = round($remaining - $allocate, 2);

                if ($saleBalance - $allocate <= 0.001) {
                    $saleIndex++;
                }
            }
        }
    }

    private function resolveInvoiceThemeConfig(string $mode, int $themeId): array
    {
        $mode = $mode === 'thermal' ? 'thermal' : 'regular';

        $themes = $mode === 'thermal'
            ? [
                1 => ['name' => 'Thermal Theme 1', 'variant' => 'thermal1'],
                2 => ['name' => 'Thermal Theme 2', 'variant' => 'thermal2'],
                3 => ['name' => 'Thermal Theme 3', 'variant' => 'thermal3'],
                4 => ['name' => 'Thermal Theme 4', 'variant' => 'thermal4'],
                5 => ['name' => 'Thermal Theme 5', 'variant' => 'thermal5'],
            ]
            : [
                1 => ['name' => 'Telly Theme', 'variant' => 'classicA'],
                2 => ['name' => 'Landscape Theme 1', 'variant' => 'purpleA'],
                3 => ['name' => 'Landscape Theme 2', 'variant' => 'classicB'],
                4 => ['name' => 'Tax Theme 1', 'variant' => 'purpleB'],
                5 => ['name' => 'Tax Theme 2', 'variant' => 'classicC'],
                6 => ['name' => 'Tax Theme 3', 'variant' => 'modernPurple'],
                7 => ['name' => 'Tax Theme 4', 'variant' => 'purpleC'],
                8 => ['name' => 'Tax Theme 5', 'variant' => 'classicSale'],
                9 => ['name' => 'Tax Theme 6', 'variant' => 'taxTheme6'],
                10 => ['name' => 'Double Divine', 'variant' => 'doubleDivine'],
                11 => ['name' => 'French Elite', 'variant' => 'frenchElite'],
                12 => ['name' => 'Theme 1', 'variant' => 'theme1'],
                13 => ['name' => 'Theme 2', 'variant' => 'theme2'],
                14 => ['name' => 'Theme 3', 'variant' => 'theme3'],
                15 => ['name' => 'Theme 4', 'variant' => 'theme4'],
            ];

        $theme = $themes[$themeId] ?? reset($themes);

        return [
            'id' => $themeId,
            'mode' => $mode,
            'name' => $theme['name'],
            'variant' => $theme['variant'],
        ];
    }

    private function mapPaymentInToThemePreviewData(PaymentIn $paymentIn): array
    {
        $paymentIn->loadMissing(['party', 'bankAccount', 'links.sale']);
        $paymentDate = $paymentIn->date ? \Carbon\Carbon::parse($paymentIn->date) : $paymentIn->created_at;
        $linkedSales = $paymentIn->links->map(fn ($link) => [
            'name' => $link->sale?->bill_number ?: ('Sale #' . $link->sale_id),
            'qty' => 1,
            'amt' => (float) ($link->linked_amount ?? 0),
            'rate' => (float) ($link->linked_amount ?? 0),
            'disc' => 0,
            'gst' => '',
        ])->values()->all();

        if (empty($linkedSales)) {
            $linkedSales = [[
                'name' => 'Payment Received',
                'qty' => 1,
                'amt' => (float) ($paymentIn->amount ?? 0),
                'rate' => (float) ($paymentIn->amount ?? 0),
                'disc' => 0,
                'gst' => '',
            ]];
        }

        return [
            'businessName' => config('app.name', 'My Company'),
            'phone' => $paymentIn->party?->phone ?: '',
            'title' => 'Payment In',
            'invoiceNo' => $paymentIn->receipt_no ?: (string) $paymentIn->id,
            'date' => $paymentDate ? $paymentDate->format('d/m/Y') : '',
            'time' => $paymentDate ? $paymentDate->format('h:i A') : '',
            'billTo' => $paymentIn->entity_name ?: $paymentIn->party?->name ?: '-',
            'billAddress' => $paymentIn->party?->billing_address ?: '',
            'shipTo' => '',
            'billPhone' => $paymentIn->party?->phone ?: '',
            'items' => $linkedSales,
            'subtotal' => (float) ($paymentIn->amount ?? 0),
            'discount' => 0,
            'taxAmount' => 0,
            'total' => (float) ($paymentIn->amount ?? 0),
            'received' => (float) ($paymentIn->amount ?? 0),
            'balance' => 0,
            'totalInWords' => 'Payment received in full.',
            'description' => $paymentIn->description ?: '',
            'footerTermsText' => $paymentIn->description ?: 'Payment received.',
        ];
    }

    public function getHistory(PaymentIn $paymentIn)
    {
        try {
            $paymentIn->load(['party', 'bankAccount']);

            // Build history entries - always at least one entry
            $history = [
                [
                    'entry_no' => null,
                    'action' => 'Payment Record Created',
                    'amount' => $paymentIn->amount,
                    'reference' => $paymentIn->reference_no,
                    'receipt' => $paymentIn->receipt_no,
                    'payment_type' => $paymentIn->payment_type,
                    'party' => $paymentIn->party?->name,
                    'bank' => $paymentIn->bankAccount?->display_name,
                    'created_at' => $paymentIn->created_at ? $paymentIn->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                    'user_name' => auth()->user()->name ?? 'System User',
                ]
            ];

            // Get related transactions for this payment
            $transactions = Transaction::where('party_id', $paymentIn->party_id)
                ->where('type', 'receive')
                ->where('status', 'receive')
                ->whereDate('date', $paymentIn->date)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            if ($transactions->count() > 0) {
                foreach ($transactions as $transaction) {
                    $history[] = [
                        'entry_no' => $transaction->id,
                        'action' => 'Bank Transaction Recorded',
                        'amount' => $transaction->total ?? $transaction->debit,
                        'reference' => $transaction->reference_no ?? '-',
                        'receipt' => $transaction->number ?? '-',
                        'payment_type' => $transaction->type,
                        'status' => $transaction->status,
                        'description' => $transaction->description,
                        'created_at' => $transaction->created_at ? $transaction->created_at->format('Y-m-d H:i:s') : '-',
                        'user_name' => 'Bank System',
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'history' => $history,
                'total_records' => count($history),
                'payment_details' => [
                    'entry_no' => optional($transactions->first())->id,
                    'reference_no' => $paymentIn->reference_no ?? '-',
                    'receipt_no' => $paymentIn->receipt_no ?? '-',
                    'amount' => number_format($paymentIn->amount, 2),
                    'payment_type' => ucfirst($paymentIn->payment_type),
                    'date' => $paymentIn->date,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Payment History Error: ' . $e->getMessage());
            return response()->json([
                'success' => true,
                'history' => [[
                    'entry_no' => null,
                    'action' => 'Error loading full history',
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'user_name' => 'System',
                ]],
                'total_records' => 1,
                'message' => 'Showing basic information. Full history unavailable.',
            ]);
        }
    }

    private function validateLinkedRows(Request $request, Collection $existingLinks): void
    {
        $linkedRows = collect($request->input('linked_rows', []))
            ->map(function ($row) {
                return [
                    'sale_id' => (int) ($row['sale_id'] ?? 0),
                    'amount' => round((float) ($row['amount'] ?? 0), 2),
                ];
            })
            ->filter(fn ($row) => $row['sale_id'] > 0 && $row['amount'] > 0)
            ->values();

        if ($linkedRows->isEmpty()) {
            return;
        }

        $paymentsTotal = collect($request->input('payments', []))
            ->sum(fn ($payment) => (float) ($payment['amount'] ?? 0));
        $linkedTotal = $linkedRows->sum('amount');

        if ($linkedTotal - $paymentsTotal > 0.001) {
            throw ValidationException::withMessages([
                'linked_rows' => ['Linked amount received amount se zyada nahi ho sakta.'],
            ]);
        }

        $existingBySale = $existingLinks
            ->groupBy('sale_id')
            ->map(fn ($rows) => (float) $rows->sum('linked_amount'));

        foreach ($linkedRows as $row) {
            $sale = Sale::query()
                ->whereKey($row['sale_id'])
                ->where(function ($query) {
                    $query->whereIn('type', ['invoice', 'pos', 'sale', 'sales'])
                        ->orWhereNull('type');
                })
                ->first();

            if (!$sale) {
                throw ValidationException::withMessages([
                    'linked_rows' => ['Selected transaction available nahi hai.'],
                ]);
            }

            $availableBalance = (float) ($sale->balance ?? 0) + (float) ($existingBySale[$sale->id] ?? 0);
            if ($row['amount'] - $availableBalance > 0.001) {
                throw ValidationException::withMessages([
                    'linked_rows' => ['Linked amount selected transaction ke balance se zyada hai.'],
                ]);
            }
        }
    }

    private function attachLinkedRows(Collection $paymentIns, array $linkedRows): void
    {
        $payments = $paymentIns->values();
        if ($payments->isEmpty()) {
            return;
        }

        $rows = collect($linkedRows)
            ->map(function ($row) {
                return [
                    'sale_id' => (int) ($row['sale_id'] ?? 0),
                    'amount' => round((float) ($row['amount'] ?? 0), 2),
                ];
            })
            ->filter(fn ($row) => $row['sale_id'] > 0 && $row['amount'] > 0)
            ->values();

        if ($rows->isEmpty()) {
            return;
        }

        $paymentIndex = 0;
        $remainingPaymentAmount = (float) ($payments[$paymentIndex]->amount ?? 0);

        foreach ($rows as $row) {
            $sale = Sale::query()->lockForUpdate()->findOrFail($row['sale_id']);
            $remainingLinkAmount = (float) $row['amount'];

            while ($remainingLinkAmount > 0.001) {
                while ($paymentIndex < $payments->count() && $remainingPaymentAmount <= 0.001) {
                    $paymentIndex++;
                    $remainingPaymentAmount = $paymentIndex < $payments->count()
                        ? (float) ($payments[$paymentIndex]->amount ?? 0)
                        : 0;
                }

                if ($paymentIndex >= $payments->count()) {
                    throw new \RuntimeException('Linked amount ko save karne ke liye enough payment rows available nahi hain.');
                }

                $allocate = min($remainingLinkAmount, $remainingPaymentAmount);

                PaymentInLink::create([
                    'payment_in_id' => $payments[$paymentIndex]->id,
                    'sale_id' => $sale->id,
                    'linked_amount' => $allocate,
                ]);

                SalePayment::create([
                    'sale_id' => $sale->id,
                    'payment_in_id' => $payments[$paymentIndex]->id,
                    'payment_type' => $payments[$paymentIndex]->payment_type ?? 'payment_in',
                    'direction' => 'payment_in',
                    'bank_account_id' => $payments[$paymentIndex]->bank_account_id,
                    'amount' => $allocate,
                    'reference' => $payments[$paymentIndex]->receipt_no
                        ?: ($payments[$paymentIndex]->reference_no ?? ('payment_in:' . $payments[$paymentIndex]->id)),
                ]);

                $remainingLinkAmount -= $allocate;
                $remainingPaymentAmount -= $allocate;
            }

            $this->applySaleLinkDelta($sale, (float) $row['amount']);
        }
    }

    private function rollbackLinkedRows(Collection $links): void
    {
        if ($links->isEmpty()) {
            return;
        }

        $grouped = $links->groupBy('sale_id');

        foreach ($grouped as $saleId => $saleLinks) {
            $sale = Sale::query()->lockForUpdate()->find($saleId);
            if ($sale) {
                $this->applySaleLinkDelta($sale, -1 * (float) $saleLinks->sum('linked_amount'));
            }
        }

        $paymentInIds = $links->pluck('payment_in_id')->filter()->unique()->values();
        if ($paymentInIds->isNotEmpty()) {
            SalePayment::query()
                ->whereIn('payment_in_id', $paymentInIds)
                ->delete();
        }

        PaymentInLink::query()
            ->whereIn('id', $links->pluck('id'))
            ->delete();
    }

    private function applySaleLinkDelta(Sale $sale, float $delta): void
    {
        $receivedAmount = max(0, (float) ($sale->received_amount ?? 0) + $delta);
        $grandTotal = (float) ($sale->grand_total ?? $sale->total_amount ?? 0);
        $balance = max(0, $grandTotal - $receivedAmount);
        $status = $balance <= 0.001 ? 'paid' : ($receivedAmount > 0 ? 'partially paid' : 'unpaid');

        $sale->update([
            'received_amount' => round($receivedAmount, 2),
            'balance' => round($balance, 2),
            'status' => $status,
        ]);
    }
}
