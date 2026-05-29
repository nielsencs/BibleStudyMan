$ErrorActionPreference = "Stop"

if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    Write-Error "Docker is not installed or is not on PATH. Install/start Docker Desktop, then try again."
    exit 1
}

if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
}

docker compose down -v
if ($LASTEXITCODE -ne 0) {
    Write-Error "Docker compose down failed. Is Docker Desktop running?"
    exit $LASTEXITCODE
}

docker compose up -d --build
if ($LASTEXITCODE -ne 0) {
    Write-Error "Docker did not start the containers. Is Docker Desktop running?"
    exit $LASTEXITCODE
}

Write-Host "Database volume recreated. MySQL will import database/bibleComplete.sql on first startup."
Write-Host "Run: py scripts/smoke-test.py"
