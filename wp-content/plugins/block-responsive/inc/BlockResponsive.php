<?php
/**
 * Main class for blocks responsive.
 *
 * @package BlockResponsive
 */

namespace Ashraf93\BlockResponsive;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BlockResponsive class.
 *
 * @class BlockResponsive The class that manages the block responsive.
 */
class BlockResponsive {
	/**
	 * Constructor for the BlockResponsive class.
	 *
	 * Sets up all the appropriate hooks and actions
	 * within our plugin.
	 *
	 * @return void
	 */
	public function __construct() {
		// Enqueue the block editor assets.
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_assets' ) );
		require_once BLOCKRESPONSIVE_DIR_PATH . 'inc/FrontendEnqueue.php';
	}

	/**
	 * Enqueue scripts and styles for the block editor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		$assets = include BLOCKRESPONSIVE_DIR_PATH . 'build/index.asset.php';

		wp_enqueue_script(
			'blockresponsive_editor_scripts',
			BLOCKRESPONSIVE_DIR_URL . 'build/index.js',
			$assets['dependencies'],
			$assets['version'],
			true
		);

		wp_enqueue_style(
			'blockresponsive_editor_styles',
			BLOCKRESPONSIVE_DIR_URL . 'build/index.css',
			array(),
			$assets['version']
		);
	}
}
