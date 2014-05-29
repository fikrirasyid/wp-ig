<?php
/**
 * Wrapper for Instagram API
 */
class WP_IG_API{
	var $access_token;
	var $endpoint;
	var $prefix;

	function __construct( $access_token = 'ACCESS_TOKEN' ){
		$this->access_token = $access_token;
		$this->endpoint = "https://api.instagram.com/v1/";
		$this->prefix = "wp_ig_";
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
	function user_by_id( $user_id ){
		$endpoint = "{$this->endpoint}users/$user_id/?access_token=$this->access_token";

		return $this->get( $endpoint );
	}

	/**
	 * Get self feed based on ID
	 * 
	 * @return obj
	 */
	function self_feed( $args ){

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
	function user_media( $args ){

		// Setup default values
		$defaults = array(
			'username'		=> false,
			'user_id' 		=> false,
			'count' 		=> false,
			'max_timestamp' => false,
			'min_timestamp' => false,
			'min_id'		=> false,
			'max_id'		=> false
		);

		// parse arguments
		$args = wp_parse_args( $args, $defaults );

		// If the method is used in user level, there's a chance that s/he'll use username instead of user id. This will handle it gently
		if( !$args['user_id'] && $args['username'] ){
			$user = $this->user_search( array(
				'q' => $args['username']
			) );

			// If we cannot fetch user id, stop the process
			if( !isset( $user->data[0]->id ) ){
				return __( "{$args['username']} information cannot be fetched", "wp_ig" );
			} else {
				$args['user_id'] = $user->data[0]->id;
				unset( $args['username'] );
			}			
		}

		// If no user_id nor username given, use signed in user's ID
		if( !$args['user_id'] && !$args['username'] ){

			$account 			= get_option( "{$this->prefix}account" );
			
			$args['user_id'] 	= $account->id;
		}

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

	/**
	 * Search user based on username
	 * 
	 * @param array of arguments
	 * 
	 * @return obj
	 */
	function user_search( $args ){

		// Setup default values
		$defaults = array(
			'q' 		=> false,
			'count' 	=> false
		);

		// parse arguments
		$args = wp_parse_args( $args, $defaults );

		// Define endpoint
		$endpoint = "https://api.instagram.com/v1/users/search?access_token={$this->access_token}";

		// Pushes more parameters
		foreach ($args as $key => $param) {
			if( $param ){
				$endpoint .= "&{$key}={$param}";
			}
		}

		return $this->get( $endpoint );
	}

	// TAGS ---------------------------------

	/**
	 * Get tags' media
	 * 
	 * @param of arguments
	 * 
	 * @return obj
	 */
	function tag_media( $args ){

		// Setup default values
		$defaults = array(
			'tag_name' 		=> 0,
			'min_id'		=> false,
			'max_id'		=> false
		);

		// parse arguments
		$args = wp_parse_args( $args, $defaults );

		// Define endpoint
		$endpoint = "https://api.instagram.com/v1/tags/{$args['tag_name']}/media/recent?access_token={$this->access_token}";

		// Pushes more parameters
		foreach ($args as $key => $param) {
			if( $param ){
				$endpoint .= "&{$key}={$param}";
			}
		}

		return $this->get( $endpoint );
	}
}