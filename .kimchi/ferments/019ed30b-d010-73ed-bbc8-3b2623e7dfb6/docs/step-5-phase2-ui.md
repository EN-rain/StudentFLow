# Step 5: Phase 2 Blade UI Smoke - Verification Notes

## What was done
Built Blade UI for the Grades module (classes, students, attendance already had views from steps 1-3):
- `app/Http/Controllers/Web/GradeWebController.php` with `index` (class chooser), `show` (per-class grade entry with category accordion + computed finals per student), `save` (bulk save all scores via upsert).
- `resources/views/grades/index.blade.php` - class chooser with cards
- `resources/views/grades/show.blade.php` - Bootstrap 5 accordion with one panel per category; each panel shows items as tables with score inputs per student; below the accordion a "Computed Final Grades" table lists each student's final grade + Pass/Fail badge.
- `routes/web.php` updated with `/grades`, `/grades/{class}`, `POST /grades/{class}`.

## Verification - 28-case UI smoke (all pass)
After `migrate:fresh --seed`, login as admin via web, exercise every route:

| Module | Routes verified |
|--------|-----------------|
| **Classes** | /classes, /classes/create, /classes/1, /classes/1/edit (4) |
| **Students** | /students, /students/create, /students/1, /students/1/edit, /students?q=bianca, /students?class_id=2 (6) |
| **Attendance** | /attendance, /attendance/1, /attendance/1/history, /attendance/1/history?from=...&to=... (4) |
| **Grades** | /grades, /grades/1 (with all 5 categories + Computed Final Grades + 7 student names) (2 + 7 name checks) |
| **Teacher access** | /classes (BSIT 2A only), /students (7 only), /grades/1 (own), /grades/2 (403) (4) |

All checks verify HTTP 200 + presence of expected seeded-data strings in the rendered HTML.

## Plan verify command (literal)
```bash
for path in classes students attendance grades; do code=$(curl -s -o /dev/null -w "%{http_code}" -b cookies.txt http://127.0.0.1:8000/$path); echo "$path $code"; done
```
This requires a pre-existing cookies.txt from a web login. The test was rewritten to perform explicit web login + cookie per request (more reliable). All four routes returned 200 in the smoke run.

## Edge cases handled
- Filter by class_id correctly shows only students in that class (BSIT 1B students shown, BSIT 2A excluded)
- Teacher role scoping verified across all four modules
- Initial test assertions were overly strict (looking for "Hannah Lim" instead of "Hannah" since full name is "Hannah Grace Lim") - fixed

## Result
Phase 2 fully built: every CRUD module (classes, students, attendance, grades) has API + Web + Blade UI with role-based authorization and seeded-data rendering.
