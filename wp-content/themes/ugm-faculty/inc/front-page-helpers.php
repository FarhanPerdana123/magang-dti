<?php
/**
 * Helper functions for front-page query building.
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Find first category term by preferred slugs.
 *
 * @param string[] $slugs Category slugs sorted by priority.
 * @return WP_Term|null
 */
function ugm_get_category_root_by_slugs( $slugs ) {
	foreach ( (array) $slugs as $slug ) {
		$term = get_category_by_slug( (string) $slug );
		if ( $term instanceof WP_Term ) {
			return $term;
		}
	}

	return null;
}

/**
 * Get root category ID plus all descendant category IDs.
 *
 * @param int $root_id Root category term ID.
 * @return int[]
 */
function ugm_get_category_tree_ids( $root_id ) {
	$root_id = absint( $root_id );

	if ( $root_id < 1 ) {
		return array();
	}

	$term_ids = array( $root_id );
	$children = get_term_children( $root_id, 'category' );

	if ( ! is_wp_error( $children ) && ! empty( $children ) ) {
		$term_ids = array_merge( $term_ids, array_map( 'absint', $children ) );
	}

	return array_values( array_unique( array_filter( $term_ids ) ) );
}

/**
 * Get uploaded PDF URL for magazine post.
 *
 * Accepts meta value as attachment ID or direct URL.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ugm_get_magazine_pdf_url( $post_id ) {
	$post_id = absint( $post_id );
	if ( $post_id < 1 ) {
		return '';
	}

	$pdf_value = get_post_meta( $post_id, '_ugm_majalah_pdf', true );
	if ( '' === $pdf_value || null === $pdf_value ) {
		// Backward compatibility for previous key.
		$pdf_value = get_post_meta( $post_id, '_ugm_magazine_pdf', true );
	}

	if ( '' === $pdf_value || null === $pdf_value ) {
		return '';
	}

	if ( is_numeric( $pdf_value ) && (int) $pdf_value > 0 ) {
		$url = wp_get_attachment_url( (int) $pdf_value );
		return $url ? esc_url_raw( $url ) : '';
	}

	return esc_url_raw( (string) $pdf_value );
}
