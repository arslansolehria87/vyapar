<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Party;
use App\Models\BankAccount;
use App\Models\Broker;
use App\Models\Item;
use App\Models\Sale;

class Transaction extends Model
{
    protected static bool $isSyncingPartyBalance = false;
    private const LEDGER_SNAP_EPSILON = 0.011;

    protected $fillable = [
        'party_id',
        'counter_party_id',
        'type',
        'bank_account_id',
        'payment_type',
        'number',
        'transfer_group',
        'date',
        'total',
        'credit',
        'debit',
        'paid_amount',
        'balance',
        'running_balance',
        'due_date',
        'status',
        'description',
        'broker_id',
        'item_id',
        'broker_amount',
        'labour',
        'bardana',
        'rehra_mazdori',
        'parcel_expense',
        'post_expense',
        'extra_expense',
        'attachment',
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'total' => 'decimal:2',
        'credit' => 'decimal:2',
        'debit' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'running_balance' => 'decimal:2',
        'broker_amount' => 'decimal:2',
        'labour' => 'decimal:2',
        'bardana' => 'decimal:2',
        'rehra_mazdori' => 'decimal:2',
        'parcel_expense' => 'decimal:2',
        'post_expense' => 'decimal:2',
        'extra_expense' => 'decimal:2',
    ];

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function counterParty()
    {
        return $this->belongsTo(Party::class, 'counter_party_id');
    }

    public function broker()
    {
        return $this->belongsTo(Broker::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function adjustments()
    {
        return $this->hasMany(TransactionAdjustment::class);
    }

    protected static function booted(): void
    {
        static::saved(function (Transaction $transaction) {
            static::syncPartyCurrentBalance($transaction->party_id);
        });

        static::deleted(function (Transaction $transaction) {
            static::syncPartyCurrentBalance($transaction->party_id);
        });
    }

    public static function syncPartyCurrentBalance(?int $partyId): void
    {
        if (empty($partyId) || static::$isSyncingPartyBalance) {
            return;
        }

        static::$isSyncingPartyBalance = true;

        try {
            $party = Party::query()->find($partyId);
            if (!$party) {
                return;
            }

            $transactions = static::query()
                ->where('party_id', $partyId)
                ->orderBy('date')
                ->orderBy('id')
                ->get();

            $ledgerRunningBalance = 0.0;
            $transactions
                ->each(function (Transaction $transaction) use (&$ledgerRunningBalance) {
                    $ledgerRunningBalance += static::normalizeLedgerAmount($transaction->debit ?? 0);
                    $ledgerRunningBalance -= static::normalizeLedgerAmount($transaction->credit ?? 0);
                    $ledgerRunningBalance = static::normalizeLedgerAmount($ledgerRunningBalance);

                    if ((float) ($transaction->running_balance ?? 0) !== (float) $ledgerRunningBalance) {
                        $transaction->running_balance = $ledgerRunningBalance;
                        $transaction->saveQuietly();
                    }
                });

            $hasOpeningTransaction = $transactions->contains(function (Transaction $transaction) {
                $type = strtolower((string) $transaction->type);
                $number = (string) ($transaction->number ?? '');

                return in_array($type, ['receive', 'pay'], true)
                    && str_starts_with($number, 'TXN')
                    && empty($transaction->transfer_group);
            });

            $signedOpeningBalance = 0.0;
            if (!$hasOpeningTransaction) {
                $signedOpeningBalance = (float) ($party->opening_balance ?? 0);
                if (strtolower((string) $party->transaction_type) === 'pay') {
                    $signedOpeningBalance *= -1;
                }
            }

            $latestPartyBalance = $signedOpeningBalance + $ledgerRunningBalance;

            Party::whereKey($partyId)->update([
                'current_balance' => static::normalizeLedgerAmount($latestPartyBalance),
            ]);
        } finally {
            static::$isSyncingPartyBalance = false;
        }
    }

    public function ledgerEffectValue(): float
    {
        $type = strtolower((string) $this->type);
        $storedCredit = (float) ($this->credit ?? 0);
        $storedDebit = (float) ($this->debit ?? 0);
        $total = (float) ($this->total ?? 0);
        $paidAmount = (float) ($this->paid_amount ?? 0);
        $expenseTotal = (float) ($this->broker_amount ?? 0)
            + (float) ($this->labour ?? 0)
            + (float) ($this->bardana ?? 0)
            + (float) ($this->rehra_mazdori ?? 0)
            + (float) ($this->parcel_expense ?? 0)
            + (float) ($this->post_expense ?? 0)
            + (float) ($this->extra_expense ?? 0);

        if ($storedCredit !== 0.0 || $storedDebit !== 0.0) {
            return match ($type) {
                'sale', 'invoice', 'pos', 'party to party[received]', 'receive', 'purchase_return' => $storedDebit ?: $total ?: $paidAmount,
                'purchase', 'payment_in', 'party to party[paid]', 'pay', 'sale_return', 'credit note' => -1 * ($storedCredit ?: $paidAmount ?: $total),
                'payment_out' => -1 * ($storedCredit ?: $storedDebit ?: $paidAmount ?: $total),
                'expense' => $storedDebit ?: $expenseTotal ?: $total,
                default => $storedDebit - $storedCredit,
            };
        }

        return match ($type) {
            'sale', 'invoice', 'pos', 'party to party[received]', 'receive', 'purchase_return' => $total ?: $paidAmount,
            'purchase', 'payment_in', 'party to party[paid]', 'pay', 'sale_return', 'credit note' => -1 * ($paidAmount ?: $total),
            'payment_out' => -1 * ($paidAmount ?: $total),
            'expense' => $expenseTotal ?: $total,
            default => 0.0,
        };
    }

    public function ledgerDebitValue(): float
    {
        $storedDebit = (float) ($this->debit ?? 0);
        if ($storedDebit !== 0.0) {
            return $storedDebit;
        }

        return max($this->ledgerEffectValue(), 0);
    }

    public function ledgerCreditValue(): float
    {
        $storedCredit = (float) ($this->credit ?? 0);
        if ($storedCredit !== 0.0) {
            return $storedCredit;
        }

        return abs(min($this->ledgerEffectValue(), 0));
    }

    public static function normalizeLedgerAmount($value): float
    {
        $rounded = round((float) $value, 2);
        $nearestWhole = round($rounded);

        if (abs($rounded - $nearestWhole) <= self::LEDGER_SNAP_EPSILON) {
            return (float) number_format($nearestWhole, 2, '.', '');
        }

        return (float) number_format($rounded, 2, '.', '');
    }
}
