# StudentFlow User Manual

## Roles

StudentFlow has three application roles:

- Administrator
- Teacher
- Student

Available pages and API routes depend on the authenticated role.

## Administrator

Administrators can:

- create, edit, disable, and restore teacher accounts
- update school settings
- review activity logs
- inspect school-wide reports
- manage records allowed by administrator routes

Main web areas:

```text
Dashboard
Teachers
Settings
Activity Logs
Reports
```

Teacher seed passwords are controlled through environment values. Do not share production credentials through documentation.

## Teacher

Teachers can:

- create and update classes
- manage students and enrollments
- approve or reject classroom join requests
- mark attendance
- create grade categories and grade items
- record student scores
- create assignments and review submissions
- publish announcements
- create and publish exams
- review exam attempts and answers
- generate class and student reports

Typical workflow:

1. Create a class.
2. Add students or approve join requests.
3. Create attendance, grade, assignment, and exam records.
4. Review student progress.
5. Generate reports when required.

## Student

Students use the Android client for most student-facing functions.

Students can:

- view their profile and dashboard
- view classes and announcements
- view assignments
- review grades and attendance
- view and submit exams
- request to join a class using a join code
- sign in through configured Google or GitHub accounts

A classroom join request remains pending until a teacher or administrator approves it.

## Web login

Open the deployed application URL or the local URL:

```text
http://127.0.0.1:8000
```

Enter the assigned username and password. Password reset requires the configured mail provider.

## Android login

1. Start the Laravel backend or use the deployed API.
2. Open the Android application.
3. Sign in with the method allowed for the account.
4. The app stores the Sanctum bearer token in encrypted local storage.

For local emulator testing, the API normally uses:

```text
http://10.0.2.2:8000/api/
```

## Classes and enrollment

Teachers create classes and may enroll students directly.

Students can enter a classroom join code from Android. The request must be approved before the student receives access to the class.

## Attendance

Teachers select a class and date, then mark each student using the supported attendance status. Attendance history and summary reports are available after records are saved.

## Grades

Teachers create grade categories and items, assign maximum scores, and enter student results. Final grades are calculated from the configured records and weights.

## Assignments

Teachers create assignments with instructions and deadlines. Submission records can be reviewed and scored for each enrolled student.

## Announcements

Teachers publish announcements to a class. Email delivery depends on the configured Laravel mail driver.

## Exams

Teachers create exams, questions, and answer options, then publish the exam. Students submit attempts through Android or supported magic links. Teachers can audit attempts, answers, and scores.

## Reports

Available reports include:

- student profile
- class attendance
- class grades
- class performance
- missing assignments
- failing grades
- frequent absences

Some reports can be exported as PDF.

## Passwords and sessions

Users can change their password from supported web or Android screens. Logging out revokes the active API token for the client session.

Contact an administrator when an account is disabled or credentials cannot be recovered through the configured reset process.
