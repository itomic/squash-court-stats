# GitHub Webhook Troubleshooting Guide

## Quick Check

Run this diagnostic script on the server:
```bash
php /home/stats/repo/check-webhook-status.php
```

Or check via SSH:
```bash
ssh root@atlas.itomic.com "php /home/stats/repo/check-webhook-status.php"
```

## Common Issues

### 1. Webhook Not Configured in GitHub

**Symptoms:**
- No entries in `/home/stats/logs/webhook-deploy.log`
- Pushes to main don't trigger deployment
- Last deployment was hours/days ago

**Solution:**
1. Go to: https://github.com/itomic/squash-court-stats/settings/hooks
2. Check if a webhook exists pointing to `https://stats.squashplayers.app/webhook-deploy.php`
3. If not, click "Add webhook" and configure:
   - **Payload URL:** `https://stats.squashplayers.app/webhook-deploy.php`
   - **Content type:** `application/json`
   - **Secret:** `413d66fed586f3447e62dd9f2f574400868b1ebf738cdd4278cf31b0a0be3b6b`
   - **Which events?** Select "Just the push event"
   - **Active:** ✅ Checked
4. Click "Add webhook"
5. GitHub will send a test ping - check webhook log to verify

### 2. Webhook Configured But Not Receiving Requests

**Symptoms:**
- Webhook exists in GitHub
- No entries in webhook log
- GitHub webhook delivery shows failures

**Check:**
1. **GitHub Webhook Delivery Logs:**
   - Go to: https://github.com/itomic/squash-court-stats/settings/hooks
   - Click on the webhook
   - Check "Recent Deliveries" tab
   - Look for error messages (404, 403, 500, etc.)

2. **Common Errors:**
   - **404 Not Found:** Webhook URL is wrong or file doesn't exist
   - **403 Forbidden:** Secret mismatch or signature verification failed
   - **500 Internal Server Error:** PHP error in webhook script
   - **Timeout:** Server took too long to respond

3. **Verify Webhook URL:**
   ```bash
   curl -I https://stats.squashplayers.app/webhook-deploy.php
   ```
   Should return 403 (expected - needs GitHub signature)

### 3. Webhook Receiving Requests But Deployment Fails

**Symptoms:**
- Entries in `/home/stats/logs/webhook-deploy.log`
- But no deployment happening
- Check `/home/stats/logs/deploy-output.log` for errors

**Check:**
1. **Deployment Script Permissions:**
   ```bash
   ls -la /home/stats/deploy.sh
   ```
   Should be executable: `-rwxr-xr-x`

2. **Repository Directory:**
   ```bash
   ls -la /home/stats/repo/.git
   ```
   Should exist and be readable

3. **Log Directory:**
   ```bash
   ls -la /home/stats/logs/
   ```
   Should exist and be writable

4. **Check Deployment Log:**
   ```bash
   tail -50 /home/stats/logs/deploy-output.log
   ```
   Look for error messages

### 4. Signature Verification Failing

**Symptoms:**
- Webhook log shows "Invalid signature" errors
- GitHub delivery shows 403 responses

**Solution:**
1. Verify secret matches in both places:
   - GitHub webhook settings
   - `webhook-deploy.php` file (line 8)
2. Secret must be exactly: `413d66fed586f3447e62dd9f2f574400868b1ebf738cdd4278cf31b0a0be3b6b`
3. No extra spaces or characters

### 5. Wrong Branch Triggering

**Symptoms:**
- Webhook fires but deployment doesn't happen
- Log shows "Not main branch" messages

**Check:**
- Webhook is configured for "push" events
- Only pushes to `main` branch trigger deployment
- Pushes to `develop` or other branches are ignored (by design)

## Verification Steps

### Step 1: Check Webhook Configuration
1. Go to: https://github.com/itomic/squash-court-stats/settings/hooks
2. Verify webhook exists and is active
3. Check "Recent Deliveries" for recent activity

### Step 2: Check Server Logs
```bash
# Webhook requests
tail -20 /home/stats/logs/webhook-deploy.log

# Deployment output
tail -50 /home/stats/logs/deploy-output.log
```

### Step 3: Test Webhook Manually
```bash
# Trigger a test deployment
git commit --allow-empty -m "Test webhook deployment"
git push origin main

# Watch logs in real-time
tail -f /home/stats/logs/webhook-deploy.log
tail -f /home/stats/logs/deploy-output.log
```

### Step 4: Verify Deployment
```bash
# Check current commit in production
cd /home/stats/current && git log --oneline -1

# Should match latest commit on GitHub main branch
```

## Manual Deployment (Fallback)

If webhook isn't working, deploy manually:

### Option 1: cPanel UI
1. Login to cPanel
2. Go to "Git Version Control"
3. Find repository `squash-court-stats` (or `repo`)
4. Click "Manage"
5. Click "Pull or Deploy" tab
6. Click "Deploy HEAD Commit"

### Option 2: SSH
```bash
ssh root@atlas.itomic.com "bash /home/stats/deploy.sh"
```

## Prevention

To ensure webhook works reliably:
1. ✅ Keep webhook secret secure (don't commit to git)
2. ✅ Monitor webhook delivery logs regularly
3. ✅ Test after repository renames or URL changes
4. ✅ Verify webhook after server migrations
5. ✅ Keep deployment script executable
6. ✅ Ensure log directory exists and is writable

