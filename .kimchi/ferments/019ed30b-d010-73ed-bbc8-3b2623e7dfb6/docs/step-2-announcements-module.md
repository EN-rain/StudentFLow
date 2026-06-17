# Step 2: Announcements Module — Verification Notes

## What was done
1. Created `app/Http/Requests/StoreAnnouncementRequest.php` — validation including priority enum (Normal/Important/Urgent), publish_date required, expiration_date >= publish_date, optional class_id.
2. Created `app/Http/Controllers/Api/AnnouncementController.php` — REST CRUD with role scoping. Teacher can only post to own classes (returns 403 otherwise). Admin can post on behalf of any teacher via `teacher_id`.
3. Created `app/Http/Controllers/Web/AnnouncementWebController.php` — web CRUD with same authorization.
4. Created 5 Blade views: `announcements/_form.blade.php` (shared form partial), `announcements/index.blade.php` (cards with priority color badges), `announcements/create.blade.php`, `announcements/edit.blade.php`, `announcements/show.blade.php` (full message + metadata).
5. Updated `routes/api.php` and `routes/web.php` with announcement routes.

## Verification — 13-case smoke (all pass)
After `migrate:fresh --seed`:

| # | Case | Result |
|---|------|--------|
| 1 | Admin list announcements | 200, count=3 ✓ |
| 2 | Teacher list (own only) | 200, count=1 (BSIT 2A) ✓ |
| 3 | Show announcement 1 | 200, priority=Important ✓ |
| 4 | Plan verify — teacher creates Smoke Announcement | 201 ✓ |
| 5 | Teacher forbidden from class 2 | 403 ✓ |
| 6 | Validation: expiration < publish | 422 ✓ |
| 7 | Update announcement (title + priority change) | 200 ✓ |
| 8 | Delete announcement | 200 ✓ |
| 9 | Admin posts on behalf of Cruz | 201 ✓ |
| 10 | Cruz sees the admin-posted announcement | ✓ |
| 11 | Web GET /announcements | 200, "Java Project Consultation" ✓ |
| 12 | Web GET /announcements/1 | 200, "Project consultation will be held" ✓ |
| 13 | Web GET /announcements/create | 200, "New Announcement" form ✓ |

## Plan verify command
`POST /api/announcements` with teacher token + Smoke Announcement payload is smoke case #4 — returns HTTP 201.

## Edge cases handled
- Teacher cross-class post returns 403
- Expiration date validation (must be on/after publish_date)
- Admin can post on behalf of any teacher via explicit `teacher_id`; teacher's own posts use their linked teacher record
- Teacher CRUD operations scoped to their own announcements (show/update/delete return 403 for others')
