# Webhook Setup - Quick Start Guide

## 🎯 Goal
Configure GitHub to automatically trigger deployment when you push to the `main` branch.

**Deployment Method:** Trigger File + Cron Job Pattern
- Webhook creates a trigger file (`/home/stats/logs/webhook-trigger`)
- Cron job (running as root every minute) checks for trigger and executes deployment
- This is the industry-standard pattern for webhooks requiring elevated privileges

## ⚡ Quick Steps

### 1. Check Current Status
Visit: **https://github.com/itomic/squash-court-stats/settings/hooks**

Look for a webhook pointing to: `https://stats.squashplayers.app/webhook-deploy.php`

### 2. If No Webhook Exists → Add It

Click **"Add webhook"** and fill in:

| Field | Value |
|-------|-------|
| **Payload URL** | `https://stats.squashplayers.app/webhook-deploy.php` |
| **Content type** | `application/json` |
| **Secret** | `413d66fed586f3447e62dd9f2f574400868b1ebf738cdd4278cf31b0a0be3b6b` |
| **Which events?** | Select **"Just the push event"** |
| **Active** | ✅ **Checked** |

Click **"Add webhook"**

### 3. Verify It Works

GitHub will automatically send a test ping. Check:

1. **GitHub Webhook Page**: Look for a green checkmark ✅ next to the webhook
2. **Server Logs**: Run this to see if the ping was received:
   ```bash
   ssh root@atlas.itomic.com "tail -5 /home/stats/logs/webhook-deploy.log"
   ```

### 4. Test with a Real Push

```bash
git commit --allow-empty -m "Test webhook deployment"
git push origin main
```

Then check deployment logs:
```bash
ssh root@atlas.itomic.com "tail -20 /home/stats/logs/deploy-output.log"
```

## ✅ Success Indicators

- ✅ Webhook shows green checkmark in GitHub
- ✅ Recent deliveries show "200 OK" responses
- ✅ Webhook log shows incoming requests
- ✅ Deployment log shows successful deployment

## ❌ Common Issues

### Webhook Not Receiving Requests
- Check GitHub webhook delivery logs (click on webhook → "Recent Deliveries")
- Look for error codes (404, 403, 500)
- Verify webhook URL is correct

### 403 Forbidden Errors
- Secret mismatch - verify secret matches exactly
- Check `webhook-deploy.php` line 8 for the secret

### Deployment Not Happening
- Check `/home/stats/logs/deploy-output.log` for errors
- Verify `deploy.sh` is executable: `chmod +x /home/stats/deploy.sh`

## 📚 More Help

- **Full Troubleshooting Guide**: `docs/deployment/WEBHOOK-TROUBLESHOOTING.md`
- **Diagnostic Script**: Run `php /home/stats/repo/check-webhook-status.php` on server

## 🔄 After Setup

Once configured, every push to `main` will:
1. ✅ Trigger GitHub webhook automatically
2. ✅ Verify signature securely
3. ✅ Run deployment script
4. ✅ Update production site

**No manual intervention needed!** 🎉

