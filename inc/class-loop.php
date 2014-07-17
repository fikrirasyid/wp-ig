<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Integration WP-IG's feature to WordPress' loop of content
 */
class WP_IG_Loop{
	function __construct(){
		add_filter( 'post_class', array( $this, 'post_class') );
		add_filter( 'the_content', array( $this, 'prepend_media' ) );
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

	/**
	 * Optionally prepend Instagram media to content
	 * 
	 * @param string of content
	 * 
	 * @return string of modified content
	 */
	function prepend_media( $content ){
		global $post;

		$formatted_content = '';

		if( is_single() && get_option( 'wp_ig_prepend_on_single', 'yes' ) == 'yes' ){
		 	$formatted_content .= $this->get_prepend_media( $post );
		}

		if( !is_single() && get_option( 'wp_ig_prepend_on_index', 'yes' ) == 'yes' ){
			$formatted_content .= $this->get_prepend_media( $post );
		}

		$formatted_content .= $content;

		return $formatted_content;
	}

	/**
	 * Get instagram media to be prepended
	 * 
	 * @param obj post
	 * 
	 * @param string media
	 */
	function get_prepend_media( $post ){
		if( 'image' == get_post_format( $post->ID ) ){
			return $this->get_prepend_image( $post );
		}

		if( 'video' == get_post_format( $post->ID ) ){
			return $this->get_prepend_video( $post );
		}		
	}

	/**
	 * Get Instagram image to be prepended
	 * 
	 * @param obj post
	 * 
	 * @param string prepended image
	 */
	function get_prepend_image( $post ){
		$id 	= get_post_thumbnail_id( $post->ID );
		$url 	= wp_get_attachment_url( $id );
		return "<p><img src='$url' title='{$post->post_title}' style='width: 100%;' /></p>";		
	}

	/**
	 * Get Instagram video to be prepended
	 * 
	 * @param obj post
	 * 
	 * @param string prepended video
	 */
	function get_prepend_video( $post ){
		$url = get_post_meta( $post->ID, '_format_video_embed', true );

		if( $url ){
			return '<p>' . do_shortcode( "[video src='{$url}']" ) . '</p>';
		} else {
			return false;
		}
	}
}
new WP_IG_Loop;