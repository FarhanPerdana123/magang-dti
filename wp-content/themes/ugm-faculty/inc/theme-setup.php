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
}
add_action( 'after_setup_theme', 'ugm_theme_setup' );
