<div class="wrap">
	<h2><?php _e( 'Setup', 'wp-ig' ); ?></h2>
	<form action="admin.php?page=<?php echo $_GET['page']; ?>" name="form" method="post">
		<h3><?php _e( 'A. Create an Instagram Client App', 'wp-ig' ); ?></h3>
		<ol>
			<li><a href="http://instagram.com/developer/clients/register/" target="_blank" title="<?php _e( 'Register a Client &bull; Instagram Developer Documentation', 'wp-ig' ); ?>"><?php _e( 'Create new Instagram client app here', 'wp-ig' ); ?></a></li>
			<li><?php printf( __( 'Use this for <strong>OAuth redirect_uri</strong> field: <code>%s</code>', 'wp-ig' ), $this->redirect_uri ); ?></li>		
		</ol>
		<p><?php _e( 'After registering an Instagram client app, you can go back to this page and proceed the next step.', 'wp-ig' ); ?></p>

		<br>
		<h3><?php _e( 'B. Saving your Instagram client app credential', 'wp-ig' );?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="client_id"><?php _e( 'Client ID', 'wp-ig' ); ?></label>
					</th>
					<td>
						<input name="client_id" type="text" id="client_id" value="<?php echo $this->client_id; ?>" class="regular-text" placeholder="<?php _e( 'Insert your Instagram Client ID here', 'wp-ig' ); ?>">
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="client_secret"><?php _e( 'Client Secret', 'wp-ig' ); ?></label>
					</th>
					<td>
						<input name="client_secret" type="text" id="client_secret" value="<?php echo $this->client_secret; ?>" class="regular-text" placeholder="<?php _e( 'Insert your Instagram Client Secret here', 'wp-ig' ); ?>">
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="client_secret"><?php _e( 'Redirect URI', 'wp-ig' ); ?></label>
					</th>
					<td>
						<code><?php echo $this->redirect_uri; ?></code>
					</td>
				</tr>
			</tbody>
		</table>

		<br>
		<h3><?php _e( 'C. Assign Post Type and Category for Sync and Import', 'wp-ig' ); ?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="post_type"><?php _e( 'Post Type', 'wp-ig' ); ?></label>
					</th>
					<td>
						<?php 
							$post_type_default = get_option( 'wp_ig_post_type' );

							$this->settings()->select_dropdown( 'post_type', $post_type_default ); 
						?>
						<p class="description"><?php _e( 'Instagram media will be imported/synced as selected post type. Generally, you will want to use <strong>post</strong>.', 'wp-ig' ); ?></p>
					</td>
				</tr>
				<tr id="post-category-row">
					<th scope="row">
						<label for="post_category"><?php _e( 'Post Category', 'wp-ig' ); ?></label>
					</th>
					<td>
						<?php 
							$category_default = get_option( 'wp_ig_post_category' );

							$this->settings()->select_dropdown( 'post_category', $category_default ); 
						?>
						<p class="description"><?php _e( 'Instagram media will be imported/synced will be assigned to selected category', 'wp-ig' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>

		<?php wp_nonce_field( "wp_ig_setup", "_wpnonce" ); ?>
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes', 'wp-ig' ); ?>">
		</p>
	</form>
</div>