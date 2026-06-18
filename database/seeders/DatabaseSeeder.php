<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\GradeCategory;
use App\Models\GradeItem;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    private array $seededUsers = [];

    private array $seededTeachers = [];

    private array $seededClasses = [];

    public function run(): void
    {
        if (app()->environment('production') && ! config('studentflow.allow_starter_seed', false)) {
            throw new \RuntimeException('Refusing to run the destructive starter-data seeder directly in production.');
        }

        if (! config('studentflow.seed_starter_data')) {
            $this->seedBootstrapOnly();

            return;
        }

        $driver = DB::connection()->getDriverName();
        $usesSqlitePragma = $driver === 'sqlite';

        // Disable FK checks only for SQLite to allow truncate-cascade.
        if ($usesSqlitePragma) {
            DB::statement('PRAGMA foreign_keys = OFF');
        }

        DB::table('exam_answers')->truncate();
        DB::table('exam_attempts')->truncate();
        DB::table('exam_questions')->truncate();
        DB::table('exams')->truncate();
        DB::table('activity_logs')->truncate();

        // PostgreSQL implements truncate() with CASCADE. Since users.student_id
        // references students and teachers.user_id references users, truncating
        // students after creating users/teachers deletes those new parent rows.
        // Seed students first, then rebuild users and teachers from their IDs.
        $this->seedStudents();
        $this->seedUsers();
        $this->seedTeachers();
        $this->seedSchoolSettings();

        if (config('studentflow.seed_starter_data')) {
            $this->seedClasses();
            $this->seedEnrollments();
            $this->seedAttendance();
            $this->seedGradeCategories();
            $this->seedGradeItems();
            $this->seedStudentGrades();
            $this->seedAssignments();
            $this->seedAssignmentSubmissions();
            $this->seedAnnouncements();
            $this->seedExams();
        }

        if ($usesSqlitePragma) {
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }

    private function seedUsers(): void
    {
        DB::table('users')->truncate();

        $users = [
            [
                'username' => 'admin',
                'name' => 'Maria Santos',
                'email' => 'admin@studentflow.local',
                'password' => Hash::make($this->seedPassword('STUDENTFLOW_SEED_ADMIN_PASSWORD')),
                'role' => 'admin',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'john.reyes',
                'name' => 'John Michael Reyes',
                'email' => 'john.reyes@studentflow.local',
                'password' => Hash::make($this->seedPassword('STUDENTFLOW_SEED_TEACHER_PASSWORD')),
                'role' => 'teacher',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'angela.cruz',
                'name' => 'Angela Marie Cruz',
                'email' => 'angela.cruz@studentflow.local',
                'password' => Hash::make($this->seedPassword('STUDENTFLOW_SEED_TEACHER_PASSWORD')),
                'role' => 'teacher',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'roberto.delapena',
                'name' => 'Roberto Dela Peña',
                'email' => 'roberto.delapena@studentflow.local',
                'password' => Hash::make($this->seedPassword('STUDENTFLOW_SEED_TEACHER_PASSWORD')),
                'role' => 'teacher',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'paolo.mercado',
                'name' => 'Paolo Luis Mercado',
                'email' => 'paolo.mercado@studentflow.local',
                'password' => Hash::make($this->seedPassword('STUDENTFLOW_SEED_TEACHER_PASSWORD')),
                'role' => 'teacher',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'sophia.tan',
                'name' => 'Sophia Marie Tan',
                'email' => 'sophia.tan@studentflow.local',
                'password' => Hash::make($this->seedPassword('STUDENTFLOW_SEED_TEACHER_PASSWORD')),
                'role' => 'teacher',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => '2026-0001',
                'name' => 'Aaron Miguel Villanueva',
                'email' => 'aaron.villanueva@studentflow.local',
                'password' => Hash::make($this->seedPassword('STUDENTFLOW_SEED_STUDENT_PASSWORD')),
                'role' => 'student',
                'status' => 'active',
                'student_id' => Student::where('student_number', '2026-0001')->value('id'),
                'google_id' => 'google-aaron-villanueva',
                'github_id' => 'github-aaron-villanueva',
                'github_username' => 'aaron-villanueva',
                'social_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            ...collect(range(2, 10))->map(function ($number) {
                $student = Student::where('student_number', sprintf('2026-%04d', $number))->firstOrFail();

                return [
                    'username' => $student->student_number,
                    'name' => $student->full_name,
                    'email' => $student->email,
                    'password' => Hash::make($this->seedPassword('STUDENTFLOW_SEED_STUDENT_PASSWORD')),
                    'role' => 'student',
                    'status' => 'active',
                    'student_id' => $student->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all(),
        ];

        foreach ($users as $user) {
            DB::table('users')->insert($user);
        }

        $expected = collect($users)->pluck('username')->all();
        $actual = User::whereIn('username', $expected)->pluck('username')->all();
        $missing = array_values(array_diff($expected, $actual));
        if ($missing !== []) {
            throw new \RuntimeException('Seeder failed to persist expected users: '.implode(', ', $missing));
        }

        $this->seededUsers = User::whereIn('username', $expected)->pluck('id', 'username')->all();
    }

    private function seedTeachers(): void
    {
        DB::table('teachers')->truncate();

        $teachers = [
            [
                'username' => 'john.reyes',
                'employee_number' => 'TCH-2026-001',
                'first_name' => 'John Michael',
                'middle_name' => null,
                'last_name' => 'Reyes',
                'department' => 'Information Technology',
                'contact_number' => '09171234567',
                'profile_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'angela.cruz',
                'employee_number' => 'TCH-2026-002',
                'first_name' => 'Angela Marie',
                'middle_name' => null,
                'last_name' => 'Cruz',
                'department' => 'Information Technology',
                'contact_number' => '09181234567',
                'profile_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'roberto.delapena',
                'employee_number' => 'TCH-2026-003',
                'first_name' => 'Roberto',
                'middle_name' => null,
                'last_name' => 'Dela Peña',
                'department' => 'Information Technology',
                'contact_number' => '09191234567',
                'profile_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'paolo.mercado',
                'employee_number' => 'TCH-2026-004',
                'first_name' => 'Paolo Luis',
                'middle_name' => null,
                'last_name' => 'Mercado',
                'department' => 'Information Technology',
                'contact_number' => '09201234567',
                'profile_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'sophia.tan',
                'employee_number' => 'TCH-2026-005',
                'first_name' => 'Sophia Marie',
                'middle_name' => null,
                'last_name' => 'Tan',
                'department' => 'Information Technology',
                'contact_number' => '09211234567',
                'profile_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($teachers as $teacher) {
            $record = Teacher::create([
                'user_id' => $this->requiredUserId($teacher['username']),
                'employee_number' => $teacher['employee_number'],
                'first_name' => $teacher['first_name'],
                'middle_name' => $teacher['middle_name'],
                'last_name' => $teacher['last_name'],
                'department' => $teacher['department'],
                'contact_number' => $teacher['contact_number'],
                'profile_image' => $teacher['profile_image'],
            ]);

            $this->seededTeachers[$teacher['employee_number']] = $record->id;
        }
    }

    private function seedStudents(): void
    {
        DB::table('students')->truncate();

        // Per plan Ã‚Section 9 + Ã‚Section 10 (only first 3 are detailed in Ã‚Section 10; others use defaults)
        $students = [
            ['2026-0001', 'Aaron', 'Miguel', 'Villanueva', 'Male', '2006-03-12', 'aaron.villanueva@studentflow.local', '09911234567', 'Cebu City', 'Roberto Villanueva', '09171112222'],
            ['2026-0002', 'Bianca', 'Marie', 'Ramos', 'Female', '2006-07-21', 'bianca.ramos@studentflow.local', '09921234567', 'Mandaue City', 'Elena Ramos', '09182223333'],
            ['2026-0003', 'Carlo', 'James', 'Mendoza', 'Male', '2005-11-08', 'carlo.mendoza@studentflow.local', '09931234567', 'Lapu-Lapu City', 'Ramon Mendoza', '09193334444'],
            ['2026-0004', 'Denise', 'Anne', 'Garcia', 'Female', '2006-01-15', 'denise.garcia@studentflow.local', '09941234567', 'Cebu City', 'Marites Garcia', '09204445555'],
            ['2026-0005', 'Ethan', 'Luis', 'Flores', 'Male', '2006-04-22', 'ethan.flores@studentflow.local', '09951234567', 'Mandaue City', 'Pedro Flores', '09215556666'],
            ['2026-0006', 'Faith', 'Rose', 'Navarro', 'Female', '2006-09-30', 'faith.navarro@studentflow.local', '09961234567', 'Talamban', 'Lorna Navarro', '09226667777'],
            ['2026-0007', 'Gabriel', 'John', 'Torres', 'Male', '2005-12-05', 'gabriel.torres@studentflow.local', '09971234567', 'Lahug', 'Jose Torres', '09237778888'],
            ['2026-0008', 'Hannah', 'Grace', 'Lim', 'Female', '2007-02-18', 'hannah.lim@studentflow.local', '09981234567', 'Banilad', 'Teresa Lim', '09248889999'],
            ['2026-0009', 'Ivan', 'James', 'Castillo', 'Male', '2007-06-10', 'ivan.castillo@studentflow.local', '09991234567', 'Carcar City', 'Roberto Castillo', '09259990000'],
            ['2026-0010', 'Jasmine', 'Marie', 'Aquino', 'Female', '2007-08-25', 'jasmine.aquino@studentflow.local', '09902234567', 'Toledo City', 'Elena Aquino', '09260001111'],
        ];

        $students = array_slice($students, 0, 10);

        $rows = [];
        $now = now();
        foreach ($students as $s) {
            $rows[] = [
                'student_number' => $s[0],
                'first_name' => $s[1],
                'middle_name' => $s[2],
                'last_name' => $s[3],
                'gender' => $s[4],
                'birth_date' => $s[5],
                'email' => $s[6],
                'contact_number' => $s[7],
                'address' => $s[8],
                'guardian_name' => $s[9],
                'guardian_contact' => $s[10],
                'profile_image' => null,
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('students')->insert($rows);
    }

    private function seedClasses(): void
    {
        DB::table('classes')->truncate();

        $classes = [
            ['TCH-2026-001', 'BSIT 2A', 'A', 'Object-Oriented Programming with Java', 'Second Year College', 'Monday and Wednesday, 9:00 AM-10:30 AM', 'Computer Laboratory 1'],
            ['TCH-2026-002', 'BSIT 1B', 'B', 'Introduction to Programming with Python', 'First Year College', 'Tuesday and Thursday, 10:30 AM-12:00 PM', 'Computer Laboratory 2'],
            ['TCH-2026-003', 'BSIT 3A', 'A', 'Web Application Development', 'Third Year College', 'Monday and Wednesday, 1:00 PM-2:30 PM', 'Computer Laboratory 3'],
            ['TCH-2026-004', 'BSIT 3B', 'B', 'Mobile Application Development', 'Third Year College', 'Tuesday and Thursday, 2:30 PM-4:00 PM', 'Mobile Development Laboratory'],
            ['TCH-2026-005', 'BSIT 4A', 'A', 'Software Engineering and Testing', 'Fourth Year College', 'Friday, 8:00 AM-11:00 AM', 'Innovation Laboratory'],
        ];

        foreach ($classes as [$employeeNumber, $className, $section, $subject, $gradeLevel, $schedule, $room]) {
            $record = SchoolClass::create([
                'teacher_id' => $this->requiredTeacherId($employeeNumber),
                'class_name' => $className,
                'section' => $section,
                'subject' => $subject,
                'grade_level' => $gradeLevel,
                'school_year' => '2025-2026',
                'semester' => 'Second Semester',
                'schedule' => $schedule,
                'room' => $room,
                'status' => 'active',
            ]);
            $this->seededClasses[$className] = $record->id;
        }
    }

    private function seedEnrollments(): void
    {
        DB::table('class_students')->truncate();

        $classNames = ['BSIT 2A', 'BSIT 1B', 'BSIT 3A', 'BSIT 3B', 'BSIT 4A'];
        $students = Student::orderBy('student_number')->get();
        $rows = [];

        foreach ($students as $index => $student) {
            $className = $classNames[intdiv($index, 2)];
            $rows[] = [
                'class_id' => $this->requiredClassId($className),
                'student_id' => $student->id,
                'date_enrolled' => '2026-01-12',
                'status' => 'enrolled',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('class_students')->insert($rows);
    }

    private function seedAttendance(): void
    {
        DB::table('attendance')->truncate();

        $bsit2a = SchoolClass::where('class_name', 'BSIT 2A')->first();
        $john = User::where('username', 'john.reyes')->first();

        // Per plan Ã‚Section 11: BSIT 2A attendance for June 15 and June 17, 2026
        $attendanceData = [
            // June 15, 2026
            ['2026-0001', 'Present', null],
            ['2026-0002', 'Present', null],
            ['2026-0003', 'Late', 'Arrived 15 minutes late'],
            ['2026-0004', 'Present', null],
            ['2026-0005', 'Absent', 'No notification'],
            ['2026-0006', 'Excused', 'Medical appointment'],
            ['2026-0007', 'Present', null],
            // June 17, 2026
            ['2026-0001', 'Present', null],
            ['2026-0002', 'Late', 'Traffic'],
            ['2026-0003', 'Present', null],
            ['2026-0004', 'Present', null],
            ['2026-0005', 'Present', null],
            ['2026-0006', 'Present', null],
            ['2026-0007', 'Absent', 'No notification'],
        ];

        $rows = [];
        $now = now();
        $byDate = [
            '2026-06-15' => array_slice($attendanceData, 0, 7),
            '2026-06-17' => array_slice($attendanceData, 7, 7),
        ];

        foreach ($byDate as $date => $entries) {
            foreach ($entries as $entry) {
                $student = Student::where('student_number', $entry[0])->first();
                $rows[] = [
                    'class_id' => $this->requiredClassId('BSIT 2A'),
                    'student_id' => $student->id,
                    'attendance_date' => $date,
                    'status' => $entry[1],
                    'remarks' => $entry[2],
                    'recorded_by' => $john->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('attendance')->insert($rows);
    }

    private function seedGradeCategories(): void
    {
        DB::table('grade_categories')->truncate();

        $bsit2a = SchoolClass::where('class_name', 'BSIT 2A')->first();

        // Per plan Ã‚Section 12: Quizzes 20, Activities 15, Assignments 20, Project 20, Final Exam 25
        $categories = [
            ['Quizzes', 20.00],
            ['Activities', 15.00],
            ['Assignments', 20.00],
            ['Project', 20.00],
            ['Final Exam', 25.00],
        ];

        $now = now();
        $rows = [];
        foreach ($categories as $c) {
            $rows[] = [
                'class_id' => $this->requiredClassId('BSIT 2A'),
                'category_name' => $c[0],
                'percentage_weight' => $c[1],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('grade_categories')->insert($rows);
    }

    private function seedGradeItems(): void
    {
        DB::table('grade_items')->truncate();

        $bsit2a = SchoolClass::where('class_name', 'BSIT 2A')->first();

        $quizzes = GradeCategory::where('class_id', $bsit2a->id)->where('category_name', 'Quizzes')->first();
        $activities = GradeCategory::where('class_id', $bsit2a->id)->where('category_name', 'Activities')->first();
        $assignments = GradeCategory::where('class_id', $bsit2a->id)->where('category_name', 'Assignments')->first();
        $project = GradeCategory::where('class_id', $bsit2a->id)->where('category_name', 'Project')->first();
        $finalExam = GradeCategory::where('class_id', $bsit2a->id)->where('category_name', 'Final Exam')->first();

        // Per plan Ã‚Section 13: 6 grade items
        $items = [
            [$quizzes->id, 'Quiz 1: Java Basics', 20.00, '2026-06-12'],
            [$quizzes->id, 'Quiz 2: Classes and Objects', 20.00, '2026-06-19'],
            [$activities->id, 'Activity 1: Variables and Methods', 30.00, '2026-06-14'],
            [$assignments->id, 'Assignment 1: Student Record Program', 50.00, '2026-06-10'],
            [$project->id, 'Java Inventory System', 100.00, '2026-06-15'],
            [$finalExam->id, 'Final Examination', 100.00, '2026-06-26'],
        ];

        $now = now();
        $rows = [];
        foreach ($items as $it) {
            $rows[] = [
                'class_id' => $this->requiredClassId('BSIT 2A'),
                'category_id' => $it[0],
                'title' => $it[1],
                'maximum_score' => $it[2],
                'date_given' => $it[3],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('grade_items')->insert($rows);
    }

    private function seedStudentGrades(): void
    {
        DB::table('student_grades')->truncate();

        $bsit2a = SchoolClass::where('class_name', 'BSIT 2A')->first();
        $items = GradeItem::where('class_id', $bsit2a->id)->orderBy('id')->get();

        // Per plan Ã‚Section 14: scores per student per item (rows: students, columns: items in order)
        // Columns order: Quiz 1, Quiz 2, Activity 1, Assignment 1, Project, Final Exam
        $scores = [
            '2026-0001' => [18, 17, 27, 45, 92, 88], // Aaron Villanueva
            '2026-0002' => [19, 18, 29, 47, 95, 91], // Bianca Ramos
            '2026-0003' => [15, 16, 25, 40, 85, 82], // Carlo Mendoza
            '2026-0004' => [20, 19, 28, 48, 96, 94], // Denise Garcia
            '2026-0005' => [13, 14, 21, 35, 76, 72], // Ethan Flores
            '2026-0006' => [18, 19, 27, 46, 91, 90], // Faith Navarro
            '2026-0007' => [16, 15, 24, 42, 84, 80], // Gabriel Torres
        ];

        $now = now();
        $rows = [];
        foreach ($scores as $studentNumber => $studentScores) {
            $student = Student::where('student_number', $studentNumber)->first();
            foreach ($items as $idx => $item) {
                $rows[] = [
                    'grade_item_id' => $item->id,
                    'student_id' => $student->id,
                    'score' => $studentScores[$idx],
                    'remarks' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('student_grades')->insert($rows);
    }

    private function seedAssignments(): void
    {
        DB::table('assignments')->truncate();

        $bsit2a = SchoolClass::where('class_name', 'BSIT 2A')->first();
        $bsit1b = SchoolClass::where('class_name', 'BSIT 1B')->first();
        $bsit3a = SchoolClass::where('class_name', 'BSIT 3A')->first();

        // Per plan Ã‚Section 15
        $rows = [
            [
                'class_id' => $this->requiredClassId('BSIT 2A'),
                'title' => 'Java Student Record Program',
                'description' => 'Create a console application that stores and displays student records using classes and objects.',
                'date_assigned' => '2026-06-10',
                'deadline' => '2026-06-24',
                'maximum_score' => 50,
                'status' => 'Active',
                'attachment_link' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_id' => $this->requiredClassId('BSIT 1B'),
                'title' => 'Python Console Expense Tracker',
                'description' => 'Build a Python console application using variables, conditions, loops, functions, and file handling.',
                'date_assigned' => '2026-06-12',
                'deadline' => '2026-06-20',
                'maximum_score' => 40,
                'status' => 'Active',
                'attachment_link' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_id' => $this->requiredClassId('BSIT 3A'),
                'title' => 'Responsive Portfolio Website',
                'description' => 'Create a responsive portfolio using semantic HTML, CSS, and JavaScript form validation.',
                'date_assigned' => '2026-06-13',
                'deadline' => '2026-06-27',
                'maximum_score' => 100,
                'status' => 'Active',
                'attachment_link' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_id' => $this->requiredClassId('BSIT 3B'),
                'title' => 'Android Student Notes App',
                'description' => 'Build an Android notes application with multiple activities and local persistence.',
                'date_assigned' => '2026-06-14',
                'deadline' => '2026-06-28',
                'maximum_score' => 100,
                'status' => 'Active',
                'attachment_link' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_id' => $this->requiredClassId('BSIT 4A'),
                'title' => 'Automated Test Plan',
                'description' => 'Prepare unit, integration, and UI tests for a small web application.',
                'date_assigned' => '2026-06-15',
                'deadline' => '2026-06-30',
                'maximum_score' => 100,
                'status' => 'Active',
                'attachment_link' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('assignments')->insert($rows);
    }

    private function seedAssignmentSubmissions(): void
    {
        DB::table('assignment_submissions')->truncate();

        $assignment = Assignment::where('title', 'Java Student Record Program')->first();
        if (! $assignment) {
            return;
        }

        $statuses = [
            '2026-0001' => ['Submitted', 45, '2026-06-22 10:00:00'],
            '2026-0002' => ['Submitted', 47, '2026-06-22 09:15:00'],
            '2026-0003' => ['Late', 40, '2026-06-25 08:30:00'],
            '2026-0004' => ['Submitted', 48, '2026-06-21 14:20:00'],
            '2026-0005' => ['Missing', null, null],
            '2026-0006' => ['Submitted', 46, '2026-06-23 11:45:00'],
            '2026-0007' => ['Pending', null, null],
        ];

        foreach ($statuses as $studentNumber => $data) {
            $student = Student::where('student_number', $studentNumber)->first();
            AssignmentSubmission::create([
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
                'status' => $data[0],
                'score' => $data[1],
                'submitted_at' => $data[2],
                'remarks' => $data[0] === 'Missing' ? 'No submission recorded.' : null,
            ]);
        }
    }

    private function seedAnnouncements(): void
    {
        DB::table('announcements')->truncate();

        $john = Teacher::where('employee_number', 'TCH-2026-001')->first();
        $angela = Teacher::where('employee_number', 'TCH-2026-002')->first();
        $roberto = Teacher::where('employee_number', 'TCH-2026-003')->first();

        $bsit2a = SchoolClass::where('class_name', 'BSIT 2A')->first();
        $bsit1b = SchoolClass::where('class_name', 'BSIT 1B')->first();
        $bsit3a = SchoolClass::where('class_name', 'BSIT 3A')->first();

        // Per plan Ã‚Section 16
        $rows = [
            [
                'teacher_id' => $this->requiredTeacherId('TCH-2026-001'),
                'class_id' => $this->requiredClassId('BSIT 2A'),
                'title' => 'Java Project Consultation',
                'message' => 'Project consultation will be held after class on June 22. Bring your source code and project outline.',
                'priority' => 'Important',
                'publish_date' => '2026-06-17',
                'expiration_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'teacher_id' => $this->requiredTeacherId('TCH-2026-002'),
                'class_id' => $this->requiredClassId('BSIT 1B'),
                'title' => 'Quiz Schedule',
                'message' => 'Quiz 1 will be held on June 23. Review Python variables, conditions, loops, and functions.',
                'priority' => 'Normal',
                'publish_date' => '2026-06-17',
                'expiration_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'teacher_id' => $this->requiredTeacherId('TCH-2026-003'),
                'class_id' => $this->requiredClassId('BSIT 3A'),
                'title' => 'Classroom Change',
                'message' => 'Friday Web Application Development class will be held in Computer Laboratory 3.',
                'priority' => 'Urgent',
                'publish_date' => '2026-06-17',
                'expiration_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('announcements')->insert($rows);
    }

    private function seedExams(): void
    {
        DB::table('exam_answers')->truncate();
        DB::table('exam_attempts')->truncate();
        DB::table('exam_questions')->truncate();
        DB::table('exams')->truncate();

        foreach (SchoolClass::with('students')->orderBy('id')->get() as $class) {
            $assessments = [
                ['Completed Quiz 1', 'closed', now()->subDays(30), now()->subDays(28), 20, 10],
                ['Completed Midterm Examination', 'closed', now()->subDays(20), now()->subDays(18), 60, 50],
                ['Upcoming Quiz 2', 'published', now()->addDays(5), now()->addDays(7), 20, 10],
                ['Upcoming Final Examination', 'published', now()->addDays(14), now()->addDays(16), 90, 50],
            ];

            foreach ($assessments as [$title, $status, $availableFrom, $dueAt, $duration, $points]) {
                $exam = Exam::create([
                    'class_id' => $class->id,
                    'teacher_id' => $class->teacher_id,
                    'title' => $title,
                    'instructions' => 'Answer all questions based on '.$class->subject.'.',
                    'available_from' => $availableFrom,
                    'due_at' => $dueAt,
                    'duration_minutes' => $duration,
                    'maximum_score' => $points,
                    'status' => $status,
                ]);

                $question = ExamQuestion::create([
                    'exam_id' => $exam->id,
                    'prompt' => 'Which statement best describes a core concept in '.$class->subject.'?',
                    'type' => 'multiple_choice',
                    'choices' => ['It organizes program logic', 'It removes all testing', 'It prevents code reuse'],
                    'correct_answer' => 'It organizes program logic',
                    'points' => $points,
                    'sort_order' => 1,
                ]);

                foreach ($class->students as $student) {
                    $completed = $status === 'closed';
                    $attempt = ExamAttempt::create([
                        'exam_id' => $exam->id,
                        'student_id' => $student->id,
                        'magic_token' => Str::random(64),
                        'started_at' => $completed ? $availableFrom->copy()->addMinutes(5) : null,
                        'submitted_at' => $completed ? $availableFrom->copy()->addMinutes(15) : null,
                        'score' => $completed ? $points : null,
                        'status' => $completed ? 'submitted' : 'assigned',
                    ]);

                    if ($completed) {
                        ExamAnswer::create([
                            'exam_attempt_id' => $attempt->id,
                            'exam_question_id' => $question->id,
                            'answer_text' => 'It organizes program logic',
                            'is_correct' => true,
                            'score' => $points,
                        ]);
                    }
                }
            }
        }
    }

    private function seedSchoolSettings(): void
    {
        DB::table('school_setting_histories')->truncate();
        DB::table('school_settings')->truncate();

        $settings = [
            ['school_name', 'StudentFlow Academy'],
            ['school_year', '2025-2026'],
            ['semester', 'Second Semester'],
            ['principal_name', 'Maria Santos'],
            ['contact_email', 'admin@studentflow.local'],
        ];

        foreach ($settings as [$key, $value]) {
            SchoolSetting::create([
                'setting_key' => $key,
                'setting_value' => $value,
                'label' => ucwords(str_replace('_', ' ', $key)),
            ]);
        }
    }

    private function seedBootstrapOnly(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Maria Santos',
                'email' => 'admin@studentflow.local',
                'password' => Hash::make($this->seedPassword('STUDENTFLOW_SEED_ADMIN_PASSWORD')),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        $settings = [
            'school_name' => 'StudentFlow Academy',
            'school_year' => '2025-2026',
            'semester' => 'Second Semester',
            'principal_name' => 'Maria Santos',
            'contact_email' => 'admin@studentflow.local',
        ];

        foreach ($settings as $key => $value) {
            SchoolSetting::firstOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value, 'label' => ucwords(str_replace('_', ' ', $key))]
            );
        }
    }

    private function requiredUserId(string $username): int
    {
        $userId = $this->seededUsers[$username] ?? User::where('username', $username)->value('id');
        if (! $userId) {
            throw new ModelNotFoundException("Seeder expected user [{$username}] to exist.");
        }

        return (int) $userId;
    }

    private function requiredTeacherId(string $employeeNumber): int
    {
        $teacherId = $this->seededTeachers[$employeeNumber] ?? Teacher::where('employee_number', $employeeNumber)->value('id');
        if (! $teacherId) {
            throw new ModelNotFoundException("Seeder expected teacher [{$employeeNumber}] to exist.");
        }

        return (int) $teacherId;
    }

    private function requiredClassId(string $className): int
    {
        $classId = $this->seededClasses[$className] ?? SchoolClass::where('class_name', $className)->value('id');
        if (! $classId) {
            throw new ModelNotFoundException("Seeder expected class [{$className}] to exist.");
        }

        return (int) $classId;
    }

    private function seedPassword(string $envKey): string
    {
        $value = env($envKey);

        if (is_string($value) && trim($value) !== '') {
            return $value;
        }

        throw new \RuntimeException("Missing required starter password environment variable: {$envKey}");
    }
}
