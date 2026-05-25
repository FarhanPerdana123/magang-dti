<?php
/**
 * Debug script: flush WordPress template & block cache/transients.
 *
 * Akses via browser: http://localhost/magang-be/flush-template-cache.php
 * HAPUS setelah selesai debugging!
 */

define( 'ABSPATH', __DIR__ . '/' );
$_SERVER['HTTP_HOST']   = 'localhost';
$_SERVER['REQUEST_URI'] = '/magang-be/';

require_once __DIR__ . '/wp-load.php';

echo '<pre style="font-family:monospace; font-size:13px; padding:24px;">';

// Flush object cache.
wp_cache_flush();
echo "✓ wp_cache_flush()\n";

// Delete all transients yg mungkin di-cache oleh theme.
global $wpdb;
$deleted = $wpdb->query(
	"DELETE FROM {$wpdb->options}
	  WHERE option_name LIKE '_transient_ugm_%'
	     OR option_name LIKE '_transient_timeout_ugm_%'
	     OR option_name LIKE '_transient_ugmbt_%'
	     OR option_name LIKE '_transient_timeout_ugmbt_%'"
);
echo "✓ Deleted $deleted ugm_/ugmbt_ transients\n";

// Flush rewrite rules.
flush_rewrite_rules( false );
echo "✓ flush_rewrite_rules()\n";

// Clean template cache di FSE.
if ( function_exists( 'wp_clean_themes_cache' ) ) {
	wp_clean_themes_cache();
	echo "✓ wp_clean_themes_cache()\n";
}

echo "\n✅ Done! Refresh halaman sekarang.\n";
echo '</pre>';
