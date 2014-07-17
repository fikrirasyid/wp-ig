<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<div class="wrap">
	<h2><?php _e( 'Settings', 'wp-ig' ); ?></h2>
	<form action="admin.php?page=<?php echo $_GET['page']; ?>" name="form" method="post">
		<?php

			$fields_basic = array(
				/**
				 * A. Create app
				 */
				array(
					'type' 		=> 'heading',
					'content' 	=> __( 'A. Create an Instagram Client App', 'wp-ig' )
				),
				array(
					'type' 		=> 'ol_start'
				),
				array(
					'type' 		=> 'li',
					'content' 	=> '<a href="http://instagram.com/developer/clients/register/" target="_blank" title="'. __( 'Register a Client &bull; Instagram Developer Documentation', 'wp-ig' ) .'">'. __( 'Create new Instagram client app here', 'wp-ig' ) .'</a>'
				),
				array(
					'type' 		=> 'li',
					'content' 	=> sprintf( __( 'Use this for <strong>OAuth redirect_uri</strong> field: <code>%s</code>', 'wp-ig' ), $this->redirect_uri )
				),
				array(
					'type' 		=> 'ol_end'
				),
				array(
					'type'		=> 'p',
					'content'	=> __( 'After registering an Instagram client app, you can go back to this page and proceed the next step.', 'wp-ig' )
				),

				array(
					'type' 		=> 'break'
				),

				/**
				 * B. Saving
				 */
				array(
					'type'		=> 'heading',
					'content'	=> __( 'B. Saving your Instagram client app credential', 'wp-ig' )
				),
				array(
					'type'		=> 'form_table_start'
				),
				array(
					'type'			=> 'field_text',
					'id'			=> 'client_id',
					'label'			=> __( 'Client ID', 'wp-ig' ),
					'value'			=> $this->client_id,
					'placeholder' 	=> __( 'Insert your Instagram Client ID here', 'wp-ig' )
				),
				array(
					'type'			=> 'field_text',
					'id'			=> 'client_secret',
					'label'			=> __( 'Client Secret', 'wp-ig' ),
					'value'			=> $this->client_secret,
					'placeholder' 	=> __( 'Insert your Instagram Client Secret here', 'wp-ig' )
				),
				array(
					'type'		=> 'form_table_end'
				),
			);
			
			// Print basic fields
			$this->settings()->fields( $fields_basic );

			if( $this->client_id && $this->client_secret && $this->client_id != '' && $this->client_secret != '' ) : 

			$fields_adv = array(

				/**
				 * C. Post settings
				 */
				array(
					'type' 			=> 'break',
				),
				array(
					'type' 			=> 'heading',
					'content' 		=> __( 'C. Assign Post Type and Category for Sync and Import', 'wp-ig' )
				),
				array(
					'type'			=> 'form_table_start'
				),
				array(
					'type'			=> 'field_dropdown',
					'id'			=> 'post_type',
					'label'			=> __( 'Post Type', 'wp-ig' ),
					'value'			=> get_option( 'wp_ig_post_type' ),
					'dropdown_type'	=> 'post_type',
					'description'	=> __( 'Instagram media will be imported/synced as selected post type. Generally, you will want to use <strong>post</strong>.', 'wp-ig' )
				),
				array(
					'type'			=> 'field_dropdown',
					'id'			=> 'post_category',
					'label'			=> __( 'Post Category', 'wp-ig' ),
					'value'			=> get_option( 'wp_ig_post_category' ),
					'dropdown_type'	=> 'post_category',
					'description'	=> __( 'Instagram media will be imported/synced will be assigned to selected category', 'wp-ig' )
				),
				array(
					'type'			=> 'field_radio',
					'id'			=> 'sync',
					'label'			=> __( 'Sync Instagram', 'wp-ig' ),
					'value'			=> get_option( 'wp_ig_sync', 'no' ),
					'options'		=> array(
										array(
											'val' => 'yes',
											'label' => __( 'Yes', 'wp-ig' )
										),
										array(
											'val' => 'no',
											'label'	=> __( 'No', 'wp-ig' )
										)
									),
					'description'	=> __( 'Yould you like to automatically post your future Instagram media to this site?', 'wp-ig' )
				),
				array(
					'type'			=> 'form_table_end'
				),
				array(
					'type'			=> 'break'
				),

				/**
				 * D. Photo/Video Display Preference
				 */
				array(
					'type' 			=> 'heading',
					'content' 		=> __( 'D. Adjust The Way Instagram\'s Media Is Displayed', 'wp-ig' )
				),
				array(
					'type' 			=> 'heading_sub',
					'content' 		=> __( 'If you do not see the image or video on your imported Instagram post, you may want to check these boxes below.', 'wp-ig' )
				),
				array(
					'type' 			=> 'p',
					'content' 		=> __( '<strong>Technical details</strong>: Instagram\'s media (image or video) is imported and assigned to a post as <em>featured post</em> using "<em>image</em>" post format or <em>post meta</em> using "<em>video</em>" post format. Unfortunately, not every theme supports post format or displays featured image and post meta video accordingly. Thus, these options below enable you to prepend the imported Instagram media to the top of your content.', 'wp-ig' )
				),
				array(
					'type'			=> 'form_table_start'
				),
				array(
					'type'			=> 'field_checkboxes',
					'id'			=> 'prepend_photo',
					'label'			=> __( 'Prepend Photo', 'wp-ig' ),
					'options'		=> array(
										array(
											'id' 		=> 'prepend_photo_on_index',
											'value'		=> 'yes',
											'default' 	=> get_option( 'wp_ig_prepend_photo_on_index', 'yes' ),
											'label'		=> __( 'Display imported Instagram photo on top of the content at homepage, category page, and search page', 'wp-ig' ),
										),
										array(
											'id' 		=> 'prepend_photo_on_single',
											'value'		=> 'yes',
											'default' 	=> get_option( 'wp_ig_prepend_photo_on_single', 'yes' ),
											'label'		=> __( 'Display imported Instagram photo on top of the content at article page', 'wp-ig' ),
										),
					),
				),
				array(
					'type'			=> 'field_checkboxes',
					'id'			=> 'prepend_video',
					'label'			=> __( 'Prepend Video', 'wp-ig' ),
					'options'		=> array(
										array(
											'id' 		=> 'prepend_video_on_index',
											'value'		=> 'yes',
											'default' 	=> get_option( 'wp_ig_prepend_video_on_index', 'yes' ),
											'label'		=> __( 'Display imported Instagram video on top of the content at homepage, category page, and search page', 'wp-ig' ),
										),
										array(
											'id' 		=> 'prepend_video_on_single',
											'value'		=> 'yes',
											'default' 	=> get_option( 'wp_ig_prepend_video_on_single', 'yes' ),
											'label'		=> __( 'Display imported Instagram video on top of the content at article page', 'wp-ig' ),
										),
					),
				),
				array(
					'type'			=> 'form_table_end'
				),
				array(
					'type'			=> 'break'
				),

				/**
				 * E. Deauthorize
				 */
				array(
					'type' 			=> 'heading',
					'content' 		=> __( 'E. Deauthorize This Site From Your Instagram Account', 'wp-ig' ),
				),
				array(
					'type'			=> 'p',
					'content'		=> sprintf( __( '<a href="%s" title="Deauthorize this site from your Instagram account" id="deauth-instagram">Click here to deauthorize this site from your Instagram Account</a>.', 'wp-ig' ), admin_url() . 'admin.php?page=wp_ig_settings' )
				),
				array(
					'type'			=> 'p',
					'content'		=> sprintf( __( '<strong>Important</strong>: this action will only de-authorize this site from your Instagram account. If you want to delete the imported content as well, <a href="%s" title="delete your imported Instagram account" target="_blank">you can delete it here</a>.', 'wp-ig' ), admin_url() . 'admin.php?page=wp_ig_delete' )
				)
			);

			// Print advance fields
			$this->settings()->fields( $fields_adv );
		?>

		<script type="text/javascript">
			jQuery(document).ready(function($){
				$('#deauth-instagram').click(function(e){
					InstagramAuthWindow = window.open('<?php echo admin_url(); ?>admin-ajax.php?action=wp_ig_deauth_account&_wpnonce=<?php echo wp_create_nonce( "deauth_instagram_account" ); ?>', 'Instagram Authorization', 'width=800,height=400');	
				});
			});
		</script>	

		<?php endif; ?>

		<?php wp_nonce_field( "wp_ig_settings", "_wpnonce" ); ?>
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes', 'wp-ig' ); ?>">
		</p>
	</form>
</div>