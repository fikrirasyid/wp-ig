<div class="wrap">
	<?php
		$account = get_option( "{$this->prefix}account" );
		$access_token = get_option( "{$this->prefix}access_token" );

		// Display connect or profile page based on current user log in state
		if( $account ) :
	?>
	
	<h2>Your Account</h2>

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

			<a href="#" id="deauth-instagram">Disconnect this account</a>
		</div>
	</div>

	<div id="feed">
		<?php 
			if( $this->qs->get( 'tag_name' ) ){
				$feed = $this->api()->get_tag_media( array( 
					'max_id' => $this->qs->get( 'max_id' ),
					'tag_name' => sanitize_text_field( $this->qs->get( 'tag_name' ) )
				) ); 
			} else {
				$feed = $this->api()->get_user_media( array( 
					'max_id' => $this->qs->get( 'max_id' ),
					'user_id' => $account->id
				) ); 				
			}

			$this->display_items( $feed );
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
</div>