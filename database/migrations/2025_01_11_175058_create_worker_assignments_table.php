<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained();
            $table->foreignId('assignment_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_assignments');
    }
};
