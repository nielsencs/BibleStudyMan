$ErrorActionPreference = "Stop"

if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    Write-Error "Docker is not installed or is not on PATH. Install/start Docker Desktop, then try again."
    exit 1
}

if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "Created .env from .env.example"
}

Write-Host "Starting Docker containers..."
docker compose up -d --build
if ($LASTEXITCODE -ne 0) {
    Write-Error "Docker did not start the containers. Is Docker Desktop running? Start Docker Desktop, wait until it says it is running, then try ./scripts/local-up.ps1 again."
    exit $LASTEXITCODE
}

Write-Host "Waiting for local site..."
$python = Get-Command py -ErrorAction SilentlyContinue
if ($python) {
    $pythonCmd = "py"
    $pythonArgs = @("scripts/smoke-test.py")
} else {
    $pythonCmd = "python"
    $pythonArgs = @("scripts/smoke-test.py")
}

for ($i = 1; $i -le 60; $i++) {
    & $pythonCmd @pythonArgs
    if ($LASTEXITCODE -eq 0) {
        Write-Host "BibleStudyMan is running at http://localhost:8080/site/"
        exit 0
    }
    Start-Sleep -Seconds 2
}

Write-Error "Site did not pass smoke tests yet. Try: docker compose logs --tail=100"
exit 1
