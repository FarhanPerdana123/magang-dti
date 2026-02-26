<?php
/**
 * Enqueue scripts and styles
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue theme styles and scripts.
 */
function ugm_enqueue_assets() {
	$style_path  = get_stylesheet_directory() . '/style.css';
	$script_path = get_template_directory() . '/assets/js/header-scroll.js';
	$style_ver   = file_exists( $style_path ) ? (string) filemtime( $style_path ) : UGM_THEME_VERSION;
	$script_ver  = file_exists( $script_path ) ? (string) filemtime( $script_path ) : UGM_THEME_VERSION;

	// Enqueue main theme stylesheet.
	wp_enqueue_style(
		'ugm-style',
		get_stylesheet_uri(),
		array(),
		$style_ver
	);

	$css_modules = array(
		'base',
		'header',
		'hero',
		'content',
		'footer',
	);

	foreach ( $css_modules as $css_module ) {
		$module_rel_path = '/assets/css/' . $css_module . '.css';
		$module_abs_path = get_template_directory() . $module_rel_path;
		$module_ver      = file_exists( $module_abs_path ) ? (string) filemtime( $module_abs_path ) : UGM_THEME_VERSION;

		wp_enqueue_style(
			'ugm-style-' . $css_module,
			get_template_directory_uri() . $module_rel_path,
			array( 'ugm-style' ),
			$module_ver
		);
	}

	// Enqueue header scroll script.
	wp_enqueue_script(
		'ugm-header-scroll',
		get_template_directory_uri() . '/assets/js/header-scroll.js',
		array(),
		$script_ver,
		true
	);

	// Enqueue faculty slider script.
	$faculty_slider_path = get_template_directory() . '/assets/js/faculty-slider.js';
	$faculty_slider_ver  = file_exists( $faculty_slider_path ) ? (string) filemtime( $faculty_slider_path ) : UGM_THEME_VERSION;

	wp_enqueue_script(
		'ugm-faculty-slider',
		get_template_directory_uri() . '/assets/js/faculty-slider.js',
		array(),
		$faculty_slider_ver,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'ugm_enqueue_assets' );
