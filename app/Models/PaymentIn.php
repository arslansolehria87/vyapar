<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentIn extends Model
{
    protected $fillable = [
        'party_id',
        'entity_type',
        'entity_id',
        'entity_name',
        'bank_account_id',
        'amount',
        'payment_type',
        'reference_no',
        'receipt_no',
        'date',
        'description',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function party() {
        return $this->belongsTo(Party::class);
    }

    public function bankAccount() {
        return $this->belongsTo(BankAccount::class);
    }

    public function links()
    {
        return $this->hasMany(PaymentInLink::class);
    }
}
