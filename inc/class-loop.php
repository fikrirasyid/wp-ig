<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Integration WP-IG's feature to WordPress' loop of content
 */
class WP_IG_Loop{
	function __construct(){
		add_filter( 'post_class', array( $this, 'post_class') );
	}

	/**
	 * Add class to loop post
	 * 
	 * @param array of classes
	 * 
	 * @return array of un|modified classes
	 */
	function post_class( $classes ){
		global $post;

		$instagram_id = get_post_meta( $post->ID, '_instagram_id', true );

		if( $instagram_id ){
			$classes[] = 'instagram';
			$classes[] = 'wp-ig';
		}

		return $classes;
	}
}
new WP_IG_Loop;