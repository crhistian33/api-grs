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
        Schema::create('workerassignment_assists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_assignment_id')->constrained();
            $table->foreignId('assist_id')->constrained();
            $table->foreignId('state_id')->constrained();
            $table->boolean('is_assist')->default(false);
            $table->bigInteger('replace_worker_id')->nullable()->constrained('workers');
            $table->bigInteger('replace_state_id')->nullable()->constrained('states');
            $table->boolean('is_pay')->default(false);
            $table->decimal('pay_mount', 9, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workerassignment_assists');
    }
};
