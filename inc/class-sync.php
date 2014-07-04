<?php
class WP_IG_Sync{

	var $prefix;

	function __construct( $activation = false ){
		
		$this->prefix = 'wp_ig_';

		// Register new cron interval
		add_filter( 'cron_schedules', array( $this, 'cron_five_minutes' ) );		
				
		// Adding WP-Cron task
		add_action( 'wp_ig_sync', array( $this, 'sync' ) );
	}

	/**
	 * Register new cron intervals
	 * 
	 * @param array of existing schedule (try var_dump()-ing wp_get_schedules() )
	 * 
	 * @return array of modified schedule
	 */
	function cron_five_minutes( $schedules ){
		$schedules['every5minutes'] = array(
			'interval' => 300,
			'display' => __( 'Every 5 minutes', 'wp-ig' )
		);

		return $schedules;
	}

	/**
	 * Initiate API class
	 * 
	 * @return obj of Instagram API class
	 */
	function api(){
		$api = new WP_IG_API( get_option( "{$this->prefix}access_token" ) );

		return $api;
	}

	/**
	 * Initiate Import class
	 * 
	 * @return array of import class
	 */
	function import(){
		$import = new WP_IG_Import;

		return $import;
	}

	/**
	 * Get sync preference from saved option
	 * 
	 * @return bool
	 */
	function do_sync(){
		$preference = get_option( 'wp_ig_sync', 'no' );

		if( 'yes' == $preference ){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Sync the account's media to this site
	 * 
	 * @return void
	 */
	function sync( $media_params = array() ){

		// Don't sync if user prefer not to sync
		if( $this->do_sync() == false ){
			return;			
		}

		// Load wp-load to prevent fatal error
		require_once( ABSPATH . "wp-admin/includes/file.php" );
		require_once( ABSPATH . "wp-admin/includes/media.php" );
		require_once( ABSPATH . "wp-admin/includes/image.php" );
		require_once( ABSPATH . "wp-admin/includes/post.php" );

		// If min_timestamp hasn't set yet, ask for it
		// If there's already min_stamp, this method is used recursively. Don't ask for min_timestamp
		if( ! isset( $media_params['min_timestamp'] ) ){
			// Get latest timestamp
			$min_timestamp = $this->get_latest_timestamp();

			// Set min_timestamp
			if( is_wp_error( $min_timestamp ) ){
				$media_params['min_timestamp'] = get_option( "{$this->prefix}install_time" );
			} else {
				$media_params['min_timestamp'] = $min_timestamp;			
			}
		}

		// $media = $this->api()->user_media( $media_params );
		$syncing = $this->import()->import_items( $media_params );

		// If there's more media to sync, sync it
		if( isset( $syncing['pagination']->next_max_id ) ){
			$next_params = array(
				'min_timestamp' => $media_params['min_timestamp'],
				'max_id' => $syncing['pagination']->next_max_id
			);

			$this->sync( $next_params );
		}
	}	

	/**
	 * Get latest imported instagram ID
	 * Use min_timestamp instead of min_id due to min_id's behavior which display the last media with given id
	 * 
	 * @return mixed int of timestamp|obj of WP_Error
	 */
	function get_latest_timestamp(){

		return strtotime( 'Nov 13, 2013' );
		global $wpdb;

		$query = $wpdb->get_row( "SELECT posts.post_date AS id 
			FROM {$wpdb->prefix}postmeta AS postmeta,
				{$wpdb->prefix}posts AS posts			
			WHERE  posts.ID = postmeta.post_id
				AND postmeta.meta_key = '_instagram_id'
			ORDER BY posts.post_date DESC" );

		if( isset( $query->id) ){			
			return strtotime( $query->id ) + 1; // Adding one second after because we don't need last imported media to be fetched
		} else {
			return new WP_Error( 404, __( 'No ID found', 'wp-ig' ) );
		}

	}
}
new WP_IG_Sync;