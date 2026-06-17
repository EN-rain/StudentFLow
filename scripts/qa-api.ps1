param(
    [string]$BaseUrl = "http://127.0.0.1:8000",
    [string]$Php = "C:\php\php.exe"
)

$ErrorActionPreference = "Stop"
$results = New-Object System.Collections.Generic.List[object]

function Add-Result {
    param([string]$Name, [bool]$Pass, [string]$Detail = "")
    $script:results.Add([pscustomobject]@{
        Check = $Name
        Result = $(if ($Pass) { "PASS" } else { "FAIL" })
        Detail = $Detail
    })
}

function Invoke-Api {
    param(
        [string]$Method,
        [string]$Path,
        [object]$Body = $null,
        [string]$Token = $null
    )

    $headers = @{ Accept = "application/json" }
    if ($Token) {
        $headers.Authorization = "Bearer $Token"
    }

    $params = @{
        Method = $Method
        Uri = "$BaseUrl$Path"
        Headers = $headers
        UseBasicParsing = $true
        TimeoutSec = 15
    }

    if ($null -ne $Body) {
        $params.ContentType = "application/json"
        $params.Body = ($Body | ConvertTo-Json -Depth 12)
    }

    try {
        $response = Invoke-WebRequest @params
        return [pscustomobject]@{
            Status = [int]$response.StatusCode
            Body = $response.Content
            Json = ($response.Content | ConvertFrom-Json -ErrorAction SilentlyContinue)
        }
    } catch {
        $response = $_.Exception.Response
        if (-not $response) {
            throw
        }

        $reader = [System.IO.StreamReader]::new($response.GetResponseStream())
        $bodyText = $reader.ReadToEnd()
        return [pscustomobject]@{
            Status = [int]$response.StatusCode
            Body = $bodyText
            Json = ($bodyText | ConvertFrom-Json -ErrorAction SilentlyContinue)
        }
    }
}

