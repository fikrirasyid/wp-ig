<?php
	$account = get_option( "{$this->prefix}account" );

	// If there's no account information, display error
	if( ! isset( $account->id ) ){

		return;
	}

	// Define variables
	$username 		= $account->username;
	$user_link 		= "<a href='http://instagram.com/{$username}' target='_blank'>@{$username}</a>";
	$post_type 		= get_option( 'wp_ig_post_type', 'post' );
	$post_category 	= get_category( get_option( 'wp_ig_post_category', 1 ) );
	$setup_url 		= admin_url() . 'admin.php?page=wp_ig_setup';
?>
<div class="wrap">
	<h2><?php _e( 'Import', 'wp-ig' ); ?></h2>	
	<?php 
		if( isset( $_GET['action'] ) && $_GET['action'] == 'import' ): 			

			$user_media_param = array();

			// Append max id if there's any
			if( $this->current_page->query_string( 'max_id' ) ){
				$user_media_param['max_id'] = $this->current_page->query_string( 'max_id' );
			}

			$user_media = $this->api()->user_media( $user_media_param );

			if( isset( $user_media->data ) && ! empty( $user_media->data ) ){

				echo '<table class="wp-list-table widefat fixed media">';

				// Importing instagram items..
				$index = 0;
				foreach ($user_media->data as $item ) {

					// Verify user based on user_id sameness
					if( ! isset( $item->user->id ) || intval( $item->user->id ) != intval( $account->id ) )
						continue;

					// Prevent duplication
					$existing = get_posts( array(
						'meta_key' => '_instagram_id',
						'meta_value' => $item->id
					) );

					// Increase the index
					$index++;

					// Determine odd class
					if( 0 == ( $index % 2 ) ){
						$tr_class = 'class="alternate"';
					} else {
						$tr_class = '';
					}

					echo "<tr $tr_class>";

					echo '<td width="150">';

						echo "<img src='{$item->images->thumbnail->url}'>";

					echo '</td>';

					echo '<td>';

						echo "<h4 style='margin-bottom: 0;'>@{$item->user->username}</h4>";
						echo "<p>{$item->caption->text}</p>";


					if( !empty( $existing[0]->ID ) ){

						// Display status if determined id has been exist
						echo '<p style="font-weight: bold; color: red;">'. sprintf( __( 'Import cancelled. This item has been posted before: <a href="%s" target="_blank">%s</a>', 'wp-ig' ), get_permalink( $existing[0]->ID ), esc_html( $existing[0]->post_title ) ) .'</p>';

					} else {
						// Import Instagram media
						$post_id = $this->import()->import_item( $item );

						if( $post_id ){
							
							$post = get_post( $post_id );

							$post_title = $post->post_title;

							if( strlen( $post->post_title ) ){
								$post_title = substr( $post->post_title, 0, 30 ) . '...';
							}

							echo '<p style="font-weight: bold; color: green;">'. sprintf( __( 'Imported: <a href="%s" target="_blank">%s</a>', 'wp-ig' ), get_permalink( $post_id ), $post_title ) .'</p>';
						} else {
							echo '<p style="font-weight: bold; color: red;">'. __( 'Import failed', 'wp-ig' ) .'</p>';
						}

					}

					echo '</td>';

					echo '</tr>';
				}

				echo '</table>';

				// Getting next set of posts
				if( isset( $user_media->pagination->next_max_id ) ){
					// Load and import the next page
					?>
						<script type="text/javascript">
							window.location = "<?php echo admin_url(); ?>admin.php?page=wp_ig_import&action=import&max_id=<?php echo $user_media->pagination->next_max_id; ?>";
						</script>
					<?php
				} else {

					$done_message = __( 'All your Instagram media has been imported!', 'wp-ig' );

					echo "<h2>{$done_message}</h2>";
				}
			} else {
				// We're done here
			}

		else: 
	?>
		<p><?php printf( __( "Import means importing your previous Instagram post on your Instagram account (which is %s) into WordPress' %s.", 'wp-ig' ), $user_link, $post_type ); ?></p>
		<br>

		<h3><?php _e( 'Note:', 'wp-ig' ); ?></h3>
		<ol>
			<li><?php printf( __( "Your Instagram media will be imported as <strong>%s</strong> post type.", 'wp-ig' ), $post_type ); ?></li>		
			
			<?php if( 'post' == $post_type ) : ?>
				<li><?php printf( __( "Your Instagram media will be assigned to <strong>%s</strong> category.", 'wp-ig' ), $post_category->name ); ?></li>
				<li><?php _e( 'Your posts which contain Instagram media will be set as <strong>image</strong> or <strong>video</strong> post format.', 'wp-ig' ); ?></li>
			<?php endif; ?>

			<li><?php printf( __( 'If you wish to change this import settings, you can change it on the <a href="%s" title="WP-IG setup page">setup page</a>.', 'wp-ig' ), $setup_url ); ?></li>
		</ol>
		<!-- <br> -->

		<p><?php _e( 'If you are ready to import your Instagram media, click the import button below:', 'wp-ig' ); ?></p>
		<form name="form" action="admin.php?page=wp_ig_import&action=import" method="post">
			<p class="submit">
				<input type="submit" id="submit" class="button button-primary" name="submit" value="<?php _e( 'Import', 'wp-ig' ); ?>">
			</p>		
		</form>
	<?php 
		endif; 
	?>
</div>