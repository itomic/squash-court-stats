# Squash Stats Dashboard - Shortcode Usage Guide

## 🎯 Quick Start

### Step 1: Install & Activate Plugin
1. Upload `squash-stats-dashboard-1.1.0.zip` to WordPress
2. Activate the plugin

### Step 2: Create Your Page
You have **complete flexibility** to create any page you want:

#### Option A: Create a NEW Test Page
1. Go to **Pages → Add New**
2. Title: "Squash Stats - NEW"
3. URL slug: `squash-venues-courts-world-stats-new`
4. Add the shortcode (see below)
5. Publish!

#### Option B: Replace the OLD Page
1. Go to **Pages → All Pages**
2. Edit: "Squash Venues & Courts - WORLD Stats"
3. Remove the Zoho Analytics iframe
4. Add the shortcode (see below)
5. Update!

### Step 3: Add the Shortcode

Simply paste this into your page content:

```
[squash_stats_dashboard]
```

That's it! 🎉

---

## 🎨 Advanced Usage

### Custom Height

If you want to control the minimum height:

```
[squash_stats_dashboard height="2000px"]
```

### Custom CSS Class

If you want to add your own styling:

```
[squash_stats_dashboard class="full-width-stats"]
```

### Both Together

```
[squash_stats_dashboard height="2000px" class="full-width-stats"]
```

---

## 📍 Where Can I Use It?

**Anywhere!** The shortcode works on:

- ✅ **Pages** (most common)
- ✅ **Posts** (if you want to embed in a blog post)
- ✅ **Custom Post Types** (if your theme supports them)
- ✅ **Widgets** (if your theme supports shortcodes in widgets)

You can even use it **multiple times** on different pages if needed!

---

## 🔄 Migration Strategy

### Safe Approach (Recommended)

1. **Create NEW page** at `/squash-venues-courts-world-stats-new/`
2. **Test thoroughly** - make sure everything works
3. **Compare side-by-side** with old Zoho version
4. **When satisfied:**
   - Edit the old page
   - Replace Zoho iframe with `[squash_stats_dashboard]`
   - Publish
5. **Delete the test page** (optional)

### Quick Approach

1. **Edit existing page** at `/squash-venues-courts-world-stats/`
2. **Replace Zoho iframe** with `[squash_stats_dashboard]`
3. **Update** - Done!

---

## 🛠️ Troubleshooting

### "The shortcode just shows as text"

- Make sure the plugin is **activated**
- Check you're using square brackets: `[squash_stats_dashboard]` not `{squash_stats_dashboard}`

### "The dashboard isn't loading"

- Check that `https://stats.squashplayers.app` is accessible
- Try clearing your browser cache (Ctrl+Shift+R)
- Try an incognito window

### "Assets aren't loading properly"

1. Go to WordPress Admin
2. Go to **Tools → Site Health → Info → Database**
3. Delete these transients:
   - `squash_dashboard_manifest`
   - `squash_dashboard_content`
4. Refresh your page

---

## 💡 Pro Tips

### Full-Width Display

If your WordPress theme has a full-width page template, use it! This gives the dashboard maximum space.

### Mobile Responsiveness

The dashboard is fully responsive - it will automatically adapt to mobile screens.

### Performance

- Dashboard content is cached for 5 minutes
- Assets are cached for 1 hour
- Only loads on pages that use the shortcode (won't slow down your whole site)

---

## 📞 Need Help?

Contact: ross@itomic.com.au

