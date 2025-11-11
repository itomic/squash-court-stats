=== Squash Stats Dashboard ===
Contributors: itomicapps
Tags: squash, statistics, dashboard, sports, analytics
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 1.3.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Embeds the Squash Stats Dashboard from stats.squashplayers.app into your WordPress site using a simple shortcode.

== Description ==

The Squash Stats Dashboard plugin allows you to embed comprehensive squash venue and court statistics directly into your WordPress pages and posts using a simple shortcode.

**Features:**

* **Shortcode Based:** Use `[squash_stats_dashboard]` anywhere
* **Flexible:** Create any page/URL you want
* **No iFrame:** Direct HTML injection for better performance and SEO
* **Auto-Updates:** Automatic update notifications from GitHub releases
* **Smart Caching:** Intelligent caching of manifest and content
* **Multiple Instances:** Use on multiple pages if needed
* **Customizable:** Optional height and CSS class parameters

**Data Displayed:**

* Interactive global map of squash venues
* Continental and sub-continental breakdowns
* Top countries by venues and courts
* Venue categories and statistics
* Timeline of venue additions
* Court type distributions
* And much more!

**Source Code:**

This plugin pulls data from a Laravel application hosted at stats.squashplayers.app. The full source code for both the plugin and the Laravel dashboard is available on GitHub:

* Plugin & Dashboard Source: [https://github.com/itomicspaceman/spa-stats-dashboard](https://github.com/itomicspaceman/spa-stats-dashboard)

The dashboard is built using Laravel 12 and Vite for asset compilation. See the GitHub repository for build instructions.

== Installation ==

**Automatic Installation:**

1. Go to WordPress Admin → Plugins → Add New → Upload Plugin
2. Choose `squash-stats-dashboard.zip`
3. Click "Install Now"
4. Click "Activate Plugin"

**Manual Installation:**

1. Upload the `squash-stats-dashboard` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress

**Usage:**

1. Create a new WordPress page (or edit an existing one)
2. Add the shortcode: `[squash_stats_dashboard]`
3. Publish!

== Frequently Asked Questions ==

= How do I use this plugin? =

Simply add the shortcode `[squash_stats_dashboard]` to any WordPress page or post where you want the dashboard to appear.

= Can I customize the appearance? =

Yes! You can use optional parameters:
* `[squash_stats_dashboard height="2000px"]` - Set a custom minimum height
* `[squash_stats_dashboard class="my-custom-class"]` - Add custom CSS classes
* `[squash_stats_dashboard height="2000px" class="full-width"]` - Use both together

= Can I use this on multiple pages? =

Yes! You can use the shortcode on as many pages as you like.

= Where does the data come from? =

The data is pulled from stats.squashplayers.app, which aggregates squash venue and court information from the Squash Players App database.

= Is the source code available? =

Yes! The full source code is available on GitHub at https://github.com/itomicspaceman/spa-stats-dashboard

= Does this work with page builders? =

Yes! The shortcode should work with most page builders that support WordPress shortcodes (Elementor, Beaver Builder, Divi, etc.).

== Screenshots ==

1. Global squash venue map with interactive clusters
2. Continental breakdown of venues and courts
3. Top countries by squash venues
4. Dashboard statistics overview

== Changelog ==

= 1.3.1 (2025-11-11) =
* Added full-width CSS to make dashboard span entire page width
* Dashboard now breaks out of WordPress content containers
* Uses viewport width (100vw) for edge-to-edge display
* Matches width of page header and navigation

= 1.3.0 (2025-11-11) =
* **MAJOR REFACTOR:** Switched to iframe-based embedding for complete isolation
* Eliminates all JavaScript and CSS conflicts with WordPress themes/plugins
* Uses postMessage API for dynamic height adjustment (no scrollbars)
* Dramatically simplified code (100 lines vs 213 lines)
* Geolocation now works properly within iframe context
* WordPress-recommended approach for embedding external content

= 1.2.3 (2025-11-11) =
* Fixed double initialization issue by removing duplicate JavaScript enqueueing
* JavaScript is now only loaded once from the fetched HTML

= 1.2.0 (2025-11-11) =
* Added automatic update checking from GitHub releases
* Plugin now notifies when new versions are available
* Supports WordPress auto-update system
* Fixed API URLs to use absolute paths for cross-domain embedding
* Improved caching for better performance

= 1.1.0 (2025-11-10) =
* Added shortcode-based system for maximum flexibility
* Added optional `height` and `class` parameters
* Removed template-based approach for simpler implementation
* Smart asset enqueueing (only loads on pages with shortcode)
* Complete flexibility - use on any page/URL
* Fixed plugin packaging for clean WordPress installation and deletion

= 1.0.0 (2025-11-10) =
* Initial release
* Direct HTML injection (no iframe)
* Intelligent caching system
* WordPress integration

== Upgrade Notice ==

= 1.1.0 =
Major update! Switched to shortcode-based system. If upgrading from 1.0.0, you'll need to use the shortcode instead of the custom URL.

== External Services ==

This plugin connects to the following external services:

**stats.squashplayers.app**
* Purpose: Fetches dashboard HTML content and statistics data
* Privacy: No personal data is transmitted. The service only provides public statistics.
* Terms: https://squash.players.app/

**CDN Services (for frontend libraries):**
* unpkg.com - MapLibre GL JS library
* cdn.jsdelivr.net - Chart.js and plugins
* cdnjs.cloudflare.com - Font Awesome icons
* fonts.openmaptiles.org - Map font glyphs

All external services are loaded only on pages that use the `[squash_stats_dashboard]` shortcode.

