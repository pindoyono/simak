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
        Schema::table('assessment_indicators', function (Blueprint $table) {
            // Change kriteria columns from string to text for larger content
            $table->text('kriteria_sangat_baik')->nullable()->change();
            $table->text('kriteria_baik')->nullable()->change();
            $table->text('kriteria_cukup')->nullable()->change();
            $table->text('kriteria_kurang')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_indicators', function (Blueprint $table) {
            // Rollback to string type (SQLite doesn't support column type changes well, so we'll recreate)
            $table->string('kriteria_sangat_baik')->nullable()->change();
            $table->string('kriteria_baik')->nullable()->change();
            $table->string('kriteria_cukup')->nullable()->change();
            $table->string('kriteria_kurang')->nullable()->change();
        });
    }
};
