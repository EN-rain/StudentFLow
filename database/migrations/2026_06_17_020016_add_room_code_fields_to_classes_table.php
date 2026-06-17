<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->string('room_code', 16)->nullable()->unique()->after('room');
            $table->boolean('teacher_kick_all_allowed')->default(false)->after('room_code');
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn(['room_code', 'teacher_kick_all_allowed']);
        });
    }
};
