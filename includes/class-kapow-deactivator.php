<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://brightminded.com
 * @since      1.0.0
 *
 * @package    Kapow
 * @subpackage Kapow/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Kapow
 * @subpackage Kapow/includes
 * @author     BrightMinded <kapow@brightminded.com>
 */
class Kapow_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		do_action( KAPOW_AFTER_PLUGIN_DEACTIVATION );

	}

}
