<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseItem extends Model
{
    protected $fillable = [
        'user_id',
        'expense_id',
        'name',
        'price',
        'quantity',
        'unit_price',
        'tax_rate_id',
        'tax_rate_name',
        'tax_rate_value',
        'tax_amount',
        'amount',
        'tax_included',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'price' => 'decimal:2',
        'tax_rate_value' => 'decimal:4',
        'tax_amount' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }
}
