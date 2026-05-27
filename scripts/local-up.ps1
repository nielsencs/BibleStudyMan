$ErrorActionPreference = "Stop"

if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "Created .env from .env.example"
}

docker compose up -d --build

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
