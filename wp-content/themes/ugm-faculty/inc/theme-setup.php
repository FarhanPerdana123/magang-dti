<?php
/**
 * Theme setup functions
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function ugm_theme_setup() {
	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	// Let WordPress manage the document title.
	add_theme_support( 'title-tag' );

	// Enable support for Post Thumbnails on posts and pages.
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'menus' );

	// Switch default core markup to output valid HTML5.
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Add theme support for custom logo.
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	// Register navigation menus.
	register_nav_menus(
		array(
			'menu-1'             => esc_html__( 'Primary', 'ugm-faculty' ),
			'header-quick-links' => esc_html__( 'Header Quick Links', 'ugm-faculty' ),
			'footer-quick-links' => esc_html__( 'Footer Quick Links', 'ugm-faculty' ),
			'mobile-quick-links' => esc_html__( 'Mobile Quick Links', 'ugm-faculty' ),
		)
	);

	// Improve editor/content compatibility with modern blocks.
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	// Enable Full Site Editing (hybrid block theme mode).
	// Allows WordPress to load block templates from /templates/*.html
	// and template parts from /parts/*.html alongside classic PHP templates.
	add_theme_support( 'block-templates' );

	// Load theme CSS inside the block editor canvas so SSR block previews
	// (ServerSideRender) display with the same styling as the frontend.
	add_theme_support( 'editor-styles' );
	add_editor_style( 'style.css' );
	foreach ( array( 'base', 'header', 'hero', 'content', 'agenda-page', 'announcement-page', 'gallery-page', 'management-page', 'footer' ) as $ugm_module ) {
		add_editor_style( 'assets/css/' . $ugm_module . '.css' );
	}
}
add_action( 'after_setup_theme', 'ugm_theme_setup' );

/**
 * Limit excerpt length for consistent card layout.
 *
 * @param int $length Default excerpt word count.
 * @return int
 */
function ugm_custom_excerpt_length( $length ) {
	return 30;
}
add_filter( 'excerpt_length', 'ugm_custom_excerpt_length', 999 );

/**
 * Replace default excerpt ellipsis "..." with a clean "…".
 *
 * @param string $more Default more string.
 * @return string
 */
function ugm_custom_excerpt_more( $more ) {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'ugm_custom_excerpt_more' );
