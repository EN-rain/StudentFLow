<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\ClassStudent;
use App\Models\GradeCategory;
use App\Models\GradeItem;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\StudentGrade;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Disable FK checks for SQLite to allow truncate-cascade
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::table('activity_logs')->truncate();
        $this->seedUsers();
        $this->seedTeachers();
        $this->seedStudents();
        $this->seedClasses();
        $this->seedEnrollments();
        $this->seedAttendance();
        $this->seedGradeCategories();
        $this->seedGradeItems();
        $this->seedStudentGrades();
        $this->seedAssignments();
        $this->seedAssignmentSubmissions();
        $this->seedAnnouncements();
        $this->seedSchoolSettings();

        DB::statement('PRAGMA foreign_keys = ON');
    }

    private function seedUsers(): void
    {
        DB::table('users')->truncate();

        $users = [
            [
                'username' => 'admin',
                'name' => 'Maria Santos',
                'email' => 'admin@studentflow.local',
                'password' => Hash::make('Admin123!'),
                'role' => 'admin',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'john.reyes',
                'name' => 'John Michael Reyes',
                'email' => 'john.reyes@studentflow.local',
                'password' => Hash::make('Teacher123!'),
                'role' => 'teacher',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'angela.cruz',
                'name' => 'Angela Marie Cruz',
                'email' => 'angela.cruz@studentflow.local',
                'password' => Hash::make('Teacher123!'),
                'role' => 'teacher',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'roberto.delapena',
                'name' => 'Roberto Dela Peña',
                'email' => 'roberto.delapena@studentflow.local',
                'password' => Hash::make('Teacher123!'),
                'role' => 'teacher',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);
    }

    private function seedTeachers(): void
    {
        DB::table('teachers')->truncate();

        $john = User::where('username', 'john.reyes')->first();
        $angela = User::where('username', 'angela.cruz')->first();
        $roberto = User::where('username', 'roberto.delapena')->first();

        $teachers = [
            [
                'user_id' => $john->id,
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
                'user_id' => $angela->id,
                'employee_number' => 'TCH-2026-002',
                'first_name' => 'Angela Marie',
                'middle_name' => null,
                'last_name' => 'Cruz',
                'department' => 'Mathematics',
                'contact_number' => '09181234567',
                'profile_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $roberto->id,
                'employee_number' => 'TCH-2026-003',
                'first_name' => 'Roberto',
                'middle_name' => null,
                'last_name' => 'Dela Peña',
                'department' => 'General Education',
                'contact_number' => '09191234567',
                'profile_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('teachers')->insert($teachers);
    }

    private function seedStudents(): void
    {
        DB::table('students')->truncate();

        // Per plan §9 + §10 (only first 3 are detailed in §10; others use defaults)
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
            ['2026-0011', 'Kevin', 'Anthony', 'Bautista', 'Male', '2007-03-14', 'kevin.bautista@studentflow.local', '09903234567', 'Danao City', 'Mario Bautista', '09261112222'],
            ['2026-0012', 'Lara', 'Jean', 'Santiago', 'Female', '2007-11-02', 'lara.santiago@studentflow.local', '09904234567', 'Talisay City', 'Cristina Santiago', '09262223333'],
            ['2026-0013', 'Mark Anthony', 'Reyes', 'Perez', 'Male', '2006-05-20', 'mark.perez@studentflow.local', '09905234567', 'Consolacion', 'Andres Perez', '09263334444'],
            ['2026-0014', 'Nicole', 'Anne', 'Fernandez', 'Female', '2007-07-09', 'nicole.fernandez@studentflow.local', '09906234567', 'Minglanilla', 'Maricel Fernandez', '09264445555'],
            ['2026-0015', 'Owen', 'James', 'Martinez', 'Male', '2005-04-18', 'owen.martinez@studentflow.local', '09907234567', 'Compostela', 'Oscar Martinez', '09265556666'],
            ['2026-0016', 'Patricia', 'Mae', 'Lopez', 'Female', '2005-10-12', 'patricia.lopez@studentflow.local', '09908234567', 'Liloan', 'Rosario Lopez', '09266667777'],
            ['2026-0017', 'Quentin', 'Jose', 'Rivera', 'Male', '2005-08-03', 'quentin.rivera@studentflow.local', '09909234567', 'Consolacion', 'Eduardo Rivera', '09267778888'],
            ['2026-0018', 'Rachel', 'Anne', 'Gomez', 'Female', '2005-06-25', 'rachel.gomez@studentflow.local', '09910234567', 'Talisay City', 'Imelda Gomez', '09268889999'],
            ['2026-0019', 'Samuel', 'James', 'Domingo', 'Male', '2005-02-08', 'samuel.domingo@studentflow.local', '09911234568', 'Mandaue City', 'Felipe Domingo', '09269990000'],
            ['2026-0020', 'Trisha', 'Marie', 'Valencia', 'Female', '2005-09-17', 'trisha.valencia@studentflow.local', '09912234567', 'Cebu City', 'Rowena Valencia', '09270001111'],
        ];

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

        $john = Teacher::where('employee_number', 'TCH-2026-001')->first();
        $angela = Teacher::where('employee_number', 'TCH-2026-002')->first();
        $roberto = Teacher::where('employee_number', 'TCH-2026-003')->first();

        $classes = [
            [
                'teacher_id' => $john->id,
                'class_name' => 'BSIT 2A',
                'section' => 'A',
                'subject' => 'Object-Oriented Programming',
                'grade_level' => 'Second Year College',
                'school_year' => '2026-2027',
                'semester' => 'First Semester',
                'schedule' => 'Monday and Wednesday, 10:00 AM-11:30 AM',
                'room' => 'Computer Laboratory 2',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'teacher_id' => $angela->id,
                'class_name' => 'BSIT 1B',
                'section' => 'B',
                'subject' => 'Mathematics in the Modern World',
                'grade_level' => 'First Year College',
                'school_year' => '2026-2027',
                'semester' => 'First Semester',
                'schedule' => 'Tuesday and Thursday, 1:00 PM-2:30 PM',
                'room' => 'Room 204',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'teacher_id' => $roberto->id,
                'class_name' => 'BSIT 3A',
                'section' => 'A',
                'subject' => 'Ethics',
                'grade_level' => 'Third Year College',
                'school_year' => '2026-2027',
                'semester' => 'First Semester',
                'schedule' => 'Friday, 8:00 AM-11:00 AM',
                'room' => 'Room 301',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('classes')->insert($classes);
    }

    private function seedEnrollments(): void
    {
        DB::table('class_students')->truncate();

        $bsit2a = SchoolClass::where('class_name', 'BSIT 2A')->first();
        $bsit1b = SchoolClass::where('class_name', 'BSIT 1B')->first();
        $bsit3a = SchoolClass::where('class_name', 'BSIT 3A')->first();

        // BSIT 2A: 2026-0001..0007 (7 students)
        $bsit2aStudents = Student::whereBetween('student_number', ['2026-0001', '2026-0007'])->get();
        // BSIT 1B: 2026-0008..0014 (7 students)
        $bsit1bStudents = Student::whereBetween('student_number', ['2026-0008', '2026-0014'])->get();
        // BSIT 3A: 2026-0015..0020 (6 students)
        $bsit3aStudents = Student::whereBetween('student_number', ['2026-0015', '2026-0020'])->get();

        $now = now();
        $enrolledDate = '2026-06-01';

        $rows = [];
        foreach ($bsit2aStudents as $s) {
            $rows[] = ['class_id' => $bsit2a->id, 'student_id' => $s->id, 'date_enrolled' => $enrolledDate, 'status' => 'enrolled', 'created_at' => $now, 'updated_at' => $now];
        }
        foreach ($bsit1bStudents as $s) {
            $rows[] = ['class_id' => $bsit1b->id, 'student_id' => $s->id, 'date_enrolled' => $enrolledDate, 'status' => 'enrolled', 'created_at' => $now, 'updated_at' => $now];
        }
        foreach ($bsit3aStudents as $s) {
            $rows[] = ['class_id' => $bsit3a->id, 'student_id' => $s->id, 'date_enrolled' => $enrolledDate, 'status' => 'enrolled', 'created_at' => $now, 'updated_at' => $now];
        }

        DB::table('class_students')->insert($rows);
    }

    private function seedAttendance(): void
    {
        DB::table('attendance')->truncate();

        $bsit2a = SchoolClass::where('class_name', 'BSIT 2A')->first();
        $john = User::where('username', 'john.reyes')->first();

        // Per plan §11: BSIT 2A attendance for June 15 and June 17, 2026
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
                    'class_id' => $bsit2a->id,
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

        // Per plan §12: Quizzes 20, Activities 15, Assignments 20, Project 20, Final Exam 25
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
                'class_id' => $bsit2a->id,
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

        // Per plan §13: 6 grade items
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
                'class_id' => $bsit2a->id,
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

        // Per plan §14: scores per student per item (rows: students, columns: items in order)
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

        // Per plan §15
        $rows = [
            [
                'class_id' => $bsit2a->id,
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
                'class_id' => $bsit1b->id,
                'title' => 'Percentage and Interest Worksheet',
                'description' => 'Complete the worksheet involving percentages, simple interest, and compound interest.',
                'date_assigned' => '2026-06-12',
                'deadline' => '2026-06-20',
                'maximum_score' => 40,
                'status' => 'Active',
                'attachment_link' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_id' => $bsit3a->id,
                'title' => 'Ethical Case Analysis',
                'description' => 'Write a short analysis of an ethical issue involving privacy and technology.',
                'date_assigned' => '2026-06-13',
                'deadline' => '2026-06-27',
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
        if (! $assignment) return;

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

        // Per plan §16
        $rows = [
            [
                'teacher_id' => $john->id,
                'class_id' => $bsit2a->id,
                'title' => 'Java Project Consultation',
                'message' => 'Project consultation will be held after class on June 22. Bring your source code and project outline.',
                'priority' => 'Important',
                'publish_date' => '2026-06-17',
                'expiration_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'teacher_id' => $angela->id,
                'class_id' => $bsit1b->id,
                'title' => 'Quiz Schedule',
                'message' => 'Quiz 1 will be held on June 23. Review percentages, ratios, and interest calculations.',
                'priority' => 'Normal',
                'publish_date' => '2026-06-17',
                'expiration_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'teacher_id' => $roberto->id,
                'class_id' => $bsit3a->id,
                'title' => 'Classroom Change',
                'message' => "Friday's Ethics class will be held in Room 305 instead of Room 301.",
                'priority' => 'Urgent',
                'publish_date' => '2026-06-17',
                'expiration_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('announcements')->insert($rows);
    }

    private function seedSchoolSettings(): void
    {
        DB::table('school_setting_histories')->truncate();
        DB::table('school_settings')->truncate();

        $settings = [
            ['school_name', 'StudentFlow Demo School'],
            ['school_year', '2026-2027'],
            ['semester', 'First Semester'],
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
}
