<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Broker;
use App\Models\Item;
use App\Models\Party;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseExpenseController extends Controller
{
    //



    public function paymentOut()
    {
        $paymentOuts = Transaction::with(['party', 'bankAccount', 'counterParty', 'broker', 'item'])
            ->where('type', 'payment_out')
            ->latest('date')
            ->get();

        $bankAccounts = BankAccount::query()
            ->where('is_active', true)
            ->orderBy('display_name')
            ->get();

        $parties = Party::query()
            ->orderBy('name')
            ->get();

        $brokers = Broker::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $items = Item::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $paymentOutRows = $paymentOuts->map(function (Transaction $txn) {
            return [
                'id' => $txn->id,
                'date' => optional($txn->date)->format('Y-m-d') ?: now()->toDateString(),
                'receiptNo' => $txn->number ?? '',
                'party' => $txn->party?->name ?? $txn->broker?->name ?? $txn->item?->name ?? $txn->counterParty?->name ?? '',
                'party_id' => $txn->party_id,
                'broker' => $txn->broker?->name ?? '',
                'item_id' => $txn->item_id,
                'item' => $txn->item?->name ?? '',
                'paymentType' => $txn->payment_type ?? ($txn->bankAccount?->display_name ?? 'Cash'),
                'payType' => $txn->payment_type ?? ($txn->bankAccount?->display_name ?? 'Cash'),
                'bankAccountId' => $txn->bank_account_id,
                'amount' => (float) ($txn->paid_amount ?? $txn->credit ?? $txn->total ?? 0),
                'discount' => 0,
                'total' => (float) ($txn->total ?? $txn->paid_amount ?? 0),
                'status' => $txn->status ?? 'paid',
                'reference' => '',
                'description' => $txn->description ?? '',
                'dueDate' => optional($txn->due_date)->format('Y-m-d') ?: '',
            ];
        })->values();

        $nextEntryNo = (Transaction::max('id') ?? 0) + 1;

        return view('dashboard.purchases.payement-out', compact('paymentOutRows', 'bankAccounts', 'parties', 'brokers', 'items', 'nextEntryNo'));
    }

    public function linkablePurchases(Request $request, Party $party)
    {
        $paymentOutId = $request->integer('payment_out_id');

        $salesRows = \DB::table('sales')
            ->where('party_id', $party->id)
            ->whereNotIn('status', ['paid'])
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->get()
            ->map(function ($sale) {
                $balance = (float) ($sale->balance ?? max(0, (float) ($sale->grand_total ?? 0) - (float) ($sale->received_amount ?? 0)));
                return [
                    'id' => 'sale:' . $sale->id,
                    'sale_id' => $sale->id,
                    'purchase_id' => $sale->id,
                    'date' => $sale->invoice_date ? \Carbon\Carbon::parse($sale->invoice_date)->format('d/m/Y') : '-',
                    'type' => 'Sale',
                    'ref_no' => $sale->bill_number ?: ('S-' . $sale->id),
                    'total' => round((float) ($sale->grand_total ?? $sale->total_amount ?? 0), 2),
                    'balance' => round($balance, 2),
                    'linked_amount' => 0,
                ];
            });

        $transactionRows = Transaction::query()
            ->where('party_id', $party->id)
            ->whereNotIn('status', ['paid'])
            ->whereNotNull('balance')
            ->where('balance', '>', 0)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get()
            ->map(function (Transaction $txn) {
                $balance = (float) ($txn->balance ?? 0);
                return [
                    'id' => 'txn:' . $txn->id,
                    'transaction_id' => $txn->id,
                    'purchase_id' => $txn->id,
                    'date' => $txn->date ? \Carbon\Carbon::parse($txn->date)->format('d/m/Y') : '-',
                    'type' => ucfirst(str_replace('_', ' ', $txn->type ?? 'Transaction')),
                    'ref_no' => $txn->number ?: ('T-' . $txn->id),
                    'total' => round((float) ($txn->total ?? 0), 2),
                    'balance' => round($balance, 2),
                    'linked_amount' => 0,
                ];
            });

        $rows = $salesRows->merge($transactionRows)
            ->sortByDesc('date')
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

    public function storePaymentOut(Request $request)
    {
        $data = $request->validate([
            'party_id' => ['nullable', 'exists:parties,id'],
            'broker_id' => ['nullable', 'exists:brokers,id'],
            'payment_type' => ['required', 'string', 'max:80'],
            'bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'total' => ['nullable', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:255'],
            'receipt_no' => ['nullable', 'string', 'max:255'],
            'payment_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'entity_type' => ['nullable', 'in:party,item,broker'],
            'entity_name' => ['nullable', 'string', 'max:255'],
            'item_id' => ['nullable', 'exists:items,id'],
            'linked_rows' => ['nullable', 'array'],
            'linked_rows.*.purchase_id' => ['nullable', 'integer'],
            'linked_rows.*.sale_id' => ['nullable', 'integer'],
            'linked_rows.*.transaction_id' => ['nullable', 'integer'],
            'linked_rows.*.amount' => ['required_with:linked_rows', 'numeric', 'min:0.01'],
        ]);

        $amount = round((float) $data['amount'], 2);
        $discount = round((float) ($data['discount'] ?? 0), 2);
        $total = round((float) ($data['total'] ?? max($amount - $discount, 0)), 2);
        $paymentDate = !empty($data['payment_date']) ? Carbon::parse($data['payment_date']) : now();
        $balance = max($total - $amount, 0);
        $status = $balance <= 0 ? 'paid' : ($amount > 0 ? 'partial' : 'unpaid');
        $entityName = trim((string) ($data['entity_name'] ?? ''));
        if (empty($data['party_id']) && empty($data['broker_id']) && empty($data['item_id']) && $entityName === '') {
            return redirect()->route('payment-out')->withErrors(['party_id' => 'Please select a party, broker, or item.']);
        }

        $party = !empty($data['party_id']) ? Party::query()->find($data['party_id']) : null;
        $broker = !empty($data['broker_id']) ? Broker::query()->find($data['broker_id']) : null;
        $item = !empty($data['item_id']) ? Item::query()->find($data['item_id']) : null;
        $displayName = $party?->name ?: ($broker?->name ?: ($item?->name ?: ($entityName !== '' ? $entityName : 'Payment Out')));
        $paymentType = trim((string) $data['payment_type']);
        $bankAccountId = $data['bank_account_id'] ?? null;
        if (str_starts_with($paymentType, 'bank:')) {
            $bankAccountId = (int) str_replace('bank:', '', $paymentType);
            $paymentType = 'Bank';
        }
        $bank = $bankAccountId ? BankAccount::query()->find($bankAccountId) : null;
        $cashAccount = null;

        if (strtolower($paymentType) === 'cash' && !$bankAccountId) {
            $cashAccount = BankAccount::cashAccount();
            $bankAccountId = $cashAccount->id;
        }

        $savedTransaction = null;

        DB::transaction(function () use (
            $data,
            $party,
            $broker,
            $amount,
            $discount,
            $total,
            $balance,
            $status,
            $paymentDate,
            $paymentType,
            $bankAccountId,
            $bank,
            $cashAccount,
            $entityName,
            $displayName,
            $item,
            &$savedTransaction
        ) {
            $descriptionParts = ['Payment Out'];
            if (!empty($data['reference'])) {
                $descriptionParts[] = 'Ref: ' . $data['reference'];
            }
            if ($discount > 0) {
                $descriptionParts[] = 'Discount: ' . number_format($discount, 2);
            }
            if ($entityName !== '') {
                $descriptionParts[] = 'Entity: ' . $entityName;
            }

            $savedTransaction = Transaction::create([
                'party_id' => $party?->id,
                'broker_id' => $broker?->id,
                'item_id' => $item?->id,
                'bank_account_id' => $bankAccountId,
                'payment_type' => $bank?->display_name ?: $paymentType,
                'type' => 'payment_out',
                'number' => $data['receipt_no'] ?? null,
                'date' => $paymentDate->toDateString(),
                'total' => $total,
                'credit' => $amount,
                'paid_amount' => $amount,
                'balance' => $balance,
                'running_balance' => $balance,
                'status' => $status,
                'description' => trim(implode(' | ', $descriptionParts)),
            ]);

            $linkedRows = collect($data['linked_rows'] ?? [])
                ->map(function ($row) {
                    return [
                        'purchase_id' => (int) ($row['purchase_id'] ?? 0),
                        'sale_id' => (int) ($row['sale_id'] ?? 0),
                        'transaction_id' => (int) ($row['transaction_id'] ?? 0),
                        'amount' => round((float) ($row['amount'] ?? 0), 2),
                    ];
                })
                ->filter(fn ($row) => ($row['purchase_id'] > 0 || $row['sale_id'] > 0 || $row['transaction_id'] > 0) && $row['amount'] > 0)
                ->values();

            if ($linkedRows->isNotEmpty()) {
                foreach ($linkedRows as $linkedRow) {
                    // Handle Purchase links
                    if ($linkedRow['purchase_id'] > 0) {
                        $purchase = Purchase::query()->lockForUpdate()->find($linkedRow['purchase_id']);
                        if (!$purchase) continue;

                        $purchaseGrandTotal = (float) ($purchase->grand_total ?? $purchase->total_amount ?? 0);
                        $purchasePaid = (float) ($purchase->paid_amount ?? 0);
                        $purchaseBalance = max(0, $purchaseGrandTotal - $purchasePaid);
                        $allocate = min($linkedRow['amount'], $purchaseBalance);

                        if ($allocate > 0) {
                            PurchasePayment::create([
                                'purchase_id' => $purchase->id,
                                'payment_type' => $paymentType,
                                'bank_account_id' => $bankAccountId,
                                'amount' => $allocate,
                                'reference' => 'payment_out:' . $savedTransaction->id,
                                'receipt_no' => $data['receipt_no'] ?? null,
                            ]);

                            $newPaidAmount = round($purchasePaid + $allocate, 2);
                            $newBalance = max(0, round($purchaseGrandTotal - $newPaidAmount, 2));
                            $purchase->update([
                                'paid_amount' => $newPaidAmount,
                                'balance' => $newBalance,
                                'status' => $newBalance <= 0 ? 'paid' : ($newPaidAmount > 0 ? 'partial' : 'unpaid'),
                            ]);
                        }
                    }

                    // Handle Sale links
                    if ($linkedRow['sale_id'] > 0) {
                        $sale = \DB::table('sales')->lockForUpdate()->find($linkedRow['sale_id']);
                        if (!$sale) continue;

                        $saleGrandTotal = (float) ($sale->grand_total ?? $sale->total_amount ?? 0);
                        $saleReceived = (float) ($sale->received_amount ?? 0);
                        $saleBalance = max(0, $saleGrandTotal - $saleReceived);
                        $allocate = min($linkedRow['amount'], $saleBalance);

                        if ($allocate > 0) {
                            $newReceivedAmount = round($saleReceived + $allocate, 2);
                            $newBalance = max(0, round($saleGrandTotal - $newReceivedAmount, 2));
                            \DB::table('sales')->where('id', $sale->id)->update([
                                'received_amount' => $newReceivedAmount,
                                'balance' => $newBalance,
                                'status' => $newBalance <= 0 ? 'paid' : ($newReceivedAmount > 0 ? 'partial' : 'unpaid'),
                                'updated_at' => now(),
                            ]);
                        }
                    }

                    // Handle Transaction links
                    if ($linkedRow['transaction_id'] > 0) {
                        $txn = Transaction::query()->lockForUpdate()->find($linkedRow['transaction_id']);
                        if (!$txn) continue;

                        $txnTotal = (float) ($txn->total ?? 0);
                        $txnPaid = (float) ($txn->paid_amount ?? 0);
                        $txnBalance = max(0, $txnTotal - $txnPaid);
                        $allocate = min($linkedRow['amount'], $txnBalance);

                        if ($allocate > 0) {
                            $newPaidAmount = round($txnPaid + $allocate, 2);
                            $newBalance = max(0, round($txnTotal - $newPaidAmount, 2));
                            $txn->update([
                                'paid_amount' => $newPaidAmount,
                                'balance' => $newBalance,
                                'status' => $newBalance <= 0 ? 'paid' : ($newPaidAmount > 0 ? 'partial' : 'unpaid'),
                            ]);
                        }
                    }
                }
            }

            if ($bankAccountId && ($bank || $cashAccount)) {
                $bankModel = $bank ?: $cashAccount;
                $bankModel->opening_balance = (float) ($bankModel->opening_balance ?? 0) - $amount;
                $bankModel->save();

                BankTransaction::create([
                    'from_bank_account_id' => $bankModel->id,
                    'to_bank_account_id' => null,
                    'type' => 'payment_out',
                    'amount' => $amount,
                    'transaction_date' => $paymentDate->toDateString(),
                    'reference_type' => 'payment_out',
                    'reference_id' => $savedTransaction->id,
                    'description' => 'Payment Out to ' . $displayName,
                    'meta' => [
                        'party_id' => $party?->id,
                        'broker_id' => $broker?->id,
                        'item_id' => $item?->id,
                        'payment_type' => $paymentType,
                        'reference' => $data['reference'] ?? null,
                    ],
                ]);
            }
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment out saved successfully.',
                'payment' => [
                    'id' => $savedTransaction?->id,
                    'date' => optional($savedTransaction?->date)->format('Y-m-d') ?: $paymentDate->toDateString(),
                    'receiptNo' => $savedTransaction?->number,
                    'party' => $displayName,
                    'party_id' => $party?->id,
                    'broker' => $broker?->name,
                    'item_id' => $item?->id,
                    'item' => $item?->name,
                    'paymentType' => $paymentType,
                    'payType' => $paymentType,
                    'bankAccountId' => $bankAccountId,
                    'amount' => $amount,
                    'discount' => $discount,
                    'total' => $total,
                    'status' => $status,
                    'reference' => $data['reference'] ?? '',
                    'description' => $savedTransaction?->description ?? '',
                ],
            ]);
        }

        return redirect()->route('payment-out')->with('success', 'Payment out saved successfully.');
    }




    public function purchaseReturn()
    {
        return view('dashboard.purchases.purchase-return');
    }


}
