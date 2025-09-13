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
            // Tambah kolom-kolom baru setelah kolom yang ada
            $table->text('kegiatan')->nullable()->after('kriteria_penilaian')->comment('Deskripsi kegiatan penilaian');
            $table->text('sumber_data')->nullable()->after('kegiatan')->comment('Sumber data untuk penilaian');
            $table->text('keterangan')->nullable()->after('sumber_data')->comment('Keterangan tambahan');
            $table->text('kriteria_sangat_baik')->nullable()->after('keterangan')->comment('Kriteria untuk skor 4 - Sangat Baik');
            $table->text('kriteria_baik')->nullable()->after('kriteria_sangat_baik')->comment('Kriteria untuk skor 3 - Baik');
            $table->text('kriteria_cukup')->nullable()->after('kriteria_baik')->comment('Kriteria untuk skor 2 - Cukup');
            $table->text('kriteria_kurang')->nullable()->after('kriteria_cukup')->comment('Kriteria untuk skor 1 - Kurang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_indicators', function (Blueprint $table) {
            $table->dropColumn([
                'kegiatan',
                'sumber_data',
                'keterangan',
                'kriteria_sangat_baik',
                'kriteria_baik',
                'kriteria_cukup',
                'kriteria_kurang'
            ]);
        });
    }
};
