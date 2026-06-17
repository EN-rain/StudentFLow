<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('grade_categories')->cascadeOnDelete();
            $table->string('title');
            $table->decimal('maximum_score', 8, 2);
            $table->date('date_given')->nullable();
            $table->timestamps();

            $table->index('class_id');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_items');
    }
};
