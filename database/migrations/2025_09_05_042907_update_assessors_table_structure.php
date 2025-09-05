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
            // Drop unique constraint first
            $table->dropUnique(['nip']);
        });

        Schema::table('assessors', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn(['nip', 'nama', 'jabatan', 'wilayah_kerja', 'telepon']);

            // Add new columns
            $table->string('nomor_identitas')->nullable();
            $table->string('nomor_telepon')->nullable();
            $table->string('institusi')->nullable();
            $table->string('posisi_jabatan')->nullable();
            $table->integer('pengalaman_tahun')->nullable();
            $table->text('sertifikasi')->nullable();
            $table->text('bidang_keahlian')->nullable();
            $table->text('catatan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessors', function (Blueprint $table) {
            // Add back old columns
            $table->string('nip')->unique();
            $table->string('nama');
            $table->string('jabatan');
            $table->string('wilayah_kerja');
            $table->string('telepon')->nullable();

            // Drop new columns
            $table->dropColumn(['nomor_identitas', 'nomor_telepon', 'institusi', 'posisi_jabatan', 'pengalaman_tahun', 'sertifikasi', 'bidang_keahlian', 'catatan']);
        });
    }
};
