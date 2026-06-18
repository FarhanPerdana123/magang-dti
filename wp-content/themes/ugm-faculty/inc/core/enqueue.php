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
		'core/base',
		'core/header',
		'core/hero',
		'core/content',
		'pages/agenda-page',
		'pages/announcement-page',
		'pages/gallery-page',
		'pages/management-page',
		'core/footer',
	);

	foreach ( $css_modules as $css_module ) {
		$module_rel_path = '/assets/css/' . $css_module . '.css';
		$module_handle   = str_replace( '/', '-', $css_module );

		wp_enqueue_style(
			'ugm-style-' . $module_handle,
			get_template_directory_uri() . $module_rel_path,
			array( 'ugm-style', 'bootstrap' ),
			ugm_get_asset_version( $module_rel_path )
		);
	}

	// Enqueue header scroll script.
	wp_enqueue_script(
		'ugm-header-scroll',
		get_template_directory_uri() . '/assets/js/frontend/header-scroll.js',
		array(),
		ugm_get_asset_version( '/assets/js/frontend/header-scroll.js' ),
		true
	);

	// Load landing-page scripts only when the Landing Page template is selected.
	$is_landing_template = is_page_template( array( 'page-templates/template-landing-page.php', 'landing-page' ) );

	if ( $is_landing_template ) {
		wp_enqueue_script(
			'ugm-faculty-slider',
			get_template_directory_uri() . '/assets/js/frontend/faculty-slider.js',
			array(),
			ugm_get_asset_version( '/assets/js/frontend/faculty-slider.js' ),
			true
		);

		wp_enqueue_script(
			'ugm-landing-scroll-reveal',
			get_template_directory_uri() . '/assets/js/frontend/landing-scroll-reveal.js',
			array(),
			ugm_get_asset_version( '/assets/js/frontend/landing-scroll-reveal.js' ),
			true
		);
	}

	$is_video_single = is_singular( 'post' )
		&& has_category( 'video', get_queried_object_id() )
		&& function_exists( 'ugm_get_post_youtube_id' )
		&& ugm_get_post_youtube_id( get_queried_object_id() );

	if ( $is_landing_template || $is_video_single ) {
		wp_enqueue_script(
			'ugm-video-player-guard',
			get_template_directory_uri() . '/assets/js/frontend/video-player-guard.js',
			array(),
			ugm_get_asset_version( '/assets/js/frontend/video-player-guard.js' ),
			true
		);
	}

	$is_announcement_page = is_page()
		&& function_exists( 'ugm_is_announcement_page_template_slug' )
		&& ugm_is_announcement_page_template_slug( get_page_template_slug( get_queried_object_id() ) );

	if ( $is_announcement_page ) {
		wp_enqueue_script(
			'ugm-announcement-slider',
			get_template_directory_uri() . '/assets/js/frontend/announcement-slider.js',
			array(),
			ugm_get_asset_version( '/assets/js/frontend/announcement-slider.js' ),
			true
		);
	}

	if ( is_page_template( array( 'page-templates/template-gallery.php', 'gallery-page' ) ) ) {
		wp_enqueue_script(
			'ugm-gallery-slider',
			get_template_directory_uri() . '/assets/js/frontend/gallery-slider.js',
			array(),
			ugm_get_asset_version( '/assets/js/frontend/gallery-slider.js' ),
			true
		);
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
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
		get_template_directory_uri() . '/assets/js/editor/template-editor-notice.js',
		array( 'wp-data', 'wp-dom-ready', 'wp-i18n', 'wp-notices' ),
		ugm_get_asset_version( '/assets/js/editor/template-editor-notice.js' ),
		true
	);

	if ( 'page' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_script(
		'ugm-landing-page-editor',
		get_template_directory_uri() . '/assets/js/editor/landing-page-editor.js',
		array( 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-data', 'wp-components', 'wp-blocks', 'wp-block-editor' ),
		ugm_get_asset_version( '/assets/js/editor/landing-page-editor.js' ),
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
		get_template_directory_uri() . '/assets/css/editor/landing-page-editor.css',
		array(),
		ugm_get_asset_version( '/assets/css/editor/landing-page-editor.css' )
	);
}
add_action( 'admin_enqueue_scripts', 'ugm_enqueue_editor_assets' );
