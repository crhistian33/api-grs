<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assist extends Model
{
    use HasFactory;

    public function workerAssignment(): BelongsTo
    {
        return $this->belongsTo(WorkerAssignment::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
