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
			'username' 		=> 'fikrirasyid',
			'user_id' 		=> false,
			'count' 		=> false,
			'max_timestamp' => false,
			'min_timestamp' => false,
			'min_id'		=> false,
			'max_id'		=> false			
		), $atts );

		extract( $args );

		if( !$user_id ){
			// If user_id isn't defined, request for user id data first
			$user = $this->api()->get_user_search( array(
				'q' => $username
			) );

			// If we cannot fetch user id, stop the process
			if( !isset( $user->data[0]->id ) ){
				return __( "$username information cannot be fetched", "wp_ig" );
			} else {
				$args['user_id'] = $user->data[0]->id;
			}
		}

		// unset the $username from $args
		unset( $args['username'] );

		// Request for the feed, return as string
		ob_start();

		if( $username ){
			echo "<h2 class='wp-ig instagram-items-title'>";
			printf( __( "%s's Instagram Feed", "wp_ig" ), $username ); 			
			echo "</h2>";
		}

		$this->templates()->display( 'get_user_media', $args );

		return ob_get_clean();
	}
}
new WP_IG_Shortcodes;