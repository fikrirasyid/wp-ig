<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

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
	 * Initiating Instagram API
	 * 
	 * @return obj of Instagram API
	 */
	function api(){
		$api = new WP_IG_API( get_option( "{$this->prefix}access_token" ) );

		return $api;
	}

	/**
	 * Get account information
	 * 
	 * @return obj of connected account
	 */
	function current_account(){

		$account = get_option( "{$this->prefix}account" );

		return $account;
	}

	/**
	 * Pre-import message. Displaying before import message
	 * 
	 * @param obj account information
	 * 
	 * @return void
	 */
	function pre_import_message(){
		// Variables
		$username 		= $this->current_account()->username;
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
	 * Importing Instagram media
	 * 
	 * @param array of params similar to WP_IG_API->user_media
	 * @param bool confirming self feed
	 * 
	 * @return array of operation review
	 */
	function import_items( $user_media_param = array(), $self_feed = true ){
		$user_media = $this->api()->user_media( $user_media_param );

		if( isset( $user_media->data ) && ! empty( $user_media->data ) ){

			$output = array(
				'status' => array(
					'duplicate' 	=> 0,
					'success' 		=> 0,
					'error' 		=> 0,
				),
				'pagination' => $user_media->pagination,
				'count' => count( $user_media->data ),
			);

			foreach ($user_media->data as $item ){
				
				// Verify user based on user_id sameness (conditional)
				if( $self_feed && ( ! isset( $item->user->id ) || intval( $item->user->id ) != intval( $this->current_account()->id ) ) )
					continue;

				// Prevent duplication
				$existing = get_posts( array(
					'meta_key' => '_instagram_id',
					'meta_value' => $item->id
				) );	

				if( !empty( $existing[0]->ID ) ){

					// Instagram media existed
					$output['importing'][$item->id] = array(
						'status' 	=> 'duplicate',
						'permalink' => get_permalink( $existing[0]->ID ),
						'data' 		=> $existing[0]
					);

					// Count status
					$output['status']['duplicate'] = intval( $output['status']['duplicate'] ) + 1;

				} else {

					// Import Instagram media
					$post_id = $this->import_item( $item );

					if( $post_id ){
						
						$post = get_post( $post_id );

						// Instagram media imported
						$output['importing'][$item->id] = array(
							'status' 	=> 'success',
							'data' 		=> $post,
						);	

						// Count status
						$output['status']['success'] = intval( $output['status']['success'] ) + 1;						

					} else {

						// Instagram media imported
						$output['importing'][$item->id] = array(
							'status' 	=> 'error',
						);	

						// Count status
						$output['status']['error'] = intval( $output['status']['error'] ) + 1;

					}

				}				
			}

			return $output;

		} else {

			return new WP_Error( 400, __( 'Cannot get Instagram media', 'wp-ig' ) );

		}
	}

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
	 * Prevent duplication. Return true to proceed, return int of post ID if current item exist
	 * 
	 * @param int post ID
	 * 
	 * @return bool|obj
	 */
	function prevent_duplication( $media_id ){
		$existing = get_posts( array(
			'meta_key' => '_instagram_id',
			'meta_value' => $media_id
		) );			

		if( isset( $existing[0]->ID ) ){
			return $existing[0]->ID;
		} else {
			return true;
		}
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

		// Adding original link
		$post_content .= '<p><cite>'. sprintf( __( 'View Original: %s', 'wp-ig' ), "<a href='{$item->link}' title='{$item->caption->text}' target='_blank'>{$item->link}</a>" ) .'</cite></p>';

		$post_status	 = 'publish';

		$post_date		 = date( 'Y-m-d H:i:s', ( intval( $item->created_time ) + ( wp_timezone_override_offset() * 60 * 60 ) ) ); // Instagram gives timestamp in GMT hence it should be adjusted to user's preference

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
			'post_author'	=> get_current_user_id(),
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

		if( $post_id ){
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
		} else {
			return new WP_Error( 400, __( 'Cannot import Instagram media', 'wp-ig' ) );
		}
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

	/**
	 * Get url of WP IG archive page
	 * Note: user can set post_type and category of imported item
	 * 
	 * @return string of URL
	 */
	function get_archive_url(){
		if( 'post' == $this->post_type ){
			// display category archive if this post type is set to post
			return get_category_link( $this->post_category );
		} else {
			// otherwise, display post type archive		
			return get_post_type_archive_link( $this->post_type );	
		}
	}	
}