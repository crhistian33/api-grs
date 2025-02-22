<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function unitShift(): BelongsTo
    {
        return $this->belongsTo(UnitShift::class);
    }

    public function workers(): BelongsToMany
    {
        return $this->belongsToMany(Worker::class, 'worker_assignments');
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
