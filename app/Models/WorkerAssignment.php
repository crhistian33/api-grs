<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkerAssignment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'worker_id',
        'assignment_id',
    ];

    public function scopeActive($query) {
        return $query->whereHas('assignment', function($query) {
            $query->where('state', 1);
        });
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function inassists(): HasMany
    {
        return $this->hasMany(Inassist::class);
    }
}
