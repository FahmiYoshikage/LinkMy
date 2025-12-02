#!/bin/bash
# Force update and clear all cache

echo "=== Force Update Script ==="
cd /opt/LinkMy

echo "1. Stashing any local changes..."
git stash

echo "2. Force pull from origin..."
git fetch origin
git reset --hard origin/master

echo "3. Verify Profiles menu exists in navbar..."
if grep -q "Profiles" partials/admin_nav.php; then
    echo "✓ Profiles menu found in admin_nav.php"
else
    echo "✗ ERROR: Profiles menu NOT found!"
fi

echo "4. Restart web container to clear PHP cache..."
docker restart linkmy_web

echo "5. Waiting for container to be ready..."
sleep 5

echo "6. Container status:"
docker ps | grep linkmy_web

echo ""
echo "=== Update Complete ==="
echo "Please refresh your browser and hard reload (Ctrl+F5)"
echo "Test URL: https://linkmy.iet.ovh/admin/dashboard.php"
