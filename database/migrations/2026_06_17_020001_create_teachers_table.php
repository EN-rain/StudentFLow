<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('employee_number')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('department')->nullable();
            $table->string('contact_number', 32)->nullable();
            $table->string('profile_image')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('last_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
