<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Registering and processing endpoints
 */
class WP_IG_Endpoints{
	var $prefix;

	function __construct(){
		$this->prefix = "wp_ig_";

		// Adding import & repost endpoint
		add_action( 'wp_ajax_wp_ig_import_item', array( $this, 'endpoint_import' ) );
		add_action( 'wp_ajax_wp_ig_repost_item', array( $this, 'endpoint_repost' ) );		
	}

	/**
	 * Detecting whether current request is ajax or not
	 * 
	 * @return bool
	 */
	function is_ajax(){
		if( isset( $_REQUEST['is_ajax'] ) ){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Initiating api class as method
	 * 
	 */
	function api(){
		$api = new WP_IG_API( get_option( "{$this->prefix}access_token" ) );

		return $api;
	}

	/**
	 * Initiating import class as method
	 * 
	 * @return obj WP_IG_Import
	 */
	function import(){
		return new WP_IG_Import;
	}

	/**
	 * Get media data
	 * 
	 * @return obj
	 */
	function media( $intent = '' ){
		// Prepare variables
		$defaults = array(
			'id' 	=> false,
			'_n'	=> false
		);

		$args = wp_parse_args( $_REQUEST, $defaults );

		extract( $args );

		// Verify intent to get media
		if( ! wp_verify_nonce( $_n, "{$intent}_{$id}" ) ){
			return new WP_Error( 401, sprintf( __( 'You are not authorized to %s this media', 'wp-ig' ), $intent ) );
		}

		// Get media information
		$media = $this->api()->media( array( 
			'media_id' => $id ) 
		);

		if( isset( $media->meta->code ) && 200 == $media->meta->code){
			return $media->data;
		} else {
			return new WP_Error( 503, __( 'Cannot reach Instagram', 'wp-ig' ) );
		}
	}

	/**
	 * Import item endpoint
	 * 
	 * @return void
	 */
	function endpoint_import(){
			
		// Get media data 
		$media = $this->media( 'import' );

		// Check if media data is fetched or not
		if( is_wp_error( $media ) ){
			$output = $media;
		} else {

			// Prevent duplication
			$prevent_duplication = $this->import()->prevent_duplication( $media->id );

			if( $prevent_duplication === true ){
				// Import media
				$import = $this->import()->import_item( $media );

				// Check if media is successfully imported or not
				if( is_wp_error( $import ) ){

					$output = new WP_Error( 400, __( 'Error importing media', 'wp-ig' ) );

				} else {

					$output = array(
						'id' 		=> $import,
						'permalink' => get_permalink( $import )
					);

				}
			} else {
				$output = array(
					'id' 		=> $prevent_duplication,
					'permalink' => get_permalink( $prevent_duplication )
				);
			}
		}

		// Determine type of response
		if( $this->is_ajax() ){
			
			echo json_encode( $output );

		} else {

			// Redirect based on process status
			if( isset( $output['permalink'] ) ){
			
				wp_redirect( $output['permalink'] );
			
			} else {

				_e( 'Error importing media. Please try again later', 'wp-ig' );

			}

		}

		die();
	}

	/**
	 * Repost item endpoint
	 * 
	 * @return void
	 */
	function endpoint_repost(){

		$defaults = array(
			'id' 	=> false,
			'_n'	=> false
		);

		$args = wp_parse_args( $_REQUEST, $defaults );

		die();
	}
}
new WP_IG_Endpoints;