<?php

defined( 'ABSPATH' ) || exit;

use OptinCraft\WpMVC\Enqueue\Enqueue;

$frontend_asset = include optincraft_dir( 'assets/build/js/frontend/app.asset.php' );

wp_enqueue_script( 'wp-api-fetch' );
wp_enqueue_script( 'jquery' );
wp_enqueue_script( 'wp-hooks' );
wp_enqueue_script( "moment" );
wp_enqueue_script( "lodash" );
wp_enqueue_script_module( 'optincraft/frontend', optincraft_url( 'assets/build/js/frontend/app.js' ), $frontend_asset['dependencies'], $frontend_asset['version'] );

Enqueue::style( 'optincraft-elements-style', 'build/css/elements' );
Enqueue::style( 'optincraft-frontend-style', 'build/css/frontend' );