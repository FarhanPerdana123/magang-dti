<?php
/**
 * Theme routing hooks.
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register custom query vars used by theme routes.
 *
 * @param array $vars Public query vars.
 * @return array
 */
function ugm_register_query_vars( $vars ) {
	$vars[] = 'ugm_latest_news';
	$vars[] = 'ugm_magazine_news';
	return $vars;
}
add_filter( 'query_vars', 'ugm_register_query_vars' );

/**
 * Route latest news listing without requiring Reading settings.
 *
 * @param string $template Current template path.
 * @return string
 */
function ugm_route_latest_news_template( $template ) {
	if ( is_admin() ) {
		return $template;
	}

	if ( '1' !== get_query_var( 'ugm_latest_news' ) ) {
		return $template;
	}

	$latest_news_template = get_theme_file_path( 'page-templates/latest-news.php' );

	if ( file_exists( $latest_news_template ) ) {
		return $latest_news_template;
	}

	return $template;
}
add_filter( 'template_include', 'ugm_route_latest_news_template' );

/**
 * Route digital magazine listing without requiring Reading settings.
 *
 * @param string $template Current template path.
 * @return string
 */
function ugm_route_magazine_news_template( $template ) {
	if ( is_admin() ) {
		return $template;
	}

	if ( '1' !== get_query_var( 'ugm_magazine_news' ) ) {
		return $template;
	}

	$magazine_template = get_theme_file_path( 'page-templates/magazine-news.php' );

	if ( file_exists( $magazine_template ) ) {
		return $magazine_template;
	}

	return $template;
}
add_filter( 'template_include', 'ugm_route_magazine_news_template' );
