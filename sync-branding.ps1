$ErrorActionPreference = "Stop"

$root = Split-Path -Parent $MyInvocation.MyCommand.Path
$source = Join-Path $root "StudentFlow_Icon.png"

if (-not (Test-Path $source)) {
    throw "Missing branding source: $source"
}

$targets = @(
    (Join-Path $root "public\images\studentflow-logo.png"),
    (Join-Path $root "android\app\src\main\res\drawable-nodpi\studentflow_logo.png"),
    (Join-Path $root "android\app\src\main\res\mipmap-mdpi\ic_launcher.png"),
    (Join-Path $root "android\app\src\main\res\mipmap-hdpi\ic_launcher.png"),
    (Join-Path $root "android\app\src\main\res\mipmap-xhdpi\ic_launcher.png"),
    (Join-Path $root "android\app\src\main\res\mipmap-xxhdpi\ic_launcher.png"),
    (Join-Path $root "android\app\src\main\res\mipmap-xxxhdpi\ic_launcher.png")
)

foreach ($target in $targets) {
    $directory = Split-Path -Parent $target
    New-Item -ItemType Directory -Force -Path $directory | Out-Null
    Copy-Item -Path $source -Destination $target -Force
    Write-Host "Updated $target"
}

Write-Host "StudentFlow branding synchronized from StudentFlow_Icon.png"
