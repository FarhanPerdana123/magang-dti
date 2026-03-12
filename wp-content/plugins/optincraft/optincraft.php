<?php

defined( 'ABSPATH' ) || exit;

use OptinCraft\WpMVC\App;
use OptinCraft\Database\Setup;

/**
 * Plugin Name:       OptinCraft
 * Description:       The Powerful Drag & Drop Popup Builder for WordPress
 * Version:           1.0.2
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Tested up to:      6.9
 * Author:            Crafium
 * Author URI:        https://crafium.com
 * License:           GPL v3 or later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       optincraft
 * Domain Path:       /languages
 */

require_once __DIR__ . '/vendor/vendor-src/autoload.php';
require_once __DIR__ . '/app/Helpers/helper.php';

define( 'OPTINCRAFT_DEPENDENCY_VERSION', '0.1.0' );

final class OptinCraft
{
    public static OptinCraft $instance;

    public static function instance(): OptinCraft {
        if ( empty( self::$instance ) ) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function load() {
        // Run Activation Tasks
        register_activation_hook(
            __FILE__, function() {
                $plugin_activate_time = get_option( 'optincraft_activate_time' );

                if ( ! $plugin_activate_time ) {
                    add_option( 'optincraft_activate_time', time() );
                }

                ( new Setup )->execute();
                add_option( 'optincraft_activation_redirect', true );
            } 
        );

        // Run Deactivation Tasks
        register_deactivation_hook(
            __FILE__, function() {
                $this->deactivation_tasks();
            } 
        );

        $application = App::instance();

        $application->boot( __FILE__, __DIR__ );

        $client = new \OptinCraft\Appsero\Client( '115e90bc-7e45-431c-860f-1cbc7dea7363', 'OptinCraft', __FILE__ );
        $client->insights()->init();

        /**
         * Fires once activated plugins have loaded.
         *
         */
        add_action(
            'plugins_loaded', function () use ( $application ): void {
                $application->load();
            }
        );
    }

    public function deactivation_tasks() {
        wp_clear_scheduled_hook( 'optincraft_daily_cron' );
    }
}

OptinCraft::instance()->load();
