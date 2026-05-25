<?php
/**
 * Debug script: list registered page templates & template assignments.
 *
 * Akses via browser: http://localhost/magang-be/check-templates.php
 * HAPUS setelah selesai debugging!
 */

define( 'ABSPATH', __DIR__ . '/' );
$_SERVER['HTTP_HOST']   = 'localhost';
$_SERVER['REQUEST_URI'] = '/magang-be/';

require_once __DIR__ . '/wp-load.php';

$theme     = wp_get_theme();
$templates = $theme->get_page_templates();

echo '<pre style="font-family:monospace; font-size:13px; padding:24px;">';
echo "=== Active Theme ===\n";
echo "Name   : " . $theme->get( 'Name' ) . "\n";
echo "Version: " . $theme->get( 'Version' ) . "\n\n";

echo "=== Registered Page Templates ===\n";
if ( empty( $templates ) ) {
	echo "  (none)\n";
} else {
	foreach ( $templates as $file => $name ) {
		echo "  [$file] => $name\n";
	}
}

echo "\n=== Template Assignments (published pages) ===\n";
$pages = get_posts( array(
	'post_type'      => 'page',
	'posts_per_page' => -1,
	'post_status'    => 'publish',
	'orderby'        => 'ID',
	'order'          => 'ASC',
) );

foreach ( $pages as $page ) {
	$tpl = get_page_template_slug( $page->ID );
	echo "  [ID {$page->ID}] {$page->post_title}: " . ( $tpl ?: 'default' ) . "\n";
}

echo "\n=== FSE Templates in DB (wp_template) ===\n";
$fse_templates = get_posts( array(
	'post_type'      => 'wp_template',
	'posts_per_page' => -1,
	'post_status'    => 'publish',
) );
if ( empty( $fse_templates ) ) {
	echo "  (none in DB — using theme files)\n";
} else {
	foreach ( $fse_templates as $t ) {
		echo "  [{$t->post_name}] {$t->post_title}\n";
	}
}

echo '</pre>';
