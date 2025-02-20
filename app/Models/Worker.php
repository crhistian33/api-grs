<?php

namespace App\Models;

use App\Exceptions\HandleException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Worker extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'dni',
        'birth_date',
        'type_worker_id',
        'company_id',
        'created_by',
        'updated_by',
    ];

    public function scopeType($query, string $typeName) {
        return $query->whereHas('typeWorker', function($query) use ($typeName) {
            $query->where('name', $typeName);
        });
    }

    public function scopeUnassigned($query) {
        return $query->whereDoesntHave('assignments');
    }


    public function typeWorker(): BelongsTo
    {
        return $this->belongsTo(TypeWorker::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assignments(): BelongsToMany
    {
        return $this->belongsToMany(Assignment::class, 'worker_assignments');
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
        if ($this->assignments()->exists()) {
            $message = $force
                ? config("messages.validate.worker_assign_delete")
                : config("messages.validate.worker_assign_remove");

            throw new HandleException($message);
        }

        return $force ? $this->forceDelete() : $this->delete();
    }

    public static function destroyAll(Collection|array $ids, bool $force = false): array
    {
        return DB::transaction(function () use ($ids, $force) {
            $items = Worker::withTrashed()
                ->whereIn('id', $ids)
                ->get();

            if ($items->count() !== count($ids)) {
                throw new handleException('Uno de los registros no existe.');
            }

            $itemsToProcess = $items->partition(function ($item) {
                return !$item->assignments()->exists();
            });

            $toDeletes = $itemsToProcess->get(0);
            $nonToDeletes = $itemsToProcess->get(1);

            if ($toDeletes->isEmpty() && $nonToDeletes->isNotEmpty()) {
                throw new HandleException(config("messages.validate.worker_assign_no_all"));
            }

            $toDeletes->each(function ($item) use ($force) {
                $force ? $item->forceDelete() : $item->delete();
            });

            $messageType = $force
                ? 'deleteall'
                : 'removeall';

            $title = config("messages.success.{$messageType}_title");

            $message = $nonToDeletes->isEmpty()
                ? config("messages.success.{$messageType}_message")
                : config("messages.success.{$messageType}_no_message") .
                  $nonToDeletes->pluck('name')->join(',');

            return [
                'title' => $title,
                'message' => 'Los trabajadores '.$message
            ];
        }, 5);
    }
}
