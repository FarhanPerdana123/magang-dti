<?php

if ( ! class_exists( 'ReduxLegacy_Validation_str_replace' ) ) {
	class ReduxLegacy_Validation_str_replace {

		/**
		 * Field Constructor.
		 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
		 *
		 * @since ReduxFrameworkLegacy 1.0.0
		 */
		function __construct( $parent, $field, $value, $current ) {

			$this->parent  = $parent;
			$this->field   = $field;
			$this->value   = $value;
			$this->current = $current;

			$this->validate();
		} //function

		/**
		 * Field Render Function.
		 * Takes the vars and validates them
		 *
		 * @since ReduxFrameworkLegacy 1.0.0
		 */
		function validate() {

			$this->value = str_replace( $this->field['str']['search'], $this->field['str']['replacement'], $this->value );
		} //function
	} //class
}
