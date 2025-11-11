<?php
/**
 * Plugin Name: Squash Stats Dashboard
 * Plugin URI: https://stats.squashplayers.app
 * Description: Embeds the Squash Stats Dashboard from stats.squashplayers.app into WordPress using shortcode [squash_stats_dashboard]
 * Version: 1.2.3
 * Author: Itomic Apps
 * Author URI: https://www.itomic.com.au
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: https://github.com/itomicspaceman/spa-stats-dashboard
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load the updater class
require_once plugin_dir_path(__FILE__) . 'includes/class-plugin-updater.php';

class Squash_Stats_Dashboard {
    
    private $dashboard_url = 'https://stats.squashplayers.app';
    private $assets_enqueued = false;
    
    public function __construct() {
        // Register shortcode
        add_shortcode('squash_stats_dashboard', array($this, 'render_dashboard_shortcode'));
        
        // Enqueue assets when shortcode is used
        add_action('wp_enqueue_scripts', array($this, 'maybe_enqueue_dashboard_assets'));
    }
    
    /**
     * Render the dashboard shortcode
     */
    public function render_dashboard_shortcode($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'height' => 'auto', // Allow custom height
            'class' => '',      // Allow custom CSS classes
        ), $atts);
        
        // Ensure assets are enqueued
        $this->enqueue_dashboard_assets();
        
        // Get dashboard content
        $content = $this->get_dashboard_content();
        
        // Wrap in container with optional custom class
        $wrapper_class = 'squash-dashboard-wrapper ' . esc_attr($atts['class']);
        $height_style = $atts['height'] !== 'auto' ? 'min-height: ' . esc_attr($atts['height']) . ';' : '';
        
        return sprintf(
            '<div class="%s" style="%s">%s</div>',
            $wrapper_class,
            $height_style,
            $content
        );
    }
    
    /**
     * Check if we need to enqueue assets (when shortcode is present)
     */
    public function maybe_enqueue_dashboard_assets() {
        global $post;
        
        // Check if the current post/page contains our shortcode
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'squash_stats_dashboard')) {
            $this->enqueue_dashboard_assets();
        }
    }
    
    /**
     * Enqueue dashboard assets
     * 
     * NOTE: We only enqueue CSS here. JavaScript is already included in the fetched HTML
     * from stats.squashplayers.app to avoid double initialization.
     */
    public function enqueue_dashboard_assets() {
        // Prevent multiple enqueueing
        if ($this->assets_enqueued) {
            return;
        }
        
        $this->assets_enqueued = true;
        
        // Get the manifest to find the hashed asset filenames
        $manifest_url = $this->dashboard_url . '/build/manifest.json';
        $manifest = $this->fetch_manifest($manifest_url);
        
        if ($manifest) {
            // Enqueue CSS only - JS is already in the HTML
            if (isset($manifest['resources/css/app.css'])) {
                wp_enqueue_style(
                    'squash-dashboard-app',
                    $this->dashboard_url . '/build/' . $manifest['resources/css/app.css']['file'],
                    array(),
                    null
                );
            }
            
            // Enqueue MapLibre GL CSS only
            wp_enqueue_style(
                'maplibre-gl',
                'https://unpkg.com/maplibre-gl@4.0.0/dist/maplibre-gl.css',
                array(),
                '4.0.0'
            );
            
            // Font Awesome
            wp_enqueue_style(
                'font-awesome',
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
                array(),
                '6.5.1'
            );
        }
    }
    
    /**
     * Fetch and cache the Vite manifest
     */
    private function fetch_manifest($url) {
        $transient_key = 'squash_dashboard_manifest';
        $manifest = get_transient($transient_key);
        
        if (false === $manifest) {
            $response = wp_remote_get($url);
            
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $manifest = json_decode(wp_remote_retrieve_body($response), true);
                // Cache for 1 hour
                set_transient($transient_key, $manifest, HOUR_IN_SECONDS);
            }
        }
        
        return $manifest;
    }
    
    /**
     * Fetch dashboard HTML content
     */
    public function get_dashboard_content() {
        $transient_key = 'squash_dashboard_content';
        $content = get_transient($transient_key);
        
        if (false === $content) {
            $response = wp_remote_get($this->dashboard_url);
            
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $html = wp_remote_retrieve_body($response);
                
                // Extract the body content (between <body> tags)
                preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches);
                $content = isset($matches[1]) ? $matches[1] : '';
                
                // No URL replacement needed - Laravel app already outputs absolute URLs
                
                // Cache for 5 minutes
                set_transient($transient_key, $content, 5 * MINUTE_IN_SECONDS);
            }
        }
        
        return $content;
    }
}

// Initialize the plugin
new Squash_Stats_Dashboard();

// Initialize the updater
if (is_admin()) {
    new Squash_Stats_Dashboard_Updater(
        plugin_basename(__FILE__),
        'itomicspaceman/spa-stats-dashboard'
    );
}

