#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

mkdir -p docs scripts sql diagnostics
backup_dir="backup_before_reorg_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$backup_dir"

move_safe() {
  pattern="$1"; dest="$2"
  shopt -s nullglob
  for f in $pattern; do
    echo "→ Moving $f to $dest"
    cp -f "$f" "$backup_dir/$(basename "$f")"
    mkdir -p "$dest"
    git mv -f "$f" "$dest/" || mv -f "$f" "$dest/"
  done
}

# Docs
move_safe "*.md" docs

# SQL
move_safe "*.sql" sql

# Scripts
move_safe "*.sh" scripts
move_safe "*.ps1" scripts

# Diagnostics
for f in diagnostic.php diagnostic_boxed_layout.php debug_profile.php debug_profile_stats.php view_errors.php cekidot.php demo.php fahmi.php; do
  if [[ -f "$f" ]]; then
    echo "→ Moving $f to diagnostics"
    cp -f "$f" "$backup_dir/$f"
    git mv -f "$f" diagnostics/ || mv -f "$f" diagnostics/
  fi
done

echo "✅ Reorg complete. Backup at $backup_dir"
