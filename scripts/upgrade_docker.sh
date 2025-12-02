#!/bin/bash
###############################################################################
# LinkMy Docker Stack Upgrade Script
# Upgrades to: MySQL 8.4 LTS, PHP 8.3, phpMyAdmin 5.2
###############################################################################

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo "========================================"
echo "   LinkMy Docker Stack Upgrade"
echo "========================================"
echo ""

# Check if Docker is running
if ! docker version > /dev/null 2>&1; then
    echo -e "${RED}[ERROR]${NC} Docker is not running!"
    echo ""
    echo "Please start Docker first:"
    echo "  sudo systemctl start docker"
    echo "  sudo systemctl enable docker"
    echo ""
    exit 1
fi

echo -e "${GREEN}[OK]${NC} Docker is running"
echo ""

# Check if docker-compose is available
if ! command -v docker-compose &> /dev/null; then
    echo -e "${YELLOW}[WARNING]${NC} docker-compose not found, using 'docker compose' instead"
    COMPOSE_CMD="docker compose"
else
    COMPOSE_CMD="docker-compose"
fi

# Ask for confirmation
echo "This will:"
echo "  - Stop current containers"
echo "  - Pull MySQL 8.4 LTS (~500MB)"
echo "  - Pull PHP 8.3 (~450MB)"
echo "  - Pull phpMyAdmin 5.2 (~150MB)"
echo "  - Rebuild web container"
echo "  - Start new stack"
echo ""
read -p "Continue? (Y/N): " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Cancelled."
    exit 0
fi

echo ""
echo -e "${BLUE}[STEP 1/6]${NC} Creating backup..."
echo "========================================"
if [ -f "backup_before_upgrade.sql" ]; then
    echo -e "${YELLOW}[WARNING]${NC} Backup already exists, skipping..."
else
    if docker exec linkmy_mysql mysqldump -u root -prootpassword linkmy_db > backup_before_upgrade.sql 2>/dev/null; then
        echo -e "${GREEN}[OK]${NC} Database backed up to backup_before_upgrade.sql"
    else
        echo -e "${YELLOW}[WARNING]${NC} Could not create backup - MySQL might not be running"
        echo "Continuing anyway..."
    fi
fi
echo ""

echo -e "${BLUE}[STEP 2/6]${NC} Stopping current containers..."
echo "========================================"
$COMPOSE_CMD down
echo -e "${GREEN}[OK]${NC} Containers stopped"
echo ""

echo -e "${BLUE}[STEP 3/6]${NC} Pulling new images..."
echo "========================================"
echo "This may take 5-10 minutes depending on your internet speed..."
if ! $COMPOSE_CMD pull; then
    echo -e "${RED}[ERROR]${NC} Failed to pull images!"
    exit 1
fi
echo -e "${GREEN}[OK]${NC} Images pulled successfully"
echo ""

echo -e "${BLUE}[STEP 4/6]${NC} Rebuilding web container..."
echo "========================================"
if ! $COMPOSE_CMD build --no-cache web; then
    echo -e "${RED}[ERROR]${NC} Failed to build web container!"
    exit 1
fi
echo -e "${GREEN}[OK]${NC} Web container rebuilt"
echo ""

echo -e "${BLUE}[STEP 5/6]${NC} Starting new stack..."
echo "========================================"
if ! $COMPOSE_CMD up -d; then
    echo -e "${RED}[ERROR]${NC} Failed to start containers!"
    exit 1
fi
echo -e "${GREEN}[OK]${NC} Containers started"
echo ""

echo -e "${BLUE}[STEP 6/6]${NC} Waiting for MySQL to be healthy..."
echo "========================================"
echo "Waiting 30 seconds for MySQL health check..."
sleep 10
echo -n "Progress: "
for i in {1..20}; do
    echo -n "▓"
    sleep 1
done
echo ""
$COMPOSE_CMD ps
echo ""

echo "========================================"
echo -e "   ${GREEN}Upgrade Complete!${NC}"
echo "========================================"
echo ""

echo "New versions running:"
docker exec linkmy_mysql mysql -u root -prootpassword -e "SELECT VERSION();" 2>/dev/null | grep -v VERSION
docker exec linkmy_web php -v | grep "PHP"
echo ""

echo "Services:"
echo "  - Website:     http://localhost:83"
echo "  - phpMyAdmin:  http://localhost:8083"
echo "  - Migration:   http://localhost:83/migrate_to_v2.php"
echo ""

echo "Next steps:"
echo "  1. Open http://localhost:83 to verify website works"
echo "  2. Check http://localhost:83/admin/profiles.php for stats"
echo "  3. Run migration: http://localhost:83/migrate_to_v2.php"
echo ""

echo -e "${YELLOW}Rollback (if needed):${NC}"
echo "  $COMPOSE_CMD down"
echo "  git checkout HEAD -- docker-compose.yml Dockerfile"
echo "  $COMPOSE_CMD up -d"
echo ""

# Make script show in color when done
echo -e "${GREEN}✓ All done!${NC}"
