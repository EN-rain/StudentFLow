<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->string('title');
            $table->text('message');
            $table->enum('priority', ['Normal', 'Important', 'Urgent'])->default('Normal');
            $table->date('publish_date');
            $table->date('expiration_date')->nullable();
            $table->timestamps();

            $table->index('teacher_id');
            $table->index('class_id');
            $table->index('priority');
            $table->index('publish_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
