<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentBreak extends Model
{
    use HasFactory;

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function unitShift(): BelongsTo
    {
        return $this->belongsTo(UnitShift::class);
    }

}
