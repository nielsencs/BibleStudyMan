$ErrorActionPreference = "Stop"

if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
}

docker compose down -v
docker compose up -d --build

Write-Host "Database volume recreated. MySQL will import database/bibleComplete.sql on first startup."
Write-Host "Run: py scripts/smoke-test.py"
