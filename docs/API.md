# StudentFlow API

Base path:

```text
/api
```

Use JSON requests with:

```text
Accept: application/json
Content-Type: application/json
```

Authenticated routes require:

```text
Authorization: Bearer <sanctum-token>
```

## Authentication

```text
POST /auth/login
POST /auth/google
POST /auth/github
POST /auth/forgot-password
POST /auth/reset-password
GET  /auth/me
POST /auth/change-password
POST /auth/logout
```

Password login accepts a username and password. Student social sign-in links a verified provider email to an existing student record.

Required provider environment values:

```env
GOOGLE_CLIENT_ID=
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
```

GitHub callback:

```text
/api/auth/github/callback
```

## Student routes

```text
GET  /student/dashboard
GET  /student/profile
GET  /student/classes
GET  /student/announcements
GET  /student/assignments
GET  /student/grades
GET  /student/attendance
GET  /student/exams
POST /student/exams/{attempt}/submit
```

## Administrator routes

```text
GET  /admin/teachers
POST /admin/teachers
GET  /admin/teachers/{teacher}
PUT  /admin/teachers/{teacher}
PATCH /admin/teachers/{teacher}/status
GET  /admin/settings
PUT  /admin/settings
GET  /admin/activity-logs
```

## Classes and enrollment

```text
GET    /classes
POST   /classes
GET    /classes/{class}
PUT    /classes/{class}
PATCH  /classes/{class}
DELETE /classes/{class}

GET    /classes/{class}/enrollments
POST   /classes/{class}/enrollments
PUT    /classes/{class}/enrollments/{student}
DELETE /classes/{class}/enrollments/{student}
```

Classroom join requests use the class join-code workflow and require teacher or administrator approval.

## Students

```text
GET    /students
POST   /students
GET    /students/{student}
PUT    /students/{student}
PATCH  /students/{student}
DELETE /students/{student}
```

## Attendance

```text
GET  /attendance
POST /attendance
POST /attendance/mark-all-present
GET  /attendance/student/{studentId}/stats
```

## Grades

Grade categories, grade items, and student scores are managed under class-specific routes. Use `php artisan route:list --path=api` for the exact generated route list.

## Assignments

```text
GET    /assignments
POST   /assignments
GET    /assignments/{assignment}
PUT    /assignments/{assignment}
DELETE /assignments/{assignment}
GET    /assignments/{assignment}/submissions
POST   /assignments/{assignment}/submissions
```

## Announcements

```text
GET    /announcements
POST   /announcements
GET    /announcements/{announcement}
PUT    /announcements/{announcement}
DELETE /announcements/{announcement}
```

## Exams

```text
GET  /exams
POST /exams
GET  /exams/{exam}
POST /exams/{exam}/publish
GET  /exams/{exam}/audit
```

Magic exam routes:

```text
GET  /exam/magic/{token}
POST /exam/magic/{token}/submit
```

Browser route:

```text
/exam/magic/{token}
```

## Reports

```text
GET /reports/student-profile?student_id=ID
GET /reports/attendance?class_id=ID
GET /reports/grades?class_id=ID
GET /reports/class-performance?class_id=ID
GET /reports/missing-assignments?class_id=ID
GET /reports/failing-grades?class_id=ID
GET /reports/frequent-absences?class_id=ID
```

## Errors

Validation errors use Laravel's standard JSON response format. Authorization failures return `401` or `403`. Missing records return `404`.

## Route verification

```bash
php artisan route:list --path=api
```
