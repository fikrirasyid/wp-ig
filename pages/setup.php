<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

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
				<tr>
					<th scope="row">
						<label for="sync"><?php _e( 'Sync Instagram', 'wp-id' ); ?></label>
					</th>
					<td>
						<fieldset>
							<?php 
								$this->settings()->radio( array(
									'id' 		=> 'sync',
									'options' 	=> array(
										array(
											'value' => 'yes',
											'label' => __( 'Yes', 'wp-ig' )
										),
										array(
											'value' => 'no',
											'label'	=> __( 'No', 'wp-ig' )
										)
									),
									'default' 	=> get_option( 'wp_ig_sync', 'no' )
								) );
							?>							
						</fieldset>
						<p class="description"><?php _e( 'Yould you like to automatically post your future Instagram media to this site?', 'wp-ig' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>

		<br>
		<h3><?php _e( 'D. Adjust The Way Instagram\'s Media Is Displayed', 'wp-ig' ); ?></h3>
		<h4><?php _e( 'If you do not see the image or video on your imported Instagram post, you may want to check these boxes below.', 'wp-ig' ); ?></h4>
		<p><?php _e( '<strong>Technical details</strong>: Instagram\'s media (image or video) is imported and assigned to a post as <em>featured post</em> using "<em>image</em>" post format or <em>post meta</em> using "<em>video</em>" post format. Unfortunately, not every theme supports post format or displays featured image and post meta video accordingly. Thus, these options below enable you to prepend the imported Instagram media to the top of your content.', 'wp-ig' ); ?></p>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<?php _e( 'Prepend Photo', 'wp-ig' ); ?>
					</th>
					<td>
						<fieldset>
							<?php
								$this->settings()->checkbox( array(
									'id' 		=> 'prepend_photo_on_index',
									'value'		=> 'yes',
									'default' 	=> get_option( 'wp_ig_prepend_photo_on_index', 'yes' ),
									'label'		=> __( 'Display imported Instagram photo on top of the content at homepage, category page, and search page', 'wp-ig' ),
								) );

								$this->settings()->checkbox( array(
									'id' 		=> 'prepend_photo_on_single',
									'value'		=> 'yes',
									'default' 	=> get_option( 'wp_ig_prepend_photo_on_single', 'yes' ),
									'label'		=> __( 'Display imported Instagram photo on top of the content at article page', 'wp-ig' ),
								) );								
							?>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php _e( 'Prepend Video', 'wp-ig' ); ?>
					</th>
					<td>
						<fieldset>
							<?php
								$this->settings()->checkbox( array(
									'id' 		=> 'prepend_video_on_index',
									'value'		=> 'yes',
									'default' 	=> get_option( 'wp_ig_prepend_video_on_index', 'yes' ),
									'label'		=> __( 'Display imported Instagram video on top of the content at homepage, category page, and search page', 'wp-ig' ),
								) );

								$this->settings()->checkbox( array(
									'id' 		=> 'prepend_video_on_single',
									'value'		=> 'yes',
									'default' 	=> get_option( 'wp_ig_prepend_video_on_single', 'yes' ),
									'label'		=> __( 'Display imported Instagram video on top of the content at article page', 'wp-ig' ),
								) );								
							?>
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>

		<br>
		<h3><?php _e( 'E. Deauthorize This Site From Your Instagram Account', 'wp-ig' ); ?></h3>
		<p><?php printf( __( '<a href="%s" title="Deauthorize this site from your Instagram account" id="deauth-instagram">Click here to deauthorize this site from your Instagram Account</a>.', 'wp-ig' ), admin_url() . 'admin.php?page=wp_ig_setup' ); ?></p>
		<p><?php printf( __( '<strong>Important</strong>: this action will only de-authorize this site from your Instagram account. If you want to delete the imported content as well, <a href="%s" title="delete your imported Instagram account" target="_blank">you can delete it here</a>.', 'wp-ig' ), admin_url() . 'admin.php?page=wp_ig_delete' ); ?></p>

		<script type="text/javascript">
			jQuery(document).ready(function($){
				$('#deauth-instagram').click(function(e){
					InstagramAuthWindow = window.open('<?php echo admin_url(); ?>admin-ajax.php?action=wp_ig_deauth_account&_wpnonce=<?php echo wp_create_nonce( "deauth_instagram_account" ); ?>', 'Instagram Authorization', 'width=800,height=400');	
				});
			});
		</script>	

		<?php wp_nonce_field( "wp_ig_setup", "_wpnonce" ); ?>
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes', 'wp-ig' ); ?>">
		</p>
	</form>
</div>