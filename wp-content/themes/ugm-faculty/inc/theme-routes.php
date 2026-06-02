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
	$vars[] = 'ugm_berita_terbaru';
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

/**
 * Route halaman yang menggunakan block template "berita-terbaru"
 * ke PHP template page-templates/berita-terbaru.php.
 *
 * @param string $template Current template path.
 * @return string
 */
function ugm_route_berita_terbaru_template( $template ) {
	if ( is_admin() ) {
		return $template;
	}

	// Ambil queried object — bisa page atau post.
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		$post = get_post();
	}
	if ( ! $post instanceof WP_Post ) {
		return $template;
	}

	$template_meta = (string) get_post_meta( $post->ID, '_wp_page_template', true );

	$is_berita = (
		'berita-terbaru' === $template_meta ||
		'page-templates/berita-terbaru.php' === $template_meta
	);

	if ( ! $is_berita ) {
		return $template;
	}

	$php_template = get_theme_file_path( 'page-templates/berita-terbaru.php' );
	if ( file_exists( $php_template ) ) {
		return $php_template;
	}

	return $template;
}
add_filter( 'template_include', 'ugm_route_berita_terbaru_template', 1 );

/**
 * Fallback via template_redirect — lebih reliable untuk block themes (WP 6.x+).
 * Fires BEFORE WordPress memilih template, termasuk sebelum block template renderer.
 * Include PHP template langsung dan exit untuk membypass block rendering system.
 */
add_action(
	'template_redirect',
	static function () {
		if ( is_admin() ) return;

		$post = get_queried_object();
		if ( ! $post instanceof WP_Post ) {
			$post = get_post();
		}
		if ( ! $post instanceof WP_Post ) return;

		$template_meta = (string) get_post_meta( $post->ID, '_wp_page_template', true );

		$is_berita = (
			'berita-terbaru' === $template_meta ||
			'page-templates/berita-terbaru.php' === $template_meta
		);

		if ( ! $is_berita ) return;

		$php_template = get_theme_file_path( 'page-templates/berita-terbaru.php' );
		if ( file_exists( $php_template ) ) {
			// Setup global $post agar template berjalan dengan benar.
			global $wp_query;
			$wp_query->is_page       = true;
			$wp_query->is_singular   = true;
			$wp_query->is_home       = false;
			$wp_query->is_archive    = false;
			$wp_query->queried_object    = $post;
			$wp_query->queried_object_id = $post->ID;

			include $php_template;
			exit; // Hentikan WordPress dari memuat template lain.
		}
	},
	1
);

/**
 * Auto-fix: saat halaman disimpan dengan block template slug 'berita-terbaru',
 * langsung konversi ke PHP template slug agar routing bekerja tanpa manual fix.
 */
add_action(
	'save_post_page',
	static function ( $post_id ) {
		// Hindari infinite loop dan auto-save.
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) return;

		$meta = get_post_meta( $post_id, '_wp_page_template', true );
		if ( 'berita-terbaru' === $meta ) {
			// Konversi block template slug ke PHP template slug.
			update_post_meta( $post_id, '_wp_page_template', 'page-templates/berita-terbaru.php' );
		}
	},
	20
);

/**
 * Route posts using the "Berita Detail" template to the PHP template so the
 * frontend uses the complete theme header/navigation.
 *
 * @param string $template Current template path.
 * @return string
 */
function ugm_route_single_berita_template( $template ) {
	if ( is_admin() || ! is_singular( 'post' ) ) {
		return $template;
	}

	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		$post = get_post();
	}
	if ( ! $post instanceof WP_Post ) {
		return $template;
	}

	$template_meta = (string) get_post_meta( $post->ID, '_wp_page_template', true );
	if ( ! in_array( $template_meta, array( 'single-berita', 'single-berita.php' ), true ) ) {
		return $template;
	}

	$php_template = get_theme_file_path( 'single-berita.php' );
	if ( file_exists( $php_template ) ) {
		return $php_template;
	}

	return $template;
}
add_filter( 'template_include', 'ugm_route_single_berita_template', 1 );

add_action(
	'template_redirect',
	static function () {
		if ( is_admin() || ! is_singular( 'post' ) ) return;

		$post = get_queried_object();
		if ( ! $post instanceof WP_Post ) {
			$post = get_post();
		}
		if ( ! $post instanceof WP_Post ) return;

		$template_meta = (string) get_post_meta( $post->ID, '_wp_page_template', true );
		if ( ! in_array( $template_meta, array( 'single-berita', 'single-berita.php' ), true ) ) return;

		$php_template = get_theme_file_path( 'single-berita.php' );
		if ( file_exists( $php_template ) ) {
			include $php_template;
			exit;
		}
	},
	1
);

