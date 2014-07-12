<?php
/**
 * Setting content / element
 * 
 * @package WP_IG/Classes
 * @since 0.1
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WP_IG_Settings{

	var $prefix;

	function __construct(){
		$this->prefix = "wp_ig_";
	}

	/**
	 * Get and format post type data for dropdown
	 * 
	 * @return array
	 */
	function get_post_types(){
		$post_types = get_post_types( array( 
			'public' => true
		), 'names' );

		unset( $post_types['attachment'] );

		return $post_types;
	}

	/**
	 * Get and format category data for dropdown
	 * 
	 * @return array
	 */
	function get_categories(){
		$categories_data = get_categories( array( 'hide_empty' => false ) );

		$categories = array();

		foreach ($categories_data as $cat) {
			$categories[$cat->cat_ID] = $cat->name;
		}

		// Sort naturally based on value while keeping the key order
		natcasesort( $categories );

		return $categories;
	}

	/**
	 * Printing form fields based on the array given
	 * 
	 * @param array of formatted fields
	 * 
	 * @return void
	 */
	function fields( $fields ){
		if( empty( $fields) ){
			return;
		}

		// Loop the fields settings
		foreach ($fields as $field) {

			$default_field = array(
				'type' 			=> '',
				'content' 		=> '',
				'label'			=> '',
				'id'			=> 'id',
				'value'			=> 'value',
				'placeholder' 	=> '',
				'attr'			=> array(),
				'dropdown_type'	=> false,
				'description'	=> false,
				'options'		=> false,
			);

			$field = wp_parse_args( $field, $default_field );

			extract( $field );

			switch ( $type ) {
				case 'heading':
					echo "<h3>$content</h3>";
					break;

				case 'heading_sub':
					echo "<h4>$content</h4>";
					break;

				case 'ol_start':
					echo '<ol>';
					break;

				case 'ol_end':
					echo '</ol>';
					break;

				case 'form_table_start':
					echo '<table class="form-table"><tbody>';
					break;

				case 'form_table_end':
					echo '</tbody></table>';
					break;

				case 'break':
					echo '<br />';
					break;

				case 'p':
					echo "<p>$content</p>";
					break;

				case 'li':				
					echo "<li>$content</li>";
					break;

				case 'field_text':
					?>
						<tr>
							<th scope="row">
								<label for="<?php echo $id; ?>"><?php echo $label; ?></label>
							</th>
							<td>
								<?php 
									$this->text( $field ); 

									if( $description ){
										echo "<p class='description'>$description</p>";
									}
								?>
							</td>
						</tr>
					<?php
					break;
				
				case 'field_dropdown':
					?>
						<tr>
							<th scope="row">
								<label for="<?php echo $id; ?>"><?php echo $label; ?></label>
							</th>
							<td>
								<?php 
									$this->select_dropdown( $dropdown_type, $value ); 

									if( $description ){
										echo "<p class='description'>$description</p>";
									}
								?>
							</td>
						</tr>
					<?php
					break;

				case 'field_radio':
					?>
						<tr>
							<th scope="row">
								<label for="<?php echo $id; ?>"><?php echo $label; ?></label>
							</th>
							<td>
								<?php 
									$this->radio( $field ); 

									if( $description ){
										echo "<p class='description'>$description</p>";
									}
								?>
							</td>
						</tr>
					<?php
					break;

				case 'field_checkboxes':
					?>	
						<tr>
							<th scope="row">
								<label for="<?php echo $id; ?>"><?php echo $label; ?></label>
							</th>
							<td>
								<fieldset>
									<?php
										foreach ($options as $option_key => $option_value) {
											$this->checkbox( $option_value );
										}
									?>
								</fieldset>
								<?php 
									if( $description ){
										echo "<p class='description'>$description</p>";
									}
								?>
							</td>
						</tr>
					<?php
					break;

				default:
					# code...
					break;
			}

		}
	}

	/**
	 * Print text
	 * 
	 * @param array
	 * 
	 * @return void
	 */
	function text( $args ){

		$defaults = array(
			'id'			=> 'id',
			'value'			=> '',
			'placeholder' 	=> '',
			'attr'			=> array(),
		);

		$args = wp_parse_args( $args, $defaults );

		extract( $args );

		// Printing custom attributes
		$attributes = '';

		if( !empty( $attr ) ){
			foreach ($attr as $attr_key => $attr_value) {
				$attributes .= " {$attr_key}='{$attr_value}'";
			}
		}

		echo "<input type='text' name='$id' id='$id' value='$value' placeholder='$placeholder' class='regular-text'$attributes>";
	}

	/**
	 * Get dropdown
	 * 
	 * @param $data 
	 */
	function select_dropdown( $type = 'post_type', $default = false ){
		switch ( $type ) {
			case 'post_category':
				$items = $this->get_categories();

				if( ! $default || $default == '' ){
					$default = 1;
				}

				break;
			
			default:
				$items = $this->get_post_types();

				if( ! $default || $default == '' ){
					$default = 'post';
				}

				break;
		}

		echo "<select id='{$type}' name='{$this->prefix}{$type}' style='width: 25em;'>";

		// Print the options
		foreach ( $items as $value => $item ) {
			if( $value == $default ){
				echo "<option value='$value' selected='selected'>$item</option>";
			} else {
				echo "<option value='$value'>$item</option>";
			}
		}

		echo "</select>";
	}

	/**
	 * Display radio button option
	 * 
	 * @param array of arguments
	 * 
	 * @return void
	 */
	function radio( $args ){

		// Basic default setting
		$default = array(
			'id' 		=> '_radio_name',
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
			'value' 	=> 'no'
		);

		$default_option = array(
			'val'	=> 'yes',
			'label' => __( 'Yes', 'wp-ig' )
		);

		// Parse argument toward default values
		$args = wp_parse_args( $args, $default );

		extract( $args );

		// Print buttons
		foreach ( $options as $option ) {

			$option = wp_parse_args( $option, $default_option );

			extract( $option );

			$option_id = $id . '_' . sanitize_title( $val );

			?>
				<label for="<?php echo $option_id; ?>">
					<input type="radio" name="<?php echo $this->prefix . $id; ?>" value="<?php echo $val; ?>" id="<?php echo $option_id; ?>" <?php if( $value == $val ){ echo 'checked="checked"';}?>>
					<?php echo $label; ?>
				</label>
				<br>
			<?php
		}
	}

	/**
	 * Display checkbox input
	 * 
	 * @param array of arguments
	 * 
	 * @return void
	 */
	function checkbox( $args ){

		$default = array(
			'id' 		=> '_checkbox_name',
			'value'		=> 'yes',
			'default' 	=> 'yes',
			'label'		=> __( 'label of checkbox here', 'wp-ig' ),
		);

		$args = wp_parse_args( $args, $default );

		extract( $args );

		?>
			<label for="<?php echo $id; ?>">
				<input type="checkbox" name="<?php echo $this->prefix . $id; ?>" id="<?php echo $id; ?>" value="<?php echo $value; ?>" <?php if( $default == $value ) echo 'checked="checked"'; ?>>
				<?php echo $label; ?>
			</label>
		<?php
	}
}