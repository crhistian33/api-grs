<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete(('cascade'));
            $table->foreignId('shift_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
            $table->softDeletes()->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_shifts');
    }
};
