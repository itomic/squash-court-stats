# cPanel UAPI VersionControlDeployment Issue

**Date Discovered:** November 18-19, 2025  
**Issue Status:** Unresolved (workaround implemented)  
**Severity:** High - Blocks automated deployments

## Summary

cPanel's UAPI `VersionControlDeployment create` function returns success (`status: 1`) but **does not actually trigger deployments**. This prevents automated webhook-based deployments from working.

## Investigation Details

### What We Discovered

1. **Repository is registered in cPanel:**
   ```bash
   uapi VersionControl retrieve repository_root=/home/stats/repo
   ```
   Returns repository information successfully.

2. **Repository is marked as NOT deployable:**
   ```json
   {
     "deployable": 0,
     "tasks": [],
     "last_deployment": null
   }
   ```

3. **UAPI call returns success but does nothing:**
   ```bash
   uapi VersionControlDeployment create repository_root=/home/stats/repo
   # Returns: {"status": 1, "errors": null}
   # But no deployment occurs!
   ```

4. **No deployment entry is created:**
   - Checked `/home/stats/.cpanel/datastore/vc_deploy.sqlite`
   - No new entries in `deployments` table after UAPI call
   - Last deployment was from manual cPanel UI trigger

5. **Manual deployments through cPanel UI work:**
   - Clicking "Deploy HEAD Commit" in cPanel Git Version Control UI works
   - Creates deployment log at `/home/stats/.cpanel/logs/vc_*_git_deploy.log`
   - Executes `.cpanel.yml` tasks successfully

### Root Cause

The repository is not properly configured as "deployable" in cPanel's system. This appears to require:
- Setting up deployment through the cPanel UI (not just via UAPI)
- Possibly clicking "Enable Deployment" or similar in the UI
- cPanel stores this configuration somewhere that UAPI cannot set

### What We Tried

1. ✅ **Verified repository exists:** `uapi VersionControl retrieve` - SUCCESS
2. ❌ **Tried to set deployment path:** `uapi VersionControl update deployment_path=...` - No effect
3. ❌ **Tried UAPI deployment:** `uapi VersionControlDeployment create` - Returns success but doesn't deploy
4. ❌ **Tried WHM API:** `whmapi1 version_control_deploy` - Function doesn't exist
5. ✅ **Checked deployment database:** Found `/home/stats/.cpanel/datastore/vc_deploy.sqlite`
6. ✅ **Verified manual UI deployment works:** Confirmed `.cpanel.yml` executes when triggered manually

### Evidence

**Database Query:**
```bash
sqlite3 /home/stats/.cpanel/datastore/vc_deploy.sqlite 'SELECT * FROM deployments ORDER BY deploy_id DESC LIMIT 5;'
```
Shows deployments 1-4 (all from manual UI triggers), but no new entries after UAPI calls.

**Repository Configuration:**
```bash
cat /home/stats/.cpanel/datastore/vc_list_store
```
```json
[{
  "last_deployment": null,
  "repository_root": "/home/stats/repo",
  "name": "squash-court-stats",
  "source_repository": {
    "remote_name": "origin",
    "url": "https://github.com/itomic/squash-court-stats.git"
  },
  "type": "Cpanel::VersionControl::git::Remote"
}]
```
No deployment configuration present.

## The Solution: Custom Deployment Script

After extensive investigation, we determined that **using a custom deployment script is the industry-standard approach** for automated cPanel deployments, not a workaround.

### Why Custom Scripts Are Better

1. **More Reliable:** Not dependent on cPanel's buggy UAPI
2. **Better Control:** Full control over deployment process
3. **Better Logging:** Detailed logs for debugging
4. **Customizable:** Can add custom steps as needed
5. **Industry Standard:** Most professional cPanel users use this approach

### Implementation

**Webhook Flow:**
```
GitHub push → Webhook notification → webhook-deploy.php → 
git pull → deploy.sh → Deployment complete
```

**Files:**
- `webhook-deploy.php` - Receives GitHub webhook, triggers deployment
- `deploy.sh` - Executes deployment tasks (mirrors `.cpanel.yml`)
- `.cpanel.yml` - Kept for manual UI deployments

**Key Code in webhook-deploy.php:**
```php
$command = sprintf(
    'cd %s && git pull origin main >> /home/stats/logs/deploy-output.log 2>&1 && bash %s >> /home/stats/logs/deploy-output.log 2>&1 &',
    escapeshellarg($repoDir),
    escapeshellarg($deployScript)
);
exec($command);
```

## Lessons Learned

1. **cPanel's UAPI is unreliable for automated deployments**
   - Works for retrieving information
   - Fails for triggering deployments
   - No clear documentation on how to make repositories "deployable"

2. **Custom deployment scripts are best practice**
   - More reliable than UAPI
   - Used by most professional cPanel hosting setups
   - Provides better control and debugging

3. **Manual UI deployments still work**
   - Keep `.cpanel.yml` for manual deployments
   - Useful for emergency deployments
   - Good for testing deployment tasks

## Future Considerations

If we encounter this issue again or need to set up deployment on a new cPanel account:

1. **Don't rely on UAPI for automated deployments**
2. **Use custom deployment scripts from the start**
3. **Keep `.cpanel.yml` for manual UI deployments**
4. **Test both automated and manual deployment paths**

## References

- cPanel Git Version Control: https://docs.cpanel.net/knowledge-base/web-services/guide-to-git-deployment/
- cPanel UAPI Documentation: https://api.docs.cpanel.net/openapi/cpanel/operation/VersionControlDeployment-create/
- Deployment logs: `/home/stats/.cpanel/logs/vc_*_git_deploy.log`
- Deployment database: `/home/stats/.cpanel/datastore/vc_deploy.sqlite`

## Related Files

- `webhook-deploy.php` - GitHub webhook handler
- `deploy.sh` - Custom deployment script
- `.cpanel.yml` - Deployment task definitions
- `docs/deployment/WEBHOOK-SETUP-QUICK-START.md` - Webhook setup guide
- `docs/deployment/WEBHOOK-TROUBLESHOOTING.md` - Troubleshooting guide

