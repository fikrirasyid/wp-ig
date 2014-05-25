<?php
class WP_IG_QueryStrings{
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