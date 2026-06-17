<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('date_assigned');
            $table->date('deadline');
            $table->decimal('maximum_score', 8, 2)->default(100);
            $table->enum('status', ['Upcoming', 'Active', 'Overdue', 'Completed', 'Cancelled'])->default('Active');
            $table->string('attachment_link')->nullable();
            $table->timestamps();

            $table->index('class_id');
            $table->index('deadline');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
