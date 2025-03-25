<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    use HasFactory;

    public function inassists(): HasMany
    {
        return $this->hasMany(Inassist::class);
    }

    public static function getIdByValue($value)
    {
        return self::where('shortName', $value)->value('id');
    }
}
