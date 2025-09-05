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
        Schema::create('assessment_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_assessment_id')->constrained()->onDelete('cascade');
            $table->foreignId('assessment_indicator_id')->constrained()->onDelete('cascade');
            $table->decimal('skor', 4, 2); // Changed from integer to decimal for more precise scoring
            $table->text('bukti_dukung')->nullable();
            $table->text('catatan')->nullable();
            $table->json('file_bukti')->nullable();
            $table->timestamp('tanggal_penilaian')->nullable(); // Added assessment date
            $table->timestamps();

            $table->unique(['school_assessment_id', 'assessment_indicator_id']);
            $table->index(['skor']); // Index for performance
            $table->index(['tanggal_penilaian']); // Index for performance
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_scores');
    }
};
