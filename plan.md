# StudentFlow — Student Management System

## 1. Project Overview

**StudentFlow** is a student management system designed to help teachers manage classes, student records, attendance, grades, assignments, announcements, and academic reports.

The system will include:

1. Android application written in Java
2. PHP backend API
3. Responsive web dashboard
4. MySQL database
5. Teacher and administrator accounts
6. Sample data for testing and demonstrations

The application should use a clean and simple interface so teachers can perform common tasks quickly.

---

# 2. Technology Stack

## Android Application

* Java
* Android Studio
* XML layouts
* Material Design components
* Retrofit for API communication
* Gson for JSON parsing
* SharedPreferences or EncryptedSharedPreferences
* Room Database for optional offline caching

## Backend

* PHP 8 or newer
* Laravel or structured PHP MVC
* REST API
* Token-based authentication
* JSON responses

## Web Dashboard

* PHP
* HTML
* CSS
* JavaScript
* Bootstrap 5

## Database

* MySQL or MariaDB

---

# 3. User Roles

## Administrator

The administrator can:

* Add and manage teacher accounts
* View all students
* View all classes
* Disable or reactivate accounts
* Review activity logs
* View system statistics
* Manage school settings

## Teacher

Teachers can:

* Create and manage classes
* Add and edit students
* Enroll students in classes
* Record attendance
* Enter grades
* Create assignments
* Publish announcements
* Generate reports

---

# 4. Main Features

## 4.1 Authentication

Users log in using:

* Email or username
* Password

Features:

* Login
* Logout
* Change password
* Forgot password
* Remember login
* Disabled account checking

---

## 4.2 Dashboard

The teacher dashboard will show:

* Total students
* Total classes
* Today's classes
* Students absent today
* Pending assignments
* Recent announcements
* Recent grade updates

Example dashboard statistics:

* Total students: 20
* Total classes: 3
* Absent today: 2
* Pending assignments: 4
* Recent announcements: 3

---

## 4.3 Class Management

Teachers can:

* Create classes
* Edit class details
* Delete classes
* Assign subjects
* Add schedules
* Set school year and semester
* View enrolled students

Class information:

* Class name
* Section
* Subject
* Grade level
* School year
* Semester
* Schedule
* Room
* Assigned teacher

---

## 4.4 Student Management

Teachers can:

* Add students
* Edit student information
* Remove students
* Search students
* Filter students by class
* View student profiles
* Enroll students in multiple classes

Student information:

* Student number
* First name
* Middle name
* Last name
* Gender
* Birth date
* Email
* Contact number
* Address
* Guardian name
* Guardian contact number
* Profile image
* Account status

---

## 4.5 Attendance Management

Attendance statuses:

* Present
* Absent
* Late
* Excused

Attendance process:

1. Select a class.
2. Select the attendance date.
3. Display all enrolled students.
4. Mark attendance status.
5. Add optional remarks.
6. Save the attendance record.

Additional features:

* Mark all present
* Edit attendance records
* View attendance history
* Filter attendance by date
* Calculate attendance percentage
* Generate attendance reports

---

## 4.6 Grade Management

Teachers can create grading categories.

Example categories:

* Quizzes: 20%
* Assignments: 20%
* Activities: 15%
* Projects: 20%
* Exams: 25%

Teachers can:

* Create grade items
* Set maximum scores
* Enter student scores
* Edit scores
* Add remarks
* Calculate category averages
* Calculate final grades
* View class rankings

Example formula:

```text
Final Grade =
Quiz Average × 20%
+ Assignment Average × 20%
+ Activity Average × 15%
+ Project Average × 20%
+ Exam Average × 25%
```

---

## 4.7 Assignment Management

Teachers can:

* Create assignments
* Add instructions
* Select a class
* Set a deadline
* Set maximum score
* Add attachment links
* Record completion status
* Enter assignment scores

Assignment statuses:

* Upcoming
* Active
* Overdue
* Completed
* Cancelled

---

## 4.8 Announcement Management

Teachers can publish announcements for selected classes.

