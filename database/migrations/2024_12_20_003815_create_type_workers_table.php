<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('type_workers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20)->unique();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
            $table->softDeletes()->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('type_workers');
    }
};
