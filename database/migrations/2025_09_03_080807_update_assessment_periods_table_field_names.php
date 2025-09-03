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
        Schema::table('assessment_periods', function (Blueprint $table) {
            // Rename Indonesian field names to English
            $table->renameColumn('nama_periode', 'name');
            $table->renameColumn('tahun_ajaran', 'academic_year');
            $table->renameColumn('tanggal_mulai', 'start_date');
            $table->renameColumn('tanggal_selesai', 'end_date');
            $table->renameColumn('deskripsi', 'description');
        });

        // Update enum values to English
        Schema::table('assessment_periods', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('assessment_periods', function (Blueprint $table) {
            $table->enum('status', ['draft', 'active', 'completed'])->default('draft');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_periods', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('assessment_periods', function (Blueprint $table) {
            $table->enum('status', ['draft', 'aktif', 'selesai'])->default('draft');
        });

        Schema::table('assessment_periods', function (Blueprint $table) {
            $table->renameColumn('name', 'nama_periode');
            $table->renameColumn('academic_year', 'tahun_ajaran');
            $table->renameColumn('start_date', 'tanggal_mulai');
            $table->renameColumn('end_date', 'tanggal_selesai');
            $table->renameColumn('description', 'deskripsi');
        });
    }
};
