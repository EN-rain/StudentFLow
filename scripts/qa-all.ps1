param(
    [string]$Php = "C:\php\php.exe",
    [string]$BaseUrl = "http://127.0.0.1:8000",
    [switch]$SkipAndroid
)

$ErrorActionPreference = "Stop"

function Invoke-Step {
    param([string]$Name, [scriptblock]$Block)
    Write-Host ""
    Write-Host "== $Name =="
    & $Block
    if ($LASTEXITCODE -ne 0) {
        throw "$Name failed with exit code $LASTEXITCODE"
    }
}

function Test-Server {
    param([string]$Url)
    try {
        $response = Invoke-WebRequest -Uri "$Url/login" -UseBasicParsing -TimeoutSec 3
        return $response.StatusCode -eq 200
    } catch {
        return $false
    }
}

$serverProcess = $null
$qaDatabase = Join-Path ([System.IO.Path]::GetTempPath()) ("studentflow-qa-{0}.sqlite" -f [guid]::NewGuid())
New-Item -ItemType File -Path $qaDatabase -Force | Out-Null

# Every child process launched by this script uses an isolated disposable database.
$env:APP_ENV = "testing"
$env:APP_DEBUG = "true"
$env:DB_CONNECTION = "sqlite"
$env:DB_DATABASE = $qaDatabase
$env:CACHE_STORE = "array"
$env:SESSION_DRIVER = "array"
$env:QUEUE_CONNECTION = "sync"
$env:MAIL_MAILER = "array"
$env:STUDENTFLOW_SEED_STARTER_DATA = "true"

try {
    Invoke-Step "Clear cached configuration" {
        & $Php artisan config:clear
    }

    Invoke-Step "Build disposable QA database" {
        & $Php artisan migrate:fresh --seed --force
    }

    Invoke-Step "Laravel feature tests" {
        & $Php artisan test
    }

    if (-not (Test-Server $BaseUrl)) {
        Invoke-Step "Start Laravel QA server" {
            $uri = [Uri]$BaseUrl
            $serverProcess = Start-Process -FilePath $Php -ArgumentList @("artisan", "serve", "--host=$($uri.Host)", "--port=$($uri.Port)") -WorkingDirectory (Get-Location) -WindowStyle Hidden -PassThru
            Start-Sleep -Seconds 3
            if (-not (Test-Server $BaseUrl)) {
                throw "Laravel QA server did not start at $BaseUrl"
            }
        }
    } else {
        throw "A server is already running at $BaseUrl. Stop it first so QA cannot target a non-disposable database."
    }

    Invoke-Step "API QA checks" {
        & "$PSScriptRoot\qa-api.ps1" -BaseUrl $BaseUrl -Php $Php
    }

    Invoke-Step "Web QA checks" {
        & "$PSScriptRoot\qa-web.ps1" -BaseUrl $BaseUrl
    }

    if (-not $SkipAndroid) {
        Invoke-Step "Android unit tests and debug build" {
            $gradleCommand = Join-Path $PSScriptRoot "..\android\gradlew.bat"
            if (-not (Test-Path $gradleCommand)) {
                $downloadedGradle = Join-Path $env:TEMP "gradle-8.7-bin\gradle-8.7\bin\gradle.bat"
                if (Test-Path $downloadedGradle) {
                    $gradleCommand = $downloadedGradle
                } else {
                    $systemGradle = Get-Command gradle.bat -ErrorAction SilentlyContinue
                    if (-not $systemGradle) {
                        $systemGradle = Get-Command gradle -ErrorAction SilentlyContinue
                    }
                    if (-not $systemGradle) {
                        throw "Gradle 8.7 or an Android Gradle wrapper is required for Android QA."
                    }
                    $gradleCommand = $systemGradle.Source
                }
            }

            if ([string]::IsNullOrWhiteSpace($env:GOOGLE_WEB_CLIENT_ID)) {
                $env:GOOGLE_WEB_CLIENT_ID = "qa-client-id.apps.googleusercontent.com"
            }

            Push-Location "$PSScriptRoot\..\android"
            try {
                & $gradleCommand ":app:testDebugUnitTest" ":app:assembleDebug" "--no-daemon" "--console=plain"
            } finally {
                Pop-Location
            }
        }
    }
} finally {
    if ($serverProcess -and -not $serverProcess.HasExited) {
        Stop-Process -Id $serverProcess.Id -Force
    }
    Remove-Item -Path $qaDatabase -Force -ErrorAction SilentlyContinue
}

Write-Host ""
Write-Host "QA completed successfully against a disposable SQLite database."
