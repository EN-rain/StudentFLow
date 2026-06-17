<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->string('category_name');
            $table->decimal('percentage_weight', 5, 2); // e.g. 20.00 for 20%
            $table->timestamps();

            $table->unique(['class_id', 'category_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_categories');
    }
};
