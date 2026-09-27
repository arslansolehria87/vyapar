<?php

namespace App\Traits;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToCompany
{
    /**
     * Boot the BelongsToCompany trait.
     */
    protected static function bootBelongsToCompany(): void
    {
        // 1. Global Scope: automatically filter queries by tenant company_id
        static::addGlobalScope('company_scope', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();

                // If super admin and explicitly viewing a specific company in session
                if ($user->is_super_admin) {
                    $selectedCompanyId = session('current_company_id');
                    if ($selectedCompanyId) {
                        $builder->where($builder->getModel()->getTable() . '.company_id', $selectedCompanyId);
                    }
                    return;
                }

                // Normal Tenant User: Strictly scoped to their own company
                $companyId = session('current_company_id') ?: $user->company_id;
                if ($companyId) {
                    $builder->where($builder->getModel()->getTable() . '.company_id', $companyId);
                }
            }
        });

        // 2. Creating Hook: automatically assign company_id when saving new records
        static::creating(function ($model) {
            if (empty($model->company_id) && Auth::check()) {
                $user = Auth::user();
                $companyId = session('current_company_id') ?: $user->company_id;
                if ($companyId) {
                    $model->company_id = $companyId;
                }
            }
        });
    }

    /**
     * Relationship to the company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
