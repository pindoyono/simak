<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Langkah 1: Ubah kolom enum menjadi string
        Schema::table('assessment_categories', function (Blueprint $table) {
            $table->string('komponen_new')->nullable()->after('id');
        });

        // Langkah 2: Copy data dari enum ke string baru
        DB::table('assessment_categories')->update([
            'komponen_new' => DB::raw('komponen')
        ]);

        // Langkah 3: Drop kolom enum lama
        Schema::table('assessment_categories', function (Blueprint $table) {
            $table->dropColumn('komponen');
        });

        // Langkah 4: Rename kolom baru menjadi komponen
        Schema::table('assessment_categories', function (Blueprint $table) {
            $table->renameColumn('komponen_new', 'komponen');
        });

        // Langkah 5: Update komponen dengan nilai baru
        $komponenUpdates = [
            ['old' => 'MANAGEMENT KEPALA SEKOLAH', 'new' => 'KEPALA SEKOLAH'],
            ['old' => 'SISWA', 'new' => 'PELANGGAN (SISWA, ORANG TUA DAN MASYARAKAT)'],
            ['old' => 'GURU', 'new' => 'TENAGA KERJA (TENAGA PENDIDIK DAN KEPENDIDIKAN)'],
            ['old' => 'KINERJA GURU DALAM MENGELOLA PROSES PEMBELAJARAN', 'new' => 'PROSES'],
        ];

        foreach ($komponenUpdates as $update) {
            DB::table('assessment_categories')
                ->where('komponen', $update['old'])
                ->update(['komponen' => $update['new']]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke komponen lama
        $komponenReverse = [
            ['new' => 'KEPALA SEKOLAH', 'old' => 'MANAGEMENT KEPALA SEKOLAH'],
            ['new' => 'PELANGGAN (SISWA, ORANG TUA DAN MASYARAKAT)', 'old' => 'SISWA'],
            ['new' => 'TENAGA KERJA (TENAGA PENDIDIK DAN KEPENDIDIKAN)', 'old' => 'GURU'],
            ['new' => 'PROSES', 'old' => 'KINERJA GURU DALAM MENGELOLA PROSES PEMBELAJARAN'],
        ];

        foreach ($komponenReverse as $reverse) {
            DB::table('assessment_categories')
                ->where('komponen', $reverse['new'])
                ->update(['komponen' => $reverse['old']]);
        }

        // Ubah kembali ke enum (opsional, bisa diabaikan untuk simplicity)
        Schema::table('assessment_categories', function (Blueprint $table) {
            $table->enum('komponen_enum', ['SISWA', 'GURU', 'KINERJA GURU DALAM MENGELOLA PROSES PEMBELAJARAN', 'MANAGEMENT KEPALA SEKOLAH'])->after('id');
        });

        DB::table('assessment_categories')->update([
            'komponen_enum' => DB::raw('komponen')
        ]);

        Schema::table('assessment_categories', function (Blueprint $table) {
            $table->dropColumn('komponen');
        });

        Schema::table('assessment_categories', function (Blueprint $table) {
            $table->renameColumn('komponen_enum', 'komponen');
        });
    }
};