Announcement information:

* Title
* Message
* Target class
* Priority
* Publication date
* Expiration date

Priority levels:

* Normal
* Important
* Urgent

---

## 4.9 Reports

Available reports:

* Student profile report
* Attendance report
* Grade report
* Class performance report
* Missing assignments report
* Students with failing grades
* Students with frequent absences

Export formats:

* PDF
* CSV
* Printable web page

---

# 5. Android Application Screens

## Authentication Screens

* Splash screen
* Login screen
* Forgot password screen
* Change password screen

## Main Screens

* Dashboard
* Classes
* Students
* Attendance
* Grades
* Assignments
* Announcements
* Reports
* Profile

## Recommended Bottom Navigation

* Dashboard
* Classes
* Attendance
* Grades
* Profile

---

# 6. Web Dashboard

## Administrator Features

* Manage teachers
* Manage students
* View classes
* Disable accounts
* View activity logs
* View statistics

## Teacher Features

* Manage classes
* Manage students
* Record attendance
* Enter grades
* Create assignments
* Create announcements
* Generate reports

---

# 7. Sample Teacher Accounts

## Administrator Account

| Field    | Sample Value                                              |
| -------- | --------------------------------------------------------- |
| Name     | Maria Santos                                              |
| Username | admin                                                     |
| Email    | [admin@studentflow.local](mailto:admin@studentflow.local) |
| Password | Admin123!                                                 |
| Role     | Administrator                                             |
| Status   | Active                                                    |

## Teacher 1

| Field           | Sample Value                                                        |
| --------------- | ------------------------------------------------------------------- |
| Employee Number | TCH-2026-001                                                        |
| Name            | John Michael Reyes                                                  |
| Email           | [john.reyes@studentflow.local](mailto:john.reyes@studentflow.local) |
| Username        | john.reyes                                                          |
| Password        | Teacher123!                                                         |
| Department      | Information Technology                                              |
| Contact Number  | 09171234567                                                         |
| Status          | Active                                                              |

## Teacher 2

| Field           | Sample Value                                                          |
| --------------- | --------------------------------------------------------------------- |
| Employee Number | TCH-2026-002                                                          |
| Name            | Angela Marie Cruz                                                     |
| Email           | [angela.cruz@studentflow.local](mailto:angela.cruz@studentflow.local) |
| Username        | angela.cruz                                                           |
| Password        | Teacher123!                                                           |
| Department      | Mathematics                                                           |
| Contact Number  | 09181234567                                                           |
| Status          | Active                                                                |

## Teacher 3

| Field           | Sample Value                                                                    |
| --------------- | ------------------------------------------------------------------------------- |
| Employee Number | TCH-2026-003                                                                    |
| Name            | Roberto Dela Peña                                                               |
| Email           | [roberto.delapena@studentflow.local](mailto:roberto.delapena@studentflow.local) |
| Username        | roberto.delapena                                                                |
| Password        | Teacher123!                                                                     |
| Department      | General Education                                                               |
| Contact Number  | 09191234567                                                                     |
| Status          | Active                                                                          |

These passwords are for demonstration only and must be changed in production.

---

# 8. Sample Classes

## Class 1

| Field       | Sample Value                            |
| ----------- | --------------------------------------- |
| Class Name  | BSIT 2A                                 |
| Section     | A                                       |
| Subject     | Object-Oriented Programming             |
| Grade Level | Second Year College                     |
| School Year | 2026–2027                               |
| Semester    | First Semester                          |
| Schedule    | Monday and Wednesday, 10:00 AM–11:30 AM |
| Room        | Computer Laboratory 2                   |
| Teacher     | John Michael Reyes                      |

## Class 2

| Field       | Sample Value                          |
| ----------- | ------------------------------------- |
| Class Name  | BSIT 1B                               |
| Section     | B                                     |
| Subject     | Mathematics in the Modern World       |
| Grade Level | First Year College                    |
| School Year | 2026–2027                             |
| Semester    | First Semester                        |
| Schedule    | Tuesday and Thursday, 1:00 PM–2:30 PM |
| Room        | Room 204                              |
| Teacher     | Angela Marie Cruz                     |

