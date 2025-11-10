<?php
/**
 * Plugin Name: Squash Stats Dashboard
 * Plugin URI: https://stats.squashplayers.app
 * Description: Embeds the Squash Stats Dashboard from stats.squashplayers.app into WordPress
 * Version: 1.0.0
 * Author: Itomic Apps
 * Author URI: https://www.itomic.com.au
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Squash_Stats_Dashboard {
    
    private $dashboard_url = 'https://stats.squashplayers.app';
    
    public function __construct() {
        add_action('init', array($this, 'register_custom_page'));
        add_filter('template_include', array($this, 'load_custom_template'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_dashboard_assets'));
    }
    
    /**
     * Register custom page for the new dashboard
     */
    public function register_custom_page() {
        // Add rewrite rule for the new page
        add_rewrite_rule(
            '^squash-venues-courts-world-stats-new/?$',
            'index.php?squash_stats_dashboard=1',
            'top'
        );
        
        // Add query var
        add_filter('query_vars', function($vars) {
            $vars[] = 'squash_stats_dashboard';
            return $vars;
        });
    }
    
    /**
     * Load custom template for our dashboard page
     */
    public function load_custom_template($template) {
        if (get_query_var('squash_stats_dashboard')) {
            // Use our custom template
            $custom_template = plugin_dir_path(__FILE__) . 'templates/dashboard-template.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }
    
    /**
     * Enqueue dashboard assets only on our custom page
     */
    public function enqueue_dashboard_assets() {
        if (get_query_var('squash_stats_dashboard')) {
            // Get the manifest to find the hashed asset filenames
            $manifest_url = $this->dashboard_url . '/build/manifest.json';
            $manifest = $this->fetch_manifest($manifest_url);
            
            if ($manifest) {
                // Enqueue CSS
                if (isset($manifest['resources/css/app.css'])) {
                    wp_enqueue_style(
                        'squash-dashboard-app',
                        $this->dashboard_url . '/build/' . $manifest['resources/css/app.css']['file'],
                        array(),
                        null
                    );
                }
                
                // Enqueue JS
                if (isset($manifest['resources/js/dashboard.js'])) {
                    wp_enqueue_script(
                        'squash-dashboard-js',
                        $this->dashboard_url . '/build/' . $manifest['resources/js/dashboard.js']['file'],
                        array(),
                        null,
                        true
                    );
                }
                
                // Enqueue MapLibre GL JS and CSS
                wp_enqueue_style(
                    'maplibre-gl',
                    'https://unpkg.com/maplibre-gl@4.0.0/dist/maplibre-gl.css',
                    array(),
                    '4.0.0'
                );
                
                wp_enqueue_script(
                    'maplibre-gl',
                    'https://unpkg.com/maplibre-gl@4.0.0/dist/maplibre-gl.js',
                    array(),
                    '4.0.0',
                    true
                );
                
                // Enqueue Chart.js and plugins
                wp_enqueue_script(
                    'chartjs',
                    'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js',
                    array(),
                    '4.4.0',
                    true
                );
                
                wp_enqueue_script(
                    'chartjs-datalabels',
                    'https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js',
                    array('chartjs'),
                    '2.2.0',
                    true
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
                
                // Update asset URLs to use absolute paths
                $content = str_replace('/build/', $this->dashboard_url . '/build/', $content);
                
                // Cache for 5 minutes
                set_transient($transient_key, $content, 5 * MINUTE_IN_SECONDS);
            }
        }
        
        return $content;
    }
}

// Initialize the plugin
new Squash_Stats_Dashboard();

/**
 * Activation hook - flush rewrite rules
 */
register_activation_hook(__FILE__, function() {
    flush_rewrite_rules();
});

/**
 * Deactivation hook - flush rewrite rules
 */
register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});

