<?php
/**
 * Security hardening for ugm-faculty theme.
 *
 * Removes information leakage and applies WordPress security best practices.
 * None of these changes affect public-facing design or functionality.
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove WordPress version string from <head> and RSS feeds.
 * Exposing version numbers makes targeted attacks easier.
 */
remove_action( 'wp_head', 'wp_generator' );

/**
 * Remove version query string from stylesheet and script URLs.
 * This prevents version fingerprinting via static asset URLs.
 *
 * @param string $src Asset URL.
 * @return string
 */
function ugm_remove_version_from_assets( $src ) {
	if ( strpos( $src, '/wp-content/themes/ugm-faculty/' ) !== false ) {
		return $src;
	}

	if ( strpos( $src, 'ver=' ) !== false ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}
add_filter( 'style_loader_src', 'ugm_remove_version_from_assets', 9999 );
add_filter( 'script_loader_src', 'ugm_remove_version_from_assets', 9999 );

/**
 * Remove X-Pingback header to reduce attack surface.
 *
 * @param array $headers HTTP response headers.
 * @return array
 */
function ugm_remove_x_pingback( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
}
add_filter( 'wp_headers', 'ugm_remove_x_pingback' );

/**
 * Disable XML-RPC pingback method.
 * Full XML-RPC can be disabled via wp-config.php or a security plugin;
 * here we only remove the pingback capability to avoid DDoS amplification.
 *
 * @param array $methods XML-RPC method list.
 * @return array
 */
function ugm_disable_xmlrpc_pingback( $methods ) {
	unset( $methods['pingback.ping'] );
	unset( $methods['pingback.extensions.getPingbacks'] );
	return $methods;
}
add_filter( 'xmlrpc_methods', 'ugm_disable_xmlrpc_pingback' );

/**
 * Remove Really Simple Discovery (RSD) link from <head>.
 * Not needed for modern WordPress installations.
 */
remove_action( 'wp_head', 'rsd_link' );

/**
 * Remove Windows Live Writer manifest link from <head>.
 */
remove_action( 'wp_head', 'wlwmanifest_link' );

/**
 * Remove shortlink from <head>.
 * Reduces unnecessary HTTP requests and <head> clutter.
 */
remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );

/**
 * Disable REST API user enumeration for non-authenticated users.
 * Prevents listing WordPress usernames via /wp-json/wp/v2/users.
 *
 * @param WP_Error|null|true $result Permission result.
 * @return WP_Error|null|true
 */
function ugm_restrict_rest_api_users( $result ) {
	if ( null !== $result ) {
		return $result;
	}

	// Allow authenticated users (admins, editors, etc.) through.
	if ( is_user_logged_in() ) {
		return $result;
	}

	// Block unauthenticated access to the /users endpoint.
	$request_route = isset( $GLOBALS['wp']->query_vars['rest_route'] ) ? $GLOBALS['wp']->query_vars['rest_route'] : '';
	if ( 0 === strpos( $request_route, '/wp/v2/users' ) ) {
		return new WP_Error(
			'rest_forbidden',
			__( 'Tidak diizinkan.', 'ugm-faculty' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	return $result;
}
add_filter( 'rest_authentication_errors', 'ugm_restrict_rest_api_users' );
