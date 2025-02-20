<?php

namespace App\Models;

use App\Exceptions\HandleException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TypeWorker extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'created_by',
        'update_by'
    ];

    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class);
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
        if ($this->workers()->exists()) {
            $message = $force
                ? config("messages.validate.type_worker_delete")
                : config("messages.validate.type_worker_remove");

            throw new HandleException($message);
        }

        return $force ? $this->forceDelete() : $this->delete();
    }

    public static function destroyAll(Collection|array $Ids, bool $force = false): array
    {
        return DB::transaction(function () use ($Ids, $force) {
            $items = TypeWorker::withTrashed()
                ->whereIn('id', $Ids)
                ->get();

            if ($items->count() !== count($Ids)) {
                throw new handleException('Uno de los registros no existe.');
            }

            $itemsToProcess = $items->partition(function ($item) {
                return !$item->workers()->exists();
            });

            $toDeletes = $itemsToProcess->get(0);
            $nonToDeletes = $itemsToProcess->get(1);

            if ($toDeletes->isEmpty() && $nonToDeletes->isNotEmpty()) {
                throw new HandleException(config("messages.validate.type_worker_no_all"));
            }

            $toDeletes->each(function ($item) use ($force) {
                $force ? $item->forceDelete() : $item->delete();
            });

            $titleType = $force
                ? 'deleteall'
                : 'removeall';

            $messageType = $force
                ? 'deleteall_worker'
                : 'removeall_worker';

            $title = config("messages.success.{$titleType}_title");

            $message = $nonToDeletes->isEmpty()
                ? config("messages.success.{$titleType}_message")
                : config("messages.success.{$messageType}_no_message") .
                  $nonToDeletes->pluck('name')->join(',');

            return [
                'title' => $title,
                'message' => 'Los tipos de trabajador '.$message
            ];
        }, 5);
    }
}
