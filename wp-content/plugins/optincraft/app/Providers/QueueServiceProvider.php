<?php

namespace OptinCraft\App\Providers;

defined( "ABSPATH" ) || exit;

use OptinCraft\WpMVC\Contracts\Provider;
use OptinCraft\App\Jobs\Queue;
use stdClass;

class QueueServiceProvider implements Provider {
    public Queue $background_process;

    public function __construct() {
        $this->background_process = optincraft_singleton( Queue::class );
    }

    public function boot() {
        add_action( 'optincraft_after_create_response', [$this, 'add_queue_data'], 10, 2 );
        add_action( 'optincraft_rest_response_action', [$this, 'cleanup_cache'] );
    }

    public function add_queue_data( int $response_id, stdClass $campaign ) {
        global $wpdb;

        //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}optincraft_queues (campaign_id, response_id, task_id, status, queue_type) SELECT t.campaign_id,
                %s AS response_id,
                t.id AS task_id,
                'in_queue' AS status,
                'response' AS queue_type
                FROM {$wpdb->prefix}optincraft_tasks AS t WHERE t.status = 1 AND t.campaign_id = %s", $response_id, $campaign->id 
            )
        );
    }

    public function cleanup_cache( \WP_REST_Request $request ) {
        if ( in_array( $request->get_method(), ['POST', 'PUT', 'PATCH', 'DELETE'] ) ) {
            $this->clear_all_caches_after_db_operation();
        }
    }

    protected function clear_all_caches_after_db_operation() {
        // Clear WordPress object cache
        wp_cache_flush();

        // W3 Total Cache
        if ( function_exists( 'w3tc_flush_all' ) ) {
            w3tc_flush_all();
        }
        
        // WP Super Cache
        if ( function_exists( 'wp_cache_clear_cache' ) ) {
            wp_cache_clear_cache();
        }
        
        // WP Rocket
        if ( function_exists( 'rocket_clean_domain' ) ) {
            rocket_clean_domain();
        }

        // LiteSpeed Cache
        if ( class_exists( '\LiteSpeed\Purge' ) ) {
            \LiteSpeed\Purge::purge_all();
        }
    }
}
