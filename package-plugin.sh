#!/bin/bash

# Package WordPress Plugin for Deployment
# This script creates a zip file ready for WordPress installation

PLUGIN_NAME="squash-stats-dashboard"
VERSION="1.0.0"
OUTPUT_FILE="${PLUGIN_NAME}-${VERSION}.zip"

echo "Packaging ${PLUGIN_NAME} plugin..."

# Create temporary directory
mkdir -p temp/${PLUGIN_NAME}

# Copy plugin files
cp squash-stats-dashboard-plugin.php temp/${PLUGIN_NAME}/
cp -r templates temp/${PLUGIN_NAME}/
cp PLUGIN-README.md temp/${PLUGIN_NAME}/README.md

# Create zip file
cd temp
zip -r ../${OUTPUT_FILE} ${PLUGIN_NAME}
cd ..

# Cleanup
rm -rf temp

echo "Plugin packaged successfully: ${OUTPUT_FILE}"
echo ""
echo "To install:"
echo "1. Upload ${OUTPUT_FILE} to WordPress"
echo "2. Or extract to wp-content/plugins/"
echo "3. Activate in WordPress Admin"

