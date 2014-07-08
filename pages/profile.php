<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<div class="wrap">
	<?php
		$access_token = get_option( "{$this->prefix}access_token" );

		// Display connect or profile page based on current user log in state
		$account = get_option( "{$this->prefix}account" );

		if( $account ) :
	?>
	
	<h2><?php _e( "Instagram", "wp_ig" ); ?></h2>

	<?php 	

		if( $this->current_page->query_string( 'popular' ) ){
			// Popular Items
			$method = "popular";
		} elseif( $this->current_page->query_string( 'self' ) ){
			// Self feed
			$method = "self_feed";
		} elseif( $this->current_page->query_string( 'tag_name' ) ){
			// Hashtag page		
			$method = "tag_media";

		} elseif ( $this->current_page->query_string( 'username' ) || $this->current_page->query_string( 'user_id' ) ) {
			$method = 'user_media';
		} else {
			// Default (user feed)
			$method = "self_feed";
		}

		$args = $_REQUEST;
		unset( $_REQUEST['page'] );
		
		$this->templates->display( $method, $args, false, array( 'title' => true, 'profile' => true ) );
	?>

	<script type="text/javascript">
		jQuery(document).ready(function($){
			$('#deauth-instagram').click(function(e){
				InstagramAuthWindow = window.open('<?php echo admin_url(); ?>admin-ajax.php?action=wp_ig_deauth_account&_wpnonce=<?php echo wp_create_nonce( "deauth_instagram_account" ); ?>', 'Instagram Authorization', 'width=800,height=400');	
			});
		});
	</script>	

	<?php else : ?>
	
		<?php if( isset( $username ) ): ?>
			<h2><?php _e( "Not Found", "wp_ig" ); ?></h2>
			<p><?php printf( __( "%s's profile cannot be found.", "wp_ig"), $username ); ?></p>
		<?php else: ?>
			<h2><?php _e( "Connect Your Account", "wp_ig" ); ?></h2>

			<p><a href="#" id="auth-instagram" title="<?php _e( "Connect To Instagram", "wp_ig" ); ?>"><?php _e( "Connect To Instagram", "wp_ig" ); ?></a></p>
			<script type="text/javascript">
				jQuery(document).ready(function($){
					$('#auth-instagram').click(function(e){
						InstagramAuthWindow = window.open('<?php echo $this->authorization_url; ?>', 'Instagram Authorization', 'width=800,height=400');	
					});
				});
			</script>	
		<?php endif; ?>

	<?php endif; ?>
</div>