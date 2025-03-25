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
        Schema::table('inassists', function (Blueprint $table) {
            $table->dropForeign(['worker_assignment_id']);
            $table->dropColumn('worker_assignment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inassists', function (Blueprint $table) {
            //
        });
    }
};
