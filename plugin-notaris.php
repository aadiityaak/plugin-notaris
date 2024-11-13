<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://websweetstudio.com
 * @since             1.0.0
 * @package           Custom_Plugin
 *
 * @wordpress-plugin
 * Plugin Name:       Plugin Notaris
 * Plugin URI:        https://websweetstudio.com
 * Description:       Plugin untuk web custom.
 * Version:           1.0.0
 * Author:            Websweet Studio
 * Author URI:        https://websweetstudio.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       custom-plugin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

// Membuat tabel attachments di database WordPress
function create_attachment_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'attachments';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        invoice varchar(255) NOT NULL,
        attachment_id mediumint(9) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_setup_theme', 'create_attachment_table');

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('CUSTOM_PLUGIN_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-custom-plugin-activator.php
 */
function activate_custom_plugin()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-custom-plugin-activator.php';
    Custom_Plugin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-custom-plugin-deactivator.php
 */
function deactivate_custom_plugin()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-custom-plugin-deactivator.php';
    Custom_Plugin_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_custom_plugin');
register_deactivation_hook(__FILE__, 'deactivate_custom_plugin');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-custom-plugin.php';
require plugin_dir_path(__FILE__) . 'includes/shortcode-legacy.php';
require plugin_dir_path(__FILE__) . 'includes/action-filter.php';
require plugin_dir_path(__FILE__) . 'includes/ajax.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_custom_plugin()
{

    $plugin = new Custom_Plugin();
    $plugin->run();
}
run_custom_plugin();
