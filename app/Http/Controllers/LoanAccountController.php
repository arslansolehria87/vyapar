<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\LoanAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanAccountController extends Controller
{
    public function index()
    {
        BankAccount::cashAccount();

        $loanAccounts = LoanAccount::with(['lenderBank', 'receivedInBank', 'processingFeeBank'])->orderByDesc('created_at')->get();
        $banks = BankAccount::active()->orderBy('display_name')->get();
        $loanIds = $loanAccounts->pluck('id');
        $loanTransactions = BankTransaction::with(['toBankAccount', 'fromBankAccount'])
            ->where('reference_type', LoanAccount::class)
            ->whereIn('reference_id', $loanIds)
            ->whereIn('type', ['loan_more', 'loan_adjustment', 'loan_charge', 'emi_pay'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy('reference_id');

        return view('dashboard.accounts.loan', compact('loanAccounts', 'banks', 'loanTransactions'));
    }

    public function show(LoanAccount $loanAccount)
    {
        // Return JSON for JS-powered edit form and other UI use.
        $loanAccount->load(['lenderBank', 'receivedInBank', 'processingFeeBank']);
        return response()->json($loanAccount);
    }

    public function allJson(Request $request)
    {
        $loanQuery = LoanAccount::query()
            ->with(['lenderBank', 'receivedInBank', 'processingFeeBank'])
            ->orderBy('display_name');

        if ($request->filled('account_id')) {
            $loanQuery->whereKey($request->integer('account_id'));
        }

        $loans = $loanQuery->get();

        if ($loans->isEmpty()) {
            return response()->json([
                'success' => true,
                'transactions' => [],
                'opening_balance' => 0,
                'balance_due' => 0,
                'principal_paid' => 0,
                'principal_due' => 0,
            ]);
        }

        $from = $request->filled('from') ? $request->date('from')->toDateString() : null;
        $to = $request->filled('to') ? $request->date('to')->toDateString() : null;
        $loanIds = $loans->pluck('id');
        $statementTypes = [
            'loan_more',
            'loan_adjustment',
            'loan_charge',
            'emi_pay',
            'loan_processing_fee',
            'loan_processing_fee_refund',
        ];

        $allTransactions = BankTransaction::with(['toBankAccount', 'fromBankAccount'])
            ->where('reference_type', LoanAccount::class)
            ->whereIn('reference_id', $loanIds)
            ->whereIn('type', $statementTypes)
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get()
            ->groupBy('reference_id');

        $rows = collect();
        $openingTotal = 0;
        $dueTotal = 0;
        $paidTotal = 0;

        foreach ($loans as $loan) {
            $loanTransactions = $allTransactions->get($loan->id, collect());
            $currentBalance = (float) ($loan->current_balance ?? 0);
            $originalBalance = $currentBalance - $loanTransactions->sum(fn ($tx) => $this->loanStatementBalanceDelta($tx));
            $balanceAtPeriodStart = $originalBalance;

            if ($from) {
                $balanceAtPeriodStart += $loanTransactions
                    ->filter(fn ($tx) => optional($tx->transaction_date)->toDateString() < $from)
                    ->sum(fn ($tx) => $this->loanStatementBalanceDelta($tx));
            }

            $openingTotal += $balanceAtPeriodStart;
            $dueTotal += $currentBalance;
            $paidTotal += $loanTransactions
                ->filter(fn ($tx) => $tx->type === 'emi_pay')
                ->sum(fn ($tx) => (float) (($tx->meta ?? [])['principal'] ?? 0));

            $runningBalance = $balanceAtPeriodStart;

            if (!$from || optional($loan->balance_as_of)->toDateString() >= $from) {
                $rows->push([
                    'id' => 'opening-' . $loan->id,
                    'account_id' => $loan->id,
                    'account_name' => $loan->display_name,
                    'date' => optional($loan->balance_as_of)->format('Y-m-d'),
                    'date_display' => optional($loan->balance_as_of)->format('d/m/Y'),
                    'type' => 'Opening Loan',
                    'details' => $loan->description ?: 'Opening balance',
                    'amount' => $originalBalance,
                    'principal' => $originalBalance,
                    'charges' => 0,
                    'ending_balance' => $originalBalance,
                ]);
            }

            foreach ($loanTransactions as $transaction) {
                $date = optional($transaction->transaction_date)->toDateString();
                if (($from && $date < $from) || ($to && $date > $to)) {
                    continue;
                }

                $runningBalance += $this->loanStatementBalanceDelta($transaction);
                $meta = $transaction->meta ?? [];

                $rows->push([
                    'id' => $transaction->id,
                    'account_id' => $loan->id,
                    'account_name' => $loan->display_name,
                    'date' => $date,
                    'date_display' => optional($transaction->transaction_date)->format('d/m/Y'),
                    'type' => $this->loanStatementLabel($transaction->type),
                    'details' => $meta['details'] ?? $transaction->description ?? '',
                    'amount' => (float) ($transaction->amount ?? 0),
                    'principal' => (float) ($meta['principal'] ?? 0),
                    'charges' => (float) ($meta['charges'] ?? 0),
                    'ending_balance' => $runningBalance,
                    'bank_name' => $transaction->toBankAccount?->display_with_account
                        ?: $transaction->fromBankAccount?->display_with_account
                        ?: '-',
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'transactions' => $rows
                ->sortBy([['date', 'asc'], ['id', 'asc']])
                ->values()
                ->toArray(),
            'opening_balance' => $openingTotal,
            'balance_due' => $dueTotal,
            'principal_paid' => $paidTotal,
            'principal_due' => $dueTotal,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'display_name' => 'required|string|max:255',
            'lender_bank_id' => 'nullable|exists:bank_accounts,id',
            'account_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'current_balance' => 'required|numeric',
            'balance_as_of' => 'nullable|date',
            'received_in' => 'required|exists:bank_accounts,id',
            'interest_rate' => 'nullable|numeric',
            'term_months' => 'nullable|integer',
            'processing_fee' => 'nullable|numeric',
            'processing_fee_paid_from_id' => 'nullable|exists:bank_accounts,id',
        ]);

        $data['processing_fee'] = $data['processing_fee'] ?? 0;

        DB::transaction(function () use ($data) {
            /** @var LoanAccount $loan */
            $loan = LoanAccount::create($data);

            // Deduct processing fee from the selected bank account.
            if ($data['processing_fee'] > 0 && !empty($data['processing_fee_paid_from_id'])) {
                $bank = BankAccount::lockForUpdate()->find($data['processing_fee_paid_from_id']);
                if ($bank) {
                    $bank->opening_balance -= $data['processing_fee'];
                    $bank->save();

                    BankTransaction::create([
                        'from_bank_account_id' => $bank->id,
                        'type' => 'loan_processing_fee',
                        'amount' => $data['processing_fee'],
                        'transaction_date' => $data['balance_as_of'] ?? now()->toDateString(),
                        'reference_type' => LoanAccount::class,
                        'reference_id' => $loan->id,
                        'description' => 'Loan processing fee deducted',
                        'meta' => [
                            'loan_name' => $loan->display_name,
                            'action' => 'deduct',
                        ],
                    ]);
                }
            }
        });

        return redirect()->route('loan-accounts')->with('success', 'Loan account added successfully.');
    }

    public function edit(LoanAccount $loanAccount)
    {
        // Return JSON for JS-powered edit form.
        return response()->json($loanAccount);
    }

    public function update(Request $request, LoanAccount $loanAccount)
    {
        $data = $request->validate([
            'display_name' => 'required|string|max:255',
            'lender_bank_id' => 'nullable|exists:bank_accounts,id',
            'account_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'current_balance' => 'required|numeric',
            'balance_as_of' => 'nullable|date',
            'received_in' => 'required|exists:bank_accounts,id',
            'interest_rate' => 'nullable|numeric',
            'term_months' => 'nullable|integer',
            'processing_fee' => 'nullable|numeric',
            'processing_fee_paid_from_id' => 'nullable|exists:bank_accounts,id',
        ]);

        $data['processing_fee'] = $data['processing_fee'] ?? 0;

        DB::transaction(function () use ($data, $loanAccount) {
            $oldFee = $loanAccount->processing_fee ?? 0;
            $oldFeeBankId = $loanAccount->processing_fee_paid_from_id;

            $loanAccount->update($data);

            $newFee = $data['processing_fee'];
            $newFeeBankId = $data['processing_fee_paid_from_id'] ?? null;

            // If fee bank changed, refund old fee to old bank first.
            if ($oldFeeBankId && $oldFeeBankId !== $newFeeBankId && $oldFee > 0) {
                $oldBank = BankAccount::lockForUpdate()->find($oldFeeBankId);
                if ($oldBank) {
                    $oldBank->opening_balance += $oldFee;
                    $oldBank->save();

                    BankTransaction::create([
                        'to_bank_account_id' => $oldBank->id,
                        'type' => 'loan_processing_fee_refund',
                        'amount' => $oldFee,
                        'transaction_date' => $data['balance_as_of'] ?? now()->toDateString(),
                        'reference_type' => LoanAccount::class,
                        'reference_id' => $loanAccount->id,
                        'description' => 'Loan processing fee refunded',
                        'meta' => [
                            'loan_name' => $loanAccount->display_name,
                            'action' => 'refund_old_fee',
                        ],
                    ]);
                }
            }

            // Adjust new fee bank by fee delta (handles same bank or new bank).
            if ($newFeeBankId && $newFee > 0) {
                $feeDelta = $newFee;
                if ($oldFeeBankId === $newFeeBankId) {
                    $feeDelta = $newFee - $oldFee;
                }
                if ($feeDelta !== 0) {
                    $bank = BankAccount::lockForUpdate()->find($newFeeBankId);
                    if ($bank) {
                        $bank->opening_balance -= $feeDelta;
                        $bank->save();

                        $transactionPayload = [
                            'type' => $feeDelta > 0 ? 'loan_processing_fee' : 'loan_processing_fee_refund',
                            'amount' => abs($feeDelta),
                            'transaction_date' => $data['balance_as_of'] ?? now()->toDateString(),
                            'reference_type' => LoanAccount::class,
                            'reference_id' => $loanAccount->id,
                            'description' => $feeDelta > 0 ? 'Loan processing fee deducted' : 'Loan processing fee adjustment reversal',
                            'meta' => [
                                'loan_name' => $loanAccount->display_name,
                                'action' => $feeDelta > 0 ? 'deduct_delta' : 'reverse_delta',
                            ],
                        ];

                        if ($feeDelta > 0) {
                            $transactionPayload['from_bank_account_id'] = $bank->id;
                        } else {
                            $transactionPayload['to_bank_account_id'] = $bank->id;
                        }

                        BankTransaction::create($transactionPayload);
                    }
                }
            }
        });

        return redirect()->route('loan-accounts')->with('success', 'Loan account updated successfully.');
    }

    public function destroy(LoanAccount $loanAccount)
    {
        DB::transaction(function () use ($loanAccount) {
            $fee = $loanAccount->processing_fee ?? 0;
            $feeBankId = $loanAccount->processing_fee_paid_from_id;

            $loanAccount->delete();

            if ($fee > 0 && $feeBankId) {
                $bank = BankAccount::lockForUpdate()->find($feeBankId);
                if ($bank) {
                    $bank->opening_balance += $fee;
                    $bank->save();

                    BankTransaction::create([
                        'to_bank_account_id' => $bank->id,
                        'type' => 'loan_processing_fee_refund',
                        'amount' => $fee,
                        'transaction_date' => now()->toDateString(),
                        'reference_type' => LoanAccount::class,
                        'reference_id' => $loanAccount->id,
                        'description' => 'Loan processing fee refunded on delete',
                        'meta' => [
                            'loan_name' => $loanAccount->display_name,
                            'action' => 'delete_refund',
                        ],
                    ]);
                }
            }
        });

        return redirect()->route('loan-accounts')->with('success', 'Loan account deleted successfully.');
    }

    public function storeTransaction(Request $request, LoanAccount $loanAccount)
    {
        $data = $request->validate([
            'entry_type' => 'required|in:loan_more,loan_adjustment,loan_charge,emi_pay',
            'amount' => 'required|numeric|min:0',
            'principal_amount' => 'nullable|required_if:entry_type,emi_pay|numeric|min:0',
            'interest_amount' => 'nullable|required_if:entry_type,emi_pay|numeric|min:0',
            'transaction_date' => 'required|date',
            'bank_account_id' => 'nullable|required_unless:entry_type,loan_charge|exists:bank_accounts,id',
            'details' => 'nullable|string|max:255',
        ]);

        $transaction = DB::transaction(function () use ($data, $loanAccount) {
            $principal = (float) ($data['principal_amount'] ?? 0);
            $interest = (float) ($data['interest_amount'] ?? 0);
            $amount = $data['entry_type'] === 'emi_pay'
                ? $principal + $interest
                : (float) $data['amount'];
            $bankId = $data['bank_account_id'] ?? null;

            $balanceDelta = match ($data['entry_type']) {
                'emi_pay' => -$principal,
                default => $amount,
            };
            $loanAccount->current_balance = (float) ($loanAccount->current_balance ?? 0) + $balanceDelta;
            $loanAccount->save();

            if (in_array($data['entry_type'], ['loan_more', 'loan_adjustment'], true) && $bankId) {
                $bank = BankAccount::lockForUpdate()->find($bankId);
                if ($bank) {
                    $bank->opening_balance = (float) ($bank->opening_balance ?? 0) + $amount;
                    $bank->save();
                }
            }
            if ($data['entry_type'] === 'emi_pay' && $bankId) {
                $bank = BankAccount::lockForUpdate()->find($bankId);
                if ($bank) {
                    $bank->opening_balance = (float) ($bank->opening_balance ?? 0) - $amount;
                    $bank->save();
                }
            }

            return BankTransaction::create([
                'to_bank_account_id' => in_array($data['entry_type'], ['loan_more', 'loan_adjustment'], true) ? $bankId : null,
                'from_bank_account_id' => $data['entry_type'] === 'emi_pay' ? $bankId : null,
                'type' => $data['entry_type'],
                'amount' => $amount,
                'transaction_date' => $data['transaction_date'],
                'reference_type' => LoanAccount::class,
                'reference_id' => $loanAccount->id,
                'description' => $data['details'] ?: match ($data['entry_type']) {
                    'loan_more' => 'Loan Adjustment',
                    'loan_adjustment' => 'Loan Adjustment',
                    'emi_pay' => 'EMI Pay',
                    default => 'Charges on loan',
                },
                'meta' => [
                    'details' => $data['details'] ?? '',
                    'principal' => $data['entry_type'] === 'emi_pay'
                        ? $principal
                        : (in_array($data['entry_type'], ['loan_more', 'loan_adjustment'], true) ? $amount : 0),
                    'charges' => $data['entry_type'] === 'emi_pay'
                        ? $interest
                        : ($data['entry_type'] === 'loan_charge' ? $amount : 0),
                    'total_amount' => $amount,
                ],
            ]);
        });

        $transaction->load(['toBankAccount', 'fromBankAccount']);

        return response()->json([
            'message' => 'Loan transaction saved successfully.',
            'loan' => $loanAccount->fresh(['lenderBank', 'receivedInBank', 'processingFeeBank']),
            'transaction' => $this->formatLoanTransaction($transaction),
        ]);
    }

    public function updateTransaction(Request $request, LoanAccount $loanAccount, BankTransaction $transaction)
    {
        abort_unless($transaction->reference_type === LoanAccount::class && (int) $transaction->reference_id === (int) $loanAccount->id, 404);

        $data = $request->validate([
            'amount' => 'required|numeric|min:0',
            'principal_amount' => 'nullable|numeric|min:0',
            'interest_amount' => 'nullable|numeric|min:0',
            'transaction_date' => 'required|date',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'details' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data, $loanAccount, $transaction) {
            $oldAmount = (float) ($transaction->amount ?? 0);
            $isLoanMore = in_array($transaction->type, ['loan_more', 'loan_adjustment'], true);
            $isEmiPay = $transaction->type === 'emi_pay';
            $oldMeta = $transaction->meta ?? [];
            $oldPrincipal = $isEmiPay ? (float) ($oldMeta['principal'] ?? 0) : $oldAmount;
            $newPrincipal = $isEmiPay ? (float) ($data['principal_amount'] ?? 0) : (float) $data['amount'];
            $newInterest = $isEmiPay ? (float) ($data['interest_amount'] ?? 0) : 0;
            $newAmount = $isEmiPay ? $newPrincipal + $newInterest : (float) $data['amount'];
            $delta = $newAmount - $oldAmount;
            $balanceDelta = $isEmiPay ? -($newPrincipal - $oldPrincipal) : $delta;

            $loanAccount->current_balance = (float) ($loanAccount->current_balance ?? 0) + $balanceDelta;
            $loanAccount->save();

            $oldBankId = $isEmiPay ? $transaction->from_bank_account_id : $transaction->to_bank_account_id;
            $newBankId = $data['bank_account_id'] ?: $oldBankId;

            if ($isLoanMore) {
                if ($oldBankId && (int) $oldBankId !== (int) $newBankId) {
                    $oldBank = BankAccount::lockForUpdate()->find($oldBankId);
                    if ($oldBank) {
                        $oldBank->opening_balance = (float) ($oldBank->opening_balance ?? 0) - $oldAmount;
                        $oldBank->save();
                    }
                    $newBank = BankAccount::lockForUpdate()->find($newBankId);
                    if ($newBank) {
                        $newBank->opening_balance = (float) ($newBank->opening_balance ?? 0) + $newAmount;
                        $newBank->save();
                    }
                } elseif ($newBankId) {
                    $bank = BankAccount::lockForUpdate()->find($newBankId);
                    if ($bank) {
                        $bank->opening_balance = (float) ($bank->opening_balance ?? 0) + $delta;
                        $bank->save();
                    }
                }

                $transaction->to_bank_account_id = $newBankId;
            } elseif ($isEmiPay) {
                if ($oldBankId && (int) $oldBankId !== (int) $newBankId) {
                    $oldBank = BankAccount::lockForUpdate()->find($oldBankId);
                    if ($oldBank) {
                        $oldBank->opening_balance = (float) ($oldBank->opening_balance ?? 0) + $oldAmount;
                        $oldBank->save();
                    }
                    $newBank = BankAccount::lockForUpdate()->find($newBankId);
                    if ($newBank) {
                        $newBank->opening_balance = (float) ($newBank->opening_balance ?? 0) - $newAmount;
                        $newBank->save();
                    }
                } elseif ($newBankId) {
                    $bank = BankAccount::lockForUpdate()->find($newBankId);
                    if ($bank) {
                        $bank->opening_balance = (float) ($bank->opening_balance ?? 0) - $delta;
                        $bank->save();
                    }
                }

                $transaction->from_bank_account_id = $newBankId;
                $transaction->to_bank_account_id = null;
            } else {
                $transaction->to_bank_account_id = $newBankId;
            }

            $transaction->amount = $newAmount;
            $transaction->transaction_date = $data['transaction_date'];
            $transaction->description = $data['details'] ?: ($isLoanMore ? 'Loan Adjustment' : ($isEmiPay ? 'EMI Pay' : 'Charges on loan'));
            $transaction->meta = [
                'details' => $data['details'] ?? '',
                'principal' => $isEmiPay ? $newPrincipal : ($isLoanMore ? $newAmount : 0),
                'charges' => $isEmiPay ? $newInterest : ($isLoanMore ? 0 : $newAmount),
                'total_amount' => $newAmount,
            ];
            $transaction->save();
        });

        $transaction->refresh()->load(['toBankAccount', 'fromBankAccount']);

        return response()->json([
            'message' => 'Loan transaction updated successfully.',
            'loan' => $loanAccount->fresh(['lenderBank', 'receivedInBank', 'processingFeeBank']),
            'transaction' => $this->formatLoanTransaction($transaction),
        ]);
    }

    private function formatLoanTransaction(BankTransaction $transaction): array
    {
        $meta = $transaction->meta ?? [];
        $isLoanMore = in_array($transaction->type, ['loan_more', 'loan_adjustment'], true);
        $isEmiPay = $transaction->type === 'emi_pay';
        $amount = (float) ($transaction->amount ?? 0);

        return [
            'id' => $transaction->id,
            'loan_id' => $transaction->reference_id,
            'type' => $transaction->type,
            'label' => match ($transaction->type) {
                'loan_more' => 'Loan Adjustment',
                'loan_adjustment' => 'Loan Adjustment',
                'loan_charge' => 'Charges on Loan',
                'emi_pay' => 'EMI Pay',
                default => 'Processing Fee',
            },
            'details' => $meta['details'] ?? $transaction->description,
            'date' => optional($transaction->transaction_date)->format('d/m/Y'),
            'date_value' => optional($transaction->transaction_date)->format('Y-m-d'),
            'principal' => $isEmiPay ? (float) ($meta['principal'] ?? 0) : ($isLoanMore ? $amount : 0),
            'charges' => $isEmiPay ? (float) ($meta['charges'] ?? 0) : ($isLoanMore ? 0 : $amount),
            'total_amount' => $amount,
            'bank_account_id' => $transaction->to_bank_account_id ?: $transaction->from_bank_account_id,
            'bank_name' => $transaction->toBankAccount?->display_with_account ?: $transaction->fromBankAccount?->display_with_account ?: '-',
        ];
    }

    private function loanStatementBalanceDelta(BankTransaction $transaction): float
    {
        $meta = $transaction->meta ?? [];
        $amount = (float) ($transaction->amount ?? 0);

        return match ($transaction->type) {
            'emi_pay' => -1 * (float) ($meta['principal'] ?? 0),
            'loan_more', 'loan_adjustment', 'loan_charge' => $amount,
            default => 0,
        };
    }

    private function loanStatementLabel(?string $type): string
    {
        return match ($type) {
            'loan_more', 'loan_adjustment' => 'Loan Adjustment',
            'loan_charge' => 'Charges on Loan',
            'emi_pay' => 'EMI Paid',
            'loan_processing_fee' => 'Processing Fee',
            'loan_processing_fee_refund' => 'Processing Fee Refund',
            default => 'Loan Transaction',
        };
    }
}