## Class 3

| Field       | Sample Value             |
| ----------- | ------------------------ |
| Class Name  | BSIT 3A                  |
| Section     | A                        |
| Subject     | Ethics                   |
| Grade Level | Third Year College       |
| School Year | 2026–2027                |
| Semester    | First Semester           |
| Schedule    | Friday, 8:00 AM–11:00 AM |
| Room        | Room 301                 |
| Teacher     | Roberto Dela Peña        |

---

# 9. Sample Students

| Student Number | Name               | Gender | Email                                                                           | Class   |
| -------------- | ------------------ | ------ | ------------------------------------------------------------------------------- | ------- |
| 2026-0001      | Aaron Villanueva   | Male   | [aaron.villanueva@studentflow.local](mailto:aaron.villanueva@studentflow.local) | BSIT 2A |
| 2026-0002      | Bianca Ramos       | Female | [bianca.ramos@studentflow.local](mailto:bianca.ramos@studentflow.local)         | BSIT 2A |
| 2026-0003      | Carlo Mendoza      | Male   | [carlo.mendoza@studentflow.local](mailto:carlo.mendoza@studentflow.local)       | BSIT 2A |
| 2026-0004      | Denise Garcia      | Female | [denise.garcia@studentflow.local](mailto:denise.garcia@studentflow.local)       | BSIT 2A |
| 2026-0005      | Ethan Flores       | Male   | [ethan.flores@studentflow.local](mailto:ethan.flores@studentflow.local)         | BSIT 2A |
| 2026-0006      | Faith Navarro      | Female | [faith.navarro@studentflow.local](mailto:faith.navarro@studentflow.local)       | BSIT 2A |
| 2026-0007      | Gabriel Torres     | Male   | [gabriel.torres@studentflow.local](mailto:gabriel.torres@studentflow.local)     | BSIT 2A |
| 2026-0008      | Hannah Lim         | Female | [hannah.lim@studentflow.local](mailto:hannah.lim@studentflow.local)             | BSIT 1B |
| 2026-0009      | Ivan Castillo      | Male   | [ivan.castillo@studentflow.local](mailto:ivan.castillo@studentflow.local)       | BSIT 1B |
| 2026-0010      | Jasmine Aquino     | Female | [jasmine.aquino@studentflow.local](mailto:jasmine.aquino@studentflow.local)     | BSIT 1B |
| 2026-0011      | Kevin Bautista     | Male   | [kevin.bautista@studentflow.local](mailto:kevin.bautista@studentflow.local)     | BSIT 1B |
| 2026-0012      | Lara Santiago      | Female | [lara.santiago@studentflow.local](mailto:lara.santiago@studentflow.local)       | BSIT 1B |
| 2026-0013      | Mark Anthony Perez | Male   | [mark.perez@studentflow.local](mailto:mark.perez@studentflow.local)             | BSIT 1B |
| 2026-0014      | Nicole Fernandez   | Female | [nicole.fernandez@studentflow.local](mailto:nicole.fernandez@studentflow.local) | BSIT 1B |
| 2026-0015      | Owen Martinez      | Male   | [owen.martinez@studentflow.local](mailto:owen.martinez@studentflow.local)       | BSIT 3A |
| 2026-0016      | Patricia Lopez     | Female | [patricia.lopez@studentflow.local](mailto:patricia.lopez@studentflow.local)     | BSIT 3A |
| 2026-0017      | Quentin Rivera     | Male   | [quentin.rivera@studentflow.local](mailto:quentin.rivera@studentflow.local)     | BSIT 3A |
| 2026-0018      | Rachel Gomez       | Female | [rachel.gomez@studentflow.local](mailto:rachel.gomez@studentflow.local)         | BSIT 3A |
| 2026-0019      | Samuel Domingo     | Male   | [samuel.domingo@studentflow.local](mailto:samuel.domingo@studentflow.local)     | BSIT 3A |
| 2026-0020      | Trisha Valencia    | Female | [trisha.valencia@studentflow.local](mailto:trisha.valencia@studentflow.local)   | BSIT 3A |

