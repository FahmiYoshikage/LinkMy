#!/bin/bash
# LinkMy Docker Setup Script
# This script helps you set up and run LinkMy in Docker

set -e  # Exit on error

echo "=========================================="
echo "  LinkMy v2.1 - Docker Setup"
echo "=========================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Function to print colored messages
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    print_error "Docker is not installed. Please install Docker first."
    exit 1
fi
print_success "Docker is installed"

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    print_error "Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi
print_success "Docker Compose is installed"

# Check if required files exist
echo ""
echo "Checking required files..."
if [ ! -f "database.sql" ]; then
    print_error "database.sql not found!"
    exit 1
fi
print_success "database.sql found"

if [ ! -f "database_update_v2.1.sql" ]; then
    print_warning "database_update_v2.1.sql not found (optional for v2.1 features)"
else
    print_success "database_update_v2.1.sql found"
fi

# Create necessary directories
echo ""
echo "Creating directories..."
mkdir -p uploads/profile_pics uploads/backgrounds uploads/folder_pics
print_success "Upload directories created"

# Copy database config for Docker
echo ""
echo "Setting up database configuration..."
if [ -f "config/db.docker.php" ]; then
    cp config/db.php config/db.backup.php 2>/dev/null || true
    cp config/db.docker.php config/db.php
    print_success "Database configuration updated for Docker"
else
    print_warning "config/db.docker.php not found, using existing config/db.php"
fi

# Ask user for action
echo ""
echo "What would you like to do?"
echo "1) Build and start containers"
echo "2) Start existing containers"
echo "3) Stop containers"
echo "4) Rebuild containers (fresh start)"
echo "5) View logs"
echo "6) Remove containers and volumes (clean up)"
echo ""
read -p "Enter your choice (1-6): " choice

case $choice in
    1)
        echo ""
        echo "Building and starting containers..."
        docker-compose up -d --build
        print_success "Containers are starting!"
        ;;
    2)
        echo ""
        echo "Starting existing containers..."
        docker-compose up -d
        print_success "Containers started!"
        ;;
    3)
        echo ""
        echo "Stopping containers..."
        docker-compose down
        print_success "Containers stopped!"
        exit 0
        ;;
    4)
        echo ""
        echo "Rebuilding containers (this will remove existing containers)..."
        docker-compose down
        docker-compose build --no-cache
        docker-compose up -d
        print_success "Containers rebuilt and started!"
        ;;
    5)
        echo ""
        echo "Showing logs (press Ctrl+C to exit)..."
        docker-compose logs -f
        exit 0
        ;;
    6)
        echo ""
        read -p "⚠️  This will remove all containers and data! Are you sure? (yes/no): " confirm
        if [ "$confirm" == "yes" ]; then
            docker-compose down -v
            print_success "Containers and volumes removed!"
        else
            print_warning "Cleanup cancelled"
        fi
        exit 0
        ;;
    *)
        print_error "Invalid choice"
        exit 1
        ;;
esac

# Wait for services to be healthy
echo ""
echo "Waiting for services to be ready..."
sleep 10

# Check container status
echo ""
echo "Container status:"
docker-compose ps

# Print access information
echo ""
echo "=========================================="
print_success "LinkMy is now running!"
echo "=========================================="
echo ""
echo "🌐 Access your application:"
echo "   Web Application: http://localhost:83"
echo "   phpMyAdmin:      http://localhost:8083"
echo ""
echo "📊 Database credentials:"
echo "   Host:     linkmy-db (inside Docker) or localhost:3307 (from host)"
echo "   Database: linkmy_db"
echo "   User:     linkmy_user"
echo "   Password: linkmy_pass"
echo ""
echo "🔧 Useful commands:"
echo "   View logs:        docker-compose logs -f"
echo "   Stop containers:  docker-compose down"
echo "   Restart:          docker-compose restart"
echo "   Shell access:     docker exec -it linkmy_web bash"
echo ""
echo "=========================================="
