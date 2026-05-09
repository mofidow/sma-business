# =======================================================
#  Upload SMA to Hostinger VPS — Windows PowerShell
#  Edit the 3 variables below, then right-click →
#  "Run with PowerShell" (or run in terminal)
# =======================================================

$VPS_IP   = "YOUR_VPS_IP_HERE"      # e.g. "185.185.68.12"
$VPS_USER = "root"
$APP_DIR  = "C:\Users\hp\Downloads\SMA_Online\SMA_v4"
$DEST     = "/var/www/sma"

# Exclude vendor, node_modules, .git (they are installed on the server)
$EXCLUDE  = @("vendor", "node_modules", ".git", "storage/logs/*.log")

Write-Host "Packaging app (excluding vendor & node_modules)..." -ForegroundColor Yellow

$zipPath = "$env:TEMP\sma_deploy_$(Get-Date -Format 'yyyyMMddHHmm').zip"

# Create zip excluding heavy folders
$items = Get-ChildItem -Path $APP_DIR -Exclude "vendor","node_modules",".git"
Compress-Archive -Path ($items | ForEach-Object { $_.FullName }) -DestinationPath $zipPath -Force

$sizeMB = [math]::Round((Get-Item $zipPath).Length / 1MB, 1)
Write-Host "Zip created: $zipPath ($sizeMB MB)" -ForegroundColor Green

Write-Host ""
Write-Host "Uploading to VPS $VPS_IP ..." -ForegroundColor Yellow
Write-Host "(You will be asked for your VPS root password)"
Write-Host ""

# Upload the zip
scp $zipPath "${VPS_USER}@${VPS_IP}:/tmp/sma.zip"

# Extract and run setup on the VPS
$REMOTE_CMDS = @"
mkdir -p $DEST
apt install -y unzip -qq
unzip -oq /tmp/sma.zip -d $DEST
rm /tmp/sma.zip
chown -R www-data:www-data $DEST
echo 'Files uploaded and extracted to $DEST'
"@

ssh "${VPS_USER}@${VPS_IP}" $REMOTE_CMDS

Write-Host ""
Write-Host "Upload complete!" -ForegroundColor Green
Write-Host ""
Write-Host "Now SSH into your VPS and run:" -ForegroundColor Cyan
Write-Host "  cd $DEST && bash deploy/app-setup.sh" -ForegroundColor White
Write-Host ""
