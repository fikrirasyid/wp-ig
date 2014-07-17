<?php
class WP_IG_QueryStrings{
	/**
	 * Get max id
	 * 
	 * @author Fikri Rasyid
	 * 
	 * return bool|string
	 */
	function max_id(){
		if( isset( $_GET['max_id'] ) ){
			return $_GET['max_id'];
		} else {
			return false;
		}
	}

	/**
	 * Get query string value. Return as false if no query string is stated
	 * 
	 * @author Fikri Rasyid
	 * 
	 * return bool|string
	 */
	function get( $key ){
		if( isset( $_GET[$key] ) ){
			return $_GET[$key];
		} else {
			return false;
		}		
	}
}