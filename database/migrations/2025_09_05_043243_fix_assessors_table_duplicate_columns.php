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
        Schema::table('assessors', function (Blueprint $table) {
            // Drop the old instansi column that's causing NOT NULL constraint
            $table->dropColumn('instansi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessors', function (Blueprint $table) {
            $table->string('instansi')->after('user_id');
        });
    }
};
