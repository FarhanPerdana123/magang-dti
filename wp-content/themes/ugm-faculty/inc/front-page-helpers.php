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
