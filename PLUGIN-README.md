# Squash Stats Dashboard WordPress Plugin

This WordPress plugin embeds the Squash Stats Dashboard from `stats.squashplayers.app` into your WordPress site.

## Installation

1. **Create Plugin Directory:**
   ```bash
   mkdir -p wp-content/plugins/squash-stats-dashboard
   ```

2. **Upload Files:**
   - Copy `squash-stats-dashboard-plugin.php` to `wp-content/plugins/squash-stats-dashboard/`
   - Copy the `templates/` folder to `wp-content/plugins/squash-stats-dashboard/`

3. **Activate Plugin:**
   - Go to WordPress Admin → Plugins
   - Find "Squash Stats Dashboard"
   - Click "Activate"

4. **Flush Permalinks:**
   - Go to Settings → Permalinks
   - Click "Save Changes" (this flushes the rewrite rules)

## Usage

Once activated, the dashboard will be available at:
```
https://squash.players.app/squash-venues-courts-world-stats-new/
```

## Features

- **No iFrame:** Direct HTML injection for better performance and SEO
- **Asset Optimization:** Loads CSS/JS from stats.squashplayers.app
- **Caching:** Intelligent caching of manifest and content
- **WordPress Integration:** Works seamlessly with your WordPress theme
- **Side-by-side Testing:** Runs alongside existing page without conflicts

## Technical Details

### How It Works

1. **Custom Rewrite Rule:** Creates a virtual page at `/squash-venues-courts-world-stats-new/`
2. **Content Fetching:** Pulls HTML content from `https://stats.squashplayers.app`
3. **Asset Loading:** Dynamically loads Vite-built assets using manifest.json
4. **Caching Strategy:**
   - Manifest cached for 1 hour
   - Content cached for 5 minutes
   - Automatic cache invalidation

### Dependencies

The plugin loads these external assets:
- MapLibre GL JS (4.0.0)
- Chart.js (4.4.0)
- Chart.js Datalabels Plugin (2.2.0)
- Font Awesome (6.5.1)
- Dashboard CSS/JS from stats.squashplayers.app

### File Structure

```
squash-stats-dashboard/
├── squash-stats-dashboard-plugin.php  (Main plugin file)
├── templates/
│   └── dashboard-template.php         (Page template)
└── README.md                          (This file)
```

## Troubleshooting

### Page Shows 404

1. Go to Settings → Permalinks
2. Click "Save Changes"
3. This will flush the rewrite rules

### Assets Not Loading

1. Check that `https://stats.squashplayers.app` is accessible
2. Clear WordPress transient cache:
   - Delete transient: `squash_dashboard_manifest`
   - Delete transient: `squash_dashboard_content`

### Content Not Updating

The content is cached for 5 minutes. To force refresh:
1. Delete the `squash_dashboard_content` transient
2. Or wait 5 minutes for automatic refresh

## Migration Path

When ready to replace the old page:

1. **Test thoroughly** on `/squash-venues-courts-world-stats-new/`
2. **Update the rewrite rule** in the plugin to use the original URL
3. **Remove or redirect** the old Zoho Analytics page
4. **Flush permalinks** again

## Support

For issues or questions:
- Email: ross@itomic.com.au
- Website: https://www.itomic.com.au

## License

GPL v2 or later

## Changelog

### 1.0.0 (2025-11-10)
- Initial release
- Custom page at `/squash-venues-courts-world-stats-new/`
- Direct HTML injection (no iframe)
- Intelligent caching system
- WordPress integration

