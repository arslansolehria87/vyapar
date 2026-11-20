<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Party;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class UdharRegisterController extends Controller
{
    /**
     * Daily Udhar Register & Customer Khatas
     */
    public function index(Request $request)
    {
        $company = Auth::user()?->currentCompany();
        if (!$company) {
            return redirect()->route('login');
        }

        $selectedDate = $request->input('date', Carbon::today()->toDateString());
        $dateObj = Carbon::parse($selectedDate);

        // 1. Today's Credit Sales (where payment_type is credit OR balance > 0)
        $creditSalesQuery = Sale::where('company_id', $company->id)
            ->whereDate('invoice_date', $selectedDate)
            ->where(function ($q) {
                $q->where('payment_type', 'credit')
                  ->orWhere('balance', '>', 0);
            })
            ->with(['party', 'items'])
            ->latest('id');

        $creditSales = $creditSalesQuery->get();

        // 2. Metrics for the selected date
        $dayTotalCredit = (float) $creditSales->sum('balance');
        $dayGrandTotal = (float) $creditSales->sum('grand_total');
        $dayCustomerCount = $creditSales->pluck('party_id')->filter()->unique()->count();

        // 3. Overall Market Outstanding Balance across all customers
        $totalMarketOutstanding = (float) Party::where('company_id', $company->id)
            ->where('party_type', 'customer')
            ->sum('current_balance');

        // 4. All active Customers with their cumulative Khata balance
        $search = $request->input('search');
        $allKhatas = Party::where('company_id', $company->id)
            ->where('party_type', 'customer')
            ->when(!empty($search), function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            })
            ->where('current_balance', '>', 0)
            ->orderByDesc('current_balance')
            ->paginate(30);

        return view('dashboard.udhar.register', compact(
            'company',
            'selectedDate',
            'dateObj',
            'creditSales',
            'dayTotalCredit',
            'dayGrandTotal',
            'dayCustomerCount',
            'totalMarketOutstanding',
            'allKhatas',
            'search'
        ));
    }

    /**
     * API for Today's Udhar Summary (Used by Alex AI Voice Assistant)
     */
    public function todaySummaryApi()
    {
        $company = Auth::user()?->currentCompany();
        if (!$company) {
            return response()->json(['success' => false, 'message' => 'No active company'], 400);
        }

        $today = Carbon::today()->toDateString();

        $creditSales = Sale::where('company_id', $company->id)
            ->whereDate('invoice_date', $today)
            ->where(function ($q) {
                $q->where('payment_type', 'credit')
                  ->orWhere('balance', '>', 0);
            })
            ->with(['party', 'items'])
            ->latest('id')
            ->get();

        $totalUdhar = (float) $creditSales->sum('balance');
        $count = $creditSales->pluck('party_id')->filter()->unique()->count();

        $list = $creditSales->map(function ($s) {
            $itemsSummary = $s->items->map(function ($it) {
                return "{$it->quantity} {$it->unit} {$it->item_name}";
            })->implode('، ');

            return [
                'party_name' => $s->party?->name ?: 'Walk-in',
                'bill_number' => $s->bill_number,
                'amount' => (float)$s->grand_total,
                'balance' => (float)$s->balance,
                'current_total_balance' => (float)($s->party?->current_balance ?: 0),
                'items_summary' => $itemsSummary ?: 'General Items',
                'time' => $s->created_at ? $s->created_at->format('h:i A') : '',
            ];
        });

        if ($count > 0) {
            $firstNames = $creditSales->take(3)->map(fn($s) => $s->party?->name)->filter()->implode('، ');
            $reply = "آج کل {$count} گاہکوں نے " . number_format($totalUdhar) . " روپے کا ادھار لیا ہے، جن میں {$firstNames} شامل ہیں۔";
        } else {
            $reply = "آج ابھی تک کسی گاہک کا کوئی نیا ادھار درج نہیں ہوا ہے۔";
        }

        return response()->json([
            'success' => true,
            'date' => $today,
            'total_udhar' => $totalUdhar,
            'customers_count' => $count,
            'sales' => $list,
            'voice_reply' => $reply,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Customer 30/60 Days Khata Ledger & Running Schedule
     */
    public function customerKhata(Request $request, Party $party)
    {
        $company = Auth::user()?->currentCompany();
        if (!$company || $party->company_id !== $company->id) {
            abort(403, 'Unauthorized access to customer Khata.');
        }

        $days = $request->input('days', '30');
        $from = $request->input('from');
        $to = $request->input('to');

        $now = Carbon::now();
        $startDate = null;
        $endDate = $now->endOfDay();

        if (!empty($from)) {
            $startDate = Carbon::parse($from)->startOfDay();
            if (!empty($to)) {
                $endDate = Carbon::parse($to)->endOfDay();
            }
        } elseif ($days === '30') {
            $startDate = Carbon::now()->subDays(30)->startOfDay();
        } elseif ($days === '60') {
            $startDate = Carbon::now()->subDays(60)->startOfDay();
        } elseif ($days === '90') {
            $startDate = Carbon::now()->subDays(90)->startOfDay();
        }

        // 1. Fetch Sales with items
        $sales = Sale::where('company_id', $company->id)
            ->where('party_id', $party->id)
            ->with('items')
            ->orderBy('invoice_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // 2. Fetch Transactions (payments, sales, etc.)
        $transactions = $party->transactions()
            ->whereNull('transfer_group')
            ->whereIn('type', ['sale', 'sale_return', 'payment_in', 'payment_out', 'receive', 'pay'])
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Map sales by bill_number for easy item lookup
        $salesByBill = [];
        foreach ($sales as $s) {
            if ($s->bill_number) {
                $salesByBill[$s->bill_number] = $s;
            }
        }

        // 3. Compute Chronological Running Ledger
        $runningBalance = 0.0;
        $totalPurchases = 0.0;
        $totalPaid = 0.0;
        $fullSchedule = [];

        // Build combined events from transactions (or sales if transactions is empty)
        if ($transactions->isNotEmpty()) {
            foreach ($transactions as $txn) {
                $debit = (float) $txn->ledgerDebitValue();
                $credit = (float) $txn->ledgerCreditValue();

                $runningBalance += $debit;
                $runningBalance -= $credit;

                $totalPurchases += $debit;
                $totalPaid += $credit;

                $txnDate = $txn->date ? Carbon::parse($txn->date) : null;
                $bill = $salesByBill[$txn->number] ?? null;

                $itemsDetails = [];
                if ($bill && $bill->items) {
                    foreach ($bill->items as $it) {
                        $itemsDetails[] = [
                            'name' => $it->item_name,
                            'qty' => (float)$it->quantity,
                            'unit' => $it->unit,
                            'price' => (float)$it->price,
                            'amount' => (float)$it->total,
                        ];
                    }
                }

                $fullSchedule[] = [
                    'date' => $txnDate ? $txnDate->format('Y-m-d') : '-',
                    'date_formatted' => $txnDate ? $txnDate->format('d M Y') : '-',
                    'time' => $txn->created_at ? $txn->created_at->format('h:i A') : '',
                    'type' => $debit > 0 ? 'ادھار خریداری (Sale)' : 'وصولی / ادائیگی (Payment)',
                    'type_raw' => $debit > 0 ? 'sale' : 'payment',
                    'ref_number' => $txn->number ?: '-',
                    'description' => $txn->description ?: ($debit > 0 ? 'کریانہ سامان خریداری' : 'نقد وصولی'),
                    'items' => $itemsDetails,
                    'items_summary' => !empty($itemsDetails) ? implode('، ', array_map(fn($i) => "{$i['qty']} {$i['unit']} {$i['name']}", $itemsDetails)) : '',
                    'debit' => $debit,
                    'credit' => $credit,
                    'running_balance' => $runningBalance,
                    'raw_date' => $txnDate,
                ];
            }
        } else {
            // Fallback directly to sales if standalone transactions not recorded
            foreach ($sales as $s) {
                $grand = (float) $s->grand_total;
                $paid = (float) $s->received_amount;
                $netDebit = (float) $s->balance;

                $runningBalance += $netDebit;
                $totalPurchases += $grand;
                $totalPaid += $paid;

                $sDate = $s->invoice_date ? Carbon::parse($s->invoice_date) : null;

                $itemsDetails = [];
                foreach ($s->items as $it) {
                    $itemsDetails[] = [
                        'name' => $it->item_name,
                        'qty' => (float)$it->quantity,
                        'unit' => $it->unit,
                        'price' => (float)$it->price,
                        'amount' => (float)$it->total,
                    ];
                }

                $fullSchedule[] = [
                    'date' => $sDate ? $sDate->format('Y-m-d') : '-',
                    'date_formatted' => $sDate ? $sDate->format('d M Y') : '-',
                    'time' => $s->created_at ? $s->created_at->format('h:i A') : '',
                    'type' => 'ادھار خریداری (Sale)',
                    'type_raw' => 'sale',
                    'ref_number' => $s->bill_number ?: '-',
                    'description' => 'کریانہ سامان خریداری',
                    'items' => $itemsDetails,
                    'items_summary' => implode('، ', array_map(fn($i) => "{$i['qty']} {$i['unit']} {$i['name']}", $itemsDetails)),
                    'debit' => $grand,
                    'credit' => $paid,
                    'running_balance' => $runningBalance,
                    'raw_date' => $sDate,
                ];
            }
        }

        // Filter by date range if specified
        $filteredSchedule = $fullSchedule;
        if ($startDate) {
            $filteredSchedule = array_values(array_filter($fullSchedule, function ($row) use ($startDate, $endDate) {
                if (!$row['raw_date']) return true;
                return $row['raw_date']->between($startDate, $endDate);
            }));
        }

        $periodPurchases = array_sum(array_column($filteredSchedule, 'debit'));
        $periodPaid = array_sum(array_column($filteredSchedule, 'credit'));
        $netBalance = (float) ($party->current_balance ?: $runningBalance);

        // Build WhatsApp text message for the customer
        $phone = preg_replace('/\D/', '', (string)$party->phone);
        if (str_starts_with($phone, '03') && strlen($phone) === 11) $phone = '92' . substr($phone, 1);
        
        $waText = "محترم {$party->name} صاحب! {$company->name} سے آپ کا کھاتہ حساب:\n";
        $waText .= "کل خریداری: Rs " . number_format($periodPurchases) . "\n";
        $waText .= "کل وصول شدہ / ادا: Rs " . number_format($periodPaid) . "\n";
        $waText .= "موجودہ کل بقایا ادھار: Rs " . number_format($netBalance) . "\n";
        $waText .= "شکریہ! دکان پر تشریف لائیں۔";
        $waUrl = !empty($phone) ? "https://wa.me/{$phone}?text=" . urlencode($waText) : null;

        return view('dashboard.udhar.khata', compact(
            'company',
            'party',
            'days',
            'from',
            'to',
            'filteredSchedule',
            'totalPurchases',
            'totalPaid',
            'periodPurchases',
            'periodPaid',
            'netBalance',
            'waUrl'
        ));
    }

    /**
     * Printable Thermal / Slip Receipt for Customer Khata
     */
    public function customerReceipt(Request $request, Party $party)
    {
        $company = Auth::user()?->currentCompany();
        if (!$company || $party->company_id !== $company->id) {
            abort(403, 'Unauthorized access to customer receipt.');
        }

        $days = $request->input('days', '30');
        $from = $request->input('from');
        $to = $request->input('to');

        // Fetch sales with items
        $sales = Sale::where('company_id', $company->id)
            ->where('party_id', $party->id)
            ->with('items')
            ->orderBy('invoice_date', 'asc')
            ->get();

        $startDate = $days === 'all' ? null : Carbon::now()->subDays((int)$days)->startOfDay();

        $rows = [];
        $totalPurchased = 0.0;
        $totalPaid = 0.0;

        foreach ($sales as $s) {
            $sDate = $s->invoice_date ? Carbon::parse($s->invoice_date) : null;
            if ($startDate && $sDate && $sDate->lt($startDate)) {
                continue;
            }

            $itemsText = $s->items->map(fn($it) => "{$it->quantity} {$it->unit} {$it->item_name}")->implode('، ');

            $rows[] = [
                'date' => $sDate ? $sDate->format('d/m/Y') : '-',
                'bill' => $s->bill_number,
                'items' => $itemsText ?: 'کریانہ سامان',
                'amount' => (float) $s->grand_total,
                'paid' => (float) $s->received_amount,
                'balance' => (float) $s->balance,
            ];

            $totalPurchased += (float) $s->grand_total;
            $totalPaid += (float) $s->received_amount;
        }

        $currentBalance = (float) $party->current_balance;

        return view('dashboard.udhar.receipt', compact(
            'company',
            'party',
            'days',
            'rows',
            'totalPurchased',
            'totalPaid',
            'currentBalance'
        ));
    }

    /**
     * Ultra-Simple Village Shopkeeper Dashboard (آسان کریانہ موڈ)
     */
    public function asanDukan(Request $request)
    {
        $company = Auth::user()?->currentCompany();
        if (!$company) {
            return redirect()->route('login');
        }

        $today = Carbon::today()->toDateString();

        // Today's metrics
        $todayCreditSales = Sale::where('company_id', $company->id)
            ->whereDate('invoice_date', $today)
            ->where(function ($q) {
                $q->where('payment_type', 'credit')
                  ->orWhere('balance', '>', 0);
            })
            ->with(['party', 'items'])
            ->latest('id')
            ->get();

        $todayCreditTotal = (float) $todayCreditSales->sum('balance');
        $todayCreditCount = $todayCreditSales->pluck('party_id')->filter()->unique()->count();

        // Overall outstanding
        $marketOutstanding = (float) Party::where('company_id', $company->id)
            ->where('party_type', 'customer')
            ->sum('current_balance');

        // Recent Khatas
        $recentCustomers = Party::where('company_id', $company->id)
            ->where('party_type', 'customer')
            ->where('current_balance', '>', 0)
            ->orderByDesc('current_balance')
            ->take(8)
            ->get();

        return view('dashboard.udhar.asan-dukan', compact(
            'company',
            'today',
            'todayCreditSales',
            'todayCreditTotal',
            'todayCreditCount',
            'marketOutstanding',
            'recentCustomers'
        ));
    }
}
