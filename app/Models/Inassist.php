<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inassist extends Model
{
    use HasFactory;

    protected $fillable = [
        'worker_assignment_id',
        'state_id',
        'start_date',
        'description',
        'replacement_id',
        'created_by',
        'update_by'
    ];


    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function workerAssignment(): BelongsTo
    {
        return $this->belongsTo(WorkerAssignment::class);
    }

    public function unitShift(): BelongsTo
    {
        return $this->belongsTo(UnitShift::class);
    }

    public function replacement(): BelongsTo
    {
        return $this->belongsTo(Replacement::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
