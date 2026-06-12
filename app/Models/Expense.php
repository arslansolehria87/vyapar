<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'user_id',
        'expense_category_id',
        'expense_no',
        'expense_date',
        'party_id',
        'party',
        'po_no',
        'po_date',
        'transaction_time',
        'deal_days',
        'due_date',
        'payment_terms_name',
        'status',
        'discount_percent',
        'discount_amount',
        'tax_enabled',
        'tax_rate_id',
        'tax_rate_name',
        'tax_rate_value',
        'tax_amount',
        'items_json',
        'additional_charges',
        'transportation_details',
        'attachments',
        'description',
        'bank_account_id',
        'total_amount',
        'payment_type',
        'reference_no',
        'balance'
    ];

    protected $casts = [
        'expense_date' => 'date',
        'po_date' => 'date',
        'due_date' => 'date',
        'tax_enabled' => 'boolean',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate_value' => 'decimal:4',
        'tax_amount' => 'decimal:2',
        'items_json' => 'array',
        'additional_charges' => 'array',
        'transportation_details' => 'array',
        'attachments' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function party()
    {
        return $this->belongsTo(Party::class, 'party_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function taxRate()
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
    }

    public function items()
    {
        return $this->hasMany(ExpenseItem::class);
    }
}
