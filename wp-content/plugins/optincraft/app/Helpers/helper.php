<?php

defined( 'ABSPATH' ) || exit;

use OptinCraft\WpMVC\App;
use OptinCraft\WpMVC\Container\Container;
use OptinCraft\App\Fields\Field;
use OptinCraft\App\Repositories\CampaignRepository;
use OptinCraft\App\Repositories\TaskRepository;
use OptinCraft\App\Repositories\ResponseRepository;
use OptinCraft\App\Repositories\AnswerRepository;
use OptinCraft\App\Repositories\SettingsRepository;

function optincraft():App {
    return App::$instance;
}

function optincraft_config( string $config_key ) {
    return optincraft()::$config->get( $config_key );
}

function optincraft_app_config( string $config_key ) {
    return optincraft_config( "app.{$config_key}" );
}

function optincraft_version() {
    return optincraft_app_config( 'version' );
}

function optincraft_container():Container {
    return optincraft()::$container;
}

/**
 * Get a singleton instance from the container.
 *
 * @template T
 * @param class-string<T> $class Class name to resolve.
 * @return T Instance of the requested class.
 */
function optincraft_singleton( string $class ) {
    return optincraft_container()->get( $class );
}

function optincraft_url( string $url = '' ) {
    return optincraft()->get_url( $url );
}

function optincraft_dir( string $dir = '' ) {
    return optincraft()->get_dir( $dir );
}

function optincraft_field_handler( string $field_type ): Field {
    $class = optincraft_config( "fields.{$field_type}.class" );

    if ( ! class_exists( $class ) ) {
        throw new Exception( esc_html__( 'Field handler not found.', 'optincraft' ), 500 );
    }

    return optincraft_singleton( $class );
}

function optincraft_campaign_repository() {
    return optincraft_singleton( CampaignRepository::class );
}

function optincraft_get_campaign_form( int $campaign_id ) {
    $campaign = optincraft_campaign_repository()->get_by_id( $campaign_id );
    $steps    = $campaign->content['steps'] ?? [];

    $form_element = [];

    foreach ( $steps as $step ) {
        if ( ! isset( $step['elements'] ) || ! is_array( $step['elements'] ) ) {
            continue;
        }

        foreach ( $step['elements'] as $element ) {
            if ( $element['type'] === 'form' ) {
                $form_element = $element;
                break 2;
            }
        }
    }

    if ( empty( $form_element ) ) {
        return [];
    }

    $fields = [];

    foreach ( $form_element['attributes']['form_fields'] as $field ) {
        $fields[$field['field_name']] = [
            'label'      => $field['label'],
            'field_type' => $field['field_type'],
            'field_name' => $field['field_name'],
            'required'   => $field['required'] ?? false,
        ];
    }

    return $fields;
}

function optincraft_get_nested_value( string $keys, array $values, $default = null ) {
    $keys = explode( '.', $keys );

    $item = $values;

    foreach ( $keys as $key ) {
        if ( ! isset( $item[ $key ] ) ) {
            return $default;
        }
        $item = $item[$key];
    }
    return $item;
}

function optincraft_response_repository() {
    return optincraft_singleton( ResponseRepository::class );
}

function optincraft_answer_repository() {
    return optincraft_singleton( AnswerRepository::class );
}

function optincraft_task_repository() {
    return optincraft_singleton( TaskRepository::class );
}

function optincraft_settings_repository() {
    return optincraft_singleton( SettingsRepository::class );
}

function optincraft_get_user_ip() {
    foreach ( ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'] as $key ) {
        if ( ! array_key_exists( $key, $_SERVER ) ) {
            continue;
        }
        foreach ( explode( ',', sanitize_text_field( wp_unslash( $_SERVER[$key] ) ) ) as $ip ) {
            $ip = trim( $ip );
            if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
                return $ip;
            }
        }
    }
    return '';
}

function optincraft_get_user_country_code() {
    $user_ip = optincraft_get_user_ip();

    if ( empty( $user_ip ) ) {
        return '';
    }

    // Start session if not already started
    $start_session = false;

    if ( ! session_id() ) {
        $start_session = @session_start();
    }

    // Check if country code is cached in session
    $cache_key = 'optincraft_country_code_' . md5( $user_ip );
    if ( isset( $_SESSION[ $cache_key ] ) ) {
        $country_code = sanitize_text_field( $_SESSION[ $cache_key ] );
        if ( $start_session ) {
            session_write_close();
        }
        return $country_code;
    }

    // If not cached, fetch from API
    $response = wp_remote_get( "https://ipinfo.io/{$user_ip}/json" );

    if ( is_wp_error( $response ) ) {
        if ( $start_session ) {
            session_write_close();
        }
        return '';
    }

    $data         = json_decode( wp_remote_retrieve_body( $response ) );
    $country_code = ! empty( $data->country ) ? strtolower( $data->country ) : '';

    // Cache the country code in session
    if ( ! empty( $country_code ) ) {
        $_SESSION[ $cache_key ] = sanitize_text_field( $country_code );
    }

    if ( $start_session ) {
        session_write_close();
    }

    return $country_code;
}

function optincraft_get_user_infoby_ip( string $ip ) {
    $response = wp_remote_get( "https://ipinfo.io/{$ip}/json" );

    if ( is_wp_error( $response ) ) {
        return [];
    }

    $data = json_decode( wp_remote_retrieve_body( $response ) );

    if ( empty( $data ) ) {
        return [];
    }

    return [
        'country_code' => ! empty( $data->country ) ? strtolower( $data->country ) : '',
        'city'         => ! empty( $data->city ) ? sanitize_text_field( $data->city ) : '',
        'region'       => ! empty( $data->region ) ? sanitize_text_field( $data->region ) : '',
        'postal'       => ! empty( $data->postal ) ? sanitize_text_field( $data->postal ) : '',
        'timezone'     => ! empty( $data->timezone ) ? sanitize_text_field( $data->timezone ) : '',
    ];
}

function optincraft_get_browser_info( WP_REST_Request $request ): array {
    static $optincraft_which_browser_info = null;

    if ( $optincraft_which_browser_info ) {
        return $optincraft_which_browser_info;
    }

    $which_browser = new \OptinCraft\WhichBrowser\Parser( $request->get_header( 'user-agent' ) );
    $browser       = $which_browser->browser;

    if ( $browser ) {
        $optincraft_which_browser_info = [
            'browser'         => $browser->name,
            'browser_version' => $browser->version instanceof \OptinCraft\WhichBrowser\Model\Version ? $browser->version->value : null,
            'device'          => $which_browser->os->name,
        ];

        return $optincraft_which_browser_info;
    }

    return [];
}

function optincraft_process_data_columns( array $columns, array $answers ) {
    $processed_columns = [];

    foreach ( $columns as $column ) {
        $processed_columns[ $column['key'] ] = optincraft_parse_field_element( (string) $column['value'], $answers );
    }

    return $processed_columns;
}

function optincraft_parse_field_element( string $element, array $answers ) {
    return preg_replace_callback(
        '/\{field:([^}]+)\}/',
        function ( $matches ) use ( $answers ) {
            $field_name = trim( $matches[1] );

            if ( ! isset( $answers[ $field_name ] ) ) {
                return null;
            }

            return $answers[ $field_name ]->value;
        },
        $element
    );
}