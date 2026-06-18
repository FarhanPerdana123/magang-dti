<?php
/**
 * Custom image sizes for ugm-faculty theme.
 *
 * Registers named image sizes used by theme card and hero components.
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add custom image sizes used by the theme.
 */
function ugm_add_image_sizes() {
	// Thumbnail used in news card / archive list.
	add_image_size( 'ugm-card-thumb', 480, 320, true );

	// Image used for the featured/hero single-article banner.
	add_image_size( 'ugm-article-hero', 1200, 560, true );

	// Cover image for majalah digital grid cards (portrait ratio 3:4).
	add_image_size( 'ugm-majalah-cover', 300, 400, true );

	// Wide banner image (e.g. hero background fallback).
	add_image_size( 'ugm-banner-wide', 1600, 600, true );
}
add_action( 'after_setup_theme', 'ugm_add_image_sizes' );

/**
 * Make custom image sizes available in the block editor image size picker.
 *
 * @param array $sizes Existing size labels.
 * @return array
 */
function ugm_custom_image_sizes_labels( $sizes ) {
	return array_merge(
		$sizes,
		array(
			'ugm-card-thumb'    => __( 'UGM — Card Thumbnail (480×320)', 'ugm-faculty' ),
			'ugm-article-hero'  => __( 'UGM — Article Hero (1200×560)', 'ugm-faculty' ),
			'ugm-majalah-cover' => __( 'UGM — Majalah Cover (300×400)', 'ugm-faculty' ),
			'ugm-banner-wide'   => __( 'UGM — Banner Wide (1600×600)', 'ugm-faculty' ),
		)
	);
}
add_filter( 'image_size_names_choose', 'ugm_custom_image_sizes_labels' );
