<?php
/*
Plugin Name: WP-IG
Plugin URI: http://fikrirasyid.com/kicau
Description: Integrating your Instagram account to your WordPress site.
Version: 0.1
Author: Fikri Rasyid
Author URI: http://fikrirasyid.com/
*/

// Constants
if (!defined('WP_IG_DIR'))
    define('WP_IG_DIR', plugin_dir_path( __FILE__ ));


if (!defined('WP_IG_URL'))
    define('WP_IG_URL', plugin_dir_url( __FILE__ ));	


// Requiring files
require_once( 'inc/class-settings.php' );
require_once( 'inc/class-instagram-api.php' );
require_once( 'inc/class-content.php' );
require_once( 'inc/class-current-page.php' );
require_once( 'inc/class-templates.php' );
require_once( 'inc/class-dashboard.php' );
require_once( 'inc/class-shortcodes.php' );
require_once( 'inc/class-public.php' );

/** 
 * Universal template tags
 * Basically do what sortcode does without output buffering
 * 
 * @param array of attributes
 * 
 * @return void
 */
function wp_ig( $args = array() ){

	$defaults = array(
		'username' 		=> false,
		'user_id' 		=> false,
		'tag_name'		=> false,
		'self'			=> false,
		'liked'			=> false,
		'popular'		=> false,
		'count' 		=> false,
		'max_timestamp' => false,
		'min_timestamp' => false,
		'min_id'		=> false,
		'max_id'		=> false,
		'cache'			=> 60, // cache the request for a minute
		'ignore_cache'	=> false // ignore the cache and get the data from the API instead 
	);

	// Parse arguments
	$args = wp_parse_args( $args, $defaults );

	// define source url
	$wpspin_url = home_url( '/wp-includes/images/wpspin-2x.gif' );
	$wp_ig_source_url = home_url( '/wp-admin/admin-ajax.php?action=instagram' );

	foreach ( $args as $key => $arg ) {

		if( $arg )
			$wp_ig_source_url .= "&{$key}={$arg}";

	}

	// Print the wrapper. Javascript will fetch the data from here
	echo "<div class='wp-ig-wrap' data-source='{$wp_ig_source_url}'>";

	echo "<p class='wp-ig-wrap-loading'><img src='{$wpspin_url}' width='16' height='16' class='loading' /><br /> ". __( 'Loading Instagram Contents...', 'wp-ig' ) ."</p>";

	echo "</div>";		
}