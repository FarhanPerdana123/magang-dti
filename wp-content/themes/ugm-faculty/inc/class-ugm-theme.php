<?php
/**
 * Theme bootstrap class.
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UGM_Faculty_Theme' ) ) {
	/**
	 * Bootstrap and load theme modules.
	 */
	class UGM_Faculty_Theme {
		/**
		 * Module files loaded from /inc.
		 *
		 * @var string[]
		 */
		protected $module_files = array(
			'theme-setup.php',
			'theme-routes.php',
			'front-page-helpers.php',
			'magazine-meta.php',
			'image-sizes.php',
			'enqueue.php',
			'widgets.php',
			'customizer.php',
			'security.php',
			'landing-page-meta.php',
			'landing-page.php',
			'agenda-page.php',
			'announcement-page.php',
			'gallery-page.php',
			'blocks.php',
		);

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->define_constants();
			$this->load_modules();

			add_action( 'after_setup_theme', array( $this, 'load_translation' ), 5 );
		}

		/**
		 * Define theme constants.
		 */
		protected function define_constants() {
			$theme = wp_get_theme( get_template() );
			$version = $theme ? $theme->get( 'Version' ) : '';
			$this->define_constant( 'UGM_THEME_VERSION', $version ? $version : '1.0.0' );
			$this->define_constant( 'UGM_THEME_NAME', get_template() );

			$this->define_constant( 'UGM_THEME_DIR', get_template_directory() );
			$this->define_constant( 'UGM_THEME_DIR_ASSETS', get_template_directory() . '/assets' );
			$this->define_constant( 'UGM_THEME_DIR_INC', get_template_directory() . '/inc' );

			$this->define_constant( 'UGM_THEME_URI', get_template_directory_uri() );
			$this->define_constant( 'UGM_THEME_URI_ASSETS', get_template_directory_uri() . '/assets' );
			$this->define_constant( 'UGM_THEME_URI_INC', get_template_directory_uri() . '/inc' );
		}

		/**
		 * Define a constant once.
		 *
		 * @param string $name  Constant name.
		 * @param mixed  $value Constant value.
		 */
		protected function define_constant( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Load module files from the inc directory.
		 */
		protected function load_modules() {
			foreach ( $this->module_files as $module_file ) {
				$module_path = UGM_THEME_DIR_INC . '/' . $module_file;

				if ( file_exists( $module_path ) ) {
					require_once $module_path;
				}
			}
		}

		/**
		 * Load theme translation files.
		 */
		public function load_translation() {
			load_theme_textdomain( 'ugm-faculty', UGM_THEME_DIR . '/languages' );
		}
	}
}
