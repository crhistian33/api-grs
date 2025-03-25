<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inassists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_assignment_id')->constrained();
            $table->foreignId('state_id')->constrained();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description');
            $table->foreignId('replacement_id')->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inassists');
    }
};
