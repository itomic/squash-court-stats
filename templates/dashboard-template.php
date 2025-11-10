<?php
/**
 * Template for Squash Stats Dashboard
 * 
 * This template loads the dashboard content from stats.squashplayers.app
 */

// Get the plugin instance to access methods
$plugin = new Squash_Stats_Dashboard();

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Squash Venues & Courts - WORLD Stats (New) | <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
    <style>
        /* Remove WordPress admin bar space if logged in */
        body {
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* Ensure full width */
        .squash-dashboard-wrapper {
            width: 100%;
            max-width: 100%;
            margin: 0;
            padding: 0;
        }
        
        /* Hide WordPress elements that might interfere */
        #wpadminbar {
            display: none !important;
        }
    </style>
</head>
<body <?php body_class('squash-dashboard-page'); ?>>

<div class="squash-dashboard-wrapper">
    <?php echo $plugin->get_dashboard_content(); ?>
</div>

<?php wp_footer(); ?>
</body>
</html>

