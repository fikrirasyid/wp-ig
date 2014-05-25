<?php
/**
 * Registering all dashboard pages needed
 */
class WP_IG_Dashboard{
	var $redirect_uri;
	var $prefix;
	var $client_id;
	var $client_secret;
	var $authorization_url;
	var $exchange_code;
	var $qs;

	function __construct(){
		$this->prefix 				= 'wp_ig_';
		$this->client_id 			= get_option( "{$this->prefix}client_id" );
		$this->client_secret 		= get_option( "{$this->prefix}client_secret" );
		$this->redirect_uri 		= admin_url() . 'admin-ajax.php?action=wp_ig_redirect_uri';
		$this->redirect_uri_encoded = urlencode( $this->redirect_uri );
		$this->authorization_url 	= "https://api.instagram.com/oauth/authorize/?client_id={$this->client_id}&redirect_uri={$this->redirect_uri_encoded}&response_type=code";		
		$this->exchange_code 		= get_option( "{$this->prefix}exchange_code" );
		$this->qs 					= new WP_IG_QueryStrings;

		// If user is currently on wp_ig pages
		if( isset( $_GET['page'] ) && substr( $_GET['page'], 0, 5 ) == "wp_ig" ){
			// Saving form submission
			add_action( 'plugins_loaded', array( $this, 'saving' ) );
		}

		// Register dashboard menu
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );
		add_action( 'admin_menu', array( $this, 'register_pages' ) );

		// Processing auth-redirect data
		add_action( 'wp_ajax_wp_ig_redirect_uri', array( $this, 'page_redirect_callback' ) );
		add_action( 'wp_ajax_wp_ig_deauth_account', array( $this, 'page_deauth' ) );
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
	 * Initiating api class as method
	 * 
	 */
	function api(){
		$api = new WP_IG_API( get_option( "{$this->prefix}access_token" ) );

		return $api;
	}

