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

	// GLOBAL --------------------------------

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

	// USERS ---------------------------------

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
	function get_self_feed( $args ){

		// Setup default values
		$defaults = array(
			'count' 		=> false,	
			'min_id'		=> false,
			'max_id'		=> false		
		);

		// parse arguments
		$args = wp_parse_args( $args, $defaults );

		// Define endpoint
		$endpoint = "{$this->endpoint}users/self/feed?access_token=$this->access_token";

		// Pushes more parameters
		foreach ($args as $key => $param) {
			if( $param ){
				$endpoint .= "&{$key}={$param}";
			}
		}

		return $this->get( $endpoint );
	}

	/**
	 * Get user's recent media
	 * 
	 * @param array of arguments
	 * 
	 * @return obj
	 */
	function get_user_media( $args ){

		// Setup default values
		$defaults = array(
			'user_id' 		=> 0,
			'count' 		=> false,
			'max_timestamp' => false,
			'min_timestamp' => false,
			'min_id'		=> false,
			'max_id'		=> false
		);

		// parse arguments
		$args = wp_parse_args( $args, $defaults );

		// Define endpoint
		$endpoint = "https://api.instagram.com/v1/users/{$args['user_id']}/media/recent/?access_token={$this->access_token}";

		// Pushes more parameters
		foreach ($args as $key => $param) {
			if( $param ){
				$endpoint .= "&{$key}={$param}";
			}
		}

		return $this->get( $endpoint );
	}
}