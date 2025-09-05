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
        Schema::table('assessment_reviews', function (Blueprint $table) {
            // Add new fields for enhanced review functionality
            $table->text('review_notes')->nullable()->after('comments');
            $table->enum('grade_recommendation', ['A', 'B', 'C', 'D'])->nullable()->after('review_notes');
            $table->decimal('score_adjustment', 5, 2)->nullable()->after('grade_recommendation');
            $table->string('approval_level')->nullable()->after('score_adjustment');

            // Update status enum to include new statuses
            $table->dropColumn('status');
        });

        Schema::table('assessment_reviews', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'in_progress',
                'approved',
                'rejected',
                'revision_needed',
                'submitted'
            ])->default('pending')->after('reviewer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_reviews', function (Blueprint $table) {
            $table->dropColumn([
                'review_notes',
                'grade_recommendation',
                'score_adjustment',
                'approval_level'
            ]);

            $table->dropColumn('status');
        });

        Schema::table('assessment_reviews', function (Blueprint $table) {
            $table->string('status')->after('reviewer_id');
        });
    }
};
