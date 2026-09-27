<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class Broker extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'phone',
        'city',
        'address',
        'commission_type',
        'commission_rate',
        'total_brokerage',
        'paid_brokerage',
        'notes',
        'status',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'total_brokerage' => 'decimal:2',
        'paid_brokerage' => 'decimal:2',
        'remaining_brokerage' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function getCommissionLabelAttribute(): string
    {
        $rate = number_format((float) ($this->commission_rate ?? 0), 2);

        return $this->commission_type === 'percent'
            ? $rate . '%'
            : 'Rs ' . $rate;
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) ($this->total_brokerage ?? 0) - (float) ($this->paid_brokerage ?? 0));
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
