<?php
/**
 * Front page template loader.
 *
 * WordPress always loads front-page.php for the static front page, which
 * bypasses any page template selected on the page itself. This loader checks
 * whether the page-on-front has a custom page template assigned; if so, that
 * template is loaded instead, allowing users to pick e.g. "Halaman Landing"
 * and have it appear at the site root.
 *
 * @package ugm-faculty
 */

$_ugm_page_on_front  = (int) get_option( 'page_on_front' );
$_ugm_front_tpl_slug = $_ugm_page_on_front > 0
	? (string) get_page_template_slug( $_ugm_page_on_front )
	: '';

if ( 'landing-page' === $_ugm_front_tpl_slug ) {
	require get_theme_file_path( 'page-templates/template-landing-page.php' );
	return;
}

if ( '' !== $_ugm_front_tpl_slug ) {
	$_ugm_custom_tpl = get_theme_file_path( $_ugm_front_tpl_slug );

	if ( file_exists( $_ugm_custom_tpl ) ) {
		require $_ugm_custom_tpl;
		return;
	}
}

// Default: load the built-in front-page design.
require get_theme_file_path( 'templates/front-page.php' );
