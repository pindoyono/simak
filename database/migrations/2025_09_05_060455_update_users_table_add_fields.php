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
        Schema::table('users', function (Blueprint $table) {
            // Add new fields
            $table->string('phone', 20)->nullable()->after('email');
            $table->boolean('is_active')->default(true)->after('email_verified_at');
            $table->timestamp('last_login_at')->nullable()->after('is_active');

            // Add indexes for better performance
            $table->index('email');
            $table->index('is_active');
            $table->index('email_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['email']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['email_verified_at']);

            // Drop columns
            $table->dropColumn(['phone', 'is_active', 'last_login_at']);
        });
    }
};
