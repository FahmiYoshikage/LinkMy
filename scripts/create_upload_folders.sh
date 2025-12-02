#!/bin/bash

# Script to create upload folders with correct permissions
# Run this on VPS: bash create_upload_folders.sh

echo "Creating upload folders for LinkMy..."

# Base directory
WEBROOT="/var/www/html"
UPLOADS_DIR="$WEBROOT/uploads"

# Create folders
echo "Creating directories..."
mkdir -p "$UPLOADS_DIR/profile_pics"
mkdir -p "$UPLOADS_DIR/backgrounds"
mkdir -p "$UPLOADS_DIR/folder_pics"

# Set ownership to www-data (Apache user)
echo "Setting ownership to www-data..."
chown -R www-data:www-data "$UPLOADS_DIR"

# Set permissions (777 for debugging, change to 755 later)
echo "Setting permissions..."
chmod -R 777 "$UPLOADS_DIR"

# Verify
echo ""
echo "Verification:"
ls -la "$UPLOADS_DIR"

echo ""
echo "Testing write permission..."
if [ -w "$UPLOADS_DIR" ]; then
    echo "✅ Uploads directory is writable!"
else
    echo "❌ ERROR: Uploads directory is NOT writable!"
    echo "Current user: $(whoami)"
    echo "Directory owner: $(stat -c '%U:%G' $UPLOADS_DIR)"
fi

echo ""
echo "Done! Upload functionality should work now."
echo ""
echo "If still not working, run inside Docker container:"
echo "docker exec linkmy_web bash /var/www/html/create_upload_folders.sh"
