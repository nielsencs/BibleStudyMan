@echo off
setlocal EnableExtensions EnableDelayedExpansion

set "PUSH_CHANGES=1"
set "BL_ROOT="

:parse_args
if "%~1"=="" goto after_args
if /I "%~1"=="--no-push" (
    set "PUSH_CHANGES=0"
    shift
    goto parse_args
)
if /I "%~1"=="--bl-root" (
    if "%~2"=="" (
        echo Missing path after --bl-root 1>&2
        exit /b 2
    )
    set "BL_ROOT=%~2"
    shift
    shift
    goto parse_args
)
if /I "%~1"=="-h" goto usage
if /I "%~1"=="--help" goto usage
echo Unknown argument: %~1 1>&2
goto usage_error

:usage
echo Usage: scripts\sync-verses-from-bookish-lamp.bat [--no-push] [--bl-root PATH]
echo.
echo Copies bookish-lamp\database\bibleVerses.sql into this BSM checkout,
echo then rebuilds database\bibleComplete.sql using the same order as
echo database\concatenate.bat:
echo.
echo   bibleStart.sql + bibleCompletedVerses.sql + bibleVerses.sql
echo.
echo By default, commits any resulting BSM changes and pushes develop.
echo Use --no-push to leave the commit local.
exit /b 0

:usage_error
call :usage
exit /b 2

:after_args
set "SCRIPT_DIR=%~dp0"
for %%I in ("%SCRIPT_DIR%..") do set "BSM_ROOT=%%~fI"

if not defined BL_ROOT (
    for %%I in ("%BSM_ROOT%\..\bookish-lamp") do set "BL_ROOT=%%~fI"
)

if not exist "%BL_ROOT%\.git" (
    echo Cannot find bookish-lamp checkout. Use --bl-root PATH. 1>&2
    exit /b 1
)

call :require_file "%BL_ROOT%\database\bibleVerses.sql" || exit /b 1
call :require_file "%BSM_ROOT%\database\bibleStart.sql" || exit /b 1
call :require_file "%BSM_ROOT%\database\bibleCompletedVerses.sql" || exit /b 1
call :require_file "%BSM_ROOT%\database\bibleVerses.sql" || exit /b 1

for /f "usebackq delims=" %%B in (`git -C "%BSM_ROOT%" branch --show-current`) do set "BSM_BRANCH=%%B"
if /I not "%BSM_BRANCH%"=="develop" (
    echo BSM must be on develop; currently on %BSM_BRANCH% 1>&2
    exit /b 1
)

call :ensure_clean_repo "%BL_ROOT%" "Bookish Lamp" || exit /b 1
call :ensure_clean_repo "%BSM_ROOT%" "BibleStudyMan" || exit /b 1

call :pull_ff_current_branch "%BL_ROOT%" "Bookish Lamp" || exit /b 1
call :pull_ff_current_branch "%BSM_ROOT%" "BibleStudyMan" || exit /b 1

copy /Y "%BL_ROOT%\database\bibleVerses.sql" "%BSM_ROOT%\database\bibleVerses.sql" >nul || exit /b 1

type "%BSM_ROOT%\database\bibleStart.sql" "%BSM_ROOT%\database\bibleCompletedVerses.sql" "%BSM_ROOT%\database\bibleVerses.sql" > "%BSM_ROOT%\database\bibleComplete.sql"
if errorlevel 1 exit /b 1

fc /b "%BL_ROOT%\database\bibleVerses.sql" "%BSM_ROOT%\database\bibleVerses.sql" >nul
if errorlevel 1 (
    echo Post-copy verification failed: BSM bibleVerses.sql does not match BL. 1>&2
    exit /b 1
)

call :repo_has_changes "%BSM_ROOT%"
if errorlevel 1 (
    echo BSM already matches BL; bibleComplete.sql regenerated with no tracked changes.
    exit /b 0
)

git -C "%BSM_ROOT%" config user.name "Ezra" || exit /b 1
git -C "%BSM_ROOT%" config user.email "ezra@openclaw.local" || exit /b 1
git -C "%BSM_ROOT%" add database/bibleVerses.sql database/bibleComplete.sql || exit /b 1
git -C "%BSM_ROOT%" commit -m "Sync Bible verses from Bookish Lamp" || exit /b 1

if "%PUSH_CHANGES%"=="1" (
    git -C "%BSM_ROOT%" push origin develop || exit /b 1
)

git -C "%BSM_ROOT%" log -1 --format="%%h %%an ^<%%ae^> %%s"
exit /b 0

:require_file
if not exist "%~1" (
    echo Required file missing: %~1 1>&2
    exit /b 1
)
exit /b 0

:ensure_clean_repo
set "REPO=%~1"
set "LABEL=%~2"
for /f "usebackq delims=" %%S in (`git -C "%REPO%" status --porcelain`) do (
    echo %LABEL% has uncommitted changes; stopping before sync. 1>&2
    git -C "%REPO%" status --short
    exit /b 1
)
exit /b 0

:pull_ff_current_branch
set "REPO=%~1"
set "LABEL=%~2"
set "BRANCH="
for /f "usebackq delims=" %%B in (`git -C "%REPO%" branch --show-current`) do set "BRANCH=%%B"
if not defined BRANCH (
    echo %LABEL% is not on a branch; stopping. 1>&2
    exit /b 1
)
git -C "%REPO%" fetch --prune origin || exit /b 1
git -C "%REPO%" pull --ff-only origin "%BRANCH%" || exit /b 1
exit /b 0

:repo_has_changes
for /f "usebackq delims=" %%S in (`git -C "%~1" status --porcelain`) do exit /b 0
exit /b 1
