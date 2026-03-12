<?php

namespace OptinCraft\Database;

defined( 'ABSPATH' ) || exit;

use OptinCraft\App\Enums\Campaign\OpenEvent as CampaignOpenEvent;
use OptinCraft\App\Enums\Campaign\Type as CampaignType;
use OptinCraft\WpMVC\Database\Schema\Schema;
use OptinCraft\WpMVC\Database\Schema\Blueprint;

class Setup {
    public function execute() {
        // -----------------------------
        // Table prefix
        // -----------------------------
        $prefix = 'optincraft_';

        // -----------------------------
        // 1. Campaigns Table
        // -----------------------------
        Schema::create(
            $prefix . 'campaigns', function( Blueprint $table ) {
                $table->big_increments( 'id' );
                $table->string( 'title', 255 );
                $table->long_text( 'description' )->nullable();
                $table->json( 'content' )->nullable();

                $table->enum( 'type', CampaignType::get_all() )->default( CampaignType::POPUP );
                $table->enum( 'open_event', CampaignOpenEvent::get_all() )->default( CampaignOpenEvent::ON_LOAD );

                $table->json( 'display_conditions' )->nullable();
                $table->json( 'device_visibility' )->nullable();

                $table->timestamp( 'start_date' )->nullable();
                $table->timestamp( 'end_date' )->nullable();
                $table->decimal( 'budget', 12, 2 )->nullable();
                $table->tiny_integer( 'status' )->default( 0 );

                $table->enum( 'geolocation_action', ['show', 'hide'] )->default( 'show' );
                $table->json( 'geolocation_countries' )->nullable();

                $table->timestamps();
            }
        );

        // -----------------------------
        // 2. Page Views Table (Global, not campaign-specific)
        // -----------------------------
        Schema::create(
            $prefix . 'page_views', function( Blueprint $table ) {
                $table->big_increments( 'id' );
                $table->string( 'page_url', 500 );
                $table->string( 'referrer', 255 )->nullable();
                $table->enum( 'device', ['desktop','mobile','tablet'] )->nullable();
                $table->string( 'browser' )->nullable();
                $table->string( 'country_code' )->nullable();
                $table->string( 'visitor_id', 64 )->nullable();
                $table->string( 'session_id', 64 )->nullable();

                $table->timestamp( 'created_at' )->use_current();

                $table->index( 'page_url' );
                $table->index( 'visitor_id' );
                $table->index( 'session_id' );
                $table->index( 'created_at' );
            }
        );

        // -----------------------------
        // 3. Campaign Events Table (Campaign-specific events only)
        // -----------------------------
        Schema::create(
            $prefix . 'events', function( Blueprint $table ) use ( $prefix ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'campaign_id' );

                $table->enum( 'event_type', ['impression','conversion','revenue'] );
                $table->string( 'page_url', 500 )->nullable();
                $table->string( 'referrer', 255 )->nullable();
                $table->enum( 'device', ['desktop','mobile','tablet'] )->nullable();
                $table->string( 'browser' )->nullable();
                $table->string( 'country_code' )->nullable();
                $table->decimal( 'revenue', 12, 2 )->default( 0 );
                $table->string( 'visitor_id', 64 )->nullable();
                $table->string( 'session_id', 64 )->nullable();

                $table->timestamp( 'created_at' )->use_current();

                $table->index( 'campaign_id' );
                $table->index( 'event_type' );
                $table->index( 'visitor_id' );
                $table->index( 'session_id' );

                $table->foreign( 'campaign_id' )->references( 'id' )->on( $prefix . 'campaigns' )->on_delete( 'cascade' );
            }
        );

        // -----------------------------
        // 4. Visitors Registry Table
        // -----------------------------
        Schema::create(
            $prefix . 'visitors', function( Blueprint $table ) {
                $table->big_increments( 'id' );
                $table->string( 'visitor_id', 64 );
                $table->timestamp( 'first_seen_at' );
                $table->timestamp( 'last_seen_at' );
                $table->integer( 'visit_count' )->default( 0 );
                $table->integer( 'session_count' )->default( 0 );

                $table->timestamps();
            }
        );

