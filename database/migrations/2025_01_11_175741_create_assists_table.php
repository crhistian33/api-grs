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
            $table->foreignId('worker_assignment_id')->constrained();
            $table->foreignId('state_id')->constrained();
            $table->boolean('isAssist')->default(false);
            $table->bigInteger('replace_worker');
            $table->bigInteger('replace_state');
            $table->boolean('isPay')->default(false);
            $table->decimal('pay_mount', 9, 2);
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
