<?php
	$account = get_option( "{$this->prefix}account" );

	// If there's no account information, display error
	if( ! isset( $account->id ) ){

		return;
	}

	// Define variables
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
							// window.location = "<?php echo admin_url(); ?>admin.php?page=wp_ig_import&action=import&max_id=<?php echo $user_media->pagination->next_max_id; ?>";
						</script>
					<?php
				} else {

					$done_message = __( 'All your Instagram media has been imported!', 'wp-ig' );

					echo "<h2>{$done_message}</h2>";
				}
			} else {
				// We're done here
				echo '<p style="color: red;">';

				_e( 'Cannot get content from Instagram. Please try again later', 'wp-ig' );

				echo '</p>';
			}

		else: 
			$this->import()->pre_import_message( $account );
		endif; 
	?>
</div>