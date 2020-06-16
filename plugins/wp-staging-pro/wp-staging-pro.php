<?php

/**
 * Plugin Name: WP Staging Pro
 * Plugin URI: https://wp-staging.com
 * Description: Create a staging clone site for testing & developing
 * Author: WP-Staging
 * Author URI: https://wordpress.org/plugins/wp-staging
 * Version: 3.0.4
 * Text Domain: wp-staging
 * Domain Path: /languages/
 *
 * @package WPSTG
 * @category Development, Migrating, Staging
 * @author WP Staging
 */
// No Direct Access
if (!defined("WPINC")) {
    die;
}

if (!defined('WPSTG_PLUGIN_SLUG')) {
    define('WPSTG_PLUGIN_SLUG', 'wp-staging-pro');
}

// Plugin Version
if (!defined('WPSTGPRO_VERSION')) {
    define('WPSTGPRO_VERSION', '3.0.4');
}

// Compatible up to WordPress Version
if (!defined('WPSTG_COMPATIBLE')) {
    define('WPSTG_COMPATIBLE', '5.4.2');
}

// Absolute path to plugin
if (!defined('WPSTG_PLUGIN_DIR')) {
    define('WPSTG_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// Expected version number of the must-use plugin 'optimizer'. Used for automatic updates of the mu-plugin
if (!defined('WPSTG_OPTIMIZER_MUVERSION')) {
    define('WPSTG_OPTIMIZER_MUVERSION', 1.2);
}

// Absolute path of plugin entry point .../plugins/wp-staging(-pro)/wp-staging(-pro).php
if (!defined('WPSTGPRO_PLUGIN_FILE')) {
    define('WPSTGPRO_PLUGIN_FILE', __FILE__);
}

// URL of the base folder
if (!defined('WPSTG_PLUGIN_URL')) {
    define('WPSTG_PLUGIN_URL', plugin_dir_url(__FILE__));
}

/**
 * Fix nonce check
 * Bug: https://core.trac.wordpress.org/ticket/41617#ticket
 * @param int $seconds
 * @return int
 * @todo Move this to separate class wpstaging/Core/Wpcore/WpFixes()
 */
if (!function_exists('wpstg_overwrite_nonce')) {
    function wpstg_overwrite_nonce($seconds)
    {
        return 86400;
    }
}

add_filter('nonce_life', 'wpstg_overwrite_nonce', 99999);

/**
 * Do not show update notifications for WP Staging Pro on the staging site
 * @param type object
 * @return object
 * @todo Move this to separate class wpstaging/Core/Wpcore/PluginUpdateNotify()
 */
if (!function_exists('wpstg_filter_plugin_updates')) {
    function wpstg_filter_plugin_updates($value)
    {
        if (wpstg_is_stagingsite()) {
            if (isset($value->response['wp-staging-pro/wp-staging-pro.php'])) {
                unset($value->response['wp-staging-pro/wp-staging-pro.php']);
            }
        }
        return $value;
    }
}

add_filter('site_transient_update_plugins', 'wpstg_filter_plugin_updates');

/**
 * Path to main WP Staging class
 * Make sure to not redeclare class in case free version has been installed previously
 */
if (!class_exists('WPStaging\WPStaging')) {
    require_once plugin_dir_path(__FILE__) . "Core/WPStaging.php";
}

if (!class_exists('Wpstg_Requirements_Check')) {
    include(dirname(__FILE__) . '/Core/Utils/requirements-check.php');
}

$plugin_requirements = new Wpstg_Requirements_Check(array(
    'title' => 'WP STAGING',
    'php' => '5.5',
    'wp' => '3.0',
    'file' => __FILE__,
));

if ($plugin_requirements->passes()) {

    // TODO; remove previous auto-loader, use composer based instead!
    require_once __DIR__ . '/vendor/autoload.php';

    $wpStaging = \WPStaging\WPStaging::getInstance();

    /**
     * Load important WP globals into WPStaging class to make them available via dependancy injection
     */
    // Wordpress DB Object
    if (isset($wpdb)) {
        $wpStaging->set("wpdb", $wpdb);
    }

    // WordPress Filter Object
    if (isset($wp_filter)) {
        $wpStaging->set("wp_filter", function () use (&$wp_filter) {
            return $wp_filter;
        });
    }

    /**
     * Inititalize WPStaging
     */
    $wpStaging->run();


    /**
     * Installation Hooks
     */
    if (!class_exists('WPStaging\Install')) {
        require_once plugin_dir_path(__FILE__) . "/install.php";
    }
}
