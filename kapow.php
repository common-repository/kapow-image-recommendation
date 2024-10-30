<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://brightminded.com
 * @since             1.0.0
 * @package           Kapow
 *
 * @wordpress-plugin
 * Plugin Name:       KAPOW Image Recommendation
 * Plugin URI:        
 * Description:       KAPOW image recommendation plugin can analyse the text from your posts and pages and return relevant, freely usable images from unsplash.com.
 * Version:           1.0.2
 * Author:            BrightMinded
 * Author URI:        https://brightminded.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       kapow
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'KAPOW_VERSION', '1.0.1' );

/**
 * Default access key for KAPOW REST API.
 */
define( 'KAPOW_DEFAULT_API_KEY', '7ec39123bcabe0ad3cafa521377a32829c01161bd7a9abba11dea5789946488d' );

/**
 * The name of the KAPOW-settings-updated Hook.
 */
define( 'KAPOW_AFTER_SETTINGS_UPDATE', 'kapow_after_settings_update' );

/**
 * The name of the action to perform on plugin deactivation
 */
define( 'KAPOW_AFTER_PLUGIN_DEACTIVATION', 'kapow_after_plugin_deactivation' );


/**
 * The name of the default source to use for retrieving freely available images.
 */
define( 'KAPOW_DEFAULT_IMAGES_SOURCE', 'Unsplash' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-kapow-activator.php
 */
function activate_kapow() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-kapow-activator.php';
	Kapow_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-kapow-deactivator.php
 */
function deactivate_kapow() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-kapow-deactivator.php';
	Kapow_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_kapow' );
register_deactivation_hook( __FILE__, 'deactivate_kapow' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-kapow.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_kapow() {

	$plugin = new Kapow();
	$plugin->run();

}
run_kapow();
