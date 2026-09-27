<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Broker;
use App\Traits\BelongsToCompany;

class Sale extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'type',
        'party_id',
        'broker_id',
        'brokerage_type',
        'brokerage_rate',
        'broker_amount',
        'phone',
        'billing_address',
        'shipping_address',
        'bill_number',
        'reference_bill_number',
        'invoice_date',
        'order_date',
        'due_date',
        'deal_days',
        'reference_id',
        'tadad',
        'total_wazan',
        'safi_wazan',
        'rate',
        'deo',
        'total_qty',
        'total_amount',
        'discount_pct',
        'discount_rs',
        'tax_pct',
        'tax_amount',
        'round_off',
        'grand_total',
        'received_amount',
        'balance',
        'status',
        'invoice_theme',
        'description',
        'image_path',
        'image_paths',
        'document_path',
        'document_paths',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'order_date' => 'date',
        'due_date' => 'date',
        'deal_days' => 'integer',
        'tadad' => 'integer',
        'total_wazan' => 'decimal:2',
        'safi_wazan' => 'decimal:2',
        'rate' => 'decimal:2',
        'deo' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount_pct' => 'decimal:2',
        'discount_rs' => 'decimal:2',
        'tax_pct' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'round_off' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'brokerage_rate' => 'decimal:2',
        'broker_amount' => 'decimal:2',
        'invoice_theme' => 'array',
        'image_paths' => 'array',
        'document_paths' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function broker()
    {
        return $this->belongsTo(Broker::class);
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function challanDetail()
    {
        return $this->hasOne(ChallanDetail::class);
    }

    public function details()
    {
        return $this->hasOne(SaleDetail::class);
    }

    public function getDisplayPartyNameAttribute(): string
    {
        if ($this->relationLoaded('party') && $this->party) {
            return (string) $this->party->name;
        }

        if ($this->party_id) {
            $party = $this->party()->first();
            if ($party) {
                return (string) $party->name;
            }
        }

        if (!empty($this->party_name) && !is_numeric($this->party_name)) {
            return (string) $this->party_name;
        }

        return '-';
    }
}
