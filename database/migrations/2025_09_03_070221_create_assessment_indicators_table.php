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
        Schema::create('assessment_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_category_id')->constrained()->onDelete('cascade');
            $table->string('nama_indikator');
            $table->text('deskripsi')->nullable();
            $table->decimal('bobot_indikator', 5, 2)->default(0);
            $table->text('kriteria_penilaian')->nullable();
            $table->integer('skor_maksimal')->default(4);
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
        Schema::dropIfExists('assessment_indicators');
    }
};
