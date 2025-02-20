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
        Schema::create('breaks', function (Blueprint $table) {
            $table->id();
            $table->date('start_date');
            $table->foreignId('worker_assignment_id')->constrained();
            $table->foreignId('state_id')->constrained();
            $table->bigInteger('replace_worker_id')->nullable()->constrained('workers');
            $table->bigInteger('replace_state_id')->nullable()->constrained('states');
            $table->boolean('is_pay')->default(false);
            $table->decimal('pay_mount', 9, 2)->nullable();
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
        Schema::dropIfExists('breaks');
    }
};
