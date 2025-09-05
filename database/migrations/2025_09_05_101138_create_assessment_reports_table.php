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
        Schema::create('assessment_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_assessment_id')->constrained('school_assessments')->onDelete('cascade');
            $table->string('judul_laporan');
            $table->text('ringkasan_eksekutif');
            $table->text('temuan_utama')->nullable();
            $table->text('rekomendasi')->nullable();
            $table->text('kesimpulan')->nullable();
            $table->json('data_statistik')->nullable();
            $table->enum('status_laporan', ['draft', 'review', 'final', 'published'])->default('draft');
            $table->foreignId('dibuat_oleh')->constrained('users')->onDelete('cascade');
            $table->foreignId('direview_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('tanggal_review')->nullable();
            $table->timestamp('tanggal_approval')->nullable();
            $table->timestamp('tanggal_publikasi')->nullable();
            $table->json('file_lampiran')->nullable();
            $table->text('catatan_reviewer')->nullable();
            $table->decimal('skor_total', 5, 2)->default(0.00);
            $table->string('grade_akhir', 2)->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            // Indexes untuk performance
            $table->index(['status_laporan', 'created_at']);
            $table->index(['school_assessment_id', 'status_laporan']);
            $table->index(['grade_akhir', 'tanggal_publikasi']);
            $table->index(['is_public', 'status_laporan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_reports');
    }
};
