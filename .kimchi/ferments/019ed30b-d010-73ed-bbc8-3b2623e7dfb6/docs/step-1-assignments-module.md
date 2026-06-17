# Step 1: Assignments Module - Verification Notes

## What was done
1. Created `app/Http/Requests/StoreAssignmentRequest.php` - validation including `deadline` after_or_equal `date_assigned`, status enum, optional URL attachment.
2. Created `app/Http/Controllers/Api/AssignmentController.php` - REST CRUD (index/show/store/update/destroy) with role scoping (teacher sees/creates own classes only), auto-status method.
3. Created `app/Http/Controllers/Web/AssignmentWebController.php` - web CRUD with same authorization.
4. Created 5 Blade views: `assignments/_form.blade.php` (shared form partial), `assignments/index.blade.php` (table with status color badges), `assignments/create.blade.php`, `assignments/edit.blade.php`, `assignments/show.blade.php` (with description rendering).
5. Updated `Assignment` model with both `schoolClass()` and alias `class()` relationships plus `getClassAttribute()` accessor - needed so `with('class')` and `$a->class->class_name` both work.
6. Updated `routes/api.php` and `routes/web.php` with assignment routes.

## Verification - 12-case smoke (all pass)
After `migrate:fresh --seed`:

| # | Case | Result |
|---|------|--------|
| 1 | Admin list assignments | 200, count=3 ✓ |
| 2 | Teacher list assignments (own only) | 200, count=1 (BSIT 2A only) ✓ |
| 3 | Show assignment 1 (own) | 200, "Java Student Record Program" ✓ |
| 4 | Plan verify - teacher creates Test Assignment | 201 ✓ |
| 5 | Teacher forbidden from class 2 | 403 ✓ |
| 6 | Validation: deadline < date_assigned | 422 ✓ |
| 7 | Update assignment | 200, maximum_score=60 ✓ |
| 8 | Delete assignment | 200 ✓ |
| 9 | Admin creates for any class | 201 ✓ |
| 10 | Web GET /assignments | 200 ✓ |
| 11 | Web GET /assignments/1 | 200, "Description" panel ✓ |
| 12 | Web GET /assignments/create | 200, "New Assignment" form ✓ |

## Plan verify command
`POST /api/assignments` with teacher token + Test Assignment payload is smoke test case #4 - returns HTTP 201.

## Edge cases handled
- Status enum validated (Upcoming/Active/Overdue/Completed/Cancelled)
- Deadline must be >= date_assigned (after_or_equal rule)
- Teacher cross-class creation returns 403
- Added `class()` alias to Assignment model - Laravel's eager-load `with('class')` requires a real method, not just an accessor
