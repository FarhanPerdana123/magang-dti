<?php

/**
 * Plugin Name: Easy Tabs Block – Fast & Responsive Tabs with Built-in Smooth Accordion
 * Plugin URI: https://easytabsblock.com/
 * Description: Create beautiful, responsive tabs in Gutenberg, no code needed. Choose a pattern, style it your way, and keep your content organized and clean.
 * Version: 1.0.8
 * Author: Easy Tabs Block
 * Author URI: https://easytabsblock.com/
 * Text Domain: easy-tabs-block
 * Domain Path: /languages/
 * License: GPLv2 or later
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Easy Tabs Block Plugin Class
 */
class Easy_Tabs_Block_Plugin {

    /**
     * Constructor.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        // Load admin notice handler
        $this->load_admin_notice();
        
        // Load plugin text domain for translations
        add_action( 'plugins_loaded', [ $this, 'init_hooks' ] );
    }

    /**
     * Load and initialize admin notice handler.
     *
     * @since 1.0.6
     *
     * @return void
     */
    private function load_admin_notice() {
        require_once __DIR__ . '/inc/class-admin-notice.php';
        new Easy_Tabs_Block_Admin_Notice();
    }

    /**
     * Initialize hooks.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init_hooks() {
        add_action( 'init', [ $this, 'block_init' ] );
        add_action( 'rest_api_init', [ $this, 'api_init' ] );
        add_action( 'enqueue_block_editor_assets', [ $this, 'localize_editor_data' ] );
    }

    /**
     * Localize editor data for JavaScript.
     *
     * @since 1.0.7
     *
     * @return void
     */
    public function localize_editor_data() {
        wp_add_inline_script(
            'easy-tabs-block-tabs-editor-script',
            'window.easyTabsBlockPlugin = ' . wp_json_encode( [
                'isProActive' => defined( 'ETBP_VERSION' ),
            ] ) . ';',
            'before'
        );
    }

    /**
     * Registers the block using the metadata loaded from the `block.json` file.
     * Behind the scenes, it registers also all assets so they can be enqueued
     * through the block editor in the corresponding context.
     *
     * @since 1.0.0
     *
     * @see   https://developer.wordpress.org/reference/functions/register_block_type/
     *
     * @return void
     */
    public function block_init() {

        /**
         * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
         * based on the registered block metadata.
         */
        if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
            wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );

            return;
        }

        /**
         * Registers the block(s) metadata from the `blocks-manifest.php` file.
         */
        if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
            wp_register_block_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
        }

        /**
         * Registers the block type(s) in the `blocks-manifest.php` file.
         */
        $manifest_data = require __DIR__ . '/build/blocks-manifest.php';
        foreach ( array_keys( $manifest_data ) as $block_type ) {
            register_block_type( __DIR__ . "/build/{$block_type}" );
        }
    }

    /**
     * Register REST route for fetching patterns.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function api_init() {
        register_rest_route(
            'etb/v1', '/patterns', [
             'methods'             => 'GET',
             'callback'            => [ $this, 'get_patterns' ],
             'permission_callback' => function () {
                return current_user_can( 'edit_posts' );
             },
          ]
        );
    }

    /**
     * Callback function to get patterns from the JSON file
     */
    public function get_patterns() {
        // Path to patterns.json file in your plugin directory
        $patterns_file = plugin_dir_path( __FILE__ ) . '/patterns/patterns.json';

        // Check if file exists
        if ( ! file_exists( $patterns_file ) ) {
            return new \WP_Error( 'patterns_not_found', __( 'Patterns file not found', 'easy-tabs-block' ), [ 'status' => 404 ] );
        }

        // Read the file
        $patterns_json = file_get_contents( $patterns_file );

        // Decode the JSON
        $patterns = json_decode( $patterns_json, true );

        // Return the response
        return rest_ensure_response( $patterns );
    }
}

new Easy_Tabs_Block_Plugin();
