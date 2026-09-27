<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class Warehouse extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'phone',
        'email',
        'city',
        'type',
        'capacity',
        'handler_name',
        'handler_phone',
        'responsible_user_id',
        'address',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }
}
