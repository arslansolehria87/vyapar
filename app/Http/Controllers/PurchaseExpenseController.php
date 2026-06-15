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
        $existingLinks = collect();

        if ($paymentOutId > 0) {
            $existingLinks = PurchasePayment::query()
                ->selectRaw('purchase_id, SUM(amount) as linked_amount')
                ->where('reference', 'payment_out:' . $paymentOutId)
                ->groupBy('purchase_id')
                ->pluck('linked_amount', 'purchase_id');
        }

        $purchases = Purchase::query()
            ->whereIn('type', ['purchase_bill', 'purchase'])
            ->where(function ($query) use ($party) {
                $query->where('party_id', $party->id);

                if (!empty($party->name)) {
                    $query->orWhereRaw('LOWER(COALESCE(party_name, "")) = ?', [mb_strtolower($party->name)]);
                }
            })
            ->where(function ($query) use ($existingLinks) {
                $query->whereRaw('(COALESCE(balance, 0) > 0 OR (COALESCE(grand_total, total_amount, 0) - COALESCE(paid_amount, 0)) > 0)');

                if ($existingLinks->isNotEmpty()) {
                    $query->orWhereIn('id', $existingLinks->keys());
                }
            })
            ->orderByDesc('bill_date')
            ->orderByDesc('id')
            ->get()
            ->map(function (Purchase $purchase) use ($existingLinks) {
                $existingAmount = (float) ($existingLinks[$purchase->id] ?? 0);
                $currentBalance = (float) ($purchase->balance ?? max(0, (float) ($purchase->grand_total ?? 0) - (float) ($purchase->paid_amount ?? 0)));
                $availableBalance = $currentBalance + $existingAmount;

                return [
                    'purchase_id' => $purchase->id,
                    'date' => optional($purchase->bill_date)->format('d/m/Y') ?: '-',
                    'type' => 'Purchase Bill',
                    'ref_no' => $purchase->bill_number ?: ('PB-' . $purchase->id),
                    'total' => round((float) ($purchase->grand_total ?? $purchase->total_amount ?? 0), 2),
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
            'rows' => $purchases,
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
            'linked_rows.*.purchase_id' => ['required_with:linked_rows', 'exists:purchases,id'],
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
                        'amount' => round((float) ($row['amount'] ?? 0), 2),
                    ];
                })
                ->filter(fn ($row) => $row['purchase_id'] > 0 && $row['amount'] > 0)
                ->values();

            if ($linkedRows->isNotEmpty()) {
                foreach ($linkedRows as $linkedRow) {
                    $purchase = Purchase::query()->lockForUpdate()->findOrFail($linkedRow['purchase_id']);
                    $purchaseGrandTotal = (float) ($purchase->grand_total ?? $purchase->total_amount ?? 0);
                    $purchasePaid = (float) ($purchase->paid_amount ?? 0);
                    $purchaseBalance = max(0, $purchaseGrandTotal - $purchasePaid);
                    $allocate = min($linkedRow['amount'], $purchaseBalance);

                    if ($allocate <= 0) {
                        continue;
                    }

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
