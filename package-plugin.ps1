# Package WordPress Plugin for Deployment
# This script creates a zip file ready for WordPress installation

$PLUGIN_NAME = "squash-stats-dashboard"
$VERSION = "1.0.0"
$OUTPUT_FILE = "$PLUGIN_NAME-$VERSION.zip"

Write-Host "Packaging $PLUGIN_NAME plugin..." -ForegroundColor Green

# Create temporary directory
New-Item -ItemType Directory -Force -Path "temp\$PLUGIN_NAME" | Out-Null

# Copy plugin files
Copy-Item "squash-stats-dashboard-plugin.php" -Destination "temp\$PLUGIN_NAME\"
Copy-Item "templates" -Destination "temp\$PLUGIN_NAME\" -Recurse
Copy-Item "PLUGIN-README.md" -Destination "temp\$PLUGIN_NAME\README.md"

# Create zip file
Compress-Archive -Path "temp\$PLUGIN_NAME" -DestinationPath $OUTPUT_FILE -Force

# Cleanup
Remove-Item -Path "temp" -Recurse -Force

Write-Host "Plugin packaged successfully: $OUTPUT_FILE" -ForegroundColor Green
Write-Host ""
Write-Host "To install:" -ForegroundColor Yellow
Write-Host "1. Upload $OUTPUT_FILE to WordPress (Plugins -> Add New -> Upload Plugin)"
Write-Host "2. Or extract to wp-content/plugins/"
Write-Host "3. Activate in WordPress Admin -> Plugins"
Write-Host "4. Go to Settings -> Permalinks and click 'Save Changes'"
Write-Host ""
Write-Host "The dashboard will be available at:" -ForegroundColor Cyan
Write-Host "https://squash.players.app/squash-venues-courts-world-stats-new/"