---

# 10. Detailed Sample Student Records

## Student 1

| Field            | Value                                                                           |
| ---------------- | ------------------------------------------------------------------------------- |
| Student Number   | 2026-0001                                                                       |
| Name             | Aaron Miguel Villanueva                                                         |
| Gender           | Male                                                                            |
| Birth Date       | March 12, 2006                                                                  |
| Email            | [aaron.villanueva@studentflow.local](mailto:aaron.villanueva@studentflow.local) |
| Contact Number   | 09911234567                                                                     |
| Address          | Cebu City                                                                       |
| Guardian         | Roberto Villanueva                                                              |
| Guardian Contact | 09171112222                                                                     |
| Status           | Active                                                                          |

## Student 2

| Field            | Value                                                                   |
| ---------------- | ----------------------------------------------------------------------- |
| Student Number   | 2026-0002                                                               |
| Name             | Bianca Marie Ramos                                                      |
| Gender           | Female                                                                  |
| Birth Date       | July 21, 2006                                                           |
| Email            | [bianca.ramos@studentflow.local](mailto:bianca.ramos@studentflow.local) |
| Contact Number   | 09921234567                                                             |
| Address          | Mandaue City                                                            |
| Guardian         | Elena Ramos                                                             |
| Guardian Contact | 09182223333                                                             |
| Status           | Active                                                                  |

## Student 3

| Field            | Value                                                                     |
| ---------------- | ------------------------------------------------------------------------- |
| Student Number   | 2026-0003                                                                 |
| Name             | Carlo James Mendoza                                                       |
| Gender           | Male                                                                      |
| Birth Date       | November 8, 2005                                                          |
| Email            | [carlo.mendoza@studentflow.local](mailto:carlo.mendoza@studentflow.local) |
| Contact Number   | 09931234567                                                               |
| Address          | Lapu-Lapu City                                                            |
| Guardian         | Ramon Mendoza                                                             |
| Guardian Contact | 09193334444                                                               |
| Status           | Active                                                                    |

---

# 11. Sample Attendance Records

## BSIT 2A Attendance

Date: June 15, 2026

| Student          | Status  | Remarks                 |
| ---------------- | ------- | ----------------------- |
| Aaron Villanueva | Present |                         |
| Bianca Ramos     | Present |                         |
| Carlo Mendoza    | Late    | Arrived 15 minutes late |
| Denise Garcia    | Present |                         |
| Ethan Flores     | Absent  | No notification         |
| Faith Navarro    | Excused | Medical appointment     |
| Gabriel Torres   | Present |                         |

Date: June 17, 2026

| Student          | Status  | Remarks         |
| ---------------- | ------- | --------------- |
| Aaron Villanueva | Present |                 |
| Bianca Ramos     | Late    | Traffic         |
| Carlo Mendoza    | Present |                 |
| Denise Garcia    | Present |                 |
| Ethan Flores     | Present |                 |
| Faith Navarro    | Present |                 |
| Gabriel Torres   | Absent  | No notification |

---

# 12. Sample Grade Categories

Class: BSIT 2A
Subject: Object-Oriented Programming

| Category    | Weight |
| ----------- | -----: |
| Quizzes     |    20% |
| Activities  |    15% |
| Assignments |    20% |
| Project     |    20% |
| Final Exam  |    25% |

Total: 100%

---

# 13. Sample Grade Items

| Category   | Grade Item                           | Maximum Score |
| ---------- | ------------------------------------ | ------------: |
| Quiz       | Quiz 1: Java Basics                  |            20 |
| Quiz       | Quiz 2: Classes and Objects          |            20 |
| Activity   | Activity 1: Variables and Methods    |            30 |
| Assignment | Assignment 1: Student Record Program |            50 |
| Project    | Java Inventory System                |           100 |
| Final Exam | Final Examination                    |           100 |

---

# 14. Sample Student Grades

