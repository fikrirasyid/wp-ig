<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * HTML templating for API output
 */
class WP_IG_Templates{
	var $prefix;	
	var $account;
	var $access_token;
	var $base_url;
	var $wpspin_url;

	/**
	 * Initialize
	 */
	function __construct(){
		$this->prefix 		= 'wp_ig_';
		$this->account 		= get_option( "{$this->prefix}account" );
		$this->access_token = get_option( "{$this->prefix}access_token" );
		$this->api 			= new WP_IG_API( $this->access_token );
		$this->wpspin_url 	= home_url( '/wp-includes/images/wpspin-2x.gif' );

		// Define base_url based on admin or public page
		if( is_admin() && ( !isset( $_REQUEST['action'] ) ) ){
			$this->base_url 	= admin_url() . 'admin.php?page=wp_ig';			
		} else {
			$this->base_url 	= admin_url() . 'admin-ajax.php?action=instagram';
		}
	}	

	/**
	 * Display output
	 * 
	 * @param string api method
	 * @param array method variables
	 * 
	 * @return void
	 */
	function display( $method, $args = array(), $context = false, $options = array() ){
		$output = $this->api->$method( $args );

		// Options config, display UI optionally
		$default_options = array(
			'title' 	=> false,
			'profile' 	=> false
		);

		$options = wp_parse_args( $options, $default_options );

		extract( $options );

		// Wrap Start
		echo "<div class='wp-ig-wrap-inside'>";

		// Display additional information conditionally based on method and options
		switch ( $method ) {
			case 'tag_media':


				// Display tag title
				if( $title && isset( $args['tag_name'] ) ){
					echo "<h2 class='wp-ig instagram-items-title'>#{$args['tag_name']}</h2>";						
				}
				break;

			case 'user_media':

				// Display user profile
				if( $profile && isset( $output->data{0}->user->id ) ){
					
					// If this is signed in user, use existing data instead fetching from endpoint
					if( $output->data{0}->user->id == $this->account->id ){
						$user = $this->account;
					} else {
						$user = $this->api->user_by_id( array( 'user_id' => $output->data{0}->user->id ) )->data;
					}

					// Just in user_id data is empty
					if( is_wp_error( $user ) ){
						_e( "User ID cannot be empty.", "wp_ig" );
						die();
					}

					if( isset( $user->id ) ){
						$this->the_user_profile( $user );
					}

				}

				// Display user title
				if( $title && isset( $output->data{0}->user->username ) ){
					echo "<h2 class='wp-ig instagram-items-title'>";
					printf( __( "%s's Instagram", "wp_ig" ), $output->data{0}->user->username  ); 			
					echo "</h2>";
				}				
				break;

			case 'self_feed':

				if( $profile ){
					$this->the_user_profile( $this->account );

					echo "<h2 class='wp-ig instagram-items-title'>";
					_e( 'Your Instagram Feed', 'wp-ig' );
					echo "</h2>";
				}

				break;
			
			default:
				// Do nothing
				break;
		}
		
		// Print output
		$this->the_items( $output, $context );

		// Wrap End
		echo "</div>";
	}

	/**
	 * Display user profile
	 * 
	 * @param obje user variable
	 * 
	 * @return void
	 */
	function the_user_profile( $user ){

		if( !isset( $user->username ) )
			return;

		?>
			<div id="current-instagram-profile">
				<div class="avatar">
					<img src="<?php echo $user->profile_picture; ?>" alt="<?php echo $user->full_name; ?>">
				</div>
				<div class="data">
					<h3 class="full-name"><?php echo $user->full_name; ?></h3>
					<h4 class="username"><a href="http://instagram.com/<?php echo $user->username; ?>" title="<?php echo $user->username; _e( " on Instagram", "wp_ig" ); ?>"><?php echo $user->username; ?></a> - <a href="<?php echo $user->website; ?>"><?php echo $user->website; ?></a></h4>
					<?php if( !empty( $user->website ) ) : ?>
					<?php endif; ?>
					<div class="bio">
						<?php echo wpautop( $user->bio ); ?>
					</div>
				</div>
			</div>
		<?php
	}

