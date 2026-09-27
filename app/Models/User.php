<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Permission;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
        'is_active',
        'company_id',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    /**
     * Retrieve permission names derived from user roles.
     */
    public function getAllPermissions(): array
    {
        // Super-admin shortcut: user with ID 1 or is_super_admin = true has all permissions
        if ($this->id === 1 || $this->is_super_admin) {
            return Permission::pluck('name')->toArray();
        }

        $rolePermissions = $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap(fn ($role) => $role->permissions->pluck('name'))
            ->unique()
            ->values()
            ->toArray();

        return $rolePermissions;
    }

    public function hasPermission(string $permission): bool
    {
        // Super-admin always has access
        if ($this->id === 1 || $this->is_super_admin) {
            return true;
        }

        return in_array($permission, $this->getAllPermissions());
    }

    /**
     * Helper to get the user's active company model.
     */
    public function currentCompany(): ?Company
    {
        $sessionCompanyId = session('current_company_id');
        if ($sessionCompanyId) {
            $comp = Company::find($sessionCompanyId);
            if ($comp) {
                return $comp;
            }
        }

        if ($this->company_id) {
            return $this->company;
        }

        return $this->companies()->first();
    }
}
