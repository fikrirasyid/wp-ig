<?php
/**
 * Providing import mechanism
 */
class WP_IG_Import{

	var $base_url;
	var $prefix;
	var $post_type;
	var $post_category;

	function __construct(){
		$this->base_url			= home_url();
		$this->prefix 			= 'wp_ig_';
		$this->post_type 		= get_option( "{$this->prefix}post_type", 'post' );
		$this->post_category 	= get_option( "{$this->prefix}post_category", 1 );
	}

	/**
	 * Pre-import message. Displaying before import message
	 * 
	 * @param obj account information
	 * 
	 * @return void
	 */
	function pre_import_message( $account ){
		// Variables
		$username 		= $account->username;
		$post_type 		= get_option( 'wp_ig_post_type', 'post' );
		$user_link 		= "<a href='http://instagram.com/{$username}' target='_blank'>@{$username}</a>";
		$post_category 	= get_category( get_option( 'wp_ig_post_category', 1 ) );
		$setup_url 		= admin_url() . 'admin.php?page=wp_ig_setup';

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
	}

	/**
	 * Importing + print 
	 */

	/**
	 * Uploading given URL to media library
	 * 
	 * @param string media URL
	 * 
	 * @return mixed import id|WP_Error object
	 */
	function upload_media( $media_url = false, $post_id = false ){

		// Media URL have to be supplied
		if( ! $media_url ){
			return new WP_Error( 400, __( 'Media URL have to be given', 'wp-ig' ) );
		}

		$import_media = download_url( $media_url );

		$import_media_array = array(
			'name' => basename( $media_url ),
			'tmp_name' => $import_media 	
		);

		if( $post_id ){
			$import_media_array['post_id'] = $post_id;
		}

	    // Check for download errors
	    if ( is_wp_error( $import_media ) ) {
	        @unlink( $import_media_array[ 'tmp_name' ] );
	        return $import_media;
	    }			

	    $import_id = media_handle_sideload( $import_media_array, 0 );

		return $import_id;
	}

	/**
	 * Importing Instagram Item
	 * Passi item's object to automatically importing it to WP
	 * 
	 * @param obj instagram media item
	 * 
	 * @return mixed int|bool of post ID or false
	 */
	function import_item( $item ){
		
		// Variables
		$post_title = substr($item->caption->text, 0, 30 );

		if( strlen( $item->caption->text ) > 30 ){
			$post_title .= "...";						
		}
		
		$link = $item->link;

		$link_exploded = explode( $link, '/' );

		if( isset( $link_exploded[4] ) ){
			$post_name = $link_exploded[4];						
		} else {
			$post_name = $post_title;
		}

		$post_content	 = $this->parse_caption( $item->caption->text );

		$post_status	 = 'publish';

		$post_date		 = date( 'Y-m-d H:i:s', $item->created_time );

		$post_tags		 = $item->tags;

		$post_category	 = array( 'Instagram' );

		$post_format	 = $item->type;

		$tax_input 		= array( 'mention' => $this->parse_users_in_photo( $item->users_in_photo ) );

		switch ( $post_format ) {
			case 'video':
				$media_url = $item->videos->standard_resolution->url;
				break;
			
			default:
				$media_url = $item->images->standard_resolution->url;
				break;
		}

		// Insert post
		$post_args = array(
			'post_name' => $post_name,
			'post_title'	=> $post_title,
			'post_content'	=> $post_content,
			'post_status'	=> $post_status,
			'post_date'		=> $post_date,
			'post_type'		=> $this->post_type,
			'tags_input'	=> $post_tags,
		);

		// Conditional arguments
		if( 'post' == $this->post_type ){
			$post_args['post_category'] = array( $this->post_category );

			$mention = $this->extract_text( $item->caption->text, 'username' );

			if( isset( $mention[1] ) && ! empty( $mention[1] ) ){
				foreach ( $mention[1] as $mentioned_user ) {
					array_push( $tax_input['mention'], $mentioned_user );
				}

				$tax_input['mention'] = array_unique( $tax_input['mention'] );
			}

			$post_args['tax_input'] = $tax_input;
		}

		$post_id = wp_insert_post( $post_args );

		// Set post format
		set_post_format( $post_id, $post_format );

		// Import media
		$media_id = $this->upload_media( $media_url, $post_id );

		switch ( $post_format ) {
			case 'video':
				$meta_id_video = update_post_meta( $post_id, '_format_video_embed', wp_get_attachment_url( $media_id ) );
				break;
			
			default:
				$featured_image = set_post_thumbnail( $post_id, $media_id );
				break;
		}

		// Insert appropriate post meta
		update_post_meta( $post_id, '_post_source', 'instagram' );
		update_post_meta( $post_id, '_instagram_id', $item->id );
		update_post_meta( $post_id, '_instagram_filter', $item->filter );
		update_post_meta( $post_id, '_instagram_user_id', $item->user->id );
		update_post_meta( $post_id, '_instagram_data', $item );		

		return $post_id;
	}

