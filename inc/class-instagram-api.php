<?php
/**
 * Wrapper for Instagram API
 */
class WP_IG_API{
	var $access_token;
	var $endpoint;

	function __construct( $access_token = 'ACCESS_TOKEN' ){
		$this->access_token = $access_token;
		$this->endpoint = "https://api.instagram.com/v1/";
	}

	/**
	 * Request to endpoint and parse its value
	 * 
	 * @param string endpoint
	 * 
	 * @return array
	 */
	function get( $param ){
		$result = wp_remote_get( $param, array(
			'timeout' => 60
		) );

		if( is_wp_error( $result ) ){
			return false;
		}

		if( isset( $result['body' ] ) ){
			return json_decode( $result['body'] );
		} else {
			return false;
		}
	}

	/**
	 * Get user info based on ID
	 * 
	 * @param int user id
	 * 
	 * @return obj
	 */
	function get_user_by_id( $user_id ){
		$endpoint = "{$this->endpoint}users/$user_id/?access_token=$this->access_token";

		return $this->get( $endpoint );
	}

	/**
	 * Get self feed based on ID
	 * 
	 * @return obj
	 */
	function get_self_feed( $max_id = false ){
		$endpoint = "{$this->endpoint}users/self/feed?access_token=$this->access_token";

		if( $max_id ){
			$endpoint .= "&max_id={$max_id}";
		}

		return $this->get( $endpoint );
	}
}