<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->index(['last_name', 'first_name'], 'students_name_sort_index');
            $table->index(['status', 'last_name'], 'students_status_name_index');
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->index(['teacher_id', 'class_name'], 'classes_teacher_name_index');
            $table->index(['status', 'class_name'], 'classes_status_name_index');
        });

        Schema::table('class_students', function (Blueprint $table) {
            $table->index(['class_id', 'status', 'student_id'], 'class_students_class_status_student_index');
            $table->index(['student_id', 'status', 'class_id'], 'class_students_student_status_class_index');
        });

        Schema::table('attendance', function (Blueprint $table) {
            $table->index(['class_id', 'attendance_date'], 'attendance_class_date_index');
            $table->index(['student_id', 'attendance_date'], 'attendance_student_date_index');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->index(['class_id', 'deadline'], 'assignments_class_deadline_index');
            $table->index(['status', 'deadline'], 'assignments_status_deadline_index');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->index(['class_id', 'publish_date'], 'announcements_class_publish_index');
            $table->index(['teacher_id', 'publish_date'], 'announcements_teacher_publish_index');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex('announcements_teacher_publish_index');
            $table->dropIndex('announcements_class_publish_index');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropIndex('assignments_status_deadline_index');
            $table->dropIndex('assignments_class_deadline_index');
        });

        Schema::table('attendance', function (Blueprint $table) {
            $table->dropIndex('attendance_student_date_index');
            $table->dropIndex('attendance_class_date_index');
        });

        Schema::table('class_students', function (Blueprint $table) {
            $table->dropIndex('class_students_student_status_class_index');
            $table->dropIndex('class_students_class_status_student_index');
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropIndex('classes_status_name_index');
            $table->dropIndex('classes_teacher_name_index');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('students_status_name_index');
            $table->dropIndex('students_name_sort_index');
        });
    }
};