	/**
	 * Parse user in photo object into array for wp_insert_post
	 * 
	 * @param obj users_in_photo
	 * 
	 * @return array
	 */
	function parse_users_in_photo( $users_in_photo ){
		$users = array();

		if( !empty( $users_in_photo ) ){
			foreach ($users_in_photo as $user) {
				array_push( $users, $user->user->username );
			}
		}

		return $users;
	}

	/**
	 * Turning #-lead string into proper hashtag
	 * Turning @-lead string into proper mention
	 * 
	 * @param string caption
	 * 
	 * @return string caption 
	 */
	function parse_caption( $text ){
		if( isset( $text ) && $text != '' ){
			// Parse username
			$mentions = $this->extract_text( $text, 'username' );

			if( isset( $mentions[0] ) && ! empty( $mentions[0] ) ){
				foreach ( $mentions[0] as $mention ) {
					$mention_text = substr( $mention, 1 );
					$text = preg_replace( "/@(\b{$mention_text}\b)/", $this->get_term_link( $mention, 'mention' ), $text );
				}
			}

			// Parse hashtags
			$text = preg_replace('/#(\w+)/', "<a href='{$this->base_url}/tag/$1' target='_blank'>#$1</a>", $text);

			$hashtags = $this->extract_text( $text, 'hashtag' );

			if( isset( $hashtags[0] ) && ! empty( $hashtags[0] ) ){
				foreach ( $hashtags[0] as $hashtag ) {
					$hashtag_text = substr( $hashtag, 1 );
					$text = preg_replace( "/#(\b{$hashtag_text}\b)/", $this->get_term_link( $hashtag, 'post_tag' ), $text );
				}
			}
		}

		return $text;
	}

	/**
	 * Extract hashtag OR username from string given
	 * 
	 * @param string text
	 * @param string mode
	 * 
	 * @return array matches
	 */
	function extract_text( $text, $mode = 'hashtag' ){
		switch ( $mode ) {
			case 'username':
				$regex = "/@([\p{L}\p{Mn}]+)/";
				break;
			
			default:
				$regex = "/#([\p{L}\p{Mn}]+)/";
				break;
		}

		preg_match_all( $regex, $text, $matches );

		return $matches;
	}

	/**
	 * Get term link safely
	 * Safely means = create if it doesn't exist, then re-get it
	 * 
	 * @param string term name
	 * @param string taxonomy name
	 * 
	 * @return url
	 */
	function get_term_link( $name, $taxonomy ){
		$term_obj = get_term_by( 'name', $name, $taxonomy );

		// If no term found, create the term
		if( ! $term_obj ){
			$new_term = wp_insert_term( $name, $taxonomy );

			$url = get_term_link( $new_term['term_id'], $taxonomy );
		} else {
			$url = get_term_link( intval( $term_obj->term_id ), $taxonomy );
		}

		switch ( $taxonomy ) {
			case 'mention':
				$title = __( 'View all items which mention %s', 'wp-ig' );
				break;
			
			default:
				$title = __( 'View all items which are tagged using %s', 'wp-ig' );
				break;
		}

		return "<a href='$url' target='_blank' title='". sprintf( $title, substr( $name, 1 ) ) ."'>$name</a>";
	}
}