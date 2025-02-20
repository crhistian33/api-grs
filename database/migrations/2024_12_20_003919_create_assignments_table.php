<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('state')->default(true);
            $table->foreignId('unit_shift_id')->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes()->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
