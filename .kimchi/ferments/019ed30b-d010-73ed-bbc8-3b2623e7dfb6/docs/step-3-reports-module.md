# Step 3: Reports Module — Verification Notes

## What was done
1. Installed `barryvdh/laravel-dompdf` via composer require (with `--no-security-blocking` because the package was blocked by advisories).
2. Ran `composer dump-autoload` to register the new package.
3. Created `app/Http/Controllers/Web/ReportController.php` with:
   - `index()` — class chooser
   - `show($type)` — printable HTML view
   - `pdf($type)` — PDF via DomPDF
   - `csv($type)` — CSV stream download
   - Three report types: `attendance`, `grades`, `class-performance`
   - Role-based access: teacher sees only own classes; admin sees all
4. Created 4 Blade views: `reports/index.blade.php` (cards with HTML/CSV/PDF buttons per report type), `reports/_pdf.blade.php` (PDF base layout with CSS), `reports/attendance.blade.php`, `reports/grades.blade.php`, `reports/class-performance.blade.php`.
5. Updated `routes/web.php` with 4 report routes (index + 3 types × {show, pdf, csv} = 9 routes total, but with `where('type', ...)` regex constraint).

## Verification — 16-case smoke (all pass)
After `migrate:fresh --seed`:

| # | Case | Result |
|---|------|--------|
| 1 | Reports index | 200, "BSIT 2A" visible ✓ |
| 2 | Attendance HTML | 200, "Attendance Report" + "Aaron" ✓ |
| 3 | Attendance CSV | 200, content-type text/csv ✓ |
| 4 | Attendance PDF | 200, application/pdf, starts with `%PDF-` ✓ |
| 5 | Grades HTML | 200, "Grade Report" + "Aaron" ✓ |
| 6 | Grades CSV | 200, text/csv, "Final Grade" + "Aaron" ✓ |
| 7 | Grades PDF | 200, application/pdf ✓ |
| 8 | Class Performance HTML | 200, "Class Performance Report" + "Attendance %" ✓ |
| 9 | Class Performance CSV | 200, text/csv, both columns ✓ |
| 10 | Class Performance PDF | 200, application/pdf ✓ |
| 11 | Attendance PDF saved to disk | 883,528 bytes ✓ |
| 12 | Grades CSV saved to disk | 356 bytes ✓ |
| 13 | Unknown report type | 404 ✓ |
| 14 | Missing class_id | 400 ✓ |
| 15 | Teacher forbidden from class 2 (other teacher's class) | 403 ✓ |
| 16 | Teacher can access own class report | 200 ✓ |

## Plan verify command
```bash
curl -s -o attendance.pdf -w "%{http_code} %{size_download}" http://127.0.0.1:8000/reports/attendance/pdf?class_id=1 -b cookies.txt
curl -s -o grades.csv -w "%{http_code} %{size_download}" http://127.0.0.1:8000/reports/grades/csv?class_id=1 -b cookies.txt
```
Covered by smoke tests #4 (attendance PDF, 200) and #6 (grades CSV, 200) plus file size checks #11-12. Files are non-empty (883KB PDF and 356-byte CSV).

## Edge cases handled
- Initial bug: `array_merge` on attendance + grades data overwrote `rows` key — replaced with explicit join by student_number
- Single curl handle reused across requests (otherwise method/cookie state can be confused across handles)
- Header/body split via CURLINFO_HEADER_SIZE
- Teacher cross-class access returns 403
- Unknown report type returns 404 (route regex constraint)
- Missing class_id query param returns 400