## BSIT 2A Grades

| Student          | Quiz 1 | Quiz 2 | Activity 1 | Assignment 1 | Project | Final Exam |
| ---------------- | -----: | -----: | ---------: | -----------: | ------: | ---------: |
| Aaron Villanueva |     18 |     17 |         27 |           45 |      92 |         88 |
| Bianca Ramos     |     19 |     18 |         29 |           47 |      95 |         91 |
| Carlo Mendoza    |     15 |     16 |         25 |           40 |      85 |         82 |
| Denise Garcia    |     20 |     19 |         28 |           48 |      96 |         94 |
| Ethan Flores     |     13 |     14 |         21 |           35 |      76 |         72 |
| Faith Navarro    |     18 |     19 |         27 |           46 |      91 |         90 |
| Gabriel Torres   |     16 |     15 |         24 |           42 |      84 |         80 |

---

# 15. Sample Assignments

## Assignment 1

| Field         | Value                                                                                            |
| ------------- | ------------------------------------------------------------------------------------------------ |
| Title         | Java Student Record Program                                                                      |
| Class         | BSIT 2A                                                                                          |
| Subject       | Object-Oriented Programming                                                                      |
| Description   | Create a console application that stores and displays student records using classes and objects. |
| Date Assigned | June 10, 2026                                                                                    |
| Deadline      | June 24, 2026                                                                                    |
| Maximum Score | 50                                                                                               |
| Status        | Active                                                                                           |

## Assignment 2

| Field         | Value                                                                                 |
| ------------- | ------------------------------------------------------------------------------------- |
| Title         | Percentage and Interest Worksheet                                                     |
| Class         | BSIT 1B                                                                               |
| Subject       | Mathematics in the Modern World                                                       |
| Description   | Complete the worksheet involving percentages, simple interest, and compound interest. |
| Date Assigned | June 12, 2026                                                                         |
| Deadline      | June 20, 2026                                                                         |
| Maximum Score | 40                                                                                    |
| Status        | Active                                                                                |

## Assignment 3

| Field         | Value                                                                        |
| ------------- | ---------------------------------------------------------------------------- |
| Title         | Ethical Case Analysis                                                        |
| Class         | BSIT 3A                                                                      |
| Subject       | Ethics                                                                       |
| Description   | Write a short analysis of an ethical issue involving privacy and technology. |
| Date Assigned | June 13, 2026                                                                |
| Deadline      | June 27, 2026                                                                |
| Maximum Score | 100                                                                          |
| Status        | Active                                                                       |

---

# 16. Sample Announcements

## Announcement 1

| Field            | Value                                                                                                 |
| ---------------- | ----------------------------------------------------------------------------------------------------- |
| Title            | Java Project Consultation                                                                             |
| Class            | BSIT 2A                                                                                               |
| Priority         | Important                                                                                             |
| Message          | Project consultation will be held after class on June 22. Bring your source code and project outline. |
| Published By     | John Michael Reyes                                                                                    |
| Publication Date | June 17, 2026                                                                                         |

## Announcement 2

| Field            | Value                                                                                  |
| ---------------- | -------------------------------------------------------------------------------------- |
| Title            | Quiz Schedule                                                                          |
| Class            | BSIT 1B                                                                                |
| Priority         | Normal                                                                                 |
| Message          | Quiz 1 will be held on June 23. Review percentages, ratios, and interest calculations. |
| Published By     | Angela Marie Cruz                                                                      |
| Publication Date | June 17, 2026                                                                          |

## Announcement 3

| Field            | Value                                                               |
| ---------------- | ------------------------------------------------------------------- |
| Title            | Classroom Change                                                    |
| Class            | BSIT 3A                                                             |
| Priority         | Urgent                                                              |
| Message          | Friday's Ethics class will be held in Room 305 instead of Room 301. |
| Published By     | Roberto Dela Peña                                                   |
| Publication Date | June 17, 2026                                                       |

---

# 17. Suggested Database Tables

## users

```text
id
username
email
password
role
status
created_at
updated_at
```

