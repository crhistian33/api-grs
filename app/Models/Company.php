<?php

namespace App\Models;

use App\Exceptions\HandleException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Company extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'created_by',
        'update_by'
    ];

    public function customers() : HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_companies');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function safeDelete(bool $force = false): bool
    {
        if ($this->customers()->exists()) {
            $message = $force
                ? config("messages.validate.company_customer_delete")
                : config("messages.validate.company_customer_remove");

            throw new HandleException($message);
        }

        if ($this->users()->exists()) {
            $message = $force
                ? config("messages.validate.company_user_delete")
                : config("messages.validate.company_user_remove");

            throw new HandleException($message);
        }

        return $force ? $this->forceDelete() : $this->delete();
    }

    public static function destroyAll(Collection|array $Ids, bool $force = false): array
    {
        return DB::transaction(function () use ($Ids, $force) {
            $items = Company::withTrashed()
                ->whereIn('id', $Ids)
                ->get();

            if ($items->count() !== count($Ids)) {
                throw new handleException('Uno de los registros no existe.');
            }

            $toDeletes = $items->filter(function ($item) {
                return !$item->customers()->exists() && !$item->users()->exists();
            });

            $nonToDeletes = $items->filter(function ($item) {
                return $item->customers()->exists() || $item->users()->exists();
            });

            if ($toDeletes->isEmpty() && $nonToDeletes->isNotEmpty()) {
                throw new HandleException(config("messages.validate.company_no_all"));
            }

            $toDeletes->each(function ($item) use ($force) {
                $force ? $item->forceDelete() : $item->delete();
            });

            $titleType = $force
                ? 'deleteall'
                : 'removeall';

            $messageType = $force
                ? 'deleteall_company'
                : 'removeall_company';

            $title = config("messages.success.{$titleType}_title");

            $message = $nonToDeletes->isEmpty()
                ? config("messages.success.{$titleType}_message")
                : config("messages.success.{$messageType}_no_message") .
                  $nonToDeletes->pluck('name')->join(',');

            return [
                'title' => $title,
                'message' => 'Las empresas '.$message
            ];
        }, 5);
    }

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::deleting(function ($company) {
    //         foreach ($company->customers as $customer) {
    //             if ($customer->hasAssignments()) {
    //                 throw new \Exception("No se puede remover la empresa porque hay asignaciones activas.");
    //             }
    //         }
    //     });

    //     static::forceDeleting(function ($company) {
    //         foreach ($company->customers as $customer) {
    //             if ($customer->hasAssignments()) {
    //                 throw new \Exception("No se puede eliminar permanentemente la empresa porque hay asignaciones activas.");
    //             }
    //         }
    //     });
    // }
}
