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
		if( isset( $_GET['action'] ) && $_GET['action'] == 'import' ){	

			// Import parameter
			$import_params = array(
				'count' => 10,
			);

			// Append max id if there's any
			if( $this->current_page->query_string( 'max_id' ) ){
				$import_params['max_id'] = $this->current_page->query_string( 'max_id' );
			}

			$import = $this->import()->import_items( $import_params );

			if( isset( $import['importing'] ) && !empty( $import['importing'] ) ){

				echo '<ul>';

				foreach ( $import['importing'] as $key => $item ) {

					echo '<li>';

					switch ( $item['status'] ) {
						case 'error':

							echo '<span style="font-weight: bold; color: red">' . __( "Error", "wp-ig" ) . '</span>: ';

							_e( 'Cannot import Instagram media', 'wp-ig' );
							
							break;
						
						case 'duplicate':

							echo '<span style="font-weight: bold; color: orange">' . __( "Duplicate", "wp-ig" ) . '</span>: ';

							$permalink = get_permalink( $item['data']->ID );

							printf( __( 'Instagram media you want to import has been exist: %s', 'wp-ig' ), "<a href='$permalink' target='_blank'>{$item['data']->post_title}</a>" );
							
							break;

						default:
							
							echo '<span style="font-weight: bold; color: green">' . __( "Success", "wp-ig" ) . '</span>: ';

							$permalink = get_permalink( $item['data']->ID );

							printf( __( 'Instagram media imported: %s', 'wp-ig' ), "<a href='$permalink' target='_blank'>{$item['data']->post_title}</a>" );
							
							break;
					}

					echo '</li>';

				}

				echo '</ul>';

				// If next set of media detected, continue to import
				if( isset( $import['pagination']->next_max_id ) && $import['pagination']->next_max_id != '' ){

					$next_import_url =  admin_url() . "admin.php?page=wp_ig_import&action=import&max_id=" . $import['pagination']->next_max_id;
					?>
						<script type="text/javascript">
							window.location = "<?php echo $next_import_url; ?>";
						</script>
							<p><?php _e( 'If your browser is not automatically moved to the next page, click here:', 'wp-ig' ); ?> <a href="<?php echo $next_import_url; ?>"><?php _e( 'Next Page', 'wp-ig' ); ?></a></p>
					<?php					
				}

			}

			// Display all has been imported
			if( intval( $import['count'] ) < 10 ){
				
				echo '<p>';

				printf( __( 'All Instagram media has been imported! <a href="%s" target="_blank">View it here</a>.', 'wp-ig' ), $this->import()->get_archive_url() );

				echo '</p>';

			}

		} else{

			$this->import()->pre_import_message();

		} 
	?>

</div>