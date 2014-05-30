<?php
class WP_IG_Shortcodes{
	var $prefix;

	function __construct(){
		$this->prefix = 'wp_ig_';

		add_shortcode( 'instagram_user_media', array( $this, 'user_media' ) );
	}

	/**
	 * Call the API
	 */
	function api(){
		return new WP_IG_API( get_option( "{$this->prefix}access_token" ) );
	}

	/**
	 * Call the template
	 */
	function templates(){
		return new WP_IG_Templates();
	}

	/**
	 * Display instagram feed based on username
	 */
	function user_media( $atts ){

		$args = shortcode_atts( array(
			'username' 		=> false,
			'user_id' 		=> false,
			'count' 		=> false,
			'max_timestamp' => false,
			'min_timestamp' => false,
			'min_id'		=> false,
			'max_id'		=> false,
			'cache'			=> 60, // cache the request for a minute
			'ignore_cache'	=> false // ignore the cache and get the data from the API instead 
		), $atts );

		extract( $args );

		// Request for the feed, return as string
		ob_start();

		$this->templates()->display( 'user_media', $args, false, array( 'title' => true ) );

		return ob_get_clean();
	}
}
new WP_IG_Shortcodes;