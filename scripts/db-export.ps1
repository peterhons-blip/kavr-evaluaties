
# EvolvIT - Databank exporteren
# Uitvoeren vanuit de hoofdmap:
# powershell -ExecutionPolicy Bypass -File .\scripts\db-export.ps1

$ErrorActionPreference = "Stop"

$project = Split-Path $PSScriptRoot -Parent
$backupDir = Join-Path $project "database\backups"
$dump = "C:\xampp\mysql\bin\mysqldump.exe"
$database = "kavr_evaluaties_dev"
$timestamp = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"
$file = Join-Path $backupDir "evolvit_$timestamp.sql"

if (-not (Test-Path $dump)) {
    throw "mysqldump niet gevonden: $dump"
}

New-Item -ItemType Directory -Force -Path $backupDir |
    Out-Null

Write-Host ""
Write-Host "EvolvIT - Databank exporteren" -ForegroundColor Cyan
Write-Host "Databank: $database"
Write-Host ""

& $dump `
    --user=root `
    --default-character-set=utf8mb4 `
    --single-transaction `
    --routines `
    --triggers `
    --events `
    --add-drop-database `
    --databases $database `
    "--result-file=$file"

if ($LASTEXITCODE -ne 0) {
    Remove-Item $file -ErrorAction SilentlyContinue
    throw "De databankexport is mislukt."
}

$backup = Get-Item $file

if ($backup.Length -eq 0) {
    throw "Het exportbestand is leeg."
}

Write-Host "Export geslaagd!" -ForegroundColor Green
Write-Host "Bestand: $file"
Write-Host "Grootte: $([math]::Round($backup.Length / 1KB, 1)) KB"
Write-Host ""
Write-Host "Kopieer dit SQL-bestand veilig naar je andere laptop."
