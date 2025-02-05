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
            $table->date('start_date');
            $table->foreignId('worker_assignment_id')->constrained();
            $table->foreignId('state_id')->constrained();
            $table->boolean('is_assist')->default(false);
            $table->bigInteger('replace_worker_id')->nullable()->constrained('workers');
            $table->bigInteger('replace_state_id')->nullable()->constrained('states');
            $table->boolean('is_pay')->default(false);
            $table->decimal('pay_mount', 9, 2)->nullable();
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
