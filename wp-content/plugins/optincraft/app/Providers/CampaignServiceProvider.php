<?php

namespace OptinCraft\App\Providers;

defined( "ABSPATH" ) || exit;

use OptinCraft\App\Repositories\CampaignRepository;
use OptinCraft\WpMVC\Contracts\Provider;
use OptinCraft\WpMVC\View\View;

class CampaignServiceProvider implements Provider {
    private CampaignRepository $repository;

    public function __construct( CampaignRepository $repository ) {
        $this->repository = $repository;
    }

    public function boot() {
        add_action( 'wp_head', [ $this, 'load_campaigns' ], 20 );
    }

    public function load_campaigns() {
        //phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( is_admin() || is_customize_preview() || isset( $_GET['elementor-preview'] ) ) {
            return;
        }

        if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
            return;
        }

        $campaigns = $this->repository->get_public_campaigns();
        $content   = '';

        foreach ( $campaigns as $campaign ) {
            $campaign->content = $campaign->content ? json_decode( $campaign->content, true ) : null;
            $this->repository->elements_to_steps( $campaign );
            $content .= View::get( 'campaign', ['campaign' => $campaign] );
        }

        add_action(
            'wp_body_open', function() use ( $content ) {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo $content;
            } 
        );
    }
}