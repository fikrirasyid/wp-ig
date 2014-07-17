<div class="wrap">
	<h2><?php _e( "Setup", "wp_ig" ); ?></h2>
	<form action="admin.php?page=<?php echo $_GET['page']; ?>" name="form" method="post">
		<h3><?php _e( "A. Create an Instagram Client App", "wp_ig" ); ?></h3>
		<ol>
			<li><a href="http://instagram.com/developer/clients/register/" target="_blank" title="Register a Client &bull; Instagram Developer Documentation"><?php _e( "Create new Instagram client app here", "wp_ig" ); ?></a></li>
			<li><?php printf( __( "Use this for <strong>OAuth redirect_uri</strong> field: <code>%s</code>", "wp_ig" ), $this->redirect_uri ); ?></li>		
		</ol>
		<p><?php _e( "After registering an Instagram client app, you can go back to this page and proceed the next step.", "wp_ig" ); ?></p>

		<br>
		<h3><?php _e( "B. Saving your Instagram client app credential" );?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="client_id"><?php _e( "Client ID", "wp_ig" ); ?></label>
					</th>
					<td>
						<input name="client_id" type="text" id="client_id" value="<?php echo $this->client_id; ?>" class="regular-text" placeholder="<?php _e( "Insert your Instagram Client ID here", "wp_ig" ); ?>">
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="client_secret"><?php _e( "Client Secret", "wp_ig" ); ?></label>
					</th>
					<td>
						<input name="client_secret" type="text" id="client_secret" value="<?php echo $this->client_secret; ?>" class="regular-text" placeholder="<?php _e( "Insert your Instagram Client Secret here", "wp_ig" ); ?>">
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="client_secret"><?php _e( "Redirect URI", "wp_ig" ); ?></label>
					</th>
					<td>
						<code><?php echo $this->redirect_uri; ?></code>
					</td>
				</tr>
			</tbody>
		</table>
		<?php wp_nonce_field( "wp_ig_setup", "_wpnonce" ); ?>
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( "Save Changes", "wp_ig" ); ?>">
		</p>
	</form>
</div>