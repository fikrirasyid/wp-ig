<div class="wrap">
	<h2><?php _e( 'Delete', 'wp-ig' ); ?></h2>
	<?php if( isset( $_GET['action'] ) && 'delete' == $_GET['action'] ) : ?>
		
		<?php
			// Check for the nonce
			if( isset( $_GET['_n'] ) && wp_verify_nonce( $_GET['_n'], 'wp_ig_delete' ) ) :
				// Get all wp-ig posts
				$posts = $this->content()->get_posts( array( 'posts_per_page' => 10 ) );

				// Delete em all
				if( ! empty( $posts ) ) :
					
					echo '<ol id="deleted-posts-list">';
					
					foreach ( $posts as $key => $post ) {

						echo '<li>';

						$permalink = get_permalink( $post->ID );

						if( isset( $post->instagram_data->link ) ){
							$source_link = $post->instagram_data->link;
						} else {
							$source_link = '#';
						}

						$title = "<a href='$permalink'>{$post->post_title}</a>";

						$source = "<a href='$source_link'>". __( 'here', 'wp-ig' ) ."</a>";


						$deleting = wp_delete_post( $post->ID, true );

						if( $deleting ){
							printf( __( '%s which is imported from %s is successfully  deleted', 'wp-ig' ), $title, $source );							
						} else {
							echo '<span style="color: red;">';
							
							printf( __( 'Error deleting %s wich is imported from %s' ), $title, $source );

							echo '</span>';
						}


						echo '</li>';
					}

					echo '</ol>';

					// Refreshing this page to keep deleting posts
					if( 10 > count( $posts ) ){
						echo '<p>'. __( 'All imported Instagram content has been deleted', 'wp-ig' ) .'</p>';
					} else {
						?>
							<script type="text/javascript">
								location.reload(true);
							</script>
						<?php
					}

				else :
					echo '<p>'. __( 'There is no imported Instagram content to be deleted.', 'wp-ig' ) .'</p>';
				endif;
			else: ?>
			<div class="message error">
				<p><?php _e( 'You attempted to delete all imported Instagram media, but we cannot verify your access right. Please try again.', 'wp-ig' ); ?></p>
			</div>
		<?php 
			endif; 
		?>

	<?php else : ?>

		<p><?php _e( 'Click delete button below, and you will delete all content imported from Instagram (which is imported using WP-IG plugin).', 'wp-ig' ); ?></p>

		<p><?php _e( 'Note: there is no way to undo this process. Please be careful before you perform this action.', 'wp-ig' ); ?></p>

		<form name="form" action="admin.php" method="get">
			<input type="hidden" name="page" value="wp_ig_delete">
			<input type="hidden" name="action" value="delete">
			<?php wp_nonce_field( 'wp_ig_delete', '_n', false ); ?>
			<p class="submit">
				<input type="submit" id="submit" class="button button-primary" name="submit" value="<?php _e( 'Delete', 'wp-ig' ); ?>">
			</p>		
		</form>	

	<?php endif; ?>
</div>