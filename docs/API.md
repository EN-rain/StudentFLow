# StudentFlow API Reference

Base URL: `/api`

Use JSON requests with `Accept: application/json` and `Content-Type: application/json`.
Authenticated endpoints require `Authorization: Bearer <token>`.

## Authentication

- `POST /auth/login` with `{ "username": "...", "password": "..." }`
- `POST /auth/google` with `{ "id_token": "..." }` for student Google sign-in.
- `POST /auth/github` with `{ "code": "..." }` or `{ "access_token": "..." }` for student GitHub sign-in.
- `POST /auth/forgot-password` with `{ "email": "..." }`
- `POST /auth/reset-password` with `{ "email": "...", "token": "...", "password": "...", "password_confirmation": "..." }`
- `GET /auth/me`
- `POST /auth/change-password`
- `POST /auth/logout`

Student social login links by verified provider email to an existing `students.email`. Configure `GOOGLE_CLIENT_ID`, `GITHUB_CLIENT_ID`, and `GITHUB_CLIENT_SECRET` for real providers.

GitHub callback URL:

```text
/api/auth/github/callback
```

## Student Mobile

- `GET /student/dashboard`
- `GET /student/profile`
- `GET /student/classes`
- `GET /student/announcements`
- `GET /student/assignments`
- `GET /student/grades`
- `GET /student/attendance`
- `GET /student/exams`
- `POST /student/exams/{attempt}/submit`

## Admin

Admin-only endpoints:

- `GET|POST /admin/teachers`
- `GET|PUT /admin/teachers/{teacher}`
- `PATCH /admin/teachers/{teacher}/status`
- `GET|PUT /admin/settings`
- `GET /admin/activity-logs`

## Core Teacher Resources

- Classes: `GET|POST /classes`, `GET|PUT|PATCH|DELETE /classes/{class}`
- Enrollments: `GET|POST /classes/{class}/enrollments`, `PUT|DELETE /classes/{class}/enrollments/{student}`
- Students: `GET|POST /students`, `GET|PUT|PATCH|DELETE /students/{student}`
- Attendance: `GET|POST /attendance`, `POST /attendance/mark-all-present`, `GET /attendance/student/{studentId}/stats`
- Grades: category, item, score, and final-grade routes under `/classes/{class}/...`
- Assignments: `GET|POST /assignments`, `GET|PUT|DELETE /assignments/{assignment}`, `GET|POST /assignments/{assignment}/submissions`
- Announcements: `GET|POST /announcements`, `GET|PUT|DELETE /announcements/{announcement}`
- Exams: `GET|POST /exams`, `GET /exams/{exam}`, `POST /exams/{exam}/publish`, `GET /exams/{exam}/audit`
- Magic exams: `GET /exam/magic/{token}`, `POST /exam/magic/{token}/submit`; browser links use `/exam/magic/{token}`.

## Reports

`GET /reports/{type}` supports:

- `student-profile?student_id=ID`
- `attendance?class_id=ID`
- `grades?class_id=ID`
- `class-performance?class_id=ID`
- `missing-assignments?class_id=ID`
- `failing-grades?class_id=ID`
- `frequent-absences?class_id=ID`
