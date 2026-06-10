<?php

/**
 * Plugin Name:             QuadMenu
 * Plugin URI:              https://quadmenu.com
 * Description:             The best drag & drop WordPress Mega Menu plugin which allow you to create Tabs Menus & Carousel Menus.
 * Version:                 3.3.4
 * Text Domain:             quadmenu
 * Author:                  QuadLayers
 * Author URI:              https://quadlayers.com
 * License:                 GPLv3
 * Domain Path:             /languages
 * Request at least:        4.7.0
 * Tested up to:            6.9
 * Requires PHP:            5.6
 * WC requires at least:    4.0
 * WC tested up to:         10.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

define( 'QUADMENU_PLUGIN_NAME', 'QuadMenu' );
define( 'QUADMENU_PLUGIN_VERSION', '3.3.4' );
define( 'QUADMENU_PLUGIN_FILE', __FILE__ );
define( 'QUADMENU_PLUGIN_DIR', __DIR__ . DIRECTORY_SEPARATOR );
define( 'QUADMENU_PLUGIN_URL', plugin_dir_url( QUADMENU_PLUGIN_FILE ) );
define( 'QUADMENU_PLUGIN_BASENAME', plugin_basename( QUADMENU_PLUGIN_FILE ) );
define( 'QUADMENU_PREFIX', 'quadmenu' );
define( 'QUADMENU_WORDPRESS_URL', 'https://wordpress.org/plugins/quadmenu/' );
define( 'QUADMENU_REVIEW_URL', 'https://wordpress.org/support/plugin/quadmenu/reviews/?filter=5#new-post' );
define( 'QUADMENU_GROUP_URL', 'https://www.facebook.com/groups/quadlayers' );
define( 'QUADMENU_DB_THEME', '_quadmenu_theme' );
define( 'QUADMENU_DB_ITEM', '_menu_item_quadmenu' );
define( 'QUADMENU_DEV', true );
define( 'QUADMENU_COMPILE', true );
// Pro compatibility
define( 'QUADMENU_DOMAIN', 'quadmenu' );
define( 'QUADMENU_PATH', QUADMENU_PLUGIN_DIR );
define( 'QUADMENU_DEMO', 'https://quadmenu.com/demo-corporate/' );

/**
 * Load composer autoload
 */
require_once __DIR__ . '/vendor/autoload.php';
/**
 * Load compatibility
 */
require_once __DIR__ . '/compatibility/old.php';
/**
 * Load vendor_packages packages
 */
require_once __DIR__ . '/vendor_packages/wp-i18n-map.php';
require_once __DIR__ . '/vendor_packages/wp-dashboard-widget-news.php';
require_once __DIR__ . '/vendor_packages/wp-plugin-table-links.php';
require_once __DIR__ . '/vendor_packages/wp-notice-plugin-promote.php';
require_once __DIR__ . '/vendor_packages/wp-plugin-suggestions.php';
require_once __DIR__ . '/vendor_packages/wp-plugin-install-tab.php';
require_once __DIR__ . '/vendor_packages/wp-plugin-feedback.php';
/**
 * Load plugin classes
 */
require_once __DIR__ . '/lib/class-plugin.php';

register_activation_hook( __FILE__, array( 'QuadLayers\\QuadMenu\\Activation', 'activation' ) );

/**
 * Declarate compatibility with WooCommerce Custom Order Tables
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);
