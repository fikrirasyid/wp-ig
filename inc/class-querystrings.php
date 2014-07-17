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
}