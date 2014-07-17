<?php
/**
 * All things related to wp-ig generated post
 */
class WP_IG_Content{
	/**
	 * Get post with 
	 * Customize WordPress' API instead of writing custom mysql for this task. More futureproof
	 * 
	 * @param array which is used by get_posts()
	 */
	function get_posts( $args = array() ){
		$args['meta_key'] = '_instagram_id';

		$posts = get_posts( $args );

		return $this->_prepare_posts( $posts );
	}

	/**
	 * Prepare post with wp-ig related post meta
	 * 
	 * @param get_posts() output
	 * 
	 * @return get_posts() output appended with related meta
	 */
	function _prepare_posts( $posts ){
		if( ! empty( $posts ) ){
			foreach ( $posts as $key => $post ) {
				$posts[$key]->post_source 			= get_post_meta( $post->ID, '_post_source', true );
				$posts[$key]->instagram_id 		= get_post_meta( $post->ID, '_instagram_id', true );
				$posts[$key]->instagram_filter 	= get_post_meta( $post->ID, '_instagram_filter', true );
				$posts[$key]->instagram_user_id 	= get_post_meta( $post->ID, '_instagram_user_id', true );
				$posts[$key]->instagram_data 		= get_post_meta( $post->ID, '_instagram_data', true );
			}
		}

		return $posts;
	}
}