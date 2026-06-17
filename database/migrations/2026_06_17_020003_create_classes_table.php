<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->string('class_name');
            $table->string('section')->nullable();
            $table->string('subject');
            $table->string('grade_level')->nullable();
            $table->string('school_year')->nullable();
            $table->string('semester')->nullable();
            $table->string('schedule')->nullable();
            $table->string('room')->nullable();
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->timestamps();

            $table->index('teacher_id');
            $table->index('class_name');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
