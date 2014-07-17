<div class="wrap">
	<?php
		$access_token = get_option( "{$this->prefix}access_token" );

		// Determine if this is another user's page or current user page
		if( $this->current_page->query_string( 'username' ) ){

			// Get username
			$username = sanitize_text_field( $this->current_page->query_string( 'username' ) );

			// Get username data from instagram API
			$user = $this->api()->user_search( array( 'q' => $username ) );

			if( isset( $user->data[0] ) ){
				$account = $user->data[0];
			} else {
				$account = false;
			}

		} else {
			$account = get_option( "{$this->prefix}account" );
		}

		// Display connect or profile page based on current user log in state
		if( $account ) :
	?>
	
	<h2><?php _e( "Instagram", "wp_ig" ); ?></h2>

	<div id="current-instagram-profile">
		<div class="avatar">
			<img src="<?php echo $account->profile_picture; ?>" alt="">
		</div>
		<div class="data">
			<h3 class="full-name"><?php echo $account->full_name; ?></h3>
			<h4 class="username"><a href="http://instagram.com/<?php echo $account->username; ?>" title="<?php echo $account->username; _e( " on Instagram", "wp_ig" ); ?>"><?php echo $account->username; ?></a> - <a href="<?php echo $account->website; ?>"><?php echo $account->website; ?></a></h4>
			<?php if( !empty( $account->website ) ) : ?>
			<?php endif; ?>
			<div class="bio">
				<?php echo wpautop( $account->bio ); ?>
			</div>			

			<a href="#" id="deauth-instagram"><?php _e( "Disconnect", "wp_ig" ); ?></a>
		</div>
	</div>
	
	<h2 id='feed-title'>
	<?php
		if( $this->current_page->query_string('tag_name') ){
			echo "#{$this->current_page->query_string('tag_name')}";
		} elseif( $this->current_page->query_string( 'username' ) ){			

			printf( __( "@%s's Feed", "wp_ig" ), $username );
			
		} else {
			_e( "Your Instagram Media", "wp_ig" );
		}
	?>
	</h2>

	<div id="feed">
		<?php 	
			if( $this->current_page->query_string( 'tag_name' ) ){

				// Hashtag page		

				$method = "tag_media";
				$args = array( 
					'max_id' => $this->current_page->query_string( 'max_id' ),
					'tag_name' => sanitize_text_field( $this->current_page->query_string( 'tag_name' ) )
				);
			} else {

				// Default

				$method = "user_media";
				$args = array( 
					'max_id' => $this->current_page->query_string( 'max_id' ),
					'user_id' => $account->id
				);
			}

			$this->templates->display( $method, $args );
		?>
	</div>

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