	/**
	 * Saving form submission
	 * 
	 * @return void
	 */
	function saving(){
		// Updating value..
		if( isset( $_POST['client_id'] ) && isset( $_POST['client_secret'] ) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'wp_ig_setup' ) ){
			$saving_client_id = update_option( "{$this->prefix}client_id", sanitize_text_field( $_POST['client_id'] ) );
			$saving_client_secret = update_option( "{$this->prefix}client_secret", sanitize_text_field( $_POST['client_secret'] ) );

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
	 * Enqueue scripts & stylesheets
	 * 
	 * @return void
	 */
	function enqueue_scripts_styles( $hook ){
		if( $hook == 'toplevel_page_wp_ig' ){
			wp_enqueue_style( 'wp_ig_dashboard', WP_IG_URL . 'css/dashboard.css', false, '1.0.0' );
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
					// Display profile
					include_once( WP_IG_DIR . '/pages/profile.php' );					

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

	/**
	 * Handling redirection of Instagram authentication process
	 * 
	 * @return void
	 */
	function page_redirect_callback(){
		if( isset( $_GET['code'] ) ){
			// Save the exchange code
			$save_exchange_code = update_option("{$this->prefix}exchange_code", esc_html( $_GET['code'] ) );

			// Request access token
			$args = array(
				'timeout' => 60,
				'body' => array(
					'client_id' => $this->client_id,
					'client_secret' => $this->client_secret,
					'grant_type' => 'authorization_code',
					'redirect_uri' => $this->redirect_uri,
					'code' => $this->exchange_code
				)
			);

			// Request access token to Instagram
			$request_access_token = wp_remote_post( "https://api.instagram.com/oauth/access_token", $args );

			if( isset( $request_access_token['body'] ) && $request_access_token['response']['code'] == '200' ){
				// Parse the body response
				$response = json_decode( $request_access_token['body'] );

				if( isset( $response->access_token ) ){
					// Save the access token
					$save_access_token = update_option( "{$this->prefix}access_token", esc_html( $response->access_token ) );

					// Save the user info
					$save_user = update_option( "{$this->prefix}account", $response->user );

					// Close the popup, then reload the parent page
					?>
						<script type="text/javascript">
						   opener.location.reload(true);
						   self.close();
						</script>
					<?php
				} else {
					_e( "Fail to connect to your Instagram Account. Please try again", "wp_ig" );
				}
			} else {
				_e( "We cannot connect to Instagram. Please close this popup window then try again.", "wp_ig" );
			}

			die();			
		} elseif( isset( $_GET['error'] ) && isset( $_GET['error_description'] ) ){
			// If user denied to give an access...
			echo $_GET['error_description'];

		} else {
			_e( "An error occurred. We cannot connect this site to your Instagram account. Please try again", "wp_ig" );
		}

		die();		
	}

	/**
	 * Deauth instagram account by deleting options value
	 * 
	 * @return void
	 */
	function page_deauth(){

		// Disconnecting...
		if( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'deauth_instagram_account' ) ){
			_e( "Disconnecting your instagram account...", "wp_ig" );

			// remove the data
			delete_option( "{$this->prefix}exchange_code" );
			delete_option( "{$this->prefix}access_token" );
			delete_option( "{$this->prefix}account" );
		} else {			
			_e( "You are not authorized to disconnect the instagram account from this site...", "wp_ig" );
		}

		// Close the popup, then reload the parent page
		?>
			<script type="text/javascript">
			   opener.location.reload(true);
			   self.close();
			</script>
		<?php		
		die();
	}

	/**
	 * Display items based on data fetched through API
	 * 
	 * @param obj fetched from API
	 * 
	 * @return void
	 */
	function display_items( $data, $context = 'index' ){
		if( isset( $data->data ) && !empty( $data->data ) ){
			foreach ($data->data as $item) {
				$this->the_item( $item, $context );		
			}

			if( isset( $data->pagination->next_max_id ) ){
				$more_link = admin_url() . "admin.php?&max_id=" . $data->pagination->next_max_id;

				// Pushes more variables
				if( isset( $_GET ) ){

					$qs = $_GET;

					unset( $qs['max_id' ] );

					foreach ($qs as $key => $qs_item) {
						$more_link .= "&{$key}={$qs_item}";
					}
				}

				printf( __( "<a href='%s' class='more-items'>Load More</a>", "wp_ig" ), $more_link );				
			}
		} else {
			_e( "Cannot connect to Instagram", "wp_ig" );
		}
	}

	function the_item( $item, $context = 'index' ){
		$current_time = current_time( 'timestamp' );
		?>
		<div class="item">
			<div class="image">	
				<?php if( isset( $item->videos->standard_resolution ) ) : ?>
					<video controls loop>
						<source src="<?php echo $item->videos->standard_resolution->url; ?>"></source>
					</video>
				<?php else : ?>
					<img src="<?php echo $item->images->standard_resolution->url; ?>" alt="<?php echo $item->caption->text; ?>">
				<?php endif; ?>
			</div>
			<div class="user">
				<div class="avatar">
					<img src="<?php echo $item->user->profile_picture; ?>" alt="<?php echo $item->user->full_name; ?>">
				</div>
				<div class="caption">
					<a href="" title="<?php echo $item->user->full_name; ?>"><?php echo $item->user->username; ?></a> <?php if( isset( $item->caption->text ) ) echo $item->caption->text; ?>
				</div>
			</div>
			<div class="comments">
				<?php if( isset( $item->comments->count ) ) printf( ngettext( "%d Comment", "%d Comments", $item->comments->count ), $item->comments->count ); ?>
			</div>
			<div class="likes">
				<?php if( isset( $item->likes->count ) ) printf( ngettext( "%d Like", "%d Likes", $item->likes->count ), $item->likes->count ); ?>				
			</div>
			<?php if( !empty( $item->tags ) ) : ?>
				<div class="tags">
					<?php		
						$tag_index = 0;			
						foreach ($item->tags as $tag) {
							$tag_index++;
							if( $tag_index > 1 )
								echo ", ";
							printf( __( "<a href='%s' title='View all #%s posts'>#%s</a>", "wp_ig" ), admin_url() . "admin.php?page=wp_ig&tag_name=" . urlencode( $tag ), $tag, $tag );
						}
					?>
				</div>
			<?php endif; ?>
			<div class="time">
				<?php echo human_time_diff( $item->created_time, $current_time ) . __( " ago", "wp_ig" ); ?> / <?php echo date( "l, j F Y H:I" ); ?>
			</div>

<!-- 			<pre>
				<?php //print_r( $item ); ?>					
			</pre> -->
		</div>
		<br><br>
		<?php
	}
}
new WP_IG_Dashboard;