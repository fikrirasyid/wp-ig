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
require_once( 'inc/class-instagram-api.php' );
require_once( 'inc/class-current-page.php' );
require_once( 'inc/class-dashboard.php' );