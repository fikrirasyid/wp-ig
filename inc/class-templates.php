<?php
/**
 * HTML templating for API output
 */
class WP_IG_Templates{
	var $prefix;	
	var $account;
	var $access_token;
	var $base_url;

	/**
	 * Initialize
	 */
	function __construct(){
		$this->prefix 		= 'wp_ig_';
		$this->account 		= get_option( "{$this->prefix}account" );
		$this->access_token = get_option( "{$this->prefix}access_token" );
		$this->api 			= new WP_IG_API( $this->access_token );

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
	function display( $method, $args = array(), $context = false ){
		$output = $this->api->$method( $args );
		
		// Print output
		$this->the_items( $output, $context );
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

				printf( __( "<a href='%s' class='more-instagram-items'>Load More</a>", "wp_ig" ), $more_link );				
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
						<a href="" title="<?php echo $item->user->full_name; ?>"><?php echo $item->user->username; ?></a>
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
							<a href="" class="username"><?php echo $comment->from->username; ?></a> <?php echo $this->parse_caption( $comment->text ); ?>
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
			$text = preg_replace('/@(\w+)/', "<a href='{$this->base_url}&username=$1'>@$1</a>", $text);

			// Parse hashtags
			$text = preg_replace('/#(\w+)/', "<a href='{$this->base_url}&tag_name=$1'>#$1</a>", $text);
		}

		return $text;
	}
}