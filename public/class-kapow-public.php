<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://brightminded.com
 * @since      1.0.0
 *
 * @package    Kapow
 * @subpackage Kapow/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Kapow
 * @subpackage Kapow/public
 * @author     BrightMinded <kapow@brightminded.com>
 */
class Kapow_Public {
	
	/**
	 * The key prefix to use in order to identify transients
	 * created by this plugin.
	 *
	 * @since 	1.0.0
	 * @access 	public
	 */
	private const CACHE_KEY_PREFIX      = 'KAPOW';
	
	/**
	 * The option key to use in order to retrieve (from transient cache) 
	 * the list of transient keys that this plugin may
	 * have created.
	 *
	 * @since 	1.0.0
	 * @access 	public
	 */
	private const CACHE_KEYCACHE_KEY    = 'KAPOW_KEYCACHE_KEY';
	
	/**
	 * The interval of time in seconds that it takes for a cached KAPOW response to expire.
	 *
	 * @since 	1.0.0
	 * @access 	public
	 */
	private const CACHE_EXPIRATION      = HOUR_IN_SECONDS;
	
	/**
	 * The interval of time in seconds that it takes for the cached list of 
	 * transient keys to expire.
	 *
	 * @since 	1.0.0
	 * @access 	public
	 */
	private const KEYCACHE_EXPIRATION   = 3 * HOUR_IN_SECONDS;
	
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
	 * The base URL for the KAPOW REST API 
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $kapow_api_url The base URL for the KAPOW REST API.
	 */
	private $kapow_api_url;

	/**
	 * The access token for the KAPOW REST API 
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $kapow_api_key The access token for the KAPOW REST API.
	 */
	private $kapow_api_key;

	/**
	 * The value for the threshold parameter for KAPOW image recommendation model. 
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $kapow_model_threshold The threshold parameter value.
	 */
	private $kapow_model_threshold;

	/**
	 * The value for the minimum topic score parameter for KAPOW image recommendation model. 
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $kapow_model_min_topic_score The min topic score parameter value.
	 */
	private $kapow_model_min_topic_score;

	/**
	 * The value for the number of images per keyword parameter for KAPOW image recommendation model. 
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $kapow_model_per_page The number of images per keyword parameter value.
	 */
	private $kapow_model_per_page;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->kapow_api_url = 'https://kapow.brightminded.ai/api/v1.0/{route}';
		$this->kapow_api_key = get_option( Kapow_Admin::KAPOW_API_KEY, KAPOW_DEFAULT_API_KEY );
		$this->kapow_model_threshold = get_option( Kapow_Admin::KAPOW_MODEL_THRESHOLD, 0.9 );
		$this->kapow_model_min_topic_score = get_option( Kapow_Admin::KAPOW_MODEL_MIN_TOPIC_SCORE, 0.9 );
		$this->kapow_model_per_page = get_option( Kapow_Admin::KAPOW_MODEL_PER_PAGE, 5 );

	}

	/**
	 * Make a request to the KAPOW REST API.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string     $method   The HTTP request method e.g. GET, POST.
	 * @param    string     $route    The required KAPOW REST API entry point.
	 * @param    array      $data     The payload to send to the KAPOW REST API.
	 * @return   array      The KAPOW REST API response.
	 */
	private function request( $method, $route, $data ) {
		$resource = str_replace( '{route}', $route, $this->kapow_api_url );

		$args = array(
			'method'      => $method,
			'body'        => json_encode( $data ),
			'headers'     => array(
				'Authorization' => 'Bearer ' . $this->kapow_api_key,
				'Content-Type'  => 'application/json'
			),
			'timeout'     => 45,
			'redirection' => 5,
			'blocking'    => true,
			'httpversion' => '1.1',
		);

		$response = wp_remote_request( $resource, $args );

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Submit text to be analysed by the KAPOW image recommendation model via 
	 * the KAPOW REST API.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string      $text    The text to be analysed.
	 * @return   array       A list of image keywords inferred by the KAPOW model.
	 */
	private function fetch_tags( $text ) {

		$results = $this->request(
			'POST',
			'task',
			[
				'model' => 'TopicTagSim',
				'payload' => [
					'text' => $text,
					'threshold' => floatval( $this->kapow_model_threshold ),
					'min_topic_score' => floatval( $this->kapow_model_min_topic_score ),
					'default_to_topics' => false
				]
			]
		);

		if( $results )
			return array_map( function( $item ){ return $item->tag; }, $results->output );

		return [];
	}

	/**
	 * Request candidate images from the KAPOW REST API given a set of image keywords.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array      $keywords    The list of image keywords to get candidate images from.
	 * @return   array    A list of candidate image URLs and metadata.
	 */

	private function fetch_images( $keywords ) {

		$results = $this->request(
			'POST',
			'images',
			[
				'source'   => KAPOW_DEFAULT_IMAGES_SOURCE,
				'keywords' => $keywords,
				'options' => [
					'per_page' => $this->kapow_model_per_page
				]
			]
		);

		if($results)
			return $results;

		return [];
	}

	/**
	 * Build a transient key in an appropriate format for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string     $text  The unique part of the key to be appended to the default prefix.
	 * @return   string   A string that can be used as transient key for a kapow response.
	 */
	private function build_key( $text ) {

		return implode( '_', [ self::CACHE_KEY_PREFIX, md5( $text ) ] );
	
	}

	/**
	 * Build a key that can be used to retrieve cached kapow responses 
	 * using a post ID.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return   string    A string that can be used a key retrievable by a post ID.
	 */
	private function build_cache_key( $post_id ) {
	
		return implode( '_', [ self::CACHE_KEY_PREFIX, 'post', $post_id ] );
	
	}

	/**
	 * Send feedback payload to KAPOW via KAPOW REST API.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $text    The original text that KAPOW analysed for the current recommendation.
	 * @param    string    $url     The URL of the image that has been selected by the user for inclusion in their text.
	 * @param    int       $like    Default to 1. Whether the user liked or disliked the recommendation. A user including a recommended image into their text is counted as an automatic like.
	 */
	private function send_feedback( $text, $url, $like=1 ) {

		return $this->request( 'POST', 'images/feedback', [
			'model' => 'TopicTagSim',
			'text'  => $text,
			'url'   => $url,
			'like'  => $like
		] );
		
	}

	/**
	 * Retrieve an paginate a set of image recommendations from KAPOW.
	 * Results are cached so filtering and requests to further results pages
	 * is faster. Results can be filtered by keyword.
	 *
	 * @since    1.0.0
	 * @param    int     $post_id     The ID of the post to analyse.
	 * @param    string  $text        The textual content to analyse.
	 * @param    int     $page        The page to retrieve from the available pages of results.
	 * @param    int     $per_page    The number of images that constitutes a page.
	 * @param    string  $search      A search term to filter result images by.
	 * @return   array   A, possibly filtered, page of image objects amenable for display in the Media Library.
	 */
	public function analyse( $post_id, $text, $page, $per_page, $search=null ) {

		// check if we have a cached result from KAPOW
		$key = $this->build_key( $text );
		$photos = get_transient( $key );
		
		if ( false === $photos ) {

			// no luck, send request to KAPOW
			$photos = $this->kapow( $text );
			if ( count( $photos ) > 0 ) {

				// cache the response
				set_transient( $key, $photos, self::CACHE_EXPIRATION );
				// cache the key as well in a transient: this will help
				// to avoid doing searches for transient keys when image
				// cache-clearing is triggered
				$this->cache_key( $post_id, $key );

			}

		}
		// filter if we have search keyword
		if ( null !== $search ) {

			$filtered = [];

			foreach ( $photos as $photo ) {

				$haystack = implode( ' ', [
					strtolower( $photo[ 'title' ] ),
					strtolower( $photo[ 'alt' ] ),
					strtolower( $photo[ 'description' ] )
				] );

				if ( false !== strpos( $haystack, $search ) )
					$filtered[] = $photo;
			}

			return $filtered;

		}

		// otherwise paginate as per request
		$offset = $per_page * ( $page - 1 );

		return array_slice( $photos, $offset, $per_page );

	}

	/**
	 * Submit a text analysis job to KAPOW and retrieve candidate images
	 * using the KAPOW REST API to query the model and image retrieval strategy.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string     $text    The text to be analysed.
	 * @return   array      A set of image objects amenable for display in the Media Library.
	 */
	private function kapow( $text ) {

		$photos = [];
		// get the tags from KAPOW
		$tags = $this->fetch_tags( $text );

		if( $tags ) {

			// get images associated to each tag
			$images = $this->fetch_images( $tags );

			if( $images ) {

				// transform payload from KAPOW metadata
				// to Wordpress Media metadata
				foreach ( $images as $image ) {

					$photos[] = [
						"id"=> $image->id,
						"title"=> $image->description,
						"filename"=> $image->description,
						"url"=> $image->url,
						"link"=> $image->link,
						"alt"=> $image->alt,
						"author"=> get_current_user_id(),
						"description"=>  $image->description,
						"caption"=>  "Photo by <a href='{$image->author}?utm_source=kapow&utm_medium=referral' target='_blank' rel='noopener'>{$image->username}</a> on <a href='https://unsplash.com/?utm_source=kapow&utm_medium=referral' target='_blank' rel='noopener'>Unsplash</a>", // TODO: the attribution here will have to be dynamic and come from the source
						"name"=>  $image->description, 
						"status"=> "inherit",
						"uploadedTo"=> 0,
						"date"=> time(),
						"modified"=> time(),
						"menuOrder"=> 0,
						"mime"=> "image/png",
						"type"=> "image",
						"subtype"=> "png",
						"icon"=>  $image->thumb,
						"dateFormatted"=> date("D M Y"),
						"nonces"=> [
							"update"=> "",
							"delete"=> "",
							"edit"=> ""
						],
						"editLink"=> "",
						"meta"=> false,
						"authorName"=> "",
						"uploadedToLink"=> "",
						"uploadedToTitle"=> "",
						"filesizeInBytes"=> 0,
						"filesizeHumanReadable"=> "Unknown",
						"context"=> "",
						"height"=> $image->height,
						"width"=> $image->width,
						"orientation"=> "landscape",
						"sizes"=> [
							"thumbnail"=> [
								"height"=> ($image->height/$image->width) * 200,
								"width"=> 200,
								"url"=> $image->thumb,
									"orientation"=> "landscape"
							],
							"medium"=> [
								"height"=> ($image->height/$image->width) * 400,
								"width"=> 400,
								"url"=> $image->small,
								"orientation"=> "landscape"
							],
							"large"=> [
								"height"=> ($image->height/$image->width) * 1080,
								"width"=> 1080,
								"url"=> $image->regular,
								"orientation"=> "landscape"
							],
							"full"=> [
								"url"=> $image->url,
								"height"=> $image->height,
								"width"=> $image->width,
								"orientation"=> "landscape"
							]
						],
						"compat"=> [
							"item"=> "",
							"meta"=> ""
						],
						"acf_errors"=> false
					];
				}
			}
		}

		return $photos;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/kapow-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/kapow-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the ajax query-attachments request handler.
	 *
	 * @since    1.0.0
	 */
	public function ajax_query_attachments() {

		if ( isset( $_REQUEST[ 'query' ], $_REQUEST[ 'query' ][ 'post_mime_type' ] ) && $_REQUEST[ 'query' ][ 'post_mime_type' ] === 'kapow' ) {
			
			$post_id = absint( $_REQUEST[ 'post_id' ] );
			// retrieve the search keyword entered by the user if any
			$search = array_key_exists( 's', $_REQUEST[ 'query' ] ) ? sanitize_text_field( $_REQUEST[ 'query' ][ 's' ] ) : null;
			// retrieve the text to analyse
			$text = ( isset( $_REQUEST[ 'query' ][ 'text' ] ) ) ? sanitize_text_field( wp_strip_all_tags( $_REQUEST[ 'query' ][ 'text' ] ) ) : get_post( $post_id )->post_content;
			$page = absint( $_REQUEST[ 'query' ][ 'paged' ] );
			$per_page = absint( $_REQUEST[ 'query' ][ 'posts_per_page' ] );
			// send to KAPOW to get image recommendations back
			$response = $this->analyse( $post_id, $text, $page, $per_page, $search );

			return wp_send_json_success( $response );

		}

	}

	/**
	 * Register the ajax entry point for receiving user feedback, i.e. the user included 
	 * one of the recommended pictures in the text.
	 *
	 * @since    1.0.0
	 */
	public function ajax_kapow_register_feedback() {

		if ( isset( $_POST[ 'postdata' ], $_POST[ 'postdata' ][ 'postid' ], $_POST[ 'postdata' ][ 'text' ], $_POST[ 'postdata' ][ 'url' ] ) && current_user_can( 'edit_post', intval( $_POST[ 'postdata' ][ 'postid' ] ) ) ) {

			$text = wp_strip_all_tags( stripslashes( $_POST[ 'postdata' ][ 'text' ] ) );
			$this->send_feedback( sanitize_text_field( $text ), esc_url( $_POST[ 'postdata' ][ 'url' ] ) );
		
		}

	}

	/**
	 * Register the POST transition action to carry out whenever the user
	 * updates the state of their post/page.
	 *
	 * @since    1.0.0
	 * @param    string    $new_status    New post status.
	 * @param    string    $old_status    Old post status.
	 * @param    WP_Post   $post          Post object.
	 */
	public function transition_post_status( $new_status, $old_status, $post ) {

		// we go ahead and clear our cache of results from KAPOW
		// if the user has published or otherwise changed the state
		// of the article they are working on.
		$this->clear_cache_by_postid( $post->ID );		

	} 

	/**
	 * Add a kapow response transient key to the list of 
	 * keys to manage.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    int      $post_id   The ID of the post to use to retrieve a transient key for that post.
	 * @param    string   $key       The transient key to add to the list of keys to manage.
	 */
	private function cache_key( $post_id, $key ) {

		$keycache = get_transient( self::CACHE_KEYCACHE_KEY );
		// if a key cache does not yet exist...
		if ( false === $keycache )
			// ...create it
			$keycache = [];
		// cache the new key associated with the given post id
		$keycache[ $post_id ] = $key;
		set_transient( self::CACHE_KEYCACHE_KEY, $keycache, self::KEYCACHE_EXPIRATION );
	
	}

	/**
	 * Remove the kapow transient key associated to a given post from
	 * the list of keys to manage.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    int       $post_id   The ID of the post that references the key to remove.
	 * @return   string    The key, if any, that was removed from the managed list.
	 */

	private function uncache_key( $post_id ) {

		$keycache = get_transient( self::CACHE_KEYCACHE_KEY );
		$value = null;

		if ( $keycache && array_key_exists( $post_id, $keycache ) ) {

			$value = $keycache[ $post_id ];
			unset( $keycache[ $post_id ] );
			set_transient( self::CACHE_KEYCACHE_KEY, $keycache, self::KEYCACHE_EXPIRATION );
		
		}

		return $value;
	
	}

	/**
	 * Remove the list of managed kapow transient keys.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return   array     the list of kapow transient keys that have been removed.
	 */

	private function uncache_all_keys() {

		$keycache = get_transient( self::CACHE_KEYCACHE_KEY );
		$values = [];

		if ( $keycache ) {

			$values = array_values( $keycache );
			delete_transient( self::CACHE_KEYCACHE_KEY );
		
		}

		return $values;
	}

	/**
	 * Clear the KAPOW response cache whenever the admin updates
	 * the kapow settings from the admin dashboard or the plugin
	 * is deactivated.
	 *
	 * @since    1.0.0
	 */
	public function clear_kapow_cache() {

		// retrieve all relevant transient keys
		$keys = $this->uncache_all_keys();
		// remove from transient cache
		foreach ( $keys as $key ) {
			
			delete_transient( $key );

		}

	}

	/**
	 * Register the media buttons action.
	 *
	 * @since 	 1.0.0
	 */
	public function media_buttons() {

		include( 'partials/kapow-public-display.php' );
	
	}

}
