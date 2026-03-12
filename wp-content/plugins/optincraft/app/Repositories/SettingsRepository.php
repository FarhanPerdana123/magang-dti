<?php

namespace OptinCraft\App\Repositories;

defined( "ABSPATH" ) || exit;

use OptinCraft\WpMVC\Helpers\Helpers;

class SettingsRepository {
    protected array $default_settings = [
        "integrations"            => [
            "email_notification" => [
                "status"       => true,
                "is_connected" => true
            ],
            "webhook"            => [
                "status"       => false,
                "is_connected" => true,
            ],
            "google_sheet"       => [
                "status"       => false,
                "is_connected" => false,
                "auth_data"    => [],
            ],
            "mailchimp"          => [
                "status"       => false,
                "is_connected" => false,
                "auth_data"    => [],
            ],
            "zoho_crm"           => [
                "status"       => false,
                "is_connected" => false,
                "api_key"      => "",
                "auth_data"    => [],
            ],
            "zapier"             => [
                "status"       => false,
                "is_connected" => true,
                "api_key"      => "",
                "auth_data"    => [],
            ],
            "fluent_crm"         => [
                "status"       => false,
                "is_connected" => true,
                "auth_data"    => [],
            ],
        ],
        "analytics_data_duration" => "forever"
    ];

    public function get() {
        $settings_cache = wp_cache_get( 'settings', 'optincraft' );

        if ( is_array( $settings_cache ) ) {
            return $settings_cache;
        }

        $settings = Helpers::array_merge_deep( $this->default_settings, get_option( 'optincraft_settings', [] ) );
    
        wp_cache_add( 'settings', $settings, 'optincraft', 3600 );

        return $settings;
    }

    public function get_by_key( string $key, $default = null ) {
        $settings = $this->get();
        return optincraft_get_nested_value( $key, $settings, $default );
    }

    public function update_settings( array $settings ) {
        return $this->save( array_merge( $this->get(), $settings ) );
    }

    public function update_setting( string $key, $value ) {
        if ( is_array( $value ) ) {
            $value = map_deep( $value, 'sanitize_text_field' );
        } else {
            $value = sanitize_text_field( $value );
        }

        $key            = sanitize_text_field( $key );
        $settings       = $this->get();
        $settings[$key] = $value;

        return $this->save( $settings );
    }

    protected function save( array $settings ) {
        $update = update_option( 'optincraft_settings', map_deep( $settings, 'sanitize_text_field' ) );
        wp_cache_delete( 'settings', 'optincraft' );
        return $update;
    }
}