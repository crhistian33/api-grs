<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Worker extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'dni',
        'birth_date',
        'type_worker_id',
        'user_id',
    ];

    public function scopeType($query, string $typeName) {
        return $query->whereHas('typeWorker', function($query) use ($typeName) {
            $query->where('name', $typeName);
        });
    }

    public function scopeUnassigned($query) {
        return $query->whereDoesntHave('workerAssignments');
    }


    public function typeWorker(): BelongsTo
    {
        return $this->belongsTo(TypeWorker::class);
    }

    public function assignments(): BelongsToMany
    {
        return $this->belongsToMany(Assignment::class, 'worker_assignments')
            ->withPivot(['status'])
            ->withTimestamps();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
