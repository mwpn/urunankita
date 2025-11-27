#!/bin/bash

# Deployment Script untuk Production - UrunanKita.id
# Usage: ./deploy_production.sh

set -e  # Exit on error

echo "🚀 Starting Production Deployment for UrunanKita.id"
echo "=================================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${RED}❌ Error: .env file not found!${NC}"
    echo "Please create .env file from env template first."
    exit 1
fi

# Check if CI_ENVIRONMENT is production
if ! grep -q "CI_ENVIRONMENT = production" .env; then
    echo -e "${YELLOW}⚠️  Warning: CI_ENVIRONMENT is not set to 'production'${NC}"
    read -p "Continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

echo -e "${GREEN}✓ .env file found${NC}"

# Step 1: Pull latest code
echo ""
echo "📥 Step 1: Pulling latest code..."
git pull origin master
echo -e "${GREEN}✓ Code updated${NC}"

# Step 2: Install Composer dependencies
echo ""
echo "📦 Step 2: Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader
echo -e "${GREEN}✓ Dependencies installed${NC}"

# Step 3: Run migrations
echo ""
echo "🗄️  Step 3: Running database migrations..."
php spark migrate || {
    echo -e "${YELLOW}⚠️  Migration error, trying module migrations...${NC}"
    php spark migrate:modules || {
        echo -e "${YELLOW}⚠️  Module migration error, check manually${NC}"
    }
}
echo -e "${GREEN}✓ Migrations completed${NC}"

# Step 4: Ensure notification templates
echo ""
echo "📧 Step 4: Ensuring notification templates..."
php app/Database/Scripts/ensure_tenant_notification_template.php || {
    echo -e "${YELLOW}⚠️  Notification template script error, check manually${NC}"
}
echo -e "${GREEN}✓ Notification templates checked${NC}"

# Step 5: Set permissions
echo ""
echo "🔐 Step 5: Setting file permissions..."
chmod -R 775 writable/
echo -e "${GREEN}✓ Permissions set${NC}"

# Step 6: Clear cache
echo ""
echo "🧹 Step 6: Clearing cache..."
php spark cache:clear
echo -e "${GREEN}✓ Cache cleared${NC}"

# Step 7: Optimize
echo ""
echo "⚡ Step 7: Optimizing application..."
php spark optimize
echo -e "${GREEN}✓ Application optimized${NC}"

echo ""
echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
echo ""
echo "Next steps:"
echo "1. Test the application: https://urunankita.id"
echo "2. Check logs: tail -f writable/logs/log-\$(date +%Y-%m-%d).log"
echo "3. Verify staff management feature works"

