<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Company extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'status',
        'is_active',
        'subscription_plan',
        'monthly_price',
        'subscription_starts_at',
        'subscription_expires_at',
        'enabled_features',
        'max_users',
        'phone',
        'email',
        'city',
        'address',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'monthly_price' => 'decimal:2',
        'subscription_starts_at' => 'datetime',
        'subscription_expires_at' => 'datetime',
        'enabled_features' => 'array',
        'max_users' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if a specific module feature is enabled for this company.
     */
    public function hasFeature(string $featureKey): bool
    {
        $features = $this->enabled_features;

        // If null or empty, by default all standard features are enabled (fallback)
        if ($features === null) {
            return true;
        }

        if (is_array($features)) {
            // Wildcard or explicit check
            return in_array('*', $features) || in_array($featureKey, $features);
        }

        return false;
    }

    /**
     * Check if company subscription is currently active and not expired.
     */
    public function isSubscriptionActive(): bool
    {
        if (!$this->is_active || $this->status === 'suspended') {
            return false;
        }

        if ($this->subscription_expires_at && $this->subscription_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Get remaining days until subscription expires.
     */
    public function daysUntilExpiry(): ?int
    {
        if (!$this->subscription_expires_at) {
            return null;
        }

        return (int) Carbon::now()->diffInDays($this->subscription_expires_at, false);
    }
}