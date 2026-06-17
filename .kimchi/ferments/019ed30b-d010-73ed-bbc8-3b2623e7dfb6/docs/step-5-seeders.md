# Step 5: Seeders — Verification Notes

## What was done
1. Updated `app/Models/User.php` to add `username`, `role`, `status` to `$fillable`, added `HasApiTokens` trait for Sanctum, added `teacher()` relationship and `isAdmin()`/`isTeacher()` helpers.
2. Created 8 Eloquent models: `Teacher`, `Student`, `SchoolClass` (renamed from `Class` since Class is reserved), `Attendance`, `GradeCategory`, `GradeItem`, `StudentGrade`, `Assignment`, `Announcement`. Each has `$fillable`, type casts, and the appropriate `belongsTo`/`hasMany` relationships.
3. Rewrote `database/seeders/DatabaseSeeder.php` to seed all 11 tables per plan.md §7–§16:
   - **Users (4)**: admin (Maria Santos), john.reyes, angela.cruz, roberto.delapena — all passwords hashed via `Hash::make()`
   - **Teachers (3)**: TCH-2026-001/002/003 with departments per plan §7
   - **Students (20)**: 2026-0001..0020 with names from §9, detailed records for first 3 per §10 + best-guess defaults for the remaining 17
   - **Classes (3)**: BSIT 2A (Reyes), BSIT 1B (Cruz), BSIT 3A (Dela Peña)
   - **Enrollments (20)**: all 20 students enrolled in their respective classes (7+7+6 split per §9)
   - **Attendance (14)**: BSIT 2A on June 15 + June 17, 2026, with exact statuses + remarks per §11
   - **Grade categories (5)**: Quizzes 20%, Activities 15%, Assignments 20%, Project 20%, Final Exam 25% per §12
   - **Grade items (6)**: Quiz 1, Quiz 2, Activity 1, Assignment 1, Java Inventory System, Final Exam per §13
   - **Student grades (42)**: full §14 table reproduced — 7 students × 6 items = 42 rows
   - **Assignments (3)**: per §15
   - **Announcements (3)**: per §16

## Verification — Row counts
```
users                  4   (1 admin + 3 teachers)
teachers               3
students               20
classes                3
class_students         20
attendance             14
grade_categories       5
grade_items            6
student_grades         42
assignments            3
announcements          3
```

All counts match the success criterion exactly. ✓

## Verification — Password hashes
```
admin                  / Admin123! -> OK
john.reyes             / Teacher123! -> OK
angela.cruz            / Teacher123! -> OK
roberto.delapena       / Teacher123! -> OK
admin                  / WrongPassword -> INVALID
```

All four seeded accounts authenticate against their documented passwords; a wrong password is rejected. ✓

## Implementation note
SQLite doesn't enforce foreign keys by default; the seeder uses `PRAGMA foreign_keys = OFF` around the truncate cascade so dependent tables can be re-truncated on `migrate:fresh --seed`.
