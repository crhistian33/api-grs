<?php

namespace App\Models;

use App\Exceptions\HandleException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'ruc',
        'phone',
        'company_id',
        'created_by',
        'update_by'
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function hasAssignments(): bool
    {
        return $this->units()->whereHas('unitShifts.assignments')->exists();
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
        if ($this->units()->exists()) {
            $message = $force
                ? config("messages.validate.customer_unit_delete")
                : config("messages.validate.customer_unit_remove");

            throw new HandleException($message);
        }

        return $force ? $this->forceDelete() : $this->delete();
    }

    public static function destroyAll(Collection|array $ids, bool $force = false): array
    {
        return DB::transaction(function () use ($ids, $force) {
            $items = Customer::withTrashed()
                ->whereIn('id', $ids)
                ->get();

            if ($items->count() !== count($ids)) {
                throw new handleException('Uno de los registros no existe.');
            }

            $itemsToProcess = $items->partition(function ($item) {
                return !$item->units()->exists();
            });

            $toDeletes = $itemsToProcess->get(0);
            $nonToDeletes = $itemsToProcess->get(1);

            if ($toDeletes->isEmpty() && $nonToDeletes->isNotEmpty()) {
                throw new HandleException(config("messages.validate.customer_unit_no_all"));
            }

            $toDeletes->each(function ($item) use ($force) {
                $force ? $item->forceDelete() : $item->delete();
            });

            $titleType = $force
                ? 'deleteall'
                : 'removeall';

            $messageType = $force
                ? 'deleteall_unit'
                : 'removeall_unit';

            $title = config("messages.success.{$titleType}_title");

            $message = $nonToDeletes->isEmpty()
                ? config("messages.success.{$titleType}_message")
                : config("messages.success.{$messageType}_no_message") .
                  $nonToDeletes->pluck('name')->join(',');

            return [
                'title' => $title,
                'message' => 'Los clientes '.$message
            ];
        }, 5);
    }
}
