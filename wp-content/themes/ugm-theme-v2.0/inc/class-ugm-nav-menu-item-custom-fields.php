<?php

class UGM_Nav_Menu_Item_Custom_Fields {
	static $options = array(
		'item_tpl' => '
			<p class="additional-menu-field-display description description-thin">
				<label for="edit-menu-item-display-{id}">
					{label}<br>
					<select name="menu-item-display[{id}]" id="edit-menu-item-display-{id}" class="widefat edit-menu-item-display" value="{value}">
						<option value="default">Default</option>
						<option value="mega-3-column">Mega Menu 3 Kolom</option>
						<option value="mega-4-column">Mega Menu 4 Kolom</option>
					</select>
				</label>
			</p>
			<script>jQuery("#edit-menu-item-display-{id}").val("{value}");</script>
		',
	);
	static function setup() {
		if ( !is_admin() )
			return;
		$new_fields = array(
			'display' => array(
				'name' => 'display',
				'label' => __('Tampilan', 'ugm-theme'),
				'container_class' => 'menu-display',
				'input_type' => 'select',
			)
		);
		if ( empty($new_fields) )
			return;
		self::$options['fields'] = self::get_fields_schema( $new_fields );
		add_filter( 'wp_edit_nav_menu_walker', function () {
			return 'UGM_Walker_Nav_Menu_Edit';
		});
		//add_filter( 'xteam_nav_menu_item_additional_fields', array( __CLASS__, '_add_fields' ), 10, 5 );
		add_action( 'save_post', array( __CLASS__, '_save_post' ), 10, 2 );
	}
	static function get_fields_schema( $new_fields ) {
		$schema = array();
		foreach( $new_fields as $name => $field) {
			if (empty($field['name'])) {
				$field['name'] = $name;
			}
			$schema[] = $field;
		}
		return $schema;
	}
	static function get_menu_item_postmeta_key($name) {
		return '_menu_item_' . $name;
	}
	/**
	 * Inject the 
	 * @hook {action} save_post
	 */
	static function get_field( $item, $depth, $args ) {
		$new_fields = '';
		foreach( self::$options['fields'] as $field ) {
			$field['value'] = get_post_meta($item->ID, self::get_menu_item_postmeta_key($field['name']), true);
			$field['id'] = $item->ID;
			$new_fields .= str_replace(
				array_map(function($key){ return '{' . $key . '}'; }, array_keys($field)),
				array_values(array_map('esc_attr', $field)),
				self::$options['item_tpl']
			);
		}
		return $new_fields;
	}
	/**
	 * Save the newly submitted fields
	 * @hook {action} save_post
	 */
	static function _save_post($post_id, $post) {
		if ( $post->post_type !== 'nav_menu_item' ) {
			return $post_id; // prevent weird things from happening
		}
		foreach( self::$options['fields'] as $field_schema ) {
			$form_field_name = 'menu-item-' . $field_schema['name'];
			// @todo FALSE should always be used as the default $value, otherwise we wouldn't be able to clear checkboxes
			if (isset($_POST[$form_field_name][$post_id])) {
				$key = self::get_menu_item_postmeta_key($field_schema['name']);
				$value = stripslashes($_POST[$form_field_name][$post_id]);
				update_post_meta($post_id, $key, $value);
			}
		}
	}
}