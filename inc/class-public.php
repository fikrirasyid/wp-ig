<?php
class WP_IG_Public{

	function __construct(){
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script_styles' ) );		

		// Adding public page for instagram content
		add_action( 'wp_ajax_instagram', array( $this, 'public_page' ) );
		add_action( 'wp_ajax_nopriv_instagram', array( $this, 'public_page' ) );
	}

	/**
	 * Enqueue front end styles & scripts
	 * 
	 * @return void
	 */	
	function enqueue_script_styles(){
		wp_enqueue_style( 'wp_ig', WP_IG_URL . 'css/wp-ig.css' );
	}

	/**
	 * Adding public page for instagram content
	 * 
	 * @return void
	 */
	function public_page(){
		get_header();

		$shortcode = new WP_IG_Shortcodes;

		echo $shortcode->user_media( $_REQUEST );

		get_footer();

		die();
	}
}
new WP_IG_Public;