#!/bin/bash

# Deployment Script untuk UrunanKita.id
# Usage: ./deploy.sh

set -e  # Exit on error

echo "🚀 Starting Deployment for UrunanKita.id"
echo "=========================================="

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
php spark migrate
echo -e "${GREEN}✓ Migrations completed${NC}"

# Step 4: Set permissions
echo ""
echo "🔐 Step 4: Setting file permissions..."
chmod -R 775 writable/
echo -e "${GREEN}✓ Permissions set${NC}"

# Step 5: Clear cache
echo ""
echo "🧹 Step 5: Clearing cache..."
php spark cache:clear
echo -e "${GREEN}✓ Cache cleared${NC}"

# Step 6: Optimize
echo ""
echo "⚡ Step 6: Optimizing application..."
php spark optimize
echo -e "${GREEN}✓ Application optimized${NC}"

# Step 7: Check encryption key
echo ""
echo "🔑 Step 7: Checking encryption key..."
if ! grep -q "encryption.key = " .env || grep -q "^# encryption.key" .env; then
    echo -e "${YELLOW}⚠️  Encryption key not set. Generating...${NC}"
    php spark key:generate
    echo -e "${GREEN}✓ Encryption key generated. Please update .env file.${NC}"
else
    echo -e "${GREEN}✓ Encryption key found${NC}"
fi

echo ""
echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
echo ""
echo "📋 Next steps:"
echo "1. Verify the application is working: https://urunankita.id"
echo "2. Check error logs if any issues: writable/logs/"
echo "3. Test critical features (login, forms, etc.)"
echo ""

