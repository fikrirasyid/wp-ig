<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WP_IG_Taxonomy{

	var $post_types;

	function __construct(){
		// Define post type
		$this->post_types = array( get_option( 'wp_ig_post_type', 'post' ) );		
	}

	/**
	 * Registering taxonomy 
	 * 
	 * @return void
	 */
	function register(){
		// Register taxonomy
		$labels = array(
			'name'                       => _x( 'Mentions', 'taxonomy general name', 'wp-ig' ),
			'singular_name'              => _x( 'Mention', 'taxonomy singular name', 'wp-ig' ),
			'search_items'               => __( 'Search Mentions', 'wp-ig' ),
			'popular_items'              => __( 'Popular Mentions', 'wp-ig' ),
			'all_items'                  => __( 'All Mentions', 'wp-ig' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Mention', 'wp-ig' ),
			'update_item'                => __( 'Update Mention', 'wp-ig' ),
			'add_new_item'               => __( 'Add New Mention', 'wp-ig' ),
			'new_item_name'              => __( 'New Mention Name', 'wp-ig' ),
			'separate_items_with_commas' => __( 'Separate mentions with commas', 'wp-ig' ),
			'add_or_remove_items'        => __( 'Add or remove mentions', 'wp-ig' ),
			'choose_from_most_used'      => __( 'Choose from the most used mentions', 'wp-ig' ),
			'not_found'                  => __( 'No mentions found.', 'wp-ig' ),
			'menu_name'                  => __( 'Mentions', 'wp-ig' ),
		);

		register_taxonomy( 'mention', apply_filters( 'wp_ig_post_type_taxonomy', $this->post_types ), array(
			'labels' 		=> $labels,
			'hierarchical' 	=> false,
		) );
	}
}