<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class PaymentLinkController extends Controller
{
    // Fetch unpaid invoices for a party
    public function linkData(Request $request)
    {
        $request->validate([
            'party_id' => 'required|integer',
            'type' => 'required|string', // 'in' or 'out'
        ]);

        $partyId = (int) $request->input('party_id');
        $type = $request->input('type');
        $search = $request->input('search');

        if ($type === 'in') {
            // unpaid sales invoices
            $query = DB::table('sales')
                ->select('id as sale_id', 'invoice_no', 'date', 'grand_total', 'received_amount', 'balance', 'status')
                ->where('party_id', $partyId)
                ->where('balance', '>', 0)
                ->where('status', '!=', 'cancelled');
        } else {
            // unpaid purchase bills
            $query = DB::table('purchases')
                ->select('id as sale_id', 'bill_no as invoice_no', 'date', 'grand_total', 'received_amount', 'balance', 'status')
                ->where('party_id', $partyId)
                ->where('balance', '>', 0)
                ->where('status', '!=', 'cancelled');
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%" . $search . "%")
                  ->orWhere('bill_no', 'like', "%" . $search . "%");
            });
        }

        $rows = $query->orderBy('date', 'asc')->get();

        return response()->json(['rows' => $rows]);
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