try {
    $adminLogin = Invoke-Api POST "/api/auth/login" @{ username = "admin"; password = "Admin123!" }
    $adminToken = $adminLogin.Json.token
    Add-Result "API valid admin login" ($adminLogin.Status -eq 200 -and $adminToken) "status=$($adminLogin.Status)"

    $teacherLogin = Invoke-Api POST "/api/auth/login" @{ username = "john.reyes"; password = "Teacher123!" }
    $teacherToken = $teacherLogin.Json.token
    Add-Result "API valid teacher login" ($teacherLogin.Status -eq 200 -and $teacherToken) "status=$($teacherLogin.Status)"

    $badLogin = Invoke-Api POST "/api/auth/login" @{ username = "admin"; password = "wrong" }
    Add-Result "API invalid password rejected" ($badLogin.Status -eq 422) "status=$($badLogin.Status)"

    $unauth = Invoke-Api GET "/api/classes"
    Add-Result "Unauthenticated API rejected" ($unauth.Status -eq 401) "status=$($unauth.Status)"

    $teacherAdmin = Invoke-Api GET "/api/admin/teachers" $null $teacherToken
    Add-Result "Teacher blocked from admin API" ($teacherAdmin.Status -eq 403) "status=$($teacherAdmin.Status)"

    $classes = (Invoke-Api GET "/api/classes" $null $adminToken).Json.data
    $johnClass = $classes | Where-Object { $_.class_name -eq "BSIT 2A" } | Select-Object -First 1
    $angelaClass = $classes | Where-Object { $_.class_name -eq "BSIT 1B" } | Select-Object -First 1

    $crossClass = Invoke-Api GET "/api/classes/$($angelaClass.id)" $null $teacherToken
    Add-Result "Teacher blocked from another class" ($crossClass.Status -eq 403) "status=$($crossClass.Status)"

    $duplicateStudent = Invoke-Api POST "/api/students" @{
        student_number = "2026-0001"
        first_name = "Duplicate"
        last_name = "Student"
        gender = "Male"
        birth_date = "2006-01-01"
        email = "duplicate.student@studentflow.local"
        status = "active"
    } $adminToken
    Add-Result "Duplicate student number rejected" ($duplicateStudent.Status -eq 422) "status=$($duplicateStudent.Status)"

    $badAssignment = Invoke-Api POST "/api/assignments" @{
        class_id = $johnClass.id
        title = "Bad Date"
        description = "deadline before assigned date"
        date_assigned = "2026-06-20"
        deadline = "2026-06-10"
        maximum_score = 10
        status = "Active"
    } $teacherToken
    Add-Result "Invalid assignment deadline rejected" ($badAssignment.Status -eq 422) "status=$($badAssignment.Status)"

    $items = (Invoke-Api GET "/api/classes/$($johnClass.id)/grade-items" $null $teacherToken).Json.data
    $item = $items | Select-Object -First 1
    $students = (Invoke-Api GET "/api/classes/$($johnClass.id)/enrollments" $null $teacherToken).Json.data
    $student = $students | Select-Object -First 1

    $negativeScore = Invoke-Api POST "/api/classes/$($johnClass.id)/students/$($student.id)/student-grades" @{
        scores = @(@{ grade_item_id = $item.id; score = -1 })
    } $teacherToken
    Add-Result "Negative grade score rejected" ($negativeScore.Status -eq 422) "status=$($negativeScore.Status)"

    $aboveMaxScore = Invoke-Api POST "/api/classes/$($johnClass.id)/students/$($student.id)/student-grades" @{
        scores = @(@{ grade_item_id = $item.id; score = ([double]$item.maximum_score + 1) })
    } $teacherToken
    Add-Result "Grade score above maximum rejected" ($aboveMaxScore.Status -eq 422) "status=$($aboveMaxScore.Status)"

    $duplicateEnrollment = Invoke-Api POST "/api/classes/$($johnClass.id)/enrollments" @{
        student_id = $student.id
        date_enrolled = "2026-06-17"
    } $teacherToken
    Add-Result "Duplicate class enrollment rejected" ($duplicateEnrollment.Status -eq 422) "status=$($duplicateEnrollment.Status)"

    $finalGrade = Invoke-Api GET "/api/classes/$($johnClass.id)/students/$($student.id)/final-grade" $null $teacherToken
    Add-Result "Final grade endpoint reachable" ($finalGrade.Status -eq 200 -and $finalGrade.Json.data.final_grade -ge 0) "status=$($finalGrade.Status); final=$($finalGrade.Json.data.final_grade)"

    $markAll = Invoke-Api POST "/api/attendance/mark-all-present" @{
        class_id = $johnClass.id
        attendance_date = "2026-06-30"
    } $teacherToken
    Add-Result "Mark all present API" ($markAll.Status -eq 200) "status=$($markAll.Status)"

    $missingAssignments = Invoke-Api GET "/api/reports/missing-assignments?class_id=$($johnClass.id)" $null $teacherToken
    Add-Result "Missing assignments report JSON" ($missingAssignments.Status -eq 200 -and $missingAssignments.Json.data.type -eq "missing-assignments") "status=$($missingAssignments.Status)"

    $gradesReport = Invoke-Api GET "/api/reports/grades?class_id=$($johnClass.id)" $null $teacherToken
    Add-Result "Grades report JSON" ($gradesReport.Status -eq 200 -and $gradesReport.Json.data.type -eq "grades") "status=$($gradesReport.Status)"

    $studentSocial = Invoke-Api POST "/api/auth/google" @{ id_token = "test-google:$($student.email)" }
    $studentToken = $studentSocial.Json.token
    Add-Result "Student Google social login links record" ($studentSocial.Status -eq 200 -and $studentSocial.Json.user.role -eq "student" -and $studentToken) "status=$($studentSocial.Status)"

    $studentDashboard = Invoke-Api GET "/api/student/dashboard" $null $studentToken
    Add-Result "Student dashboard API" ($studentDashboard.Status -eq 200 -and $studentDashboard.Json.data.student.id -eq $student.id) "status=$($studentDashboard.Status)"

    $studentBlocked = Invoke-Api GET "/api/classes" $null $studentToken
    Add-Result "Student blocked from teacher class API" ($studentBlocked.Status -eq 403) "status=$($studentBlocked.Status)"

    $exam = Invoke-Api POST "/api/exams" @{
        class_id = $johnClass.id
        grade_item_id = $item.id
        title = "QA social exam"
        instructions = "Answer one question."
        maximum_score = 10
        status = "published"
        questions = @(@{
            prompt = "Type pass"
            type = "text"
            correct_answer = "pass"
            points = 10
        })
    } $teacherToken
    $examId = $exam.Json.data.id
    $questionId = $exam.Json.data.questions[0].id
    $attempt = ($exam.Json.data.attempts | Where-Object { $_.student_id -eq $student.id } | Select-Object -First 1)
    Add-Result "Teacher creates published exam with attempts" ($exam.Status -eq 201 -and $attempt.magic_token) "status=$($exam.Status)"

    $examSubmit = Invoke-Api POST "/api/exam/magic/$($attempt.magic_token)/submit" @{
        answers = @(@{ question_id = $questionId; answer = "pass" })
    }
    Add-Result "Magic exam submit syncs score" ($examSubmit.Status -eq 200 -and [double]$examSubmit.Json.score -eq 10) "status=$($examSubmit.Status); score=$($examSubmit.Json.score)"

    $audit = Invoke-Api GET "/api/exams/$examId/audit" $null $teacherToken
    Add-Result "Teacher exam audit API" ($audit.Status -eq 200 -and $audit.Json.data.stats.submitted -ge 1) "status=$($audit.Status)"

    $announcement = Invoke-Api POST "/api/announcements" @{
        class_id = $johnClass.id
        title = "QA announcement"
        message = "This announcement verifies student email notification wiring."
        priority = "Important"
        publish_date = "2026-06-18"
    } $teacherToken
    Add-Result "Class announcement emails enrolled students" ($announcement.Status -eq 201 -and $announcement.Json.emails_sent -eq $students.Count) "status=$($announcement.Status); emails=$($announcement.Json.emails_sent)"

    $forgot = Invoke-Api POST "/api/auth/forgot-password" @{ email = "admin@studentflow.local" }
    Add-Result "Forgot password endpoint generic success" ($forgot.Status -eq 200) "status=$($forgot.Status)"

    $hash = & $Php artisan tinker --execute="echo \App\Models\User::where('username', 'admin')->first()->password;"
    Add-Result "Password stored as hash" ($hash -match '^\$2y\$|^\$argon') $hash.Trim()
} finally {
    $results | Format-Table -AutoSize
}

$failures = $results | Where-Object { $_.Result -eq "FAIL" }
if ($failures.Count -gt 0) {
    exit 1
}
