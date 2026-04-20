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
 * Resolve asset version using file modification time for cache busting.
 *
 * @param string $relative_path Relative path from theme root.
 * @return string
 */
function ugm_get_asset_version( $relative_path ) {
	$relative_path = ltrim( (string) $relative_path, '/' );
	$absolute_path = get_theme_file_path( $relative_path );

	if ( file_exists( $absolute_path ) ) {
		return (string) filemtime( $absolute_path );
	}

	return UGM_THEME_VERSION;
}

/**
 * Enqueue theme styles and scripts.
 */
function ugm_enqueue_assets() {
	// Enqueue main theme stylesheet.
	wp_enqueue_style(
		'ugm-style',
		get_stylesheet_uri(),
		array(),
		ugm_get_asset_version( 'style.css' )
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

		wp_enqueue_style(
			'ugm-style-' . $css_module,
			get_template_directory_uri() . $module_rel_path,
			array( 'ugm-style' ),
			ugm_get_asset_version( $module_rel_path )
		);
	}

	// Enqueue header scroll script.
	wp_enqueue_script(
		'ugm-header-scroll',
		get_template_directory_uri() . '/assets/js/header-scroll.js',
		array(),
		ugm_get_asset_version( '/assets/js/header-scroll.js' ),
		true
	);

	// Load faculty slider only for the landing front page.
	$is_front_landing = is_front_page()
		&& '1' !== get_query_var( 'ugm_latest_news' )
		&& '1' !== get_query_var( 'ugm_magazine_news' );

	if ( $is_front_landing ) {
		wp_enqueue_script(
			'ugm-faculty-slider',
			get_template_directory_uri() . '/assets/js/faculty-slider.js',
			array(),
			ugm_get_asset_version( '/assets/js/faculty-slider.js' ),
			true
		);
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'ugm_enqueue_assets' );
