<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('dni', 8);
            $table->date('birth_date');
            $table->foreignId('company_id')->constrained();
            $table->foreignId('type_worker_id')->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes()->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workers');
    }
};
