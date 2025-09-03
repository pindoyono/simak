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
        Schema::create('assessment_categories', function (Blueprint $table) {
            $table->id();
            $table->enum('komponen', ['SISWA', 'GURU', 'KINERJA GURU DALAM MENGELOLA PROSES PEMBELAJARAN', 'MANAGEMENT KEPALA SEKOLAH']);
            $table->string('nama_kategori');
            $table->text('deskripsi')->nullable();
            $table->decimal('bobot_penilaian', 5, 2)->default(0);
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_categories');
    }
};
