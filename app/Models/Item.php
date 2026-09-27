<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class Item extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'type', 'name', 'category_id', 'unit', 'price',
        'secondary_unit', 'unit_conversion_rate', 'bag_weight',
        'sale_price', 'wholesale_price', 'purchase_price',
        'opening_qty', 'item_code', 'location', 'description',
        'image_path', 'image_paths', 'min_stock', 'is_active',
        'custom_fields',
    ];

    protected $casts = [
        'image_paths' => 'array',
        'unit_conversion_rate' => 'decimal:4',
        'bag_weight' => 'decimal:2',
        'is_active' => 'boolean',
        'custom_fields' => 'array',
    ];

    protected $appends = ['stock_qty', 'total_net_w'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function sales()
    {
        return $this->hasManyThrough(
            Sale::class,
            SaleItem::class,
            'item_id',
            'id',
            'id',
            'sale_id'
        );
    }

    public function getStockQtyAttribute(): float
    {
        $sold = $this->saleItems()
            ->whereHas('sale', fn($q) => $q->whereIn('type', [
                'invoice', 'pos'
            ]))
            ->sum('quantity');

        $returned = $this->saleItems()
            ->whereHas('sale', fn($q) => $q->where('type', 'sale_return'))
            ->sum('quantity');

        return floatval($this->opening_qty) + floatval($returned) - floatval($sold);
    }

    public function getStockValueAttribute(): float
    {
        return $this->stock_qty * floatval($this->purchase_price);
    }

    public function getTotalNetWAttribute(): float
    {
        $sold = $this->saleItems()
            ->whereHas('sale', fn($q) => $q->whereIn('type', ['invoice', 'pos']))
            ->sum('net_w');

        $returned = $this->saleItems()
            ->whereHas('sale', fn($q) => $q->where('type', 'sale_return'))
            ->sum('net_w');

        return floatval($sold) - floatval($returned);
    }

    public function scopeActive($query)
    {
        return $query->where(function ($innerQuery) {
            $innerQuery->where('is_active', true)
                ->orWhereNull('is_active');
        });
    }
}
