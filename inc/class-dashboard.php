<?php
/**
 * Registering all dashboard pages needed
 */
class WP_IG_Dashboard{
	var $redirect_uri;
	var $prefix;
	var $client_id;
	var $client_secret;

	function __construct(){
		$this->prefix = 'wp_ig_';
		$this->redirect_uri = admin_url() . 'admin-ajax.php?action=wp_ig_redirect_uri';
		$this->client_id = get_option( "{$this->prefix}client_id" );
		$this->client_secret = get_option( "{$this->prefix}client_secret" );

		// If user is currently on wp_ig pages
		if( isset( $_GET['page'] ) && substr( $_GET['page'], 0, 5 ) == "wp_ig" ){
			// Saving form submission
			add_action( 'plugins_loaded', array( $this, 'saving' ) );
		}

		// Register dashboard menu
		add_action( 'admin_menu', array( $this, 'register_pages' ) );
	}
	
	/**
	 * Registering dashboard pages
	 * 
	 * @return void
	 */
	function register_pages(){
		add_menu_page( __( 'Instagram', 'wp_ig' ), __( 'Instagram', 'wp_ig' ), 'edit_others_posts', 'wp_ig', array( $this, 'page_main' ), 'dashicons-camera', 7 );

		// If the client ID and client secret has been saved, display edit update settings
		if( $this->client_id && $this->client_secret && $this->client_id != '' && $this->client_secret != '' ){
			add_submenu_page( 'wp_ig', __( 'Setup', 'wp_ig' ), __( 'Setup', 'wp_ig' ), 'edit_others_posts', 'wp_ig_setup', array( $this, 'page_setup') );
		}
	}

	/**
	 * Saving form submission
	 * 
	 * @return void
	 */
	function saving(){
		// Updating value..
		if( isset( $_POST['client_id'] ) && isset( $_POST['client_secret'] ) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'wp_ig_setup' ) ){
			$saving_client_id = update_option( "wp_ig_client_id", sanitize_text_field( $_POST['client_id'] ) );
			$saving_client_secret = update_option( "wp_ig_client_secret", sanitize_text_field( $_POST['client_secret'] ) );

			if( $saving_client_id ){
				$this->client_id = sanitize_text_field( $_POST['client_id'] );
			}

			if( $saving_client_secret ){
				$this->client_secret = sanitize_text_field( $_POST['client_secret'] );
			}
		}

		// If client ID & secret doesn't exist
		if( isset( $_GET['page'] ) && $_GET['page'] != 'wp_ig' && ( !$this->client_id || !$this->client_secret || $this->client_id == '' || $this->client_secret == '' ) ){
			header( "Location: " . admin_url() . 'admin.php?page=wp_ig' );
			exit();
		}		
	}

	/**
	 * Print main / setup page of WP-IG
	 * 
	 * @return void
	 */
	function page_main(){
		?>
			<?php
				if( !$this->client_id || !$this->client_secret || $this->client_id == '' || $this->client_secret == '' ){
					// First time user: create client instruction + client ID + client secret
					include_once( WP_IG_DIR . '/pages/setup.php' );					
				} else {
					// Client ID & secret found: connect your account

					// Connected: preview your account					
				}
			?>
		<?php
	}

	/**
	 * Print setup page
	 * 
	 * @return void
	 */
	function page_setup(){
		include_once( WP_IG_DIR . '/pages/setup.php' );
	}
}
new WP_IG_Dashboard;