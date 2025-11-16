<?php
/**
 * Plugin Name: Squash Stats Dashboard
 * Plugin URI: https://stats.squashplayers.app
 * Description: Embeds the Squash Stats Dashboard from stats.squashplayers.app into WordPress using shortcode [squash_stats_dashboard]
 * Version: 1.5.0
 * Author: Itomic Apps
 * Author URI: https://www.itomic.com.au
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: https://github.com/itomic/spa-stats-dashboard
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load the updater class (only for self-hosted installations, not WordPress.org)
// WordPress.org plugins must use the built-in update system
if (file_exists(plugin_dir_path(__FILE__) . 'includes/class-plugin-updater.php')) {
    require_once plugin_dir_path(__FILE__) . 'includes/class-plugin-updater.php';
}

// Load the admin settings page
if (is_admin() && file_exists(plugin_dir_path(__FILE__) . 'includes/class-admin-settings.php')) {
    require_once plugin_dir_path(__FILE__) . 'includes/class-admin-settings.php';
}

class Squash_Stats_Dashboard {
    
    private $dashboard_url = 'https://stats.squashplayers.app';
    private $api_url = 'https://stats.squashplayers.app/api';
    
    public function __construct() {
        // Register shortcodes
        add_shortcode('squash_stats_dashboard', array($this, 'render_dashboard_shortcode'));
        add_shortcode('squash_trivia', array($this, 'render_trivia_shortcode'));
        
        // Add CSS for full-width iframe
        add_action('wp_head', array($this, 'add_dashboard_styles'));
        
        // Enqueue scripts and styles for trivia page
        add_action('wp_enqueue_scripts', array($this, 'enqueue_trivia_assets'));
    }
    
    /**
     * Add CSS to make the dashboard iframe full-width
     * This breaks the iframe out of WordPress content containers
     */
    public function add_dashboard_styles() {
        global $post;
        
        // Only add styles if the shortcode is present
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'squash_stats_dashboard')) {
            ?>
            <style>
                /* Full-width dashboard container */
                .squash-dashboard-wrapper {
                    width: 100vw;
                    position: relative;
                    left: 50%;
                    right: 50%;
                    margin-left: -50vw;
                    margin-right: -50vw;
                    max-width: 100vw;
                }
                
                /* Ensure iframe is full width within wrapper */
                .squash-dashboard-iframe {
                    width: 100%;
                    display: block;
                }
            </style>
            <?php
        }
    }
    
    /**
     * Render the dashboard shortcode using iframe
     * 
     * This approach provides complete isolation between the dashboard and WordPress:
     * - No JavaScript conflicts
     * - No CSS conflicts
     * - No global variable pollution
     * - Geolocation works properly
     * - Uses postMessage API for dynamic height adjustment (no scrollbars)
     */
    public function render_dashboard_shortcode($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'dashboard' => '',  // Dashboard name (e.g., 'world', 'country', 'venue-types')
            'charts' => '',     // Comma-separated chart IDs (e.g., 'venue-map,top-venues')
            'filter' => '',     // Geographic filter (e.g., 'country:AU', 'region:19', 'continent:5')
            'title' => '',      // Custom title override for charts/map
            'class' => '',      // Allow custom CSS classes
        ), $atts);
        
        // Build the iframe URL
        $url = $this->dashboard_url;
        $query_params = array();
        
        // Add query parameters if dashboard or charts are specified
        if (!empty($atts['charts'])) {
            $url .= '/render';
            $query_params['charts'] = $atts['charts'];
        } elseif (!empty($atts['dashboard'])) {
            $url .= '/render';
            $query_params['dashboard'] = $atts['dashboard'];
        }
        
        // Add filter parameter if specified
        if (!empty($atts['filter'])) {
            $query_params['filter'] = $atts['filter'];
        }
        
        // Add title parameter if specified
        if (!empty($atts['title'])) {
            $query_params['title'] = $atts['title'];
        }
        
        // Build query string
        if (!empty($query_params)) {
            $url .= '?' . http_build_query($query_params);
        }
        // If neither is specified, default to the world dashboard (root URL)
        
        // Generate unique ID for this iframe instance
        $iframe_id = 'squash-dashboard-' . uniqid();
        
        // Wrap iframe in full-width container
        $html = '<div class="squash-dashboard-wrapper">';
        
        // Build iframe HTML
        $html .= sprintf(
            '<iframe 
                id="%s"
                src="%s" 
                width="100%%" 
                style="border: none; display: block; overflow: hidden; min-height: 500px;"
                frameborder="0"
                scrolling="no"
                class="squash-dashboard-iframe %s"
                loading="lazy"
                sandbox="allow-scripts allow-same-origin allow-popups"
                title="Squash Stats Dashboard">
            </iframe>',
            esc_attr($iframe_id),
            esc_url($url),
            esc_attr($atts['class'])
        );
        
        // Add postMessage listener for dynamic height adjustment
        $html .= sprintf(
            '<script>
            (function() {
                var iframe = document.getElementById("%s");
                
                // Listen for height messages from the iframe
                window.addEventListener("message", function(event) {
                    // Security: verify origin
                    if (event.origin !== "https://stats.squashplayers.app") {
                        return;
                    }
                    
                    // Check if this is a height update message
                    if (event.data && event.data.type === "squash-dashboard-height") {
                        iframe.style.height = event.data.height + "px";
                        console.log("Dashboard height updated:", event.data.height);
                    }
                });
                
                // Fallback: if no height message received after 5 seconds, set a default height
                setTimeout(function() {
                    if (iframe.style.height === "" || iframe.style.height === "500px") {
                        iframe.style.height = "3000px";
                        console.log("Dashboard height fallback applied");
                    }
                }, 5000);
            })();
            </script>',
            esc_js($iframe_id)
        );
        
        // Close wrapper div
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Enqueue CSS and JavaScript for trivia page
     */
    public function enqueue_trivia_assets() {
        global $post;
        
        // Only enqueue if the shortcode is present
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'squash_trivia')) {
            // Enqueue Leaflet for maps
            wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4');
            wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), '1.9.4', true);
            
            // Enqueue Chart.js for charts
            wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', array(), '4.4.0', true);
            
            // Enqueue WordCloud2.js for word cloud
            wp_enqueue_script('wordcloud2', 'https://cdn.jsdelivr.net/npm/wordcloud2.js@1.2.2/src/wordcloud2.min.js', array(), '1.2.2', true);
            
            // Enqueue custom trivia styles and scripts
            wp_enqueue_style('squash-trivia-css', plugin_dir_url(__FILE__) . 'assets/css/trivia.css', array(), '1.0.0');
            wp_enqueue_script('squash-trivia-js', plugin_dir_url(__FILE__) . 'assets/js/trivia.js', array('jquery', 'leaflet-js', 'chartjs'), '1.0.0', true);
            
            // Pass API URL to JavaScript
            wp_localize_script('squash-trivia-js', 'squashTriviaConfig', array(
                'apiUrl' => $this->api_url,
                'ajaxUrl' => admin_url('admin-ajax.php'),
            ));
        }
    }
    
    /**
     * Render the trivia shortcode with native WordPress integration
     * 
     * Attributes:
     * - section: Specific section to display (default: all)
     *   Options: all, countries-without-venues, high-altitude, extreme-latitude, 
     *            hotels-resorts, population-area, unknown-courts, country-club, 
     *            word-cloud, loneliest, graveyard
     */
    public function render_trivia_shortcode($atts) {
        $atts = shortcode_atts(array(
            'section' => 'all',
            'filter' => '',
        ), $atts);
        
        ob_start();
        ?>
        <div class="squash-trivia-wrapper">
            <div class="squash-trivia-header">
                <h1>Squash Trivia</h1>
                <p class="trivia-subtitle">Fun facts, interesting maps, and quirky statistics about squash worldwide</p>
            </div>
            
            <?php if ($atts['section'] === 'all' || $atts['section'] === 'countries-without-venues'): ?>
            <div class="trivia-section" id="countries-without-venues">
                <h2>Countries & Dependencies without Squash Venues</h2>
                <div class="trivia-map-container">
                    <div id="countries-without-map" style="height: 500px; width: 100%;"></div>
                </div>
                <div class="trivia-stats">
                    <div class="stat-box">
                        <span class="stat-value" id="countries-without-count">-</span>
                        <span class="stat-label">Countries Without Venues</span>
                    </div>
                </div>
                <button class="view-list-btn" onclick="squashTrivia.showCountriesWithoutList()">View Full List</button>
            </div>
            <?php endif; ?>
            
            <?php if ($atts['section'] === 'all' || $atts['section'] === 'high-altitude'): ?>
            <div class="trivia-section" id="high-altitude">
                <h2>High Altitude Squash Venues (2000m+ above sea level)</h2>
                <div class="trivia-map-container">
                    <div id="high-altitude-map" style="height: 500px; width: 100%;"></div>
                </div>
                <div class="trivia-table-container">
                    <h3>Top 10 Highest Venues</h3>
                    <table class="trivia-table" id="high-altitude-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Venue</th>
                                <th>City</th>
                                <th>Country</th>
                                <th>Elevation (m)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($atts['section'] === 'all' || $atts['section'] === 'extreme-latitude'): ?>
            <div class="trivia-section" id="extreme-latitude">
                <h2>Most Northerly and Southerly Squash Venues</h2>
                <p class="section-description">Discover the squash venues at the extreme northern and southern latitudes of the world.</p>
                
                <div class="trivia-tabs">
                    <button class="tab-btn active" onclick="squashTrivia.switchTab('northerly')">Most Northerly (Top 20)</button>
                    <button class="tab-btn" onclick="squashTrivia.switchTab('southerly')">Most Southerly (Top 20)</button>
                </div>
                
                <div class="trivia-map-container">
                    <div id="extreme-latitude-map" style="height: 500px; width: 100%;"></div>
                </div>
                
                <div class="trivia-table-container">
                    <div id="northerly-table-container" class="tab-content active">
                        <h3>Most Northerly Squash Venues</h3>
                        <table class="trivia-table" id="northerly-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Venue</th>
                                    <th>City</th>
                                    <th>Country</th>
                                    <th>Latitude</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td colspan="5">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="southerly-table-container" class="tab-content">
                        <h3>Most Southerly Squash Venues</h3>
                        <table class="trivia-table" id="southerly-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Venue</th>
                                    <th>City</th>
                                    <th>Country</th>
                                    <th>Latitude</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td colspan="5">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($atts['section'] === 'all' || $atts['section'] === 'hotels-resorts'): ?>
            <div class="trivia-section" id="hotels-resorts">
                <h2>Hotels & Resorts with Squash Courts</h2>
                <p class="section-description">Discover hotels and resorts around the world that offer squash facilities for their guests.</p>
                
                <div class="trivia-map-container">
                    <div id="hotels-map" style="height: 500px; width: 100%;"></div>
                </div>
                
                <div class="trivia-filters">
                    <label for="continent-filter">Filter by Continent:</label>
                    <select id="continent-filter" onchange="squashTrivia.filterHotels(this.value)">
                        <option value="all">All Continents</option>
                        <option value="1">Africa</option>
                        <option value="2">Asia</option>
                        <option value="3">Europe</option>
                        <option value="4">North America</option>
                        <option value="5">Oceania</option>
                        <option value="6">South America</option>
                    </select>
                </div>
                
                <div class="trivia-stats">
                    <div class="stat-box">
                        <span class="stat-value" id="hotels-count">-</span>
                        <span class="stat-label">Hotels & Resorts</span>
                    </div>
                </div>
                
                <button class="view-list-btn" onclick="squashTrivia.showHotelsList()">View All Hotels & Resorts</button>
            </div>
            <?php endif; ?>
            
            <?php if ($atts['section'] === 'all' || $atts['section'] === 'population-area'): ?>
            <div class="trivia-section" id="population-area">
                <h2>Squash Venues & Courts by Population & Land Area</h2>
                <p class="section-description">Comprehensive statistics for all countries with squash venues, showing venue and court density relative to population and land area.</p>
                
                <div class="trivia-table-container">
                    <table class="trivia-table sortable" id="population-area-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Country</th>
                                <th>Pop. (millions)</th>
                                <th>Area (m. sq km)</th>
                                <th>Venues</th>
                                <th>Courts</th>
                                <th>V / P</th>
                                <th>C / P</th>
                                <th>V / A</th>
                                <th>C / A</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="10">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="table-legend">
                    <p><strong>Legend:</strong></p>
                    <ul>
                        <li><strong>V / P</strong> = Venues per million population</li>
                        <li><strong>C / P</strong> = Courts per million population</li>
                        <li><strong>V / A</strong> = Venues per 1,000 sq km</li>
                        <li><strong>C / A</strong> = Courts per 1,000 sq km</li>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($atts['section'] === 'all' || $atts['section'] === 'unknown-courts'): ?>
            <div class="trivia-section" id="unknown-courts">
                <h2>Squash Venues with Unknown # of Courts</h2>
                <p class="section-description">These venues are in our database but we don't yet know how many courts they have. Can you help us complete this data?</p>
                
                <div class="trivia-map-container">
                    <div id="unknown-courts-map" style="height: 500px; width: 100%;"></div>
                </div>
                
                <div class="trivia-filters">
                    <label for="unknown-continent-filter">Filter by Continent:</label>
                    <select id="unknown-continent-filter" onchange="squashTrivia.filterUnknownCourts(this.value)">
                        <option value="all">All Continents</option>
                        <option value="1">Africa</option>
                        <option value="2">Asia</option>
                        <option value="3">Europe</option>
                        <option value="4">North America</option>
                        <option value="5">Oceania</option>
                        <option value="6">South America</option>
                    </select>
                </div>
                
                <div class="trivia-stats">
                    <div class="stat-box">
                        <span class="stat-value" id="unknown-courts-count">-</span>
                        <span class="stat-label">Venues with Unknown Courts</span>
                    </div>
                </div>
                
                <button class="view-list-btn" onclick="squashTrivia.showUnknownCourtsList()">View All Venues</button>
            </div>
            <?php endif; ?>
            
            <?php if ($atts['section'] === 'all' || $atts['section'] === 'country-club'): ?>
            <div class="trivia-section" id="country-club">
                <h2>The 100% Country Club</h2>
                <p class="section-description">Countries where we know the court count for all (or most) venues. Once we identify the number of courts in ALL venues, then all countries with squash venues will join the 100% Country Club!</p>
                
                <div class="trivia-filters">
                    <label for="club-continent-filter">Filter by Continent:</label>
                    <select id="club-continent-filter" onchange="squashTrivia.filterCountryClub(this.value)">
                        <option value="all">All Continents</option>
                        <option value="1">Africa</option>
                        <option value="2">Asia</option>
                        <option value="3">Europe</option>
                        <option value="4">North America</option>
                        <option value="5">Oceania</option>
                        <option value="6">South America</option>
                    </select>
                </div>
                
                <div class="trivia-table-container">
                    <table class="trivia-table sortable" id="country-club-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Country</th>
                                <th>Venues</th>
                                <th>Venues with > 0 Courts</th>
                                <th>Courts</th>
                                <th>% of Venues with > 0 Courts</th>
                                <th>Courts Per Venue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="7">Loading...</td></tr>
                        </tbody>
                        <tfoot>
                            <tr class="grand-summary">
                                <td colspan="2"><strong>Grand Summary:</strong></td>
                                <td id="club-total-venues">-</td>
                                <td id="club-total-known">-</td>
                                <td id="club-total-courts">-</td>
                                <td id="club-avg-percentage">-</td>
                                <td id="club-avg-courts-per-venue">-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($atts['section'] === 'all' || $atts['section'] === 'word-cloud'): ?>
            <div class="trivia-section" id="word-cloud">
                <h2>Countries by # of Squash Venues</h2>
                <p class="section-description">The size of each country name represents the number of squash venues. Larger text = more venues!</p>
                
                <div class="color-legend">
                    <span class="legend-item"><span class="color-box" style="background: #1e40af;"></span> 500+ venues</span>
                    <span class="legend-item"><span class="color-box" style="background: #3b82f6;"></span> 100-499 venues</span>
                    <span class="legend-item"><span class="color-box" style="background: #60a5fa;"></span> 50-99 venues</span>
                    <span class="legend-item"><span class="color-box" style="background: #93c5fd;"></span> 10-49 venues</span>
                    <span class="legend-item"><span class="color-box" style="background: #dbeafe;"></span> 1-9 venues</span>
                </div>
                
                <div class="trivia-wordcloud-container">
                    <canvas id="word-cloud-canvas" width="800" height="600"></canvas>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($atts['section'] === 'all' || $atts['section'] === 'loneliest'): ?>
            <div class="trivia-section" id="loneliest">
                <h2>Loneliest Squash Courts</h2>
                <p class="section-description">These are the squash venues that are furthest from their nearest neighbor. Lines connect each venue to its closest squash court.</p>
                
                <div class="trivia-map-container">
                    <div id="loneliest-map" style="height: 500px; width: 100%;"></div>
                </div>
                
                <div class="trivia-table-container">
                    <h3>Top 10 Loneliest Venues</h3>
                    <table class="trivia-table" id="loneliest-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Venue</th>
                                <th>City</th>
                                <th>Country</th>
                                <th>Distance to Nearest (km)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($atts['section'] === 'all' || $atts['section'] === 'graveyard'): ?>
            <div class="trivia-section" id="graveyard">
                <h2>Squash Court Graveyard</h2>
                <p class="section-description">A memorial to squash venues that have closed, been removed, or never existed. This data helps us track the evolution of squash infrastructure worldwide.</p>
                
                <div class="trivia-filters">
                    <label for="graveyard-country-filter">Filter by Country:</label>
                    <select id="graveyard-country-filter" onchange="squashTrivia.filterGraveyard('country', this.value)">
                        <option value="">All Countries</option>
                    </select>
                    
                    <label for="graveyard-reason-filter">Filter by Cause of Death:</label>
                    <select id="graveyard-reason-filter" onchange="squashTrivia.filterGraveyard('reason', this.value)">
                        <option value="">All Reasons</option>
                    </select>
                </div>
                
                <div class="trivia-stats graveyard-stats">
                    <div class="stat-box">
                        <span class="stat-value" id="graveyard-total-venues">-</span>
                        <span class="stat-label">Total Venues</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-value" id="graveyard-countries">-</span>
                        <span class="stat-label">Countries</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-value" id="graveyard-courts-lost">-</span>
                        <span class="stat-label">Courts Lost</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-value" id="graveyard-showing">-</span>
                        <span class="stat-label">Showing</span>
                    </div>
                </div>
                
                <div class="trivia-table-container">
                    <table class="trivia-table" id="graveyard-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Country</th>
                                <th>Courts</th>
                                <th>Cause of Death</th>
                                <th>Death Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="6">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="trivia-footer">
                <p>&copy; <?php echo date('Y'); ?> Squash Players. Data updated every 3 hours.</p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize the plugin
new Squash_Stats_Dashboard();

// Initialize the updater (only if the class exists - for self-hosted installations)
// WordPress.org plugins use the built-in update system and should not include this file
if (is_admin() && class_exists('Squash_Stats_Dashboard_Updater')) {
    new Squash_Stats_Dashboard_Updater(
        plugin_basename(__FILE__),
        'itomicspaceman/spa-stats-dashboard'
    );
}
