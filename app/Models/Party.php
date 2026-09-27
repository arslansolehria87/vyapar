<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class Party extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'phone',
        'phone_number_2',
        'ptcl_number',
        'email',
        'city',
        'address',
        'billing_address',
        'shipping_address',
        'is_active',
        'opening_balance',
        'current_balance',
        'as_of_date',
        'credit_limit_enabled',
        'credit_limit_amount',
        'due_days',
        'custom_fields',
        'transaction_type',
        'party_type',
        'party_group',
        'payment_reminder_enabled',
        'payment_reminder_phone',
        'payment_reminder_date',
        'payment_reminder_message',
        'payment_reminder_sent_at',
    ];

    protected $appends = [
        'current_balance',
        'formatted_current_balance',
    ];

    protected $casts = [
        'credit_limit_enabled' => 'boolean',
        'is_active' => 'boolean',
        'current_balance' => 'decimal:2',
        'credit_limit_amount' => 'decimal:2',
        'custom_fields' => 'array',
        'payment_reminder_enabled' => 'boolean',
        'payment_reminder_date' => 'date',
        'payment_reminder_sent_at' => 'datetime',
    ];

    public function getAsOfDateAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->orderBy('date', 'desc');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function getCurrentBalanceAttribute($value)
    {
        if ($value !== null) {
            return (float) $value;
        }

        $transactions = $this->relationLoaded('transactions')
            ? $this->transactions
            : $this->transactions()->get();

        $hasOpeningTransaction = $transactions->contains(function ($transaction) {
            $type = strtolower((string) $transaction->type);
            $number = (string) ($transaction->number ?? '');

            return in_array($type, ['receive', 'pay'], true)
                && str_starts_with($number, 'TXN')
                && empty($transaction->transfer_group);
        });

        $signedOpeningBalance = 0.0;
        if (!$hasOpeningTransaction) {
            $signedOpeningBalance = (float) ($this->opening_balance ?? 0);
            if (strtolower((string) $this->transaction_type) === 'pay') {
                $signedOpeningBalance *= -1;
            }
        }

        $ledgerNet = (float) $transactions->sum(fn ($transaction) => (float) $transaction->ledgerEffectValue());

        return $signedOpeningBalance + $ledgerNet;
    }

    public function getFormattedCurrentBalanceAttribute()
    {
        return number_format((float) $this->current_balance, 2);
    }

    public function paymentIns()
    {
        return $this->hasMany(PaymentIn::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
