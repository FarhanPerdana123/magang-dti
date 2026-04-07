<?php

require_once ABSPATH . 'wp-admin/includes/nav-menu.php';

class UGM_Walker_Nav_Menu_Edit extends Walker_Nav_Menu_Edit {
	function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
		$item_output = '';
		parent::start_el($item_output, $item, $depth, $args, $id);
		// Inject $new_fields before: <div class="menu-item-actions description-wide submitbox">
		if ( $depth == 0 && $new_fields = UGM_Nav_Menu_Item_Custom_Fields::get_field( $item, $depth, $args ) ) {
			$item_output = preg_replace('/(?=<div[^>]+class="[^"]*submitbox)/', $new_fields, $item_output);
		}
		$output .= $item_output;
	}
}