<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Models\Party;

class PaymentLinkController extends Controller
{
    // Fetch unpaid invoices for a party
    public function linkData(Request $request)
    {
        $request->validate([
            'party_id' => 'nullable|integer',
            'type' => 'required|string', // 'in' or 'out'
        ]);

        $partyId = (int) $request->input('party_id', 0);
        $type = $request->input('type');
        $search = $request->input('search');

        if ($type === 'in') {
            $query = DB::table('sales')
                ->leftJoin('parties', 'sales.party_id', '=', 'parties.id')
                ->select(
                    'sales.id as sale_id',
                    'sales.invoice_no',
                    'sales.date',
                    'sales.grand_total',
                    'sales.received_amount',
                    'sales.balance',
                    'sales.status',
                    'parties.name as party_name'
                )
                ->where('sales.balance', '>', 0)
                ->where('sales.status', '!=', 'cancelled');

            if ($partyId > 0) {
                $query->where('sales.party_id', $partyId);
            }
        } else {
            $query = DB::table('purchases')
                ->leftJoin('parties', 'purchases.party_id', '=', 'parties.id')
                ->select(
                    'purchases.id as sale_id',
                    'purchases.bill_no as invoice_no',
                    'purchases.date',
                    'purchases.grand_total',
                    'purchases.received_amount',
                    'purchases.balance',
                    'purchases.status',
                    'parties.name as party_name'
                )
                ->where('purchases.balance', '>', 0)
                ->where('purchases.status', '!=', 'cancelled');

            if ($partyId > 0) {
                $query->where('purchases.party_id', $partyId);
            }
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%" . $search . "%")
                  ->orWhere('parties.name', 'like', "%" . $search . "%");
            });
        }

        $rows = $query->orderBy('date', 'asc')->get();

        return response()->json(['rows' => $rows]);
    }

    public function expenseLinkData(Request $request, Party $party)
    {
        $search = trim((string) $request->input('search', ''));

        $query = DB::table('transactions')
            ->leftJoin('parties', 'transactions.party_id', '=', 'parties.id')
            ->select(
                'transactions.id as transaction_id',
                'transactions.number as invoice_no',
                'transactions.date',
                'transactions.type',
                'transactions.total',
                'transactions.paid_amount',
                'transactions.balance',
                'transactions.status',
                'transactions.payment_type',
                'parties.name as party_name'
            )
            ->where('transactions.party_id', $party->id)
            ->where('transactions.balance', '>', 0)
            ->where('transactions.status', '!=', 'cancelled');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('transactions.number', 'like', '%' . $search . '%')
                  ->orWhere('parties.name', 'like', '%' . $search . '%')
                  ->orWhere('transactions.type', 'like', '%' . $search . '%');
            });
        }

        $rows = $query->orderBy('transactions.date', 'asc')
            ->orderBy('transactions.id', 'asc')
            ->get()
            ->map(function ($row) {
                return [
                    'transaction_id' => $row->transaction_id,
                    'date' => $row->date ? Carbon::parse($row->date)->format('d/m/Y') : '-',
                    'type' => ucfirst(str_replace('_', ' ', (string) ($row->type ?: 'Transaction'))),
                    'ref_no' => $row->invoice_no ?: ('T-' . $row->transaction_id),
                    'total' => round((float) ($row->total ?? 0), 2),
                    'balance' => round((float) ($row->balance ?? 0), 2),
                    'linked_amount' => 0,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'party' => [
                'id' => $party->id,
                'name' => $party->name,
            ],
            'rows' => $rows,
        ]);
    }

    // Save links and update related invoices
    public function saveLinks(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|integer',
            'type' => 'required|string',
            'links' => 'required|array'
        ]);

        $transactionId = (int) $request->input('transaction_id');
        $type = $request->input('type');
        $links = $request->input('links'); // array of {sale_id, linked_amount}

        DB::beginTransaction();
        try {
            foreach ($links as $ln) {
                $saleId = (int) ($ln['sale_id'] ?? 0);
                $amount = floatval($ln['linked_amount'] ?? 0);
                if ($saleId <= 0 || $amount <= 0) continue;

                DB::table('payment_links')->insert([
                    'transaction_id' => $transactionId,
                    'sale_id' => $saleId,
                    'linked_amount' => $amount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($type === 'in') {
                    // update sales
                    $sale = DB::table('sales')->where('id', $saleId)->first();
                    if ($sale) {
                        $received = floatval($sale->received_amount ?? 0) + $amount;
                        $balance = floatval($sale->grand_total ?? 0) - $received;
                        if ($balance < 0) $balance = 0;

                        DB::table('sales')->where('id', $saleId)->update([
                            'received_amount' => $received,
                            'balance' => $balance,
                            'status' => ($balance == 0) ? 'paid' : 'partial',
                        ]);
                    }
                } else {
                    // update purchases
                    $purchase = DB::table('purchases')->where('id', $saleId)->first();
                    if ($purchase) {
                        $received = floatval($purchase->received_amount ?? 0) + $amount;
                        $balance = floatval($purchase->grand_total ?? 0) - $received;
                        if ($balance < 0) $balance = 0;

                        DB::table('purchases')->where('id', $saleId)->update([
                            'received_amount' => $received,
                            'balance' => $balance,
                            'status' => ($balance == 0) ? 'paid' : 'partial',
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PaymentLink save error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Save failed'], 500);
        }
    }
}
