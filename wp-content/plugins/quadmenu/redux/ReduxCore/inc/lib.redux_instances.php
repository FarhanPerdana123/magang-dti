<?php

	/**
	 * ReduxFrameworkInstancesLegacy Functions
	 *
	 * @package     Redux_Framework
	 * @subpackage  Core
	 */
if ( ! function_exists( 'get_redux_instance' ) ) {

	/**
	 * Retreive an instance of ReduxFrameworkLegacy
	 *
	 * @param  string $opt_name the defined opt_name as passed in $args
	 *
	 * @return object                ReduxFrameworkLegacy
	 */
	function get_redux_instance( $opt_name ) {
		return ReduxFrameworkInstancesLegacy::get_instance( $opt_name );
	}
}

if ( ! function_exists( 'get_all_redux_instances' ) ) {

	/**
	 * Retreive all instances of ReduxFrameworkLegacy
	 * as an associative array.
	 *
	 * @return array        format ['opt_name' => $ReduxFrameworkLegacy]
	 */
	function get_all_redux_instances() {
		return ReduxFrameworkInstancesLegacy::get_all_instances();
	}
}
