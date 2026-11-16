
# EvolvIT - Databank importeren
# Importeert een SQL-export van db-export.ps1.
#
# Gebruik:
# powershell -ExecutionPolicy Bypass -File .\scripts\db-import.ps1 -File ".\database\backups\evolvit_backup.sql"

param(
    [Parameter(Mandatory = $true)]
    [string]$File
)

$ErrorActionPreference = "Stop"

$mysql = "C:\xampp\mysql\bin\mysql.exe"
$dump = "C:\xampp\mysql\bin\mysqldump.exe"
$database = "kavr_evaluaties_dev"

$project = Split-Path $PSScriptRoot -Parent
$backupDir = Join-Path $project "database\backups"

# Controleer de benodigde programma's.
if (-not (Test-Path $mysql)) {
    throw "MySQL-client niet gevonden: $mysql"
}

if (-not (Test-Path $dump)) {
    throw "mysqldump niet gevonden: $dump"
}

# Controleer het SQL-bestand.
if (-not (Test-Path -LiteralPath $File -PathType Leaf)) {
    throw "SQL-bestand niet gevonden: $File"
}

$sourceFile = (Resolve-Path -LiteralPath $File).Path
$sourceInfo = Get-Item -LiteralPath $sourceFile

if ($sourceInfo.Length -eq 0) {
    throw "Het SQL-bestand is leeg."
}

# Controleer of de server bereikbaar is.
& $mysql --user=root --batch --skip-column-names `
    --execute="SELECT 1" 2>&1 | Out-Null

if ($LASTEXITCODE -ne 0) {
    throw "Kan geen verbinding maken met MySQL. Start MySQL in XAMPP."
}

Write-Host ""
Write-Host "====================================" -ForegroundColor Cyan
Write-Host " EvolvIT - Databank importeren"
Write-Host "====================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Bestand: $sourceFile"
Write-Host "Databank: $database"
Write-Host ""

Write-Host "WAARSCHUWING" -ForegroundColor Yellow
Write-Host "De bestaande lokale ontwikkelingsdatabank"
Write-Host "wordt vervangen door de inhoud van deze export."
Write-Host ""

$confirmation = Read-Host "Typ IMPORTEREN om verder te gaan"

if ($confirmation -cne "IMPORTEREN") {
    Write-Host "Import geannuleerd."
    exit 0
}

# Maak eerst automatisch een veiligheidsback-up.
New-Item -ItemType Directory -Force -Path $backupDir |
    Out-Null

$timestamp = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"
$safetyBackup = Join-Path $backupDir "evolvit_voor_import_$timestamp.sql"

Write-Host ""
Write-Host "Veiligheidsback-up maken..." -ForegroundColor Cyan

& $dump `
    --user=root `
    --default-character-set=utf8mb4 `
    --single-transaction `
    --routines `
    --triggers `
    --events `
    --add-drop-database `
    --databases $database `
    "--result-file=$safetyBackup"

if ($LASTEXITCODE -ne 0) {
    Remove-Item -LiteralPath $safetyBackup `
        -ErrorAction SilentlyContinue

    throw "Veiligheidsback-up mislukt. Import wordt niet uitgevoerd."
}

if ((Get-Item $safetyBackup).Length -eq 0) {
    throw "Veiligheidsback-up is leeg. Import wordt niet uitgevoerd."
}

Write-Host "Veiligheidsback-up opgeslagen:" -ForegroundColor Green
Write-Host $safetyBackup
Write-Host ""

# Importeer via de standaardinvoer van mysql.exe.
# Dit werkt ook wanneer het bestandspad spaties bevat.
Write-Host "Databank importeren..." -ForegroundColor Cyan

$process = Start-Process `
    -FilePath $mysql `
    -ArgumentList @(
        "--user=root",
        "--default-character-set=utf8mb4"
    ) `
    -RedirectStandardInput $sourceFile `
    -Wait `
    -PassThru `
    -NoNewWindow

if ($process.ExitCode -ne 0) {
    Write-Host ""
    Write-Host "IMPORT MISLUKT!" -ForegroundColor Red
    Write-Host "Je veiligheidsback-up staat hier:"
    Write-Host $safetyBackup

    throw "MySQL-import mislukt met exitcode $($process.ExitCode)."
}

Write-Host ""
Write-Host "Databank succesvol geimporteerd!" -ForegroundColor Green
Write-Host ""
Write-Host "Veiligheidsback-up:"
Write-Host $safetyBackup
Write-Host ""
Write-Host "Controleer Laravel met:"
Write-Host "C:\php84\php.exe artisan optimize:clear"
Write-Host "C:\php84\php.exe artisan migrate:status"
