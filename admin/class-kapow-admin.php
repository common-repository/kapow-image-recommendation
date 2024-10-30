<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://brightminded.com
 * @since      1.0.0
 *
 * @package    Kapow
 * @subpackage Kapow/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Kapow
 * @subpackage Kapow/admin
 * @author     BrightMinded <kapow@brightminded.com>
 */
class Kapow_Admin {
	/**
	 * The option key to store the user-defined KAPOW API access key.
	 *
	 * @since 	1.0.0
	 * @access 	public
	 */
	public const KAPOW_API_KEY = 'KAPOW_API_KEY';
	
	/**
	 * The option key to store the user-defined KAPOW model threshold parameter.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public const KAPOW_MODEL_THRESHOLD = 'KAPOW_MODEL_THRESHOLD';
	
	/**
	 * The option key to store the user-defined KAPOW model minimum-topic-score parameter.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public const KAPOW_MODEL_MIN_TOPIC_SCORE = 'KAPOW_MODEL_MIN_TOPIC_SCORE'; 

	/**
	 * The option key to store the user-defined KAPOW model number-of-images-per-page parameter.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public const KAPOW_MODEL_PER_PAGE = 'KAPOW_MODEL_PER_PAGE';

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The access token for the KAPOW REST API 
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $kapow_api_key The access token for the KAPOW REST API.
	 */
	private $kapow_api_key;

	/**
	 * The configuration settings for the KAPOW image recommendation model.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $kapow_model_settings The configuration settings for the KAPOW image recommendation model.
	 */
	private $kapow_model_settings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->kapow_api_key = get_option( self::KAPOW_API_KEY, KAPOW_DEFAULT_API_KEY );
		$this->kapow_model_settings = [
			'threshold'       => get_option( self::KAPOW_MODEL_THRESHOLD, 0.9 ),
			'min_topic_score' => get_option( self::KAPOW_MODEL_MIN_TOPIC_SCORE, 0.9 ),
			'per_page'        => get_option( self::KAPOW_MODEL_PER_PAGE, 5 )
		];		
	}

	/**
	 * Manage the admin page for KAPOW model settings.
	 *
	 * @since 1.0.0
	 */
	public function manage_kapow_model_settings() {
		$props = [
			[
				'name'  => 'api_key',
				'label' => 'API Key',
				'value' => $this->kapow_api_key,
				'option_name' => self::KAPOW_API_KEY
			],
			[
				'name' => 'threshold',
				'label' => 'Threshold',
				'value' => $this->kapow_model_settings[ 'threshold' ],
				'option_name' => self::KAPOW_MODEL_THRESHOLD,
				'max' => 1,
				'min' => 0.5,
				'step' => 0.1,
				'clean_func' => 'floatval'
			],
			[
				'name' => 'min_topic_score',
				'label' => 'Minimum Topic Score',
				'value' => $this->kapow_model_settings[ 'min_topic_score' ],
				'option_name' => self::KAPOW_MODEL_MIN_TOPIC_SCORE,
				'max' => 1,
				'min' => 0.5,
				'step' => 0.1,
				'clean_func' => 'floatval'
			],
			[
				'name' => 'img_per_tag',
				'label' => 'Number of Images per Keyword',
				'value' => $this->kapow_model_settings[ 'per_page' ],
				'option_name' => self::KAPOW_MODEL_PER_PAGE,
				'max' => 10,
				'min' => 1,
				'step' => 1,
				'clean_func' => 'absint'
			],
		];
		// handle the save event from the settings page
		if ( isset( $_POST[ 'kapow-save' ] ) ) {
			foreach ( $props as $key => $prop ) {
				if ( isset( $_POST[ $prop[ 'name' ] ] ) ) {
					$value = sanitize_text_field( $_POST[ $prop[ 'name' ] ] );
					// apply the appropriate sanitisation function
					// to the value retrieved from the settings page
					if( isset( $prop[ 'clean_func' ] ) && $prop[ 'clean_func' ] ) {
						$value = call_user_func( $prop[ 'clean_func' ], $value );
					}
					// update the currently stored value to the new one
					update_option( $prop[ 'option_name' ], $value, true );
					$props[ $key ][ 'value' ] = $value;
				}
			}
		}
		// broadcast that the settings have changed
		do_action( KAPOW_AFTER_SETTINGS_UPDATE );

		include( 'partials/kapow-admin-display.php' );
	}

	/**
	 * Filter used to disable the Gutember editor (for now...)
	 *
	 * @since    1.0.0
	 */
	public function use_block_editor_for_post_type() {
		
		return false;
	
	}

	/**
	 * Register the admin menu action to add the KAPOW settings page to the admin menu.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu() {
		add_menu_page(
			'KAPOW Settings',
			'KAPOW Settings',
			'manage_options',
			'kapow-model-settings',
			array( $this, 'manage_kapow_model_settings' ),
			null,
			null
		);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/kapow-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/kapow-admin.js', array( 'jquery' ), $this->version, false );

	}

}
