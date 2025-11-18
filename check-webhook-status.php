<?php
/**
 * Webhook Status Checker
 * Helps diagnose why auto-deployment isn't working
 */

echo "=== GitHub Webhook Deployment Status Check ===\n\n";

// Check 1: Webhook file exists
$webhookFile = __DIR__ . '/webhook-deploy.php';
if (file_exists($webhookFile)) {
    echo "✅ Webhook file exists: webhook-deploy.php\n";
} else {
    echo "❌ Webhook file NOT found: webhook-deploy.php\n";
}

// Check 2: Deploy script exists
$deployScript = '/home/stats/deploy.sh';
if (file_exists($deployScript)) {
    echo "✅ Deploy script exists: $deployScript\n";
} else {
    echo "⚠️  Deploy script may not exist: $deployScript\n";
}

// Check 3: Log directory exists
$logDir = '/home/stats/logs';
if (is_dir($logDir)) {
    echo "✅ Log directory exists: $logDir\n";
    
    // Check webhook log
    $webhookLog = "$logDir/webhook-deploy.log";
    if (file_exists($webhookLog)) {
        echo "✅ Webhook log exists: $webhookLog\n";
        $logContent = file_get_contents($webhookLog);
        $logLines = explode("\n", trim($logContent));
        $recentLines = array_slice($logLines, -10);
        if (!empty($recentLines)) {
            echo "\n📋 Recent webhook log entries (last 10):\n";
            foreach ($recentLines as $line) {
                if (!empty(trim($line))) {
                    echo "   $line\n";
                }
            }
        } else {
            echo "⚠️  Webhook log is empty (no webhook requests received)\n";
        }
    } else {
        echo "⚠️  Webhook log NOT found: $webhookLog\n";
        echo "   This suggests no webhook requests have been received yet.\n";
    }
    
    // Check deployment log
    $deployLog = "$logDir/deploy-output.log";
    if (file_exists($deployLog)) {
        echo "\n✅ Deployment log exists: $deployLog\n";
        $logContent = file_get_contents($deployLog);
        $logLines = explode("\n", trim($logContent));
        $recentLines = array_slice($logLines, -5);
        if (!empty($recentLines)) {
            echo "\n📋 Recent deployment log entries (last 5):\n";
            foreach ($recentLines as $line) {
                if (!empty(trim($line))) {
                    echo "   $line\n";
                }
            }
        }
    }
} else {
    echo "⚠️  Log directory may not exist: $logDir\n";
}

// Check 4: Webhook URL
$webhookUrl = 'https://stats.squashplayers.app/webhook-deploy.php';
echo "\n📡 Webhook URL: $webhookUrl\n";

// Check 5: GitHub repository
echo "\n🔍 GitHub Repository: itomic/squash-court-stats\n";
echo "📝 Webhook Configuration URL: https://github.com/itomic/squash-court-stats/settings/hooks\n\n";

echo "=== DIAGNOSIS ===\n\n";

// Check if webhook log has any entries
$webhookLog = '/home/stats/logs/webhook-deploy.log';
if (file_exists($webhookLog)) {
    $logContent = file_get_contents($webhookLog);
    if (empty(trim($logContent))) {
        echo "❌ PROBLEM: Webhook log is empty - GitHub is NOT sending webhooks!\n";
        echo "\n💡 SOLUTION: Configure GitHub webhook:\n";
        echo "   1. Go to: https://github.com/itomic/squash-court-stats/settings/hooks\n";
        echo "   2. Click 'Add webhook'\n";
        echo "   3. Payload URL: $webhookUrl\n";
        echo "   4. Content type: application/json\n";
        echo "   5. Secret: 413d66fed586f3447e62dd9f2f574400868b1ebf738cdd4278cf31b0a0be3b6b\n";
        echo "   6. Events: Just the push event\n";
        echo "   7. Active: ✅ Checked\n";
        echo "   8. Click 'Add webhook'\n\n";
    } else {
        echo "✅ Webhook log has entries - GitHub IS sending webhooks\n";
        echo "   Check the log above for any errors.\n\n";
    }
} else {
    echo "❌ PROBLEM: Webhook log doesn't exist - GitHub is NOT sending webhooks!\n";
    echo "\n💡 SOLUTION: Configure GitHub webhook (see instructions above)\n\n";
}

echo "=== NEXT STEPS ===\n\n";
echo "1. Verify webhook is configured in GitHub\n";
echo "2. Check GitHub webhook delivery logs for errors\n";
echo "3. Test by pushing a commit to main branch\n";
echo "4. Monitor webhook-deploy.log for incoming requests\n\n";

