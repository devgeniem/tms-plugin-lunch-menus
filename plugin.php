<?php
/**
 * Plugin Name: TMS Lunch Menus
 * Plugin URI: https://github.com/devgeniem/tms-plugin-lunch-menus
 * Description: TMS Lunch Menus list
 * Version: 1.0.0
 * Requires PHP: 7.4
 * Author: Hion Digital Oy
 * Author URI: https://www.hiondigital.com/
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: tms-plugin-lunch-menus
 * Domain Path: /languages
 */

use TMS\Plugin\LunchMenus\Plugin;

// Check if Composer has been initialized in this directory.
// Otherwise we just use global composer autoloading.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Get the plugin version.
$plugin_data    = get_file_data( __FILE__, [ 'Version' => 'Version' ], 'plugin' );
$plugin_version = $plugin_data['Version'];

$plugin_path = __DIR__;

// Initialize the plugin.
Plugin::init( $plugin_version, $plugin_path );

if ( ! function_exists( 'tms_plugin_lunch_menus' ) ) {
    /**
     * Get the TMS Lunch Menus plugin instance.
     *
     * @return Plugin
     */
    function tms_plugin_lunch_menus() : Plugin {
        return Plugin::plugin();
    }
}
