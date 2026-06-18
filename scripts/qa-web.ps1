param(
    [string]$BaseUrl = "http://127.0.0.1:8000",
    [string]$AdminPassword = $env:STUDENTFLOW_SEED_ADMIN_PASSWORD,
    [string]$TeacherPassword = $env:STUDENTFLOW_SEED_TEACHER_PASSWORD
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

function Get-CsrfToken {
    param([string]$Html)
    $match = [regex]::Match($Html, 'name="_token"\s+value="([^"]+)"')
    if (-not $match.Success) {
        throw "CSRF token not found"
    }
    return $match.Groups[1].Value
}

function New-WebLogin {
    param([string]$Username, [string]$Password)

    $session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    $loginPage = Invoke-WebRequest -Uri "$BaseUrl/login" -WebSession $session -UseBasicParsing -TimeoutSec 15
    $token = Get-CsrfToken $loginPage.Content
    $body = @{
        _token = $token
        username = $Username
        password = $Password
    }

    $response = Invoke-WebRequest -Uri "$BaseUrl/login" -Method POST -Body $body -WebSession $session -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
    return [pscustomobject]@{ Session = $session; Response = $response }
}

function Get-Web {
    param([object]$Session, [string]$Path)
    try {
        return Invoke-WebRequest -Uri "$BaseUrl$Path" -WebSession $Session -UseBasicParsing -TimeoutSec 15
    } catch {
        $response = $_.Exception.Response
        if (-not $response) {
            throw
        }

        $reader = [System.IO.StreamReader]::new($response.GetResponseStream())
        return [pscustomobject]@{
            StatusCode = [int]$response.StatusCode
            Content = $reader.ReadToEnd()
        }
    }
}

try {
    if ([string]::IsNullOrWhiteSpace($AdminPassword) -or [string]::IsNullOrWhiteSpace($TeacherPassword)) {
        throw "Set STUDENTFLOW_SEED_ADMIN_PASSWORD and STUDENTFLOW_SEED_TEACHER_PASSWORD before running qa-web.ps1."
    }

    $admin = New-WebLogin "admin" $AdminPassword
    $adminDashboard = Get-Web $admin.Session "/dashboard"
    Add-Result "Admin web login" ($adminDashboard.StatusCode -eq 200 -and $adminDashboard.Content -match "Administrator Dashboard") "status=$($adminDashboard.StatusCode)"

    $teachers = Get-Web $admin.Session "/admin/teachers"
    Add-Result "Admin teachers page" ($teachers.StatusCode -eq 200 -and $teachers.Content -match "Add Teacher" -and $teachers.Content -match "TCH-2026") "status=$($teachers.StatusCode)"

    $settings = Get-Web $admin.Session "/admin/settings"
    Add-Result "Admin settings page with history panel" ($settings.StatusCode -eq 200 -and $settings.Content -match "School Settings" -and $settings.Content -match "Recent Setting Changes") "status=$($settings.StatusCode)"

    $logs = Get-Web $admin.Session "/admin/activity-logs"
    Add-Result "Admin activity logs page" ($logs.StatusCode -eq 200 -and $logs.Content -match "Activity Logs" -and $logs.Content -match "CSV") "status=$($logs.StatusCode)"

    $reports = Get-Web $admin.Session "/reports"
    foreach ($reportType in @("student-profile", "attendance", "grades", "class-performance", "missing-assignments", "failing-grades", "frequent-absences")) {
        Add-Result "Report listed: $reportType" ($reports.Content -match $reportType)
    }

    $csv = Get-Web $admin.Session "/reports/grades/csv?class_id=1"
    Add-Result "Grades CSV export" ($csv.StatusCode -eq 200 -and $csv.Content -match "Student") "status=$($csv.StatusCode)"

    $teacher = New-WebLogin "john.reyes" $TeacherPassword
    $teacherDashboard = Get-Web $teacher.Session "/dashboard"
    Add-Result "Teacher web login" ($teacherDashboard.StatusCode -eq 200 -and $teacherDashboard.Content -match "Teacher Dashboard") "status=$($teacherDashboard.StatusCode)"
    Add-Result "Teacher nav hides admin modules" ($teacherDashboard.Content -notmatch "/admin/teachers" -and $teacherDashboard.Content -notmatch "/admin/settings" -and $teacherDashboard.Content -notmatch "/admin/activity-logs")

    $teacherAdmin = Get-Web $teacher.Session "/admin/teachers"
    Add-Result "Teacher direct admin URL blocked" ($teacherAdmin.StatusCode -eq 403) "status=$($teacherAdmin.StatusCode)"

    $teacherClasses = Get-Web $teacher.Session "/classes"
    Add-Result "Teacher classes scoped page" ($teacherClasses.StatusCode -eq 200 -and $teacherClasses.Content -match "BSIT 2A" -and $teacherClasses.Content -notmatch "BSIT 1B") "status=$($teacherClasses.StatusCode)"
} finally {
    $results | Format-Table -AutoSize
}

$failures = $results | Where-Object { $_.Result -eq "FAIL" }
if ($failures.Count -gt 0) {
    exit 1
}
