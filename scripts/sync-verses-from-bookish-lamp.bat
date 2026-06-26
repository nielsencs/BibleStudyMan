@echo off
setlocal

rem Sync The CleanSlate Bible verse text from sibling repo bookish-lamp into BSM,
rem bumping the canonical TCSB metadata in Bookish Lamp when verse text changed,
rem then rebuild database\bibleComplete.sql.

set "BSM_ROOT=%~dp0.."
set "BL_ROOT=%BSM_ROOT%\..\bookish-lamp"
set "VERSES_CHANGED=0"
set "TCSB_VERSION="

if not exist "%BL_ROOT%\database\bibleVerses.sql" (
    echo Could not find %BL_ROOT%\database\bibleVerses.sql
    pause
    exit /b 1
)

cd /d "%BL_ROOT%" || exit /b 1
git status --short
for /f "delims=" %%A in ('git status --porcelain') do (
    echo Bookish Lamp has local changes; stopping.
    goto fail
)
git fetch --prune origin || goto fail
git pull --ff-only origin master || goto fail

cd /d "%BSM_ROOT%" || exit /b 1
git status --short
for /f "delims=" %%A in ('git status --porcelain') do (
    echo BibleStudyMan has local changes; stopping.
    goto fail
)
git fetch --prune origin || goto fail
git pull --ff-only origin develop || goto fail

fc /b "%BL_ROOT%\database\bibleVerses.sql" "%BSM_ROOT%\database\bibleVerses.sql" >nul
if errorlevel 1 set "VERSES_CHANGED=1"

if "%VERSES_CHANGED%"=="1" (
    for /f "delims=" %%V in ('powershell -NoProfile -ExecutionPolicy Bypass -File "%BSM_ROOT%\scripts\bump-tcsb-version.ps1" -MetadataFile "%BL_ROOT%\database\tcsbMetadata.sql"') do set "TCSB_VERSION=%%V"
    cd /d "%BL_ROOT%" || goto fail
    git add database\tcsbMetadata.sql || goto fail
    git commit -m "Bump TCSB text version to %TCSB_VERSION%" || goto fail
    git push origin master || goto fail
)

cd /d "%BSM_ROOT%" || goto fail
copy /Y "%BL_ROOT%\database\tcsbMetadata.sql" "%BSM_ROOT%\database\tcsbMetadata.sql" || goto fail
copy /Y "%BL_ROOT%\database\bibleVerses.sql" "%BSM_ROOT%\database\bibleVerses.sql" || goto fail
(
    type "%BSM_ROOT%\database\bibleStart.sql"
    echo.
    type "%BSM_ROOT%\database\bibleCompletedVerses.sql"
    echo.
    type "%BSM_ROOT%\database\tcsbMetadata.sql"
    echo.
    type "%BSM_ROOT%\database\bibleVerses.sql"
) > "%BSM_ROOT%\database\bibleComplete.sql" || goto fail

fc /b "%BL_ROOT%\database\bibleVerses.sql" "%BSM_ROOT%\database\bibleVerses.sql" >nul || goto fail
fc /b "%BL_ROOT%\database\tcsbMetadata.sql" "%BSM_ROOT%\database\tcsbMetadata.sql" >nul || goto fail

git status --short
git add database\tcsbMetadata.sql database\bibleVerses.sql database\bibleComplete.sql || goto fail
git diff --cached --quiet
if not errorlevel 1 (
    echo Already synced. No commit needed.
    pause
    exit /b 0
)

git commit -m "Sync Bible verses from Bookish Lamp" || goto fail
git push origin develop || goto fail

echo Sync complete.
pause
exit /b 0

:fail
echo Sync failed. Check the messages above.
pause
exit /b 1
