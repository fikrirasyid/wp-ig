<?php
class WP_IG_Public{

	function __construct(){
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script_styles' ) );		
	}

	/**
	 * Enqueue front end styles & scripts
	 * 
	 * @return void
	 */	
	function enqueue_script_styles(){
		wp_enqueue_style( 'wp_ig', WP_IG_URL . 'css/wp-ig.css' );
	}
}
new WP_IG_Public;