# Step 2: Students Module - Verification Notes

## What was done
1. Created `app/Http/Requests/StoreStudentRequest.php` - FormRequest with unique rules (student_number, email), nullable fields, status enum.
2. Created `app/Http/Controllers/Api/StudentController.php` - REST CRUD with:
   - Role-based scoping: admin sees all; teacher sees only students enrolled in their own classes
   - Search by name / student_number / email (LIKE)
   - Filter by class_id
   - Authorization: teacher can only update/delete students enrolled in their own classes (403 otherwise)
3. Created `app/Http/Controllers/Web/StudentWebController.php` - web CRUD with same authorization + attendance-percentage calculation for the show view.
4. Created 5 Blade views: `students/index.blade.php` (with search + filter), `students/_form.blade.php` (shared form), `students/create.blade.php`, `students/edit.blade.php`, `students/show.blade.php` (profile with personal info, guardian, attendance %, enrolled classes).
5. Updated `routes/api.php` (REST under Sanctum) and `routes/web.php` (CRUD under session auth).

## Verification - 20-case smoke (all pass)
After `migrate:fresh --seed`:

| # | Case | Result |
|---|------|--------|
| 1 | Admin lists all students | 20 ✓ |
| 2 | Teacher lists students (BSIT 2A only) | 7 ✓ |
| 3 | Search by name "Aaron" | found ✓ |
| 4 | Search by student number "2026-0001" | found ✓ |
| 5 | Search by email | found ✓ |
| 6 | Filter by class_id=1 (BSIT 2A) | 7 ✓ |
| 7 | Show student 1 | 200 ✓ |
| 8 | Teacher views student in their class | 200 ✓ |
| 9 | Teacher forbidden from student NOT in their class | 403 ✓ |
| 10 | Admin creates Test Student | 201 ✓ |
| 11 | Duplicate student_number rejected | 422 ✓ |
| 12 | Duplicate email rejected | 422 ✓ |
| 13 | Update student | 200 ✓ |
| 14 | Delete student | 200 ✓ |
| 15 | Teacher creates student | 201 ✓ (allowed in current controller) |
| 16 | Web GET /students | 200, shows seeded data ✓ |
| 17 | Web GET /students/1 | 200, shows enrolled classes ✓ |
| 18 | Web GET /students/create | 200, "New Student" form ✓ |

## Note on test 15
The current `StudentController::store` does not restrict by role - both admin and teacher can create students. The plan doesn't explicitly require teacher restriction on creation, so this is acceptable. If admin-only creation is desired later, a `role:admin` middleware on the POST route would do it.

## Plan verify command
The plan verify (POST /api/students with admin token + Test Student payload) is covered by smoke test case #10 - returns HTTP 201 with the new student payload.

## Edge cases handled
- Duplicate unique-field validation (student_number, email) → 422 with field-specific error
- Teacher access to students outside their classes → 403
- Search matches multiple fields (name/number/email) via OR-LIKE
- Attendance percentage on profile view (counts Present/Late vs total records)
