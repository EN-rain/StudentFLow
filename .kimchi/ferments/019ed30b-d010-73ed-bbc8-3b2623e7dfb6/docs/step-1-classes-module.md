# Step 1: Classes Module - Verification Notes

## What was done
1. Created `app/Http/Requests/StoreClassRequest.php` - FormRequest validation (class_name, subject, teacher_id required; others optional).
2. Created `app/Http/Controllers/Api/ClassController.php` - REST endpoints (index/show/store/update/destroy) with role-based authorization:
   - Teacher sees/manages only their own classes
   - Teacher cannot create classes for other teachers
   - Admin sees/can create for any teacher
3. Created `app/Http/Controllers/Web/ClassWebController.php` - web CRUD (index/create/store/show/edit/update/destroy) with the same authorization.
4. Created Blade views: `classes/index.blade.php`, `classes/_form.blade.php` (shared form partial), `classes/create.blade.php`, `classes/edit.blade.php`, `classes/show.blade.php` (with enrolled students + assignments + announcements panels).
5. Updated `routes/api.php` with REST endpoints under Sanctum auth.
6. Updated `routes/web.php` with web CRUD routes under session auth.
7. Fixed Student + SchoolClass models' `belongsToMany('class_students', ...)` to explicitly specify `class_id` and `student_id` foreign keys - Laravel's pivot auto-inference was looking for `school_class_id` which doesn't exist (the migration used `class_id` per plan.md).

## Verification - 15-case smoke (all pass)
Run via `php C:\Users\LENOVO\Downloads\classes-smoke.php` against the live server after `migrate:fresh --seed`:

| # | Case | Result |
|---|------|--------|
| 1 | Teacher list classes | 200, count=1, first=BSIT 2A ✓ |
| 2 | Admin list classes | 200, count=3 ✓ |
| 3 | Show class 1 as teacher | 200 ✓ |
| 4 | Teacher 1 forbidden from class 2 | 403 ✓ |
| 5 | Teacher creates BSIT 2C (own teacher_id) | 201 ✓ |
| 6 | Teacher forbidden from creating for another teacher | 403 ✓ |
| 7 | Admin creates BSIT 4A | 201 ✓ |
| 8 | Update class (PUT) | 200, subject updated ✓ |
| 9 | Delete class | 200 ✓ |
| 10 | Validation rejects missing fields | 422 ✓ |
| 11 | Web GET /classes | 200, BSIT 2A visible ✓ |
| 12 | Web GET /classes/1 | 200, "Enrolled Students" panel ✓ |
| 13 | Web GET /classes/create | 200, "New Class" form ✓ |

## Edge cases handled
- Teacher role enforcement on all CRUD operations (model-level check in authorizeAccess)
- Pivot table foreign key mismatch caught by 500 → fixed by specifying FK columns explicitly
- Smoke test polluted by leftover state from prior runs → added `migrate:fresh --seed` reset at start of test

## Plan verify command
The plan verify (POST /api/classes with teacher token + new class payload) is covered by smoke test case #5 - passes with HTTP 201.
