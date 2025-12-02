#!/bin/bash
# =====================================================
# Script untuk update kode PHP ke schema v3
# =====================================================
# Jalankan di VPS: bash update_php_to_v3_schema.sh

echo "🔄 Updating PHP code to use v3 schema..."

# Backup dulu sebelum mass-replace
cp -r /opt/LinkMy /opt/LinkMy_backup_before_code_update_$(date +%Y%m%d_%H%M%S)

cd /opt/LinkMy

# 1. Update table names: link_categories → categories_v3
echo "📝 Replacing link_categories with categories_v3..."
find . -type f -name "*.php" -exec sed -i 's/link_categories/categories_v3/g' {} +

# 2. Update field names yang berubah di categories
echo "📝 Updating category field names..."
# category_name → name
find . -type f -name "*.php" -exec sed -i 's/category_name/name/g' {} +
# category_icon → icon  
find . -type f -name "*.php" -exec sed -i 's/category_icon/icon/g' {} +
# category_color → color
find . -type f -name "*.php" -exec sed -i 's/category_color/color/g' {} +
# display_order → position (di categories_v3)
find . -type f -name "*.php" -exec sed -i 's/c\.display_order/c.position/g' {} +

# 3. Update common table references (jika ada sisa old_*)
echo "📝 Checking for any remaining old_* references..."
grep -r "old_users\|old_profiles\|old_links" --include="*.php" . || echo "✅ No old_* references found"

echo "✅ Code update complete!"
echo ""
echo "⚠️  NEXT STEPS:"
echo "1. Test website: http://45.76.146.48"
echo "2. Check for errors: docker logs linkmy_web"
echo "3. If issues: restore from /opt/LinkMy_backup_before_code_update_*"
echo "4. Manual fixes may be needed for complex queries"
