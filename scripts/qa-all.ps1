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

Invoke-Step "Laravel migrate:fresh --seed" {
    & $Php artisan migrate:fresh --seed
}

Invoke-Step "Laravel feature tests" {
    & $Php artisan test
}

if (-not (Test-Server $BaseUrl)) {
    Invoke-Step "Start Laravel dev server" {
        $uri = [Uri]$BaseUrl
        $serverProcess = Start-Process -FilePath $Php -ArgumentList @("artisan", "serve", "--host=$($uri.Host)", "--port=$($uri.Port)") -WorkingDirectory (Get-Location) -WindowStyle Hidden -PassThru
        Start-Sleep -Seconds 3
        if (-not (Test-Server $BaseUrl)) {
            throw "Laravel dev server did not start at $BaseUrl"
        }
    }
}

try {
    Invoke-Step "API QA checks" {
        & "$PSScriptRoot\qa-api.ps1" -BaseUrl $BaseUrl -Php $Php
        if ($LASTEXITCODE -ne 0) {
            throw "API QA checks failed with exit code $LASTEXITCODE"
        }
    }

    Invoke-Step "Web QA checks" {
        & "$PSScriptRoot\qa-web.ps1" -BaseUrl $BaseUrl
        if ($LASTEXITCODE -ne 0) {
            throw "Web QA checks failed with exit code $LASTEXITCODE"
        }
    }

    if (-not $SkipAndroid) {
        Invoke-Step "Android debug build" {
            $gradle = Join-Path $env:TEMP "gradle-8.7-bin\gradle-8.7\bin\gradle.bat"
            $jbr = "C:\Program Files\Android\Android Studio1\jbr"
            if (-not (Test-Path $gradle)) {
                Write-Warning "Gradle not found at $gradle; skipping Android build."
                return
            }
            if (Test-Path $jbr) {
                $env:JAVA_HOME = $jbr
                $env:PATH = "$env:JAVA_HOME\bin;$env:PATH"
            }
            Push-Location "$PSScriptRoot\..\android"
            try {
                & $gradle ":app:assembleDebug" "--no-daemon" "--console=plain"
                if ($LASTEXITCODE -ne 0) {
                    throw "Android build failed with exit code $LASTEXITCODE"
                }
            } finally {
                Pop-Location
            }
        }
    }
} finally {
    if ($serverProcess -and -not $serverProcess.HasExited) {
        Stop-Process -Id $serverProcess.Id -Force
    }
}

Write-Host ""
Write-Host "QA completed successfully."
