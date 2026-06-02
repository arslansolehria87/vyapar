<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\AppSetting;
use App\Models\BankTransaction;
use App\Models\PurchasePayment;
use App\Models\SalePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BankAccountController extends Controller
{
    public function index()
    {
        $bankAccounts = BankAccount::orderByDesc('created_at')->get();

        $saleTransactions = SalePayment::with(['sale.party', 'bankAccount'])
            ->whereNotNull('bank_account_id')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($payment) {
                $typeLabel = match ($payment->sale?->type) {
                    'invoice' => 'Sale Invoice',
                    'estimate' => 'Estimate',
                    'sale_order' => 'Sale Order',
                    'proforma' => 'Proforma',
                    'delivery_challan' => 'Delivery Challan',
                    'sale_return' => 'Sale Return',
                    'pos' => 'POS',
                    default => ucfirst(str_replace('_', ' ', $payment->sale?->type ?? 'Unknown')),
                };

                return (object) [
                    'bank_account_id' => $payment->bank_account_id,
                    'source_id' => $payment->sale?->id,
                    'source_type' => $payment->sale?->type,
                    'source_url' => $payment->sale ? $this->transactionEditUrl('sale', $payment->sale) : null,
                    'delete_url' => $payment->sale ? $this->transactionDeleteUrl('sale', $payment->sale) : null,
                    'history_url' => $payment->sale ? $this->transactionHistoryUrl('sale', $payment->sale) : null,
                    'type_label' => $typeLabel,
                    'invoice_no' => $payment->sale?->bill_number ?? '-',
                    'party_name' => $payment->sale?->display_party_name ?? '-',
                    'bank_name' => $payment->bankAccount?->display_name ?? $payment->bankAccount?->bank_name ?? '-',
                    'payment_type' => $payment->payment_type ?? '-',
                    'created_at' => $payment->created_at,
                    'amount' => (float) ($payment->amount ?? 0),
                    'direction' => $payment->sale?->type === 'sale_return' ? 'out' : 'in',
                ];
            });

        $purchaseTransactions = PurchasePayment::with(['purchase.party', 'bankAccount'])
            ->whereNotNull('bank_account_id')
            ->whereHas('purchase', fn ($query) => $query->whereIn('type', ['purchase_bill', 'purchase_return']))
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($payment) {
                $typeLabel = match ($payment->purchase?->type) {
                    'purchase_bill' => 'Purchase Bill',
                    'purchase_return' => 'Purchase Return',
                    'purchase_order' => 'Purchase Order',
                    default => ucfirst(str_replace('_', ' ', $payment->purchase?->type ?? 'Unknown')),
                };

                return (object) [
                    'bank_account_id' => $payment->bank_account_id,
                    'source_id' => $payment->purchase?->id,
                    'source_type' => $payment->purchase?->type,
                    'source_url' => $payment->purchase ? $this->transactionEditUrl('purchase', $payment->purchase) : null,
                    'delete_url' => $payment->purchase ? $this->transactionDeleteUrl('purchase', $payment->purchase) : null,
                    'history_url' => $payment->purchase ? $this->transactionHistoryUrl('purchase', $payment->purchase) : null,
                    'type_label' => $typeLabel,
                    'invoice_no' => $payment->purchase?->bill_number ?? '-',
                    'party_name' => $payment->purchase?->party?->name
                        ?? $payment->purchase?->party_name
                        ?? '-',
                    'bank_name' => $payment->bankAccount?->display_name ?? $payment->bankAccount?->bank_name ?? '-',
                    'payment_type' => $payment->payment_type ?? '-',
                    'created_at' => $payment->created_at,
                    'amount' => (float) ($payment->amount ?? 0),
                    'direction' => $payment->purchase?->type === 'purchase_return' ? 'in' : 'out',
                ];
            });

        $transferTransactions = BankTransaction::with(['fromBankAccount', 'toBankAccount'])
            ->whereIn('type', [
                'bank_to_bank',
                'bank_transfer_out',
                'bank_transfer_in',
                'bank_to_cash',
                'cash_to_bank',
                'adjust_balance',
                'bank_adjust_in',
                'bank_adjust_out',
                'opening_balance',
            ])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get()
            ->flatMap(function ($transaction) {
                $meta = $transaction->meta ?? [];
                $rows = collect();
                $base = [
                    'source_id' => $transaction->id,
                    'source_type' => $transaction->type,
                    'source_url' => null,
                    'delete_url' => null,
                    'history_url' => null,
                    'invoice_no' => '-',
                    'party_name' => '-',
                    'payment_type' => 'Bank Transfer',
                    'created_at' => $transaction->transaction_date ?? $transaction->created_at,
                    'amount' => (float) ($transaction->amount ?? 0),
                ];

                if ($transaction->type === 'bank_to_bank') {
                    if ($transaction->from_bank_account_id) {
                        $rows->push((object) array_merge($base, [
                            'bank_account_id' => $transaction->from_bank_account_id,
                            'type_label' => 'Bank Transfer Out',
                            'bank_name' => $transaction->fromBankAccount?->display_name ?? $meta['from_bank_name'] ?? '-',
                            'party_name' => $transaction->toBankAccount?->display_name ?? $meta['to_bank_name'] ?? '-',
                            'direction' => 'out',
                        ]));
                    }
                    if ($transaction->to_bank_account_id) {
                        $rows->push((object) array_merge($base, [
                            'bank_account_id' => $transaction->to_bank_account_id,
                            'type_label' => 'Bank Transfer In',
                            'bank_name' => $transaction->toBankAccount?->display_name ?? $meta['to_bank_name'] ?? '-',
                            'party_name' => $transaction->fromBankAccount?->display_name ?? $meta['from_bank_name'] ?? '-',
                            'direction' => 'in',
                        ]));
                    }

                    return $rows;
                }

                $isOut = (bool) $transaction->from_bank_account_id;
                if ($transaction->type === 'cash_to_bank') {
                    $isOut = false;
                }
                $bank = $isOut ? $transaction->fromBankAccount : $transaction->toBankAccount;
                $otherBank = $isOut ? $transaction->toBankAccount : $transaction->fromBankAccount;

                return collect([(object) array_merge($base, [
                    'bank_account_id' => $isOut ? $transaction->from_bank_account_id : $transaction->to_bank_account_id,
                    'type_label' => match ($transaction->type) {
                        'bank_transfer_out' => 'Bank Transfer Out',
                        'bank_transfer_in' => 'Bank Transfer In',
                        'bank_to_cash' => 'Bank To Cash',
                        'cash_to_bank' => 'Cash To Bank',
                        'adjust_balance' => 'Bank Adjustment',
                        'bank_adjust_in' => 'Bank Adjustment In',
                        'bank_adjust_out' => 'Bank Adjustment Out',
                        'opening_balance' => 'Opening Balance',
                        default => ucwords(str_replace('_', ' ', $transaction->type)),
                    },
                    'bank_name' => $bank?->display_name ?? '-',
                    'party_name' => $otherBank?->display_name ?? $transaction->description ?? '-',
                    'direction' => $isOut ? 'out' : 'in',
                ])]);
            });

        $bankTransactions = $saleTransactions
            ->concat($purchaseTransactions)
            ->concat($transferTransactions)
            ->sortByDesc(fn ($transaction) => $transaction->created_at?->timestamp ?? 0)
            ->values();

        return view('dashboard.accounts.bank', compact('bankAccounts', 'bankTransactions'));
    }

    private function transactionEditUrl(string $source, object $record): string
    {
        if ($source === 'purchase') {
            return match ($record->type) {
                'purchase_return' => route('purchase-return.edit', $record),
                'purchase_order' => route('purchase-orders.edit', $record),
                default => route('purchase-bills.edit', $record),
            };
        }

        return match ($record->type) {
            'estimate' => route('estimates.edit', $record),
            'proforma' => route('proforma-invoice.edit', $record),
            'delivery_challan' => route('delivery-challan.edit', $record),
            'sale_return' => route('sale-return.edit', $record),
            default => route('sale.edit', $record),
        };
    }

    private function transactionDeleteUrl(string $source, object $record): string
    {
        if ($source === 'purchase') {
            return match ($record->type) {
                'purchase_return' => route('purchase-return.destroy', $record),
                'purchase_order' => route('purchase-orders.destroy', $record),
                default => route('purchase-bills.destroy', $record),
            };
        }

        return match ($record->type) {
            'estimate' => route('estimates.destroy', $record),
            'proforma' => route('proforma-invoice.destroy', $record),
            'delivery_challan' => route('delivery-challan.destroy', $record),
            'sale_return' => route('sale-return.destroy', $record),
            default => route('sale.destroy', $record),
        };
    }

    private function transactionHistoryUrl(string $source, object $record): string
    {
        if ($source === 'purchase') {
            return $record->type === 'purchase_order'
                ? route('purchase-orders.history', $record)
                : $this->transactionEditUrl($source, $record);
        }

        return match ($record->type) {
            'invoice' => route('sale.bank-history', $record),
            'sale_return' => route('sale-return.bank-history', $record),
            default => $this->transactionEditUrl($source, $record),
        };
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'display_name' => 'required|string|max:255',
            'opening_balance' => 'nullable|numeric',
            'as_of_date' => 'nullable|date',
            'account_number' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_holder_name' => 'nullable|string|max:255',
            'print_on_invoice' => 'nullable|boolean',
        ]);

        $data['print_on_invoice'] = $request->has('print_on_invoice');
        $data['is_active'] = $request->boolean('is_active', true);

        $bankAccount = DB::transaction(function () use ($data) {
            $bankAccount = BankAccount::create($data);
            $openingBalance = (float) ($bankAccount->opening_balance ?? 0);

            if ($openingBalance != 0.0) {
                BankTransaction::create([
                    'from_bank_account_id' => $openingBalance < 0 ? $bankAccount->id : null,
                    'to_bank_account_id' => $openingBalance >= 0 ? $bankAccount->id : null,
                    'type' => 'opening_balance',
                    'amount' => abs($openingBalance),
                    'transaction_date' => $bankAccount->as_of_date ?? now()->toDateString(),
                    'description' => 'Opening balance',
                    'meta' => [
                        'bank_name' => $bankAccount->display_name,
                    ],
                ]);
            }

            return $bankAccount;
        });

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            $bankAccount->refresh();

            return response()->json([
                'message' => 'Bank account added successfully.',
                'id' => $bankAccount->id,
                'name' => $bankAccount->display_with_account,
                'bank' => $bankAccount,
            ]);
        }

        return redirect()->route('bank-accounts')->with('success', 'Bank account added successfully.');
    }

    public function show(BankAccount $bankAccount)
    {
        // Return JSON for viewing/editing via AJAX.
        if (request()->wantsJson()) {
            return response()->json($bankAccount);
        }

        // Fallback: redirect to listing if accessed via browser.
        return redirect()->route('bank-accounts');
    }

    public function edit(BankAccount $bankAccount)
    {
        // Return JSON for JS-powered edit form.
        return response()->json($bankAccount);
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $data = $request->validate([
            'display_name' => 'required|string|max:255',
            'opening_balance' => 'nullable|numeric',
            'as_of_date' => 'nullable|date',
            'account_number' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_holder_name' => 'nullable|string|max:255',
            'print_on_invoice' => 'nullable|boolean',
        ]);

        $data['print_on_invoice'] = $request->has('print_on_invoice');
        $data['is_active'] = $request->boolean('is_active', $bankAccount->is_active);

        $bankAccount->update($data);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Bank account updated successfully.', 'bank' => $bankAccount]);
        }

        return redirect()->route('bank-accounts')->with('success', 'Bank account updated successfully.');
    }

    public function destroy(BankAccount $bankAccount)
    {
        $bankAccount->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Bank account deleted successfully.']);
        }

        return redirect()->route('bank-accounts')->with('success', 'Bank account deleted successfully.');
    }

    public function transfer(Request $request)
    {
        $data = $request->validate([
            'mode' => 'required|in:bank_to_bank,bank_to_cash,cash_to_bank,adjust_balance',
            'from_bank_id' => 'nullable|exists:bank_accounts,id',
            'to_bank_id' => 'nullable|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'nullable|date',
            'description' => 'nullable|string|max:500',
            'adjust_type' => 'nullable|in:increase,decrease',
        ]);

        if ($data['mode'] === 'bank_to_bank' && (int) ($data['from_bank_id'] ?? 0) === (int) ($data['to_bank_id'] ?? 0)) {
            return response()->json([
                'message' => 'Source and destination bank must be different.',
            ], 422);
        }

        $amount = (float) $data['amount'];
        $date = $data['date'] ?? now()->toDateString();
        $description = $data['description'] ?? null;

        if ($data['mode'] === 'bank_to_bank') {
            $fromBank = BankAccount::findOrFail($data['from_bank_id']);
            $toBank = BankAccount::findOrFail($data['to_bank_id']);

            if ((float) ($fromBank->opening_balance ?? 0) < $amount) {
                return response()->json([
                    'message' => 'Insufficient balance in source bank.',
                ], 422);
            }

            DB::transaction(function () use ($fromBank, $toBank, $amount, $date, $description) {
                $fromBank->opening_balance = (float) ($fromBank->opening_balance ?? 0) - $amount;
                $toBank->opening_balance = (float) ($toBank->opening_balance ?? 0) + $amount;

                $fromBank->save();
                $toBank->save();
                $transferGroup = 'bank-transfer-' . now()->format('YmdHis') . '-' . uniqid();

                BankTransaction::create([
                    'from_bank_account_id' => $fromBank->id,
                    'to_bank_account_id' => null,
                    'type' => 'bank_transfer_out',
                    'amount' => $amount,
                    'transaction_date' => $date,
                    'description' => $description ?: 'Bank to bank transfer sent',
                    'meta' => [
                        'from_bank_name' => $fromBank->display_name,
                        'to_bank_name' => $toBank->display_name,
                        'transfer_group' => $transferGroup,
                    ],
                ]);

                BankTransaction::create([
                    'from_bank_account_id' => null,
                    'to_bank_account_id' => $toBank->id,
                    'type' => 'bank_transfer_in',
                    'amount' => $amount,
                    'transaction_date' => $date,
                    'description' => $description ?: 'Bank to bank transfer received',
                    'meta' => [
                        'from_bank_name' => $fromBank->display_name,
                        'to_bank_name' => $toBank->display_name,
                        'transfer_group' => $transferGroup,
                    ],
                ]);
            });

            return response()->json([
                'message' => 'Bank to bank transfer completed successfully.',
                'from_bank_balance' => $fromBank->fresh()->opening_balance,
                'to_bank_balance' => $toBank->fresh()->opening_balance,
            ]);
        }

        if ($data['mode'] === 'bank_to_cash') {
            $fromBank = BankAccount::findOrFail($data['from_bank_id']);
            $cashAccount = BankAccount::cashAccount();

            if ((float) ($fromBank->opening_balance ?? 0) < $amount) {
                return response()->json(['message' => 'Insufficient balance in source bank.'], 422);
            }

            DB::transaction(function () use ($fromBank, $cashAccount, $amount, $date, $description) {
                $fromBank->opening_balance = (float) ($fromBank->opening_balance ?? 0) - $amount;
                $cashAccount->opening_balance = (float) ($cashAccount->opening_balance ?? 0) + $amount;
                $fromBank->save();
                $cashAccount->save();

                BankTransaction::create([
                    'from_bank_account_id' => $fromBank->id,
                    'to_bank_account_id' => $cashAccount->id,
                    'type' => 'bank_to_cash',
                    'amount' => $amount,
                    'transaction_date' => $date,
                    'description' => $description ?: 'Bank to cash transfer',
                ]);
            });

            return response()->json(['message' => 'Bank to cash transfer completed successfully.']);
        }

        if ($data['mode'] === 'cash_to_bank') {
            $cashAccount = BankAccount::cashAccount();
            $toBank = BankAccount::findOrFail($data['to_bank_id']);

            if ((float) ($cashAccount->opening_balance ?? 0) < $amount) {
                return response()->json(['message' => 'Insufficient cash balance.'], 422);
            }

            DB::transaction(function () use ($cashAccount, $toBank, $amount, $date, $description) {
                $cashAccount->opening_balance = (float) ($cashAccount->opening_balance ?? 0) - $amount;
                $toBank->opening_balance = (float) ($toBank->opening_balance ?? 0) + $amount;
                $cashAccount->save();
                $toBank->save();

                BankTransaction::create([
                    'from_bank_account_id' => $cashAccount->id,
                    'to_bank_account_id' => $toBank->id,
                    'type' => 'cash_to_bank',
                    'amount' => $amount,
                    'transaction_date' => $date,
                    'description' => $description ?: 'Cash to bank transfer',
                ]);
            });

            return response()->json(['message' => 'Cash to bank transfer completed successfully.']);
        }

        $adjustBankId = $data['to_bank_id'] ?? $data['from_bank_id'] ?? null;
        if (!$adjustBankId) {
            return response()->json(['message' => 'Please select a bank account.'], 422);
        }
        $bank = BankAccount::findOrFail($adjustBankId);
        $isIncrease = ($data['adjust_type'] ?? 'increase') === 'increase';

        if (!$isIncrease && (float) ($bank->opening_balance ?? 0) < $amount) {
            return response()->json(['message' => 'Bank balance cannot go below zero.'], 422);
        }

        DB::transaction(function () use ($bank, $amount, $date, $description, $isIncrease) {
            $bank->opening_balance = $isIncrease
                ? (float) ($bank->opening_balance ?? 0) + $amount
                : (float) ($bank->opening_balance ?? 0) - $amount;
            $bank->save();

            BankTransaction::create([
                'from_bank_account_id' => $isIncrease ? null : $bank->id,
                'to_bank_account_id' => $isIncrease ? $bank->id : null,
                'type' => $isIncrease ? 'bank_adjust_in' : 'bank_adjust_out',
                'amount' => $amount,
                'transaction_date' => $date,
                'description' => $description ?: ($isIncrease ? 'Bank balance increased' : 'Bank balance decreased'),
            ]);
        });

        return response()->json(['message' => 'Bank adjustment saved successfully.']);
    }

    public function cashInHand()
    {
        $cashAccount = BankAccount::cashAccount();
        $cashTransactions = BankTransaction::where('from_bank_account_id', $cashAccount->id)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        return view('dashboard.accounts.cash-hand', compact('cashAccount', 'cashTransactions'));
    }
  public function paymentIn(Request $request)
{
    $data = $request->validate([
        'party_id'                   => 'required|exists:parties,id',
        'payments'                   => 'required|array',
        'payments.*.type'            => 'required|string',
        'payments.*.amount'          => 'required|numeric|min:0',
        'payments.*.bank_account_id' => 'nullable|exists:bank_accounts,id',
        'reference_no'               => 'nullable|string',
        'receipt_no'                 => 'nullable|string',
        'date'                       => 'nullable|date',
    ]);

    $totalAmount = collect($data['payments'])->sum('amount');

    // ✅ 1. PaymentIn record save karo
    foreach ($data['payments'] as $payment) {
        \App\Models\PaymentIn::create([
            'party_id'        => $data['party_id'],
            'bank_account_id' => !empty($payment['bank_account_id']) ? $payment['bank_account_id'] : null,
            'amount'          => $payment['amount'],
            'payment_type'    => $payment['type'],
            'reference_no'    => $data['reference_no'] ?? null,
            'receipt_no'      => $data['receipt_no'] ?? null,
            'date'            => $data['date'] ?? now(),
        ]);
    }

    // ✅ 2. Party balance minus karo
    $party = \App\Models\Party::findOrFail($data['party_id']);
    $party->opening_balance = ($party->opening_balance ?? 0) - $totalAmount;
    $party->save();

    // ✅ 3. Bank balance add karo — bank_account_id check properly
    foreach ($data['payments'] as $payment) {
        $bankId = $payment['bank_account_id'] ?? null;

        if (!empty($bankId) && is_numeric($bankId)) { // ✅ Proper check
            $bank = BankAccount::find($bankId);
            if ($bank) {
                $bank->opening_balance = ($bank->opening_balance ?? 0) + floatval($payment['amount']);
                $bank->save();
            }
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'Payment recorded successfully.'
    ]);
}

    public function bulkStatus(Request $request)
    {
        $data = $request->validate([
            'bank_ids' => ['required', 'array', 'min:1'],
            'bank_ids.*' => ['integer', 'exists:bank_accounts,id'],
            'is_active' => ['required', 'boolean'],
            'password' => ['nullable', 'string'],
        ]);

        if ($data['is_active']) {
            $storedPasswordHash = AppSetting::getValue('bank_account_password');

            if (!$storedPasswordHash) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please set Bank Account Password in General Settings first.',
                ], 422);
            }

            if (empty($data['password']) || !Hash::check($data['password'], $storedPasswordHash)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incorrect bank account password.',
                ], 422);
            }
        }

        $updated = BankAccount::whereIn('id', $data['bank_ids'])->update([
            'is_active' => $data['is_active'],
        ]);

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'message' => $data['is_active']
                ? 'Selected bank accounts marked active.'
                : 'Selected bank accounts marked inactive.',
        ]);
    }
    public function adjustCash(Request $request)
{
    $request->validate([
        'type'        => 'required|in:add,reduce',
        'amount'      => 'required|numeric|min:0.01',
        'date'        => 'required|date',
        'description' => 'nullable|string|max:500',
    ]);

    try {
        DB::transaction(function () use ($request) {
            $cashAccount = BankAccount::cashAccount();
            $amount      = (float) $request->amount;
            $isAdd       = $request->type === 'add';

            if (!$isAdd && ($cashAccount->opening_balance - $amount) < 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'amount' => ['Cash cannot go below zero. Current balance: Rs ' . number_format($cashAccount->opening_balance, 0)],
                ]);
            }

            $cashAccount->opening_balance = $isAdd
                ? (float) $cashAccount->opening_balance + $amount
                : (float) $cashAccount->opening_balance - $amount;
            $cashAccount->save();

            BankTransaction::create([
                'from_bank_account_id' => $isAdd ? null : $cashAccount->id,
                'to_bank_account_id'   => $isAdd ? $cashAccount->id : null,
                'type'                 => $isAdd ? 'cash_adjust_in' : 'cash_adjust_out',
                'amount'               => $amount,
                'transaction_date'     => $request->date,
                'reference_type'       => 'adjustment',
                'reference_id'         => null,
                'description'          => $request->description ?: ($isAdd ? 'Cash Added (Adjustment)' : 'Cash Reduced (Adjustment)'),
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Cash adjusted successfully.']);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first()], 422);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
}
