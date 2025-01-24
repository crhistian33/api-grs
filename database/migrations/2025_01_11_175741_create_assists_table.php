<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assists', function (Blueprint $table) {
            $table->id();
            $table->date('assist_date');
            $table->boolean('isAssist')->default(false);
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
            $table->softDeletes()->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assists');
    }
};
