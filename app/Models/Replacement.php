<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Replacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'worker_id',
        'state_id',
        'is_pay',
        'pay_mount',
        'comment',
        'created_by',
        'update_by'
    ];


    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }
}
