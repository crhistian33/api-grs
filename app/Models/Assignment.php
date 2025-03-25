<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'unit_shift_id',
        'start_date',
        'end_date',
        'state',
        'created_by',
        'update_by'
    ];

    public function scopeActiveToUnitshift($query, $unit_shift_id) {
        return $query->where('state', 1)->whereHas('unitShift', function($query) use ($unit_shift_id) {
            $query->where('id', $unit_shift_id);
        });
    }

    public function unitShift(): BelongsTo
    {
        return $this->belongsTo(UnitShift::class);
    }

    public function workers(): BelongsToMany
    {
        return $this->belongsToMany(Worker::class, 'worker_assignments')
                    ->withPivot('id');
    }

    // Relación HasMany con WorkerAssignment
    public function workerAssignments(): HasMany
    {
        return $this->hasMany(WorkerAssignment::class, 'assignment_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
