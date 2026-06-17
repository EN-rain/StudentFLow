<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_number')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->date('birth_date')->nullable();
            $table->string('email')->unique();
            $table->string('contact_number', 32)->nullable();
            $table->string('address')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_contact', 32)->nullable();
            $table->string('profile_image')->nullable();
            $table->enum('status', ['active', 'disabled'])->default('active');
            $table->timestamps();

            $table->index('last_name');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
