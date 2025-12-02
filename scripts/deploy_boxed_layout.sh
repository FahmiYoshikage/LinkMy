#!/bin/bash
# ============================================
# LinkMy VPS Deployment Script
# For Boxed Layout Feature v2.3
# ============================================

echo "🚀 Starting LinkMy Boxed Layout Deployment..."
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
DB_NAME="linkmy_db"
DB_USER="root"
DB_PASS=""  # Set your MySQL password here if needed

echo "📋 Step 1: Checking database structure..."
echo ""

# Check if boxed_layout columns exist
COLUMNS_EXIST=$(mysql -u $DB_USER -p$DB_PASS $DB_NAME -se "
SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = '$DB_NAME' 
AND TABLE_NAME = 'appearance' 
AND COLUMN_NAME IN ('boxed_layout', 'outer_bg_type', 'container_max_width');
")

if [ "$COLUMNS_EXIST" -eq "3" ]; then
    echo -e "${GREEN}✅ Boxed layout columns already exist${NC}"
else
    echo -e "${YELLOW}⚠️  Boxed layout columns not found. Adding them...${NC}"
    mysql -u $DB_USER -p$DB_PASS $DB_NAME < database_add_boxed_layout.sql
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Columns added successfully${NC}"
    else
        echo -e "${RED}❌ Error adding columns${NC}"
        exit 1
    fi
fi

echo ""
echo "📋 Step 2: Updating database view..."
echo ""

# Update v_public_page_data view
mysql -u $DB_USER -p$DB_PASS $DB_NAME < database_update_view_boxed_layout.sql

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ View updated successfully${NC}"
else
    echo -e "${RED}❌ Error updating view${NC}"
    exit 1
fi

echo ""
echo "📋 Step 3: Verifying installation..."
echo ""

# Verify columns
echo "Checking appearance table columns:"
mysql -u $DB_USER -p$DB_PASS $DB_NAME -e "
SELECT COLUMN_NAME, DATA_TYPE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = '$DB_NAME' 
AND TABLE_NAME = 'appearance' 
AND COLUMN_NAME LIKE '%boxed%' OR COLUMN_NAME LIKE '%container%' OR COLUMN_NAME LIKE '%outer%';
"

echo ""
echo "Checking view columns:"
mysql -u $DB_USER -p$DB_PASS $DB_NAME -e "
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = '$DB_NAME' 
AND TABLE_NAME = 'v_public_page_data' 
AND (COLUMN_NAME LIKE '%boxed%' OR COLUMN_NAME LIKE '%container%' OR COLUMN_NAME LIKE '%outer%');
"

echo ""
echo -e "${GREEN}✅ Deployment completed!${NC}"
echo ""
echo "📝 Next steps:"
echo "1. Clear PHP opcache: sudo service php-fpm restart (or restart Apache)"
echo "2. Clear browser cache: Ctrl + Shift + R"
echo "3. Test boxed layout: Go to Appearance → Boxed Layout"
echo ""
echo "🔍 Troubleshooting commands:"
echo "  Check data: mysql -u $DB_USER -p$DB_PASS $DB_NAME -e 'SELECT user_id, boxed_layout, outer_bg_type FROM appearance;'"
echo "  Check view: mysql -u $DB_USER -p$DB_PASS $DB_NAME -e 'SELECT * FROM v_public_page_data LIMIT 1;'"
echo ""
