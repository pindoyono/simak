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
        // For SQLite, we need to recreate the table with new enum values
        Schema::table('school_assessments', function (Blueprint $table) {
            $table->string('grade_temp')->nullable();
        });

        // Copy existing data
        DB::statement('UPDATE school_assessments SET grade_temp = grade');

        // Drop the old column
        Schema::table('school_assessments', function (Blueprint $table) {
            $table->dropColumn('grade');
        });

        // Add new column with expanded enum
        Schema::table('school_assessments', function (Blueprint $table) {
            $table->enum('grade', ['A', 'B', 'C', 'D', 'F'])->nullable();
        });

        // Copy data back
        DB::statement('UPDATE school_assessments SET grade = grade_temp');

        // Drop temporary column
        Schema::table('school_assessments', function (Blueprint $table) {
            $table->dropColumn('grade_temp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum
        Schema::table('school_assessments', function (Blueprint $table) {
            $table->string('grade_temp')->nullable();
        });

        DB::statement('UPDATE school_assessments SET grade_temp = grade');

        Schema::table('school_assessments', function (Blueprint $table) {
            $table->dropColumn('grade');
        });

        Schema::table('school_assessments', function (Blueprint $table) {
            $table->enum('grade', ['A', 'B', 'C', 'D'])->nullable();
        });

        DB::statement('UPDATE school_assessments SET grade = grade_temp WHERE grade_temp IN ("A", "B", "C", "D")');

        Schema::table('school_assessments', function (Blueprint $table) {
            $table->dropColumn('grade_temp');
        });
    }
};
