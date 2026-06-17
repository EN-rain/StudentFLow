<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable()->after('status')->constrained('students')->nullOnDelete();
            $table->string('google_id')->nullable()->unique()->after('student_id');
            $table->string('github_id')->nullable()->unique()->after('google_id');
            $table->string('github_username')->nullable()->after('github_id');
            $table->string('avatar_url')->nullable()->after('github_username');
            $table->timestamp('social_verified_at')->nullable()->after('avatar_url');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('student_id');
            $table->dropColumn([
                'google_id',
                'github_id',
                'github_username',
                'avatar_url',
                'social_verified_at',
            ]);
        });
    }
};