	/**
	 * Displaying items
	 * 
	 * @param obj instagram api result
	 * @param string context of action
	 * 
	 * @return void
	 */
	function the_items( $data, $context = 'index' ){

		echo "<div class='wp-ig instagram-items'>";

		if( isset( $data->data ) && !empty( $data->data ) ){

			// Print items
			foreach ($data->data as $item) {
				$this->the_item( $item, $context );		
			}

			// Print more-items link
			if( isset( $data->pagination->next_url ) ){

				$more_link = $this->base_url;

				$more_link_query = parse_url( $data->pagination->next_url );

				if( isset( $more_link_query['query'] ) ){
					parse_str( $more_link_query['query'], $more_link_string );

					unset( $more_link_string['access_token'] );

					$more_link .= "&" . http_build_query( $more_link_string );
				}

				printf( __( "<a href='%s' class='more-instagram-items'><img src='{$this->wpspin_url}' width='16' height='16' class='loading'> <span class='label'>Load More</span></a>", "wp_ig" ), $more_link );				
			}
		} else {
			_e( "Cannot connect to Instagram", "wp_ig" );
		}		

		echo "</div><!-- .wp-ig.instagram-items -->";
	}

	/**
	 * Display item
	 * 
	 * @param obj instagram item object 
	 * @param string context of action
	 * 
	 * @return void
	 */
	function the_item( $item, $context = 'index' ){
		$current_time = current_time( 'timestamp' );
		?>
		<div class="instagram-item">
			<div class="actions">
				<?php $this->actions( $item ); ?>
			</div>
			<div class="media">	
				<?php if( isset( $item->videos->standard_resolution ) ) : ?>
					<video controls loop>
						<source src="<?php echo $item->videos->standard_resolution->url; ?>"></source>
					</video>
				<?php else : ?>
					<img src="<?php echo $item->images->standard_resolution->url; ?>" alt="<?php echo $item->caption->text; ?>">
				<?php endif; ?>
			</div>
			<?php			
				$this->the_likes( $item->likes );
			?>
			<div class="meta">
				<div class="avatar">
					<img src="<?php echo $item->user->profile_picture; ?>" alt="<?php echo $item->user->full_name; ?>">
				</div>
				<div class="info">

					<p class="username">
						<a href="<?php echo $this->base_url . "&username=" . $item->user->username;?>" title="<?php echo $item->user->full_name; ?>" class="onpage"><?php echo $item->user->username; ?></a>
					</p>
					 
					<p class="meta-time">
						<span class="time" title="<?php echo date( "l, j F Y H:I" ); ?>">
							<?php echo human_time_diff( $item->created_time, $current_time ) . __( " ago", "wp_ig" ); ?>							
						</span>
						<span class="via">
							<?php printf( __( "via <a href='%s' title='View this on Instagram' target='_blank'>Instagram</a>", "wp_ig" ), $item->link ); ?>
						</span>
					</p>

					<div class="caption">
						<?php if( isset( $item->caption->text ) ) echo wpautop( $this->parse_caption( $item->caption->text ) ); ?>
					</div>				
				</div>
				<?php 
					$this->the_comments( $item->comments, $item->link );
				?>				
			</div>
		</div>
		<?php
	}

	/**
	 * Display likes
	 * 
	 * @param obj instagram likes object
	 * 
	 * @return
	 */
	function the_likes( $likes ){
		if( isset( $likes->data ) && !empty( $likes->data ) ){

			echo '<div class="instagram-likes">';

			$like_index = 0;
			$like_length = count( $likes->data );

			foreach ($likes->data as $like) {
				$like_index++;

				if( $like_index > 1 && $like_index == $like_length && intval( $likes->count ) < 5 ){
					_e( ", and ", "wp_ig" );
				} elseif( $like_index > 1 && $like_length > 2 ) {
					echo ", ";
				} elseif( $like_index > 1 && $like_length == 2 ){
					_e( " and ", "wp_ig" );
				}
				echo "<a href='http://instagram.com/{$like->username}' target='_blank'>{$like->username}</a>";
			}

			// The after copy
			if( $likes->count > 4 ){
				$the_rest = intval( $likes->count ) - 4;
				printf( __( " and %d others like this", "wp_ig" ), $the_rest );
			} elseif( $likes->count > 1 ) {
				_e( " like this", "wp_ig" );
			} else {
				_e( " likes this", "wp_ig" );
			}

			echo '</div>';
		}
	}