        // -----------------------------
        // 5. Campaign Stats
        // -----------------------------
        Schema::create(
            $prefix . 'campaign_stats', function( Blueprint $table ) use ( $prefix ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'campaign_id' );
                $table->timestamp( 'stat_date' );

                $table->integer( 'impressions' )->default( 0 );
                $table->integer( 'conversions' )->default( 0 );
                $table->decimal( 'revenue', 12, 2 )->default( 0 );

                $table->unique( ['campaign_id','stat_date'] );
                $table->foreign( 'campaign_id' )->references( 'id' )->on( $prefix . 'campaigns' )->on_delete( 'cascade' );
            }
        );

        // -----------------------------
        // 6. Device Stats
        // -----------------------------
        Schema::create(
            $prefix . 'device_stats', function( Blueprint $table ) use ( $prefix ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'campaign_id' );
                $table->timestamp( 'stat_date' );
                $table->enum( 'device', ['desktop','mobile','tablet'] );
                $table->integer( 'views' )->default( 0 );
                $table->integer( 'conversions' )->default( 0 );
                $table->integer( 'unique_visitors' )->default( 0 );

                $table->foreign( 'campaign_id' )->references( 'id' )->on( $prefix . 'campaigns' )->on_delete( 'cascade' );
            }
        );

        // -----------------------------
        // 7. Country Stats
        // -----------------------------
        Schema::create(
            $prefix . 'country_stats', function( Blueprint $table ) use ( $prefix ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'campaign_id' );
                $table->timestamp( 'stat_date' );
                $table->string( 'country_code', 2 );
                $table->integer( 'views' )->default( 0 );
                $table->integer( 'conversions' )->default( 0 );

                $table->foreign( 'campaign_id' )->references( 'id' )->on( $prefix . 'campaigns' )->on_delete( 'cascade' );
            }
        );

        // -----------------------------
        // 8. Referrer Stats
        // -----------------------------
        Schema::create(
            $prefix . 'referrer_stats', function( Blueprint $table ) use ( $prefix ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'campaign_id' );
                $table->timestamp( 'stat_date' );
                $table->long_text( 'referrer' );
                $table->integer( 'views' )->default( 0 );
                $table->integer( 'conversions' )->default( 0 );

                $table->foreign( 'campaign_id' )->references( 'id' )->on( $prefix . 'campaigns' )->on_delete( 'cascade' );
            }
        );

        // -----------------------------
        // 9. Page Stats
        // -----------------------------
        Schema::create(
            $prefix . 'page_stats', function( Blueprint $table ) use ( $prefix ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'campaign_id' );
                $table->timestamp( 'stat_date' );
                $table->long_text( 'page_url' );
                $table->integer( 'views' )->default( 0 );
                $table->integer( 'conversions' )->default( 0 );

                $table->foreign( 'campaign_id' )->references( 'id' )->on( $prefix . 'campaigns' )->on_delete( 'cascade' );
            }
        );

        // -----------------------------
        // 10. Browser Stats
        // -----------------------------
        Schema::create(
            $prefix . 'browser_stats', function( Blueprint $table ) use ( $prefix ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'campaign_id' );
                $table->timestamp( 'stat_date' );
                $table->long_text( 'browser' );
                $table->integer( 'views' )->default( 0 );
                $table->integer( 'conversions' )->default( 0 );

                $table->foreign( 'campaign_id' )->references( 'id' )->on( $prefix . 'campaigns' )->on_delete( 'cascade' );
            }
        );

        // -----------------------------
        // 11. Responses
        // -----------------------------
        Schema::create(
            "{$prefix}responses", function ( Blueprint $table ) use ( $prefix ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'campaign_id' );
                $table->unsigned_big_integer( 'user_id' )->nullable();
                $table->string( 'ip', 50 )->nullable();
                $table->string( 'device', 50 )->nullable();
                $table->string( 'browser', 50 )->nullable();
                $table->string( 'browser_version', 50 )->nullable();
                $table->json( 'user_info' );
                $table->timestamps();

                $table->foreign( 'campaign_id' )->references( 'id' )->on( $prefix . 'campaigns' )->on_delete( 'cascade' );
            }
        );

        // -----------------------------
        // 12. Answers
        // -----------------------------
        Schema::create(
            "{$prefix}answers", function ( Blueprint $table ) use ( $prefix ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'response_id' );
                $table->unsigned_big_integer( 'campaign_id' );
                $table->string( 'field_name', 50 );
                $table->string( 'field_type', 50 );
                $table->long_text( 'value' )->nullable();
                $table->timestamps();

                $table->foreign( 'campaign_id' )->references( 'id' )->on( $prefix . 'campaigns' )->on_delete( 'cascade' );
                $table->foreign( 'response_id' )->on( "{$prefix}responses" )->on_delete( 'cascade' );
            }
        );

        // -----------------------------
        // 13. Tasks
        // -----------------------------
        Schema::create(
            "{$prefix}tasks", function( Blueprint $table ) use ( $prefix ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'campaign_id' );
                $table->string( 'type' );
                $table->tiny_integer( 'status' )->default( 0 );
                $table->json( 'data' );
                $table->timestamps();

                $table->foreign( 'campaign_id' )->references( 'id' )->on( $prefix . 'campaigns' )->on_delete( 'cascade' );
            }
        );

        // -----------------------------
        // 14. Queues
        // -----------------------------
        Schema::create(
            "{$prefix}queues", function( Blueprint $table ) use ( $prefix ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'campaign_id' );
                $table->unsigned_big_integer( 'response_id' )->default( 0 );
                $table->unsigned_big_integer( 'task_id' );
                $table->string( 'queue_type' );
                $table->enum( 'status', ['in_queue', 'in_progress', 'failed'] )->default( 'in_queue' );
                $table->timestamps();

                $table->foreign( 'campaign_id' )->references( 'id' )->on( "{$prefix}campaigns" )->on_delete( 'cascade' );
                $table->foreign( 'task_id' )->references( 'id' )->on( "{$prefix}tasks" )->on_delete( 'cascade' );
            }
        );
    }
}