<?php
/**
 * Debug script: simulate exactly what the block render callbacks do,
 * WITH all WordPress hooks/filters active (same as frontend context).
 */
define( 'ABSPATH', 'C:/xampp/htdocs/wordpress/' );
$_SERVER['HTTP_HOST']   = 'localhost';
$_SERVER['REQUEST_URI'] = '/wordpress/';

// Simulate front-page context so all hooks fire identically to frontend.
require 'C:/xampp/htdocs/wordpress/wp-load.php';

// Set up the global post to the landing page (ID=6) ΓÇö same as frontend.
global $post, $wp_the_query, $wp_query;
$landing = get_post( 6 );
$wp_query->is_front_page = true;
$wp_query->is_home       = false;
$wp_query->is_singular   = true;
$wp_query->post          = $landing;
$wp_query->posts         = array( $landing );
$GLOBALS['post']         = $landing;
setup_postdata( $landing );

echo "=== Global \$post: ID={$GLOBALS['post']->ID}, type={$GLOBALS['post']->post_type}, title={$GLOBALS['post']->post_title}\n\n";

echo "=== BERITA TERBARU QUERY (with all hooks) ===\n";
$args1 = array(
	'post_type'           => 'post',
	'posts_per_page'      => 4,
	'ignore_sticky_posts' => true,
	'post_status'         => 'publish',
	'orderby'             => 'date',
	'order'               => 'DESC',
	'no_found_rows'       => true,
);
$q1 = new WP_Query( $args1 );
echo "SQL: " . $q1->request . "\n";
echo "Results (" . count( $q1->posts ) . "):\n";
foreach ( $q1->posts as $p ) {
	echo "  - ID={$p->ID} | type={$p->post_type} | title={$p->post_title}\n";
}

echo "\n=== BERITA AKADEMIK QUERY (pendidikan) ===\n";
$cat = get_category_by_slug( 'pendidikan' );
echo "Category 'pendidikan': " . ( $cat ? "ID={$cat->term_id}" : "NOT FOUND" ) . "\n";
if ( $cat ) {
	$args2 = array(
		'post_type'           => 'post',
		'posts_per_page'      => 3,
		'category__in'        => array( $cat->term_id ),
		'ignore_sticky_posts' => true,
		'post_status'         => 'publish',
		'orderby'             => 'date',
		'order'               => 'DESC',
		'no_found_rows'       => true,
	);
	$q2 = new WP_Query( $args2 );
	echo "SQL: " . $q2->request . "\n";
	echo "Results (" . count( $q2->posts ) . "):\n";
	foreach ( $q2->posts as $p ) {
		echo "  - ID={$p->ID} | type={$p->post_type} | title={$p->post_title}\n";
	}
}

echo "\n=== ACTIVE pre_get_posts FILTERS ===\n";
global $wp_filter;
if ( isset( $wp_filter['pre_get_posts'] ) ) {
	foreach ( $wp_filter['pre_get_posts']->callbacks as $priority => $callbacks ) {
		foreach ( $callbacks as $id => $cb ) {
			$fn = $cb['function'];
			if ( is_array( $fn ) ) {
				$fn = ( is_object( $fn[0] ) ? get_class( $fn[0] ) : $fn[0] ) . '::' . $fn[1];
			} elseif ( $fn instanceof Closure ) {
				$fn = 'Closure';
			}
			echo "  priority={$priority} fn={$fn}\n";
		}
	}
} else {
	echo "  (no pre_get_posts filters registered)\n";
}
