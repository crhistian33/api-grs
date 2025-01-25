<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assist extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'start_date',
        'unit_shift_id',
        'user_id',
    ];

    public function workerAssignments(): BelongsToMany
    {
        return $this->belongsToMany(WorkerAssignment::class, 'workerassignment_assists')
            ->withPivot(['state_id', 'is_assist', 'replace_worker_id', 'replace_state_id', 'is_pay', 'pay_mount']);
    }

    public function unitShift(): BelongsTo
    {
        return $this->belongsTo(UnitShift::class);
    }
}
