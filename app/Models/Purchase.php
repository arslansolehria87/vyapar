<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'type',
        'source_purchase_order_id',
        'party_id',
        'party_name',
        'phone',
        'billing_address',
        'bill_number',
        'bill_date',
        'due_date',
        'total_qty',
        'total_amount',
        'discount_pct',
        'discount_rs',
        'tax_pct',
        'tax_amount',
        'shipping_charge',
        'round_off',
        'grand_total',
        'paid_amount',
        'balance',
        'invoice_theme',
        'description',
        'image_path',
        'document_path',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'invoice_theme' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function sourcePurchaseOrder()
    {
        return $this->belongsTo(self::class, 'source_purchase_order_id');
    }

    public function convertedPurchaseBills()
    {
        return $this->hasMany(self::class, 'source_purchase_order_id');
    }
}
