<?php
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
}