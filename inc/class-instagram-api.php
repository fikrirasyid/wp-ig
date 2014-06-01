<?php
/**
 * Wrapper for Instagram API
 */
class WP_IG_API{
	var $access_token;
	var $endpoint;
	var $prefix;
	var $plugin_defaults;

	function __construct( $access_token = 'ACCESS_TOKEN' ){
		$this->access_token = $access_token;
		$this->endpoint 	= "https://api.instagram.com/v1/";
		$this->prefix 		= "wp_ig_";
		$this->plugin_defaults 	= $this->plugin_defaults();
	}

	/**
	 * Define arguments that will be merged to method's arguments
	 * 
	 * @return array of arguments
	 */
	function plugin_defaults(){
		$plugin_defaults = array(
			'cache'			=> 60, // cache the request for a minute. A minute is enough for the sake of real timeness
			'ignore_cache'	=> false // ignore the cache and get the data from the API instead 
		);

		return $plugin_defaults;
	}

	// GLOBAL --------------------------------

	/**
	 * Request to endpoint and parse its value
	 * 
	 * @param string endpoint
	 * @param array of args - this should have been parsed on the method
	 * 
	 * @return array
	 */
	function get( $endpoint, $args = array() ){

		// Make sure that the args has transient and ignore_cache element
		$args = wp_parse_args( $args, $this->plugin_defaults );

		// Extract the args because it'll be easier to access variable directly
		extract( $args );

		// Pushes more parameters to the endpoint
		foreach ($args as $key => $param) {
			if( $param && !array_key_exists( $key, $this->plugin_defaults ) ){
				$endpoint .= "&{$key}={$param}";
			}
		}		

		// Get the transient key
		$transient_key = $this->prefix . md5( $endpoint );

		$transient = get_transient( $transient_key );

		// If transient exists and user expecting to use the cache
		if( $transient && !$ignore_cache ){
			return json_decode( $transient['body'] );
		} else {
			// Get the data from Instagram endpoint
			$result = wp_remote_get( $endpoint, array(
				'timeout' => 60
			) );

			if( is_wp_error( $result ) ){
				return false;
			}

			if( isset( $result['body' ] ) ){
				// If the data is successfully parsed, save the result as transient
				set_transient( $transient_key, $result, $cache );

				return json_decode( $result['body'] );
			} else {
				return false;
			}
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
	function user_by_id( $args ){

		// Setup default values
		$defaults = array(
			'user_id' => false
		);

		// parse arguments
		$args = wp_parse_args( $args, $defaults );

		// User ID cannot be empty
		if( !$args['user_id'] ){
			return new WP_Error( 400, __( "User ID cannot be empty", "wp_ig" ) );
		}

		$endpoint = "{$this->endpoint}users/{$args['user_id']}/?access_token=$this->access_token";

		return $this->get( $endpoint, $args );
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

		return $this->get( $endpoint, $args );
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

		// Merge the defaults
		$defaults = array_merge( $defaults, $this->plugin_defaults );

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

		return $this->get( $endpoint, $args );
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

		return $this->get( $endpoint, $args );
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

		return $this->get( $endpoint, $args );
	}
}