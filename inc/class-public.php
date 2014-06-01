<?php
class WP_IG_Public{
	var $current_page;

	function __construct(){
		$this->current_page = new WP_IG_Current_Page;

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
		wp_enqueue_script( 'wp_ig', WP_IG_URL . 'js/wp-ig.js', array( 'jquery' ), '1.0' );
	}

	/**
	 * Adding public page for instagram content
	 * 
	 * @return void
	 */
	function public_page(){
		get_header();

		if( $this->current_page->query_string( 'tag_name' ) ){
			// Hashtag page		
			$method = "tag_media";
		} else {
			// Default (user page)
			$method = "user_media";
		}

		$args = $_REQUEST;
		unset( $_REQUEST['page'] );
		
		$template = new WP_IG_Templates;

		$template->display( $method, $args, false, array( 'title' => true ) );

		get_footer();

		die();
	}
}
new WP_IG_Public;