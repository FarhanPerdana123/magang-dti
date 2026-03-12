<?php
/**
 * Plugin Name:       Block Responsive - Make Editor Blocks Responsive Easily
 * Plugin URI:        http://wordpress.org/plugins/block-responsive/
 * Description:       A plugin that provides responsive options for Gutenberg blocks, allowing you to control visibility and styles based on screen size.
 * Version:           1.0.4
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            ashrafuzzaman93
 * Author URI:        https://ashrafuzzaman.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       block-responsive
 *
 * @package BlockResponsive
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Use the necessary namespace.
use Ashraf93\BlockResponsive\BlockResponsive;

// Define constant for the Plugin file.
if ( ! defined( 'BLOCKRESPONSIVE_VERSION' ) ) {
	define( 'BLOCKRESPONSIVE_VERSION', '1.0.4' );
}

if ( ! defined( 'BLOCKRESPONSIVE_DIR_PATH' ) ) {
	define( 'BLOCKRESPONSIVE_DIR_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'BLOCKRESPONSIVE_DIR_URL' ) ) {
	define( 'BLOCKRESPONSIVE_DIR_URL', plugin_dir_url( __FILE__ ) );
}

require_once BLOCKRESPONSIVE_DIR_PATH . 'inc/BlockResponsive.php';

new BlockResponsive();
