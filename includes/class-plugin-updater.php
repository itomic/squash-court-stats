<?php
/**
 * Plugin Updater Class
 * 
 * Handles checking for updates from a remote server (GitHub releases)
 * and notifying WordPress when updates are available.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Squash_Stats_Dashboard_Updater {
    
    private $plugin_slug;
    private $plugin_basename;
    private $version;
    private $github_repo;
    private $cache_key;
    private $cache_allowed;
    
    public function __construct($plugin_basename, $github_repo) {
        $this->plugin_basename = $plugin_basename;
        $this->plugin_slug = dirname($plugin_basename);
        $this->github_repo = $github_repo; // e.g., 'itomicspaceman/spa-stats-dashboard'
        $this->cache_key = 'squash_stats_dashboard_update_check';
        $this->cache_allowed = true;
        
        // Get current version from plugin header
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_basename);
        $this->version = $plugin_data['Version'];
        
        // Hook into WordPress update system
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_update'));
        add_filter('plugins_api', array($this, 'plugin_info'), 20, 3);
        add_filter('upgrader_post_install', array($this, 'after_install'), 10, 3);
    }
    
    /**
     * Check for plugin updates
     */
    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }
        
        // Get remote version info
        $remote_version = $this->get_remote_version();
        
        if ($remote_version && version_compare($this->version, $remote_version->version, '<')) {
            $plugin_data = array(
                'slug' => $this->plugin_slug,
                'plugin' => $this->plugin_basename,
                'new_version' => $remote_version->version,
                'url' => $remote_version->homepage,
                'package' => $remote_version->download_url,
                'tested' => $remote_version->tested,
                'requires' => $remote_version->requires,
                'requires_php' => $remote_version->requires_php,
            );
            
            $transient->response[$this->plugin_basename] = (object) $plugin_data;
        }
        
        return $transient;
    }
    
    /**
     * Get plugin information for the "View details" popup
     */
    public function plugin_info($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }
        
        if ($args->slug !== $this->plugin_slug) {
            return $result;
        }
        
        $remote_version = $this->get_remote_version();
        
        if (!$remote_version) {
            return $result;
        }
        
        $result = (object) array(
            'name' => $remote_version->name,
            'slug' => $this->plugin_slug,
            'version' => $remote_version->version,
            'author' => '<a href="https://www.itomic.com.au">Itomic Apps</a>',
            'homepage' => $remote_version->homepage,
            'requires' => $remote_version->requires,
            'tested' => $remote_version->tested,
            'requires_php' => $remote_version->requires_php,
            'download_link' => $remote_version->download_url,
            'sections' => array(
                'description' => $remote_version->description,
                'changelog' => $remote_version->changelog,
            ),
        );
        
        return $result;
    }
    
    /**
     * After plugin installation, ensure correct folder name
     */
    public function after_install($response, $hook_extra, $result) {
        global $wp_filesystem;
        
        $install_directory = plugin_dir_path(WP_PLUGIN_DIR . '/' . $this->plugin_basename);
        $wp_filesystem->move($result['destination'], $install_directory);
        $result['destination'] = $install_directory;
        
        if ($this->plugin_basename) {
            activate_plugin($this->plugin_basename);
        }
        
        return $result;
    }
    
    /**
     * Get remote version information from GitHub
     */
    private function get_remote_version() {
        // Check cache first
        if ($this->cache_allowed) {
            $cached = get_transient($this->cache_key);
            if ($cached !== false) {
                return $cached;
            }
        }
        
        // Fetch latest release from GitHub API
        $api_url = "https://api.github.com/repos/{$this->github_repo}/releases/latest";
        
        $response = wp_remote_get($api_url, array(
            'timeout' => 10,
            'headers' => array(
                'Accept' => 'application/vnd.github.v3+json',
            ),
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);
        
        if (empty($data)) {
            return false;
        }
        
        // Find the plugin ZIP asset
        $download_url = '';
        if (!empty($data->assets)) {
            foreach ($data->assets as $asset) {
                if (strpos($asset->name, 'squash-stats-dashboard') !== false && 
                    strpos($asset->name, '.zip') !== false) {
                    $download_url = $asset->browser_download_url;
                    break;
                }
            }
        }
        
        // If no asset found, use the source code zip
        if (empty($download_url)) {
            $download_url = $data->zipball_url;
        }
        
        // Parse version from tag name (e.g., "v1.1.0" or "1.1.0")
        $version = ltrim($data->tag_name, 'v');
        
        $remote_version = (object) array(
            'version' => $version,
            'name' => 'Squash Stats Dashboard',
            'homepage' => 'https://github.com/' . $this->github_repo,
            'download_url' => $download_url,
            'requires' => '5.0',
            'tested' => '6.7',
            'requires_php' => '7.4',
            'description' => $data->body ?: 'Embeds the Squash Stats Dashboard into WordPress using a shortcode.',
            'changelog' => $data->body ?: 'See GitHub releases for full changelog.',
        );
        
        // Cache for 12 hours
        if ($this->cache_allowed) {
            set_transient($this->cache_key, $remote_version, 12 * HOUR_IN_SECONDS);
        }
        
        return $remote_version;
    }
}