## teachers

```text
id
user_id
employee_number
first_name
middle_name
last_name
department
contact_number
profile_image
```

## students

```text
id
student_number
first_name
middle_name
last_name
gender
birth_date
email
contact_number
address
guardian_name
guardian_contact
profile_image
status
created_at
updated_at
```

## classes

```text
id
teacher_id
class_name
section
subject
grade_level
school_year
semester
schedule
room
status
created_at
updated_at
```

## class_students

```text
id
class_id
student_id
date_enrolled
status
```

## attendance

```text
id
class_id
student_id
attendance_date
status
remarks
recorded_by
created_at
updated_at
```

## grade_categories

```text
id
class_id
category_name
percentage_weight
```

## grade_items

```text
id
class_id
category_id
title
maximum_score
date_given
```

## student_grades

```text
id
grade_item_id
student_id
score
remarks
created_at
updated_at
```

## assignments

```text
id
class_id
title
description
date_assigned
deadline
maximum_score
status
```

## announcements

```text
id
teacher_id
class_id
title
message
priority
publish_date
expiration_date
```

---

# 18. Sample SQL Seeder Data

```sql
INSERT INTO users
(username, email, password, role, status)
VALUES
('admin', 'admin@studentflow.local', '$2y$10$samplehashedpassword', 'admin', 'active'),
('john.reyes', 'john.reyes@studentflow.local', '$2y$10$samplehashedpassword', 'teacher', 'active'),
('angela.cruz', 'angela.cruz@studentflow.local', '$2y$10$samplehashedpassword', 'teacher', 'active'),
('roberto.delapena', 'roberto.delapena@studentflow.local', '$2y$10$samplehashedpassword', 'teacher', 'active');
```

```sql
INSERT INTO students
(student_number, first_name, middle_name, last_name, gender, email, status)
VALUES
('2026-0001', 'Aaron', 'Miguel', 'Villanueva', 'Male', 'aaron.villanueva@studentflow.local', 'active'),
('2026-0002', 'Bianca', 'Marie', 'Ramos', 'Female', 'bianca.ramos@studentflow.local', 'active'),
('2026-0003', 'Carlo', 'James', 'Mendoza', 'Male', 'carlo.mendoza@studentflow.local', 'active'),
('2026-0004', 'Denise', 'Anne', 'Garcia', 'Female', 'denise.garcia@studentflow.local', 'active'),
('2026-0005', 'Ethan', 'Luis', 'Flores', 'Male', 'ethan.flores@studentflow.local', 'active'),
('2026-0006', 'Faith', 'Rose', 'Navarro', 'Female', 'faith.navarro@studentflow.local', 'active'),
('2026-0007', 'Gabriel', 'John', 'Torres', 'Male', 'gabriel.torres@studentflow.local', 'active');
```

The final seeders should generate real password hashes using PHP's `password_hash()` function instead of storing plain-text passwords.

---

# 19. Suggested Development Timeline

## Weeks 1–2

* Project setup
* Database design
* Login system
* User roles
* Initial UI

## Weeks 3–4

* Class management
* Student management
* Student enrollment
* Search and filters

## Weeks 5–6

* Attendance system
* Attendance history
* Attendance reports

## Weeks 7–8

* Grade categories
* Grade items
* Score entry
* Final-grade calculation

## Weeks 9–10

* Assignments
* Announcements
* Teacher dashboard
* Administrator dashboard

## Weeks 11–12

* Reports
* Testing
* Bug fixes
* Deployment
* Documentation

---

# 20. Final Deliverables

The completed StudentFlow project should include:

1. Android application written in Java
2. PHP REST API
3. Responsive PHP web dashboard
4. MySQL database
5. Administrator account
6. Sample teacher accounts
7. Sample student records
8. Sample classes
9. Sample attendance records
10. Sample grades
11. Sample assignments
12. Sample announcements
13. SQL migrations and seeders
14. Android APK
15. Full source code
16. Build instructions
17. Deployment instructions
18. API documentation
19. User manual
20. One live deployment
