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

	$theme_version = defined( 'UGM_THEME_VERSION' ) ? UGM_THEME_VERSION : wp_get_theme()->get( 'Version' );

	return (string) $theme_version;
}

/**
 * Enqueue theme styles and scripts.
 */
function ugm_enqueue_assets() {
	// Bootstrap 5 (fondasi CSS) — dimuat sebelum CSS kustom.
	// Kelas Bootstrap langsung bisa dipakai di seluruh tema.
	// CSS kustom tetap menimpa Bootstrap di mana diperlukan.
	wp_enqueue_style(
		'bootstrap',
		'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
		array(),
		'5.3.3'
	);

	wp_enqueue_script(
		'bootstrap-bundle',
		'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
		array(),
		'5.3.3',
		true
	);

	// Main theme stylesheet (bergantung pada Bootstrap).
	wp_enqueue_style(
		'ugm-style',
		get_stylesheet_uri(),
		array( 'bootstrap' ),
		ugm_get_asset_version( 'style.css' )
	);

	$css_modules = array(
		'base',
		'header',
		'hero',
		'content',
		'agenda-page',
		'announcement-page',
		'gallery-page',
		'footer',
	);

	foreach ( $css_modules as $css_module ) {
		$module_rel_path = '/assets/css/' . $css_module . '.css';

		wp_enqueue_style(
			'ugm-style-' . $css_module,
			get_template_directory_uri() . $module_rel_path,
			array( 'ugm-style', 'bootstrap' ),
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

	// Enqueue scroll reveal script.
	$scroll_reveal_path = get_template_directory() . '/assets/js/scroll-reveal.js';
	$scroll_reveal_ver  = file_exists( $scroll_reveal_path ) ? (string) filemtime( $scroll_reveal_path ) : UGM_THEME_VERSION;

	wp_enqueue_script(
		'ugm-scroll-reveal',
		get_template_directory_uri() . '/assets/js/scroll-reveal.js',
		array(),
		$scroll_reveal_ver,
		true
	);

	// Load faculty slider on the front page and on pages using the landing template.
	$is_front_landing    = is_front_page()
		&& '1' !== get_query_var( 'ugm_latest_news' )
		&& '1' !== get_query_var( 'ugm_magazine_news' );
	$is_landing_template = is_page_template( 'page-templates/template-landing-page.php' );

	if ( $is_front_landing || $is_landing_template ) {
		wp_enqueue_script(
			'ugm-faculty-slider',
			get_template_directory_uri() . '/assets/js/faculty-slider.js',
			array(),
			ugm_get_asset_version( '/assets/js/faculty-slider.js' ),
			true
		);

		wp_enqueue_script(
			'ugm-landing-scroll-reveal',
			get_template_directory_uri() . '/assets/js/landing-scroll-reveal.js',
			array(),
			ugm_get_asset_version( '/assets/js/landing-scroll-reveal.js' ),
			true
		);
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	$template_slug = is_singular() ? get_page_template_slug( get_queried_object_id() ) : '';

	// Muat CSS khusus halaman Berita Terbaru.
	if (
		is_page_template( 'page-templates/berita-terbaru.php' ) ||
		'berita-terbaru' === $template_slug ||
		'page-templates/berita-terbaru.php' === $template_slug
	) {
		wp_enqueue_style(
			'ugm-berita-terbaru',
			get_template_directory_uri() . '/assets/css/berita-terbaru.css',
			array( 'ugm-style' ),
			ugm_get_asset_version( '/assets/css/berita-terbaru.css' )
		);
	}

	if ( is_singular( 'post' ) && in_array( $template_slug, array( 'single-berita', 'single-berita.php' ), true ) ) {
		wp_enqueue_style(
			'ugm-single-berita',
			get_template_directory_uri() . '/assets/css/single-berita.css',
			array( 'ugm-style' ),
			ugm_get_asset_version( '/assets/css/single-berita.css' )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'ugm_enqueue_assets' );

/**
 * Enqueue Gutenberg editor plugin for the Landing Page template.
 *
 * Only loaded on page edit / new-page admin screens.
 * Adds a sidebar panel + full-screen preview overlay so admins can see
 * the rendered template without leaving the block editor.
 */
function ugm_enqueue_editor_assets() {
	$screen = get_current_screen();
	if ( ! $screen || ! $screen->is_block_editor() ) {
		return;
	}

	wp_enqueue_script(
		'ugm-template-editor-notice',
		get_template_directory_uri() . '/assets/js/template-editor-notice.js',
		array( 'wp-data', 'wp-dom-ready', 'wp-i18n', 'wp-notices' ),
		ugm_get_asset_version( '/assets/js/template-editor-notice.js' ),
		true
	);

	if ( 'page' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_script(
		'ugm-landing-page-editor',
		get_template_directory_uri() . '/assets/js/landing-page-editor.js',
		array( 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-data', 'wp-components', 'wp-blocks', 'wp-block-editor' ),
		ugm_get_asset_version( '/assets/js/landing-page-editor.js' ),
		true
	);

	wp_localize_script(
		'ugm-landing-page-editor',
		'ugmLandingPageEditor',
		array(
			'defaultBlocks' => function_exists( 'ugm_get_default_landing_page_blocks' )
				? ugm_get_default_landing_page_blocks()
				: '',
		)
	);

	wp_enqueue_style(
		'ugm-landing-page-editor',
		get_template_directory_uri() . '/assets/css/landing-page-editor.css',
		array(),
		ugm_get_asset_version( '/assets/css/landing-page-editor.css' )
	);
}
add_action( 'admin_enqueue_scripts', 'ugm_enqueue_editor_assets' );

/**
 * Enqueue berita-terbaru.css ke dalam block editor
 * sehingga ServerSideRender preview tampil dengan styling yang benar.
 */
function ugm_enqueue_berita_terbaru_editor_style() {
	// Hanya load jika ini adalah block editor untuk halaman (page).
	$screen = get_current_screen();
	if ( ! $screen || ! $screen->is_block_editor() || 'page' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_style(
		'ugm-berita-terbaru',
		get_template_directory_uri() . '/assets/css/berita-terbaru.css',
		array(),
		ugm_get_asset_version( '/assets/css/berita-terbaru.css' )
	);
}
add_action( 'admin_enqueue_scripts', 'ugm_enqueue_berita_terbaru_editor_style' );

/**
 * Juga daftarkan ke enqueue_block_editor_assets untuk iframe editor (WP 6.3+).
 */
function ugm_enqueue_berita_terbaru_block_editor_style() {
	wp_enqueue_style(
		'ugm-berita-terbaru-editor',
		get_template_directory_uri() . '/assets/css/berita-terbaru.css',
		array(),
		ugm_get_asset_version( '/assets/css/berita-terbaru.css' )
	);

	wp_enqueue_style(
		'ugm-single-berita-editor',
		get_template_directory_uri() . '/assets/css/single-berita.css',
		array(),
		ugm_get_asset_version( '/assets/css/single-berita.css' )
	);
}
add_action( 'enqueue_block_editor_assets', 'ugm_enqueue_berita_terbaru_block_editor_style' );
