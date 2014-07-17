<?php
/**
 * Helper for extracting current page information
 * 
 * @package WP_IG/Classes
 * @since 0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WP_IG_Current_Page{
	/**
	 * Get query string value. Return as false if no query string is stated
	 * 
	 * @author Fikri Rasyid
	 * 
	 * return bool|string
	 */
	function query_string( $key ){
		if( isset( $_GET[$key] ) ){
			return $_GET[$key];
		} else {
			return false;
		}		
	}
}