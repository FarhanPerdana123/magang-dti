<?php

class UGM_Scripts {

	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ], 9 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 999 );
		add_action( 'wp_footer', [ $this, 'directory_menu_scripts' ], 30 );
	}

	public function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( $screen->is_block_editor ) {
			/* Load fonts */
			$font_combination = get_field( 'ugm_options_look_fonts', 'option' );
			if ( empty( $font_combination ) ) {
				$font_combination = 'lora-opensans';
			}
			$fonts = explode( '-', $font_combination );
			wp_enqueue_style( 'ugm-fonts-' . $fonts[0], UGM_THEME_URI_ASSETS . '/css/fonts/fonts-' . $fonts[0] . '.css' );
			if ( isset( $fonts[1] ) && $fonts[1] !== $fonts[0] ) {
				wp_enqueue_style( 'ugm-fonts-' . $fonts[1], UGM_THEME_URI_ASSETS . '/css/fonts/fonts-' . $fonts[1] . '.css' );
			}
			wp_enqueue_style( 'ugm-theme-editor', UGM_THEME_URI_ASSETS . '/css/admin-editor.css', array(), '1.0.0' );
		}
	}

	public function enqueue_scripts() {
		wp_dequeue_style( 'pojo-a11y' );

		/* Load fonts */
		$font_combination = get_field( 'ugm_options_look_fonts', 'option' );
		if ( empty( $font_combination ) ) {
			$font_combination = 'lora-opensans';
		}
		$fonts = explode( '-', $font_combination );
		wp_enqueue_style( 'ugm-fonts-' . $fonts[0], UGM_THEME_URI_ASSETS . '/css/fonts/fonts-' . $fonts[0] . '.min.css' );
		if ( isset( $fonts[1] ) && $fonts[1] !== $fonts[0] ) {
			wp_enqueue_style( 'ugm-fonts-' . $fonts[1], UGM_THEME_URI_ASSETS . '/css/fonts/fonts-' . $fonts[1] . '.min.css' );
		}

		// /* Set default styles */
		wp_enqueue_style( 'pojo-accessibility', UGM_THEME_URI_ASSETS . '/css/pojo-accessibility.css', array(), '2.0.0' );
		wp_enqueue_style( 'ugm-theme', UGM_THEME_URI_ASSETS . '/css/style.min.css', array(), '2.0.0' );
		if ( is_singular() || is_front_page() ) {
			wp_enqueue_style( 'ugm-theme-gutenberg', UGM_THEME_URI_ASSETS . '/css/style-gutenberg.min.css', array(), '2.0.0' );
		}

		/* Set default JS */
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'bootstrap', UGM_THEME_URI_ASSETS . '/js/bootstrap.min.js', array( 'jquery' ), '3.3.7', true );
		wp_enqueue_script( 'slick', UGM_THEME_URI_ASSETS . '/js/slick.min.js', array( 'jquery' ), '1.6.0', true );
		// wp_enqueue_script( 'cookies-bar', UGM_THEME_URI_ASSETS . '/js/cookies-bar.min.js', array(), null, true );

		/* google maps */
		if (is_page_template('page-templates/contact-page.php')) {
			$api_key = acf_get_setting('google_api_key');
			if (!empty($api_key)) {
				wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key='.acf_get_setting('google_api_key'), array(), null, true );
				wp_enqueue_script( 'google-maps-handler', UGM_THEME_URI_ASSETS . '/js/maps.js', array( 'jquery','google-maps' ), null, true );
			}
		}

		/* match height */
		if (is_page_template('page-templates/directory-double.php')) {
			wp_enqueue_script( 'matchheight', UGM_THEME_URI_ASSETS . '/js/jquery.matchHeight-min.js', array( 'jquery' ), null, true );
		}

		/* directory menu */
		if ((is_page_template('page-templates/directory-single.php') || is_page_template('page-templates/directory-double.php'))) {
			wp_enqueue_script( 'nav', UGM_THEME_URI_ASSETS . '/js/jquery.nav.js', array( 'jquery' ), null, true );
			wp_enqueue_script( 'pin', UGM_THEME_URI_ASSETS . '/js/jquery.pin.min.js', array( 'jquery' ), null, true );
		}

		wp_enqueue_script( 'apps', UGM_THEME_URI_ASSETS . '/js/apps.js', array( 'jquery' ), null, true );
	}

	public function directory_menu_scripts() {
		if ( is_page_template( 'page-templates/directory-double.php' ) ) :
			?>
			<script>
				jQuery(function($){
					$(".directory-item").matchHeight();
				});
			</script>
			<?php
		endif;
		if ( ( is_page_template( 'page-templates/directory-single.php' ) || is_page_template( 'page-templates/directory-double.php' ) ) ) :
			?>
			<script>
				// One Page Nav Sidebar
				jQuery('#sidebar-nav').onePageNav({
					currentClass: 'current',
					changeHash: false,
					scrollSpeed: 500
				});

				// Sidebar Pined
				jQuery("#sidebar aside").pin({
					minWidth: 1024,
					containerSelector: "#directory",
					padding: {top: <?php echo is_admin_bar_showing() ? 62 : 30 ?>, bottom: 30}
				});
			</script>
			<?php
		endif;
	}

}
new UGM_Scripts();