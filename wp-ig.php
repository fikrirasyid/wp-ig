<?php
/**
 * Plugin Name: WP-IG
 * Plugin URI: http://fikrirasyid.com/kicau
 * Description: Integrating Instagram account to WordPress site. WP-IG enables you to: 1) display tag / user feed using shortcode, 2) importing all of your Instagram photo/video, 3) syncing your future Instagram photo/video to your site, 4) cherry-pick your Instagram photo/video to be posted, 5) Embed other people's Instagram photo/video easily.
 * Version: 0.1
 * Author: Fikri Rasyid
 * Author URI: http://fikrirasyid.com/
 * License: GPLv2 or later
 * 
 * @package WP_IG
 * @author Fikri Rasyid
*/

// Constants
if (!defined('WP_IG_DIR'))
    define('WP_IG_DIR', plugin_dir_path( __FILE__ ));


if (!defined('WP_IG_URL'))
    define('WP_IG_URL', plugin_dir_url( __FILE__ ));	

/**
 * Main WP_IG class
 * 
 * @version 0.1.0
 */
class WP_IG{

	var $prefix;

	function __construct(){
		$this->prefix = 'wp_ig_';

		$this->requiring_files();

		register_activation_hook( __FILE__, array( $this, 'activation' ) );

		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
	}

	/**
	 * Activation task. Do this when the plugin is activated
	 * 
	 * @return void
	 */
	function activation(){
		if( !wp_next_scheduled( 'wp_ig_sync' ) ){
			wp_schedule_event( current_time( 'timestamp', wp_timezone_override_offset() ), 'every5minutes', 'wp_ig_sync' );
		}

		// Install time, for first time user
		update_option( "{$this->prefix}install_time", current_time( 'timestamp', wp_timezone_override_offset() ) );

		// flush rewrite rules, making sure that mention taxonomy can be accessed by public
		$taxonomy = new WP_IG_Taxonomy;
		
		$taxonomy->register();

		flush_rewrite_rules( false );
	}

	/**
	 * Deactivation task. Do this when the plugin is deactivated
	 * 
	 * @return void
	 */
	function deactivation(){
		wp_clear_scheduled_hook( 'wp_ig_sync' );
	}

	/**
	 * Requiring other files
	 * 
	 * @return void
	 */
	function requiring_files(){
		require_once( 'inc/class-settings.php' );
		require_once( 'inc/class-instagram-api.php' );			
		require_once( 'inc/class-taxonomy.php' );			
		require_once( 'inc/class-import.php' );
		require_once( 'inc/class-sync.php' );
		require_once( 'inc/class-content.php' );
		require_once( 'inc/class-current-page.php' );
		require_once( 'inc/class-templates.php' );
		require_once( 'inc/class-dashboard.php' );
		require_once( 'inc/class-endpoints.php' );
		require_once( 'inc/class-shortcodes.php' );
		require_once( 'inc/class-public.php' );
		require_once( 'inc/class-loop.php' );
		require_once( 'inc/template-tags.php' );
	}
}
new WP_IG;