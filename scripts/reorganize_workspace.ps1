# Reorganize LinkMy workspace into clean structure
# Usage: Open PowerShell in repo root and run: ./scripts/reorganize_workspace.ps1
# Safe: creates backup folder and moves files; skip if not found

$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $MyInvocation.MyCommand.Path
Push-Location $root

# Create target folders
$dirs = @('docs','scripts','sql','diagnostics')
foreach ($d in $dirs) { if (-not (Test-Path $d)) { New-Item -ItemType Directory -Path $d | Out-Null } }

# Backup folder
$backupDir = "backup_before_reorg_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
New-Item -ItemType Directory -Path $backupDir | Out-Null

function Move-Safe($pattern, $dest) {
  Get-ChildItem -Path . -Filter $pattern -File -ErrorAction SilentlyContinue | ForEach-Object {
    $src = $_.FullName
    $name = $_.Name
    if (-not (Test-Path $dest)) { New-Item -ItemType Directory -Path $dest | Out-Null }
    Write-Host "→ Moving $name to $dest" -ForegroundColor Cyan
    Copy-Item $src "$backupDir/$name" -Force
    Move-Item $src "$dest/$name" -Force
  }
}

# Move markdown docs
Move-Safe '*.md' 'docs'

# Move SQL files
Move-Safe '*.sql' 'sql'

# Move scripts (excluding this script and Docker files)
Move-Safe '*.sh' 'scripts'
Move-Safe '*.ps1' 'scripts'

# Move diagnostics
$diagFiles = @(
  'diagnostic.php','diagnostic_boxed_layout.php','debug_profile.php','debug_profile_stats.php',
  'view_errors.php','cekidot.php','demo.php','fahmi.php'
)
foreach ($f in $diagFiles) {
  if (Test-Path $f) { Copy-Item $f "$backupDir/$f" -Force; Move-Item $f "diagnostics/$f" -Force }
}

Write-Host "✅ Reorg complete. Backup at $backupDir" -ForegroundColor Green
Pop-Location
