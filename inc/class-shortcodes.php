<?php
class WP_IG_Shortcodes{
	var $prefix;
	var $wp_ig_source_url;
	var $wpspin_url;

	function __construct(){
		$this->prefix = 'wp_ig_';
		$this->wp_ig_source_url = home_url( '/wp-admin/admin-ajax.php?action=instagram' );
		$this->wpspin_url 	= home_url( '/wp-includes/images/wpspin-2x.gif' );

		add_shortcode( 'instagram_user_media', array( $this, 'user_media' ) );
	}

	/**
	 * Display instagram feed based on username
	 */
	function user_media( $atts ){

		$args = shortcode_atts( array(
			'username' 		=> false,
			'user_id' 		=> false,
			'count' 		=> false,
			'max_timestamp' => false,
			'min_timestamp' => false,
			'min_id'		=> false,
			'max_id'		=> false,
			'cache'			=> 60, // cache the request for a minute
			'ignore_cache'	=> false // ignore the cache and get the data from the API instead 
		), $atts );

		// define source url
		$wp_ig_source_url = $this->wp_ig_source_url;
		foreach ( $args as $key => $arg ) {
			$wp_ig_source_url .= "&{$key}={$arg}";
		}

		// Request for the feed, return as string
		ob_start();

		echo "<div class='wp-ig-wrap' data-source='{$wp_ig_source_url}'>";

		echo "<p class='wp-ig-wrap-loading'><img src='{$this->wpspin_url}' width='16' height='16' class='loading' /><br /> ". __( 'Loading Instagram Contents...', 'wp_ig' ) ."</p>";

		echo "</div>";

		return ob_get_clean();
	}
}
new WP_IG_Shortcodes;