<?php

namespace OptinCraft\App\Providers\Admin;

defined( 'ABSPATH' ) || exit;

use OptinCraft\WpMVC\Contracts\Provider;

class MenuServiceProvider implements Provider
{
    const PRO_URL = 'https://crafium.com/optincraft?utm_source=plugin&utm_medium=optincraft_plugin&utm_campaign=upgrade_to_pro';

    public function boot() {
        add_action( 'admin_menu', [$this, 'action_admin_menu'] );
        add_action( 'admin_head', [ $this, 'action_admin_head' ] );
        add_action( 'admin_init', [$this, 'plugin_activation_redirect'] );
    }

    public function action_admin_menu() {
        $page_url = admin_url( 'admin.php?page=optincraft' );
        $icon_dir = optincraft_dir( 'assets/svg/sidebar-icon.svg' );
        //phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        $icon = file_get_contents( $icon_dir );
        $icon = 'data:image/svg+xml;base64,' . base64_encode( $icon );

        add_menu_page( "OptinCraft", 'OptinCraft', 'manage_options', 'optincraft-menu', function () { }, $icon, 5 );
        add_submenu_page( 'optincraft-menu', esc_html__( 'Analytics', 'optincraft' ), esc_html__( 'Analytics', 'optincraft' ), 'manage_options', 'optincraft', [$this, 'content'] );
        add_submenu_page( 'optincraft-menu', esc_html__( 'Campaigns', 'optincraft' ), esc_html__( 'Campaigns', 'optincraft' ), 'manage_options', $page_url . '#/campaigns' );
        add_submenu_page( 'optincraft-menu', esc_html__( 'Responses', 'optincraft' ), esc_html__( 'Responses', 'optincraft' ), 'manage_options', $page_url . '#/responses' );
        add_submenu_page( 'optincraft-menu', esc_html__( 'Integrations', 'optincraft' ), esc_html__( 'Integrations', 'optincraft' ), 'manage_options', $page_url . '#/integrations' );
        add_submenu_page( 'optincraft-menu', esc_html__( 'Settings', 'optincraft' ), esc_html__( 'Settings', 'optincraft' ), 'manage_options', $page_url . '#/settings' );
        add_submenu_page( 'optincraft-menu', esc_html__( 'Our Plugins', 'optincraft' ), "<span class='optincraft-our-plugins-menu-item'>" . esc_html__( 'Our Plugins', 'optincraft' ) . "</span>", 'manage_options', $page_url . '#/our-plugins' );

        if ( ! function_exists( 'optincraft_pro' ) ) {
            add_submenu_page( 'optincraft-menu', esc_html__( 'Upgrade to Pro', 'optincraft' ), "<span class='optincraft-pro-menu-item'>" . esc_html__( 'Upgrade to Pro', 'optincraft' ) . "</span>", 'manage_options', self::PRO_URL );
        }

        remove_submenu_page( 'optincraft-menu', 'optincraft-menu' );
    }

    /**
     * Loading menu activation js code only optincraft admin page
     */
    public function action_admin_head() : void {
        ?>
        <style>
            #toplevel_page_optincraft-menu li:has(.optincraft-pro-menu-item) a{
                color: #ffffff !important;
                background: #10AC84 !important;
            }
            #toplevel_page_optincraft-menu li:has(.optincraft-our-plugins-menu-item) a{
                color: #2ca617 !important;
                font-weight: 700 !important;
            }
            #toplevel_page_optincraft-menu .current:has(.optincraft-our-plugins-menu-item) a{
                color: #2bc311 !important;
            }
        </style>
        <?php
    }

    public function content() {
        echo '<div class="optincraft-root"></div>';
    }

    public function plugin_activation_redirect() {
        $redirect        = get_option( 'optincraft_activation_redirect', false );
        $should_redirect = apply_filters( 'optincraft_redirect_after_activation', $redirect );

        if ( ! $should_redirect ) {
            if ( $redirect ) {
                delete_option( 'optincraft_activation_redirect' );
            }
            return;
        }

        delete_option( 'optincraft_activation_redirect' );

        //phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( ! isset( $_GET['activate-multi'] ) ) { // Prevent redirect if multiple plugins are activated
            wp_safe_redirect( admin_url( 'admin.php?page=optincraft' ) );
            exit;
        }
    }
}