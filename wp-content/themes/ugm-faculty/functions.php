<?php
/**
 * UGM Faculty Theme - Functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package ugm-faculty
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Define theme version constant
 */
if ( ! defined( 'UGM_THEME_VERSION' ) ) {
	define( 'UGM_THEME_VERSION', '1.0.0' );
}

/**
 * Theme setup and configuration
 */
require get_template_directory() . '/inc/theme-setup.php';

/**
 * Theme custom routes
 */
require get_template_directory() . '/inc/theme-routes.php';

/**
 * Front-page helper utilities
 */
require get_template_directory() . '/inc/front-page-helpers.php';

/**
 * Majalah Digital post type + PDF meta box
 */
require get_template_directory() . '/inc/magazine-meta.php';

/**
 * Enqueue scripts and styles
 */
require get_template_directory() . '/inc/enqueue.php';

/**
 * Widget areas registration
 */
require get_template_directory() . '/inc/widgets.php';

/**
 * Customizer settings
 */
require get_template_directory() . '/inc/customizer.php';
