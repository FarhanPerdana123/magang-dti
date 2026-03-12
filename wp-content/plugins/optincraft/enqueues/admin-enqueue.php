<?php

use OptinCraft\WpMVC\Enqueue\Enqueue;
use OptinCraft\Crafium\OurPlugins\OurPlugins;

defined( 'ABSPATH' ) || exit;

if ( 'optincraft_page_optincraft' === $hook_suffix ) {
    Enqueue::script( 'optincraft', 'build/js/app' );
    Enqueue::style( 'optincraft', 'build/css/app', ['wp-components'] );

    if ( SCRIPT_DEBUG && is_file( optincraft_dir( 'assets/build/runtime.js' ) ) ) {
        Enqueue::script( 'optincraft-runtime', 'build/runtime' );
    }

    Enqueue::style( 'optincraft-dataviews', 'build/css/style-app' );
    Enqueue::style( 'optincraft-elements', 'build/css/elements' );

    wp_enqueue_style( 'wp-block-editor' );
    wp_enqueue_media();
    wp_enqueue_editor();

    $optincraft_settings = optincraft_settings_repository()->get();
    $integrations_status = array_map(
        function( $integration ) {
            return [
                'status'       => $integration['status'],
                'is_connected' => $integration['is_connected'],
            ];
        }, $optincraft_settings['integrations'] 
    );

    $plugins = OurPlugins::enrich_plugins_data( 'optincraft' );

    wp_localize_script(
        'optincraft', 'optincraft_settings', [
            'integrations_status' => $integrations_status,
            'install_nonce'       => wp_create_nonce( 'updates' ),
            'active_plugins'      => [
                'fluent_crm' => defined( 'FLUENTCRM' )
            ],
            'is_edd_active'       => class_exists( 'Easy_Digital_Downloads' ) ? 1 : 0,
            'is_woo_active'       => class_exists( 'WooCommerce' ) ? 1 : 0,
            'plugins'             => $plugins,
        ] 
    );
}