	/**
	 * Display comments
	 * 
	 * @param obj instagram comment object
	 * 
	 * @return void
	 */
	function the_comments( $comments, $link ){
		if( isset( $comments->data ) && !empty( $comments->data ) ){

			echo "<div class='instagram-comments'>";

			foreach ( $comments->data as $comment ) {
				?>

				<div class="instagram-comment">
					<span class="avatar">
						<img src="<?php echo $comment->from->profile_picture; ?>" alt="<?php echo $comment->from->username; ?>">
					</span>
					<div class="info">
						<div class="caption">
							<a href="<?php echo $this->base_url . "&username=" . $comment->from->username; ?>" class="username onpage"><?php echo $comment->from->username; ?></a> <?php echo $this->parse_caption( $comment->text ); ?>
						</div>
					</div>
				</div>

				<?php
			}

				// Contextual more link
				$the_rest = intval( $comments->count ) - count( $comments->data );
				if( $the_rest > 0 ): 
			?>

				<div class="instagram-comment">
					<div class="info">
						<div class="caption">
							<a href="<?php echo $link; ?>" class="view-more-comments" target="_blank"><?php printf( ngettext( "View One More Comment", "View %d More Comments", $the_rest ), $the_rest ); ?></a>
						</div>
					</div>
				</div>

			<?php			
				endif;

			echo "</div>";

		}
	}

	/**
	 * Turning #-lead string into proper hashtag
	 * 
	 * @param string caption
	 * 
	 * @return string caption 
	 */
	function parse_caption( $text ){
		if( isset( $text ) && $text != '' ){
			// Parse username
			$text = preg_replace('/@(\w+)/', "<a href='{$this->base_url}&username=$1' class='onpage'>@$1</a>", $text);

			// Parse hashtags
			$text = preg_replace('/#(\w+)/', "<a href='{$this->base_url}&tag_name=$1' class='onpage'>#$1</a>", $text);
		}

		return $text;
	}

	/**
	 * Get item status on the site
	 * 
	 * @return void
	 */
	function get_status( $id ){
		global $wpdb;

		$query = $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_instagram_id' AND meta_value = %s", $id );

		$post = $wpdb->get_row( $query );

		if( $post ){
			return array(
				'post_id' => $post->post_id,
				'permalink' => get_permalink( $post->post_id ),
			);
		} else {
			return false;
		}
	}

	/**
	 * Display item action
	 * 
	 * @return void
	 */
	function actions( $item ){
		if( !current_user_can( 'edit_others_posts' ) ){
			return;
		}

		// If this has been posted on the site
		$status = $this->get_status( $item->id );

		$import_nonce = wp_create_nonce( "import_{$item->id}" );

		$repost_nonce = wp_create_nonce( "report_{$item->id}" );

		$import_url = admin_url() . "admin-ajax.php?action=wp_ig_import_item&id={$item->id}&_n={$import_nonce}";

		$repost_url = admin_url() . "admin-ajax.php?action=wp_ig_repost_item&id={$item->id}&_n={$repost_nonce}";

		if( $status ){
			// if this is current user's to post
			if( $this->account->id == $item->user->id ){
				echo '<a href="'. $status["permalink"] .'" class="item-posted" target="_blank" title="'. __( 'View post', 'wp-ig' ) .'">'. __( 'Posted', 'wp-ig' ) .'<a>';
			} else {
				echo '<a href="'. $status["permalink"] .'" class="item-posted" target="_blank" title="'. __( 'View post', 'wp-ig' ) .'">'. __( 'Reposted', 'wp-ig' ) .'<a>';
			}
		} else {
			// if this is current user's to post
			if( $this->account->id == $item->user->id ){
				// Cross post
				echo '<a href="'. $import_url .'" class="item-not-posted import-item" title="'. __( 'Post this media', 'wp-ig' ) .'">'. __( 'Post This', 'wp-ig' ) .'<a>';
			} else {
				// Embed
				echo '<a href="'. $repost_url .'" class="item-not-posted repost-item" title="'. __( 'Repost this media', 'wp-ig' ) .'">'. __( 'Repost This', 'wp-ig' ) .'<a>';
			}
		}
	}
}