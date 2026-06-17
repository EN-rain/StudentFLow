# Step 1: Runtime Provisioning - Verification Notes

## What was done
1. Downloaded PHP 8.2.31 portable (TS, VS16, x64) from windows.php.net → `C:\php\`
   - URL: https://windows.php.net/downloads/releases/php-8.2.31-Win32-vs16-x64.zip
   - Size: 33,471,636 bytes
   - sha256 verified against windows.php.net published hash
2. Downloaded composer.phar (latest stable, 2.10.1) → `C:\composer\composer.phar`
   - URL: https://getcomposer.org/download/latest-stable/composer.phar
   - Size: 3,565,131 bytes
3. Restored `C:\php\php.ini` from `php.ini-development` template.
4. Appended extension enablement block at end of `php.ini`:
   ```
   extension_dir = "C:/php/ext"
   extension=mbstring
   extension=openssl
   extension=fileinfo
   extension=curl
   extension=pdo_sqlite
   extension=sqlite3
   extension=zip
   extension=gd
   ```

## Verification
- `C:\php\php.exe --version` → PHP 8.2.31 (cli) (ZTS Visual C++ 2019 x64) ✓
- `C:\php\php.exe C:\composer\composer.phar --version` → Composer 2.10.1 ✓
- `C:\php\php.exe -m` lists: curl, fileinfo, gd, mbstring, openssl, PDO, pdo_sqlite, sqlite3, zip ✓
- All Laravel 11 required extensions are loaded.

## Tooling conventions for subsequent steps
- PHP always invoked as `C:\php\php.exe` (absolute path; PATH change via `setx` was attempted but is not reliable from this shell - use absolute paths instead).
- Composer always invoked as `C:\php\php.exe C:\composer\composer.phar <args>`.
- All work runs via `cmd.exe /c "..."` from the WSL bash; actual files live on the Windows filesystem under `C:\Users\LENOVO\Desktop\StudentFlow` (mounted at `/mnt/c/Users/LENOVO/Desktop/StudentFlow`).
