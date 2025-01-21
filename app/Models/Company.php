<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'user_id',
    ];

    public function customers() : HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($company) {
            foreach ($company->customers as $customer) {
                if ($customer->hasAssignments()) {
                    throw new \Exception("No se puede remover la empresa porque hay asignaciones activas.");
                }
            }
        });

        static::forceDeleting(function ($company) {
            foreach ($company->customers as $customer) {
                if ($customer->hasAssignments()) {
                    throw new \Exception("No se puede eliminar permanentemente la empresa porque hay asignaciones activas.");
                }
            }
        });
    }
}
