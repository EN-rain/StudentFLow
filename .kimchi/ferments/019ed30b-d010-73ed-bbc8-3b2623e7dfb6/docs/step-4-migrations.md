# Step 4: Schema Migrations - Verification Notes

## What was done
1. Modified `0001_01_01_000000_create_users_table.php` to add:
   - `username` (unique) - for login per plan §4.1
   - `role` enum ('admin', 'teacher') default 'teacher'
   - `status` enum ('active', 'disabled') default 'active'
   - indexes on `role` and `status`
2. Created 10 new migration files for the remaining plan.md §17 tables:
   - `2026_06_17_020001_create_teachers_table.php`
   - `2026_06_17_020002_create_students_table.php`
   - `2026_06_17_020003_create_classes_table.php`
   - `2026_06_17_020004_create_class_students_table.php`
   - `2026_06_17_020005_create_attendance_table.php`
   - `2026_06_17_020006_create_grade_categories_table.php`
   - `2026_06_17_020007_create_grade_items_table.php`
   - `2026_06_17_020008_create_student_grades_table.php`
   - `2026_06_17_020009_create_assignments_table.php`
   - `2026_06_17_020010_create_announcements_table.php`
3. Each table has: foreign keys (with cascadeOnDelete for owner → owned), status enums where plan calls for them, and indexes on common lookup columns.

## Tables created (11 StudentFlow + 10 system = 21 total)
**StudentFlow (per plan §17):**
1. users (id, username, name, email, password, role, status, remember_token, timestamps)
2. teachers (id, user_id, employee_number, first_name, middle_name, last_name, department, contact_number, profile_image)
3. students (id, student_number, first_name, middle_name, last_name, gender, birth_date, email, contact_number, address, guardian_name, guardian_contact, profile_image, status)
4. classes (id, teacher_id, class_name, section, subject, grade_level, school_year, semester, schedule, room, status)
5. class_students (id, class_id, student_id, date_enrolled, status)
6. attendance (id, class_id, student_id, attendance_date, status, remarks, recorded_by)
7. grade_categories (id, class_id, category_name, percentage_weight)
8. grade_items (id, class_id, category_id, title, maximum_score, date_given)
9. student_grades (id, grade_item_id, student_id, score, remarks)
10. assignments (id, class_id, title, description, date_assigned, deadline, maximum_score, status, attachment_link)
11. announcements (id, teacher_id, class_id, title, message, priority, publish_date, expiration_date)

**System (Laravel default + Sanctum):** cache, cache_locks, failed_jobs, job_batches, jobs, migrations, password_reset_tokens, personal_access_tokens, sessions, sqlite_sequence

## Verification
- `php artisan migrate:fresh` exit 0 ✓
- 14 migrations ran (3 Laravel defaults + 1 Sanctum + 10 StudentFlow)
- All 11 StudentFlow tables confirmed present via sqlite_master query
