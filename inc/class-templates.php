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

		$this->base_url 	= admin_url() . 'admin.php?page=wp_ig';
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
			if( isset( $data->pagination->next_max_id ) ){
				$more_link = $this->base_url . "&max_id=" . $data->pagination->next_max_id;

				// Pushes more variables
				if( isset( $_GET ) ){

					$qs = $_GET;

					unset( $qs['max_id' ] );
					unset( $qs['page' ] );

					foreach ($qs as $key => $qs_item) {
						$more_link .= "&{$key}={$qs_item}";
					}
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
					if( isset( $item->comments->count ) ){
						echo '<div class="comments">';
						printf( ngettext( "%d Comment", "%d Comments", $item->comments->count ), $item->comments->count );
						echo '</div>';
					} 
				?>
			
			
				<?php 
					if( isset( $item->likes->count ) ){
						echo '<div class="likes">';
						printf( ngettext( "%d Like", "%d Likes", $item->likes->count ), $item->likes->count );
						echo '</div>';
					}
				?>					
			</div>
		</div>
		<?php
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