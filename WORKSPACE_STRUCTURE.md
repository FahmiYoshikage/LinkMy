# LinkMy Workspace Structure (Proposed)

- docs/
  - All Markdown documentation (.md)
- scripts/
  - Automation scripts (PowerShell .ps1, Bash .sh)
- sql/
  - Database schema, migrations, backups (non-sensitive dumps excluded)
- diagnostics/
  - Debugging and diagnostic PHP scripts
- admin/
  - Admin panel PHP
- assets/
  - Front-end assets (css, js, images, fonts)
- config/
  - PHP config files (db.php, mail.php, performance.php, session_handler.php)
- libs/
  - Third-party libraries (PHPMailer, etc.)
- partials/
  - Reusable PHP snippets (headers, nav, favicons)
- uploads/
  - User-uploaded files
- webroot (repo root)
  - Public-facing PHP endpoints (index.php, login.php, register.php, profile.php, landing.php)

## Move Map (high level)
- *.md → docs/
- *.sql → sql/
- *.sh, *.ps1 → scripts/
- diagnostic*.php, debug_*.php, view_errors.php → diagnostics/
- admin/* → admin/
- assets/* → assets/
- config/* → config/
- libs/* → libs/
- partials/* → partials/
- uploads/* → uploads/

## Notes
- After moving, verify includes/require paths.
- Avoid moving docker-compose.yml, Dockerfile from root.
- Keep robots.txt, sitemap.xml, site.webmanifest at root.
