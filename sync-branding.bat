@echo off
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0sync-branding.ps1"
if errorlevel 1 exit /b %errorlevel%
