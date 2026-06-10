<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'reduxLegacyCoreRequired' ) ) {
	class reduxLegacyCoreRequired {
		public $parent = null;

		public function __construct( $parent ) {
			$this->parent                   = $parent;
			ReduxLegacy_Functions::$_parent = $parent;

			/**
			 * action 'redux/page/{opt_name}/'
			 */
			do_action( "redux/page/{$parent->args['opt_name']}/" );
		}
	}
}
