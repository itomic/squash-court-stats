#!/bin/bash
set -euo pipefail

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
REPO_DIR="/home/stats/repo"
CURRENT_DIR="/home/stats/current"
PUBLIC_HTML="/home/stats/public_html"
LOG_FILE="/home/stats/logs/deploy-output.log"
NODE_VERSION="24"
PHP_USER="stats"

# Function to log messages
log() {
    echo -e "${GREEN}$(date '+%Y-%m-%d %H:%M:%S')${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}$(date '+%Y-%m-%d %H:%M:%S') ERROR:${NC} $1" | tee -a "$LOG_FILE"
    exit 1
}

warning() {
    echo -e "${YELLOW}$(date '+%Y-%m-%d %H:%M:%S') WARNING:${NC} $1" | tee -a "$LOG_FILE"
}

# Start deployment
log "🚀 Starting deployment..."

# Verify repo directory exists and is a git repository
if [ ! -d "$REPO_DIR" ]; then
    error "Repository directory does not exist: $REPO_DIR"
fi

if [ ! -d "$REPO_DIR/.git" ]; then
    error "Not a git repository: $REPO_DIR"
fi

# Pull latest changes from GitHub
log "📥 Pulling latest changes from GitHub to $REPO_DIR..."
cd "$REPO_DIR" || error "Failed to change to repository directory"

# Ensure we're on main branch
git checkout main || warning "Could not checkout main branch (may already be on main)"

# Determine if we need to use sudo or su
if [ "$(id -u)" -eq 0 ]; then
    # Running as root, use su
    RUN_AS_USER="su - $PHP_USER -c"
else
    # Running as regular user, use sudo
    RUN_AS_USER="sudo -u $PHP_USER"
fi

# Pull latest changes
if ! $RUN_AS_USER "cd $REPO_DIR && git pull origin main"; then
    error "Failed to pull latest changes from GitHub"
fi

# Get the latest commit hash for logging
LATEST_COMMIT=$(cd "$REPO_DIR" && $RUN_AS_USER "cd $REPO_DIR && git rev-parse --short HEAD")
log "✅ Pulled commit: $LATEST_COMMIT"

# Verify current directory exists
if [ ! -d "$CURRENT_DIR" ]; then
    log "📁 Creating current directory: $CURRENT_DIR"
    mkdir -p "$CURRENT_DIR"
    chown "$PHP_USER:$PHP_USER" "$CURRENT_DIR"
fi

# Sync changes to the live directory
log "📋 Syncing changes to $CURRENT_DIR..."
rsync -av --delete \
  --exclude='.git' \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='storage' \
  --exclude='.env' \
  --exclude='bootstrap/cache/*.php' \
  --exclude='.cursor' \
  "$REPO_DIR/" "$CURRENT_DIR/" || error "Failed to sync files"

# Ensure proper permissions on current directory and subdirectories
log "🔒 Setting correct permissions..."
chmod 755 "$CURRENT_DIR"
chmod 755 "$CURRENT_DIR/public" 2>/dev/null || true
chown -R "$PHP_USER:$PHP_USER" "$CURRENT_DIR"

# Change to current directory for build operations
cd "$CURRENT_DIR" || error "Failed to change to current directory"

# Set up Node.js path
NODE_PATH="/opt/alt/alt-nodejs${NODE_VERSION}/root/usr/bin"
NPM_PATH="/opt/alt/alt-nodejs${NODE_VERSION}/root/usr/bin/npm"

if [ ! -f "$NPM_PATH" ]; then
    error "Node.js $NODE_VERSION not found at $NPM_PATH"
fi

# Install npm dependencies
log "📦 Installing npm dependencies..."
if ! $RUN_AS_USER "export PATH=$NODE_PATH:/usr/local/bin:/usr/bin:/bin && cd $CURRENT_DIR && $NPM_PATH install --production=false"; then
    error "Failed to install npm dependencies"
fi

# Build frontend assets
log "🔨 Building frontend assets..."
if ! $RUN_AS_USER "export PATH=$NODE_PATH:/usr/local/bin:/usr/bin:/bin && cd $CURRENT_DIR && $NPM_PATH run build"; then
    error "Failed to build frontend assets"
fi

# Clear Laravel caches
log "🧹 Clearing Laravel caches..."
# Remove bootstrap cache files
rm -f "$CURRENT_DIR/bootstrap/cache/*.php" 2>/dev/null || true

# Clear view cache
rm -rf "$CURRENT_DIR/storage/framework/views/*" 2>/dev/null || true

# Try to clear Laravel caches using artisan (may fail if .env is missing, that's OK)
$RUN_AS_USER "cd $CURRENT_DIR && php artisan view:clear" 2>/dev/null || warning "Could not clear view cache (may need .env configuration)"
$RUN_AS_USER "cd $CURRENT_DIR && php artisan config:clear" 2>/dev/null || warning "Could not clear config cache"
$RUN_AS_USER "cd $CURRENT_DIR && php artisan cache:clear" 2>/dev/null || warning "Could not clear application cache"

# Ensure build symlink is correct
log "🔗 Ensuring build symlink is correct..."
cd "$PUBLIC_HTML" || error "Failed to change to public_html directory"

BUILD_LINK="$PUBLIC_HTML/build"
EXPECTED_TARGET="$CURRENT_DIR/public/build"

# Check if symlink exists and points to correct location
if [ ! -L "$BUILD_LINK" ] || [ "$(readlink "$BUILD_LINK")" != "$EXPECTED_TARGET" ]; then
    log "📎 Updating build symlink..."
    rm -f "$BUILD_LINK"
    ln -s "$EXPECTED_TARGET" "$BUILD_LINK"
    log "✅ Build symlink updated"
else
    log "✅ Build symlink already correct"
fi

# Final permission check
log "🔒 Final permission check..."
chmod 755 "$CURRENT_DIR"
chmod 755 "$CURRENT_DIR/public" 2>/dev/null || true
chmod 755 "$PUBLIC_HTML" 2>/dev/null || true

# Verify deployment
log "✅ Deployment complete!"
log "🌐 Site: https://stats.squashplayers.app/"
log "📝 Latest commit: $LATEST_COMMIT"
log "ℹ️  Laravel will regenerate caches on first request using PHP 8.3"

exit 0

