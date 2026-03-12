<?php

namespace OptinCraft\App\Repositories;

defined( 'ABSPATH' ) || exit;

use OptinCraft\App\Repositories\Analytics\CampaignStatRepository;
use OptinCraft\App\Repositories\Analytics\DeviceStatRepository;
use OptinCraft\App\Repositories\Analytics\CountryStatRepository;
use OptinCraft\App\Repositories\Analytics\ReferrerStatRepository;
use OptinCraft\App\Repositories\Analytics\PageStatRepository;
use OptinCraft\App\Repositories\Analytics\BrowserStatRepository;
use OptinCraft\App\Repositories\EventRepository;
use OptinCraft\WpMVC\Repositories\Repository;

/**
 * Repository for aggregating analytics statistics from raw events.
 * Handles real-time aggregation of events into various analytics tables.
 */
class StatsRepository extends Repository {
    private CampaignStatRepository $daily_repo;

    private DeviceStatRepository $device_repo;

    private CountryStatRepository $country_repo;

    private ReferrerStatRepository $referrer_repo;

    private PageStatRepository $page_repo;

    private BrowserStatRepository $browser_repo;

    private EventRepository $event_repo;

    public function __construct(
        CampaignStatRepository $daily_repo,
        DeviceStatRepository $device_repo,
        CountryStatRepository $country_repo,
        ReferrerStatRepository $referrer_repo,
        PageStatRepository $page_repo,
        BrowserStatRepository $browser_repo,
        EventRepository $event_repo
    ) {
        $this->daily_repo    = $daily_repo;
        $this->device_repo   = $device_repo;
        $this->country_repo  = $country_repo;
        $this->referrer_repo = $referrer_repo;
        $this->page_repo     = $page_repo;
        $this->browser_repo  = $browser_repo;
        $this->event_repo    = $event_repo;
    }

    /**
     * Get the query builder for the daily campaign stats table.
     */
    public function get_query_builder(): \OptinCraft\WpMVC\Database\Query\Builder {
        return $this->daily_repo->get_query_builder();
    }

    /**
     * Apply a single event to all relevant analytics tables.
     * This method processes raw event data and updates aggregated statistics
     * across multiple analytics tables in real-time.
     *
     * @param array $payload Raw event data from the event ingestion
     * @return void
     */
    public function apply_event( array $payload ): void {
        // Validate campaign ID
        $campaign_id = (int) ( $payload['campaign_id'] ?? 0 );
        if ( $campaign_id <= 0 ) {
            return;
        }

        // Extract event data with fallbacks
        $event_type   = (string) ( $payload['event_type'] ?? '' );
        $device       = $payload['device'] ?? null;
        $country_code = $payload['country_code'] ?? ( $payload['country'] ?? null );
        $referrer     = $payload['referrer'] ?? null;
        $page_url     = $payload['page_url'] ?? null;
        $browser      = $payload['browser'] ?? null;
        $revenue      = (float) ( $payload['revenue'] ?? 0 );

        // Set current date for statistics
        $now       = gmdate( 'Y-m-d H:i:s' );
        $stat_date = gmdate( 'Y-m-d 00:00:00', strtotime( $now ) );

        // Calculate increments based on event type (no more view events)
        $impression_increment = $event_type === 'impression' ? 1 : 0;
        $conversion_increment = $event_type === 'conversion' ? 1 : 0;
        $revenue_increment    = $event_type === 'revenue' ? $revenue : 0.0;

        // Update daily campaign statistics (no view increment)
        $this->update_daily_stats( $campaign_id, $stat_date, $impression_increment, $conversion_increment, $revenue_increment );

        // Update dimension-specific statistics if data is available (no view increment)
        if ( $device ) {
            $this->update_device_stats( $campaign_id, $stat_date, (string) $device, $impression_increment, $conversion_increment, (string) ( $payload['visitor_id'] ?? '' ) );
        }
        if ( $country_code ) {
            $this->update_country_stats( $campaign_id, $stat_date, (string) $country_code, $impression_increment, $conversion_increment );
        }
        if ( $referrer ) {
            $this->update_referrer_stats( $campaign_id, $stat_date, (string) $referrer, $impression_increment, $conversion_increment );
        }
        if ( $page_url ) {
            $this->update_page_stats( $campaign_id, $stat_date, (string) $page_url, $impression_increment, $conversion_increment );
        }
        if ( $browser ) {
            $this->update_browser_stats( $campaign_id, $stat_date, (string) $browser, $impression_increment, $conversion_increment );
        }
    }

    private function update_daily_stats( int $campaign_id, string $stat_date, int $impressions, int $conversions, float $revenue ): void {
        $row = $this->daily_repo->get_query_builder()
            ->where( 'campaign_id', '=', $campaign_id )
            ->where( 'stat_date', '=', $stat_date )
            ->first();

        if ( $row ) {
            $dto = ( new \OptinCraft\App\DTO\Analytics\CampaignStatDTO() )
                ->set_campaign_id( $campaign_id )
                ->set_stat_date( $stat_date )
                ->set_impressions( (int) $row->impressions + $impressions )
                ->set_conversions( (int) $row->conversions + $conversions )
                ->set_revenue( (float) $row->revenue + $revenue );
            $this->daily_repo->upsert_daily_stats( $dto );
            return;
        }

        $dto = ( new \OptinCraft\App\DTO\Analytics\CampaignStatDTO() )
            ->set_campaign_id( $campaign_id )
            ->set_stat_date( $stat_date )
            ->set_impressions( $impressions )
            ->set_conversions( $conversions )
            ->set_revenue( $revenue );
        $this->daily_repo->upsert_daily_stats( $dto );
    }

    private function update_device_stats( int $campaign_id, string $stat_date, string $device, int $views, int $conversions, string $visitor_id ): void {
        $row = $this->device_repo->get_query_builder()
            ->where( 'campaign_id', '=', $campaign_id )
            ->where( 'stat_date', '=', $stat_date )
            ->where( 'device', '=', $device )
            ->first();

        $unique_increment = 0;
        if ( $views > 0 && ! empty( $visitor_id ) ) {
            // Count events for this campaign/device/visitor today; if first, increment unique_visitors
            $day_end        = gmdate( 'Y-m-d 23:59:59', strtotime( $stat_date ) );
            $existing_today = $this->event_repo->count_for_visitor_device_in_range( $campaign_id, $device, $visitor_id, $stat_date, $day_end );
            if ( 1 === (int) $existing_today ) {
                $unique_increment = 1;
            }
        }

        if ( $row ) {
            $dto = ( new \OptinCraft\App\DTO\Analytics\DeviceStatDTO() )
                ->set_campaign_id( $campaign_id )
                ->set_stat_date( $stat_date )
                ->set_device( $device )
                ->set_views( (int) $row->views + $views )
                ->set_conversions( (int) $row->conversions + $conversions )
                ->set_unique_visitors( (int) $row->unique_visitors + $unique_increment );
            $this->device_repo->upsert_device_stats( $dto );
            return;
        }

        $dto = ( new \OptinCraft\App\DTO\Analytics\DeviceStatDTO() )
            ->set_campaign_id( $campaign_id )
            ->set_stat_date( $stat_date )
            ->set_device( $device )
            ->set_views( $views )
            ->set_conversions( $conversions )
            ->set_unique_visitors( $unique_increment );
        $this->device_repo->upsert_device_stats( $dto );
    }

    private function update_country_stats( int $campaign_id, string $stat_date, string $country_code, int $views, int $conversions ): void {
        $row = $this->country_repo->get_query_builder()
            ->where( 'campaign_id', '=', $campaign_id )
            ->where( 'stat_date', '=', $stat_date )
            ->where( 'country_code', '=', $country_code )
            ->first();

        if ( $row ) {
            $dto = ( new \OptinCraft\App\DTO\Analytics\CountryStatDTO() )
                ->set_campaign_id( $campaign_id )
                ->set_stat_date( $stat_date )
                ->set_country_code( $country_code )
                ->set_views( (int) $row->views + $views )
                ->set_conversions( (int) $row->conversions + $conversions );
            $this->country_repo->upsert_country_stats( $dto );
            return;
        }

        $dto = ( new \OptinCraft\App\DTO\Analytics\CountryStatDTO() )
            ->set_campaign_id( $campaign_id )
            ->set_stat_date( $stat_date )
            ->set_country_code( $country_code )
            ->set_views( $views )
            ->set_conversions( $conversions );
        $this->country_repo->upsert_country_stats( $dto );
    }

    private function update_referrer_stats( int $campaign_id, string $stat_date, string $referrer, int $views, int $conversions ): void {
        $row = $this->referrer_repo->get_query_builder()
            ->where( 'campaign_id', '=', $campaign_id )
            ->where( 'stat_date', '=', $stat_date )
            ->where( 'referrer', '=', $referrer )
            ->first();

        if ( $row ) {
            $dto = ( new \OptinCraft\App\DTO\Analytics\ReferrerStatDTO() )
                ->set_campaign_id( $campaign_id )
                ->set_stat_date( $stat_date )
                ->set_referrer( $referrer )
                ->set_views( (int) $row->views + $views )
                ->set_conversions( (int) $row->conversions + $conversions );
            $this->referrer_repo->upsert_referrer_stats( $dto );
            return;
        }

        $dto = ( new \OptinCraft\App\DTO\Analytics\ReferrerStatDTO() )
            ->set_campaign_id( $campaign_id )
            ->set_stat_date( $stat_date )
            ->set_referrer( $referrer )
            ->set_views( $views )
            ->set_conversions( $conversions );
        $this->referrer_repo->upsert_referrer_stats( $dto );
    }

    private function update_page_stats( int $campaign_id, string $stat_date, string $page_url, int $views, int $conversions ): void {
        $row = $this->page_repo->get_query_builder()
            ->where( 'campaign_id', '=', $campaign_id )
            ->where( 'stat_date', '=', $stat_date )
            ->where( 'page_url', '=', $page_url )
            ->first();

        if ( $row ) {
            $dto = ( new \OptinCraft\App\DTO\Analytics\PageStatDTO() )
                ->set_campaign_id( $campaign_id )
                ->set_stat_date( $stat_date )
                ->set_page_url( $page_url )
                ->set_views( (int) $row->views + $views )
                ->set_conversions( (int) $row->conversions + $conversions );
            $this->page_repo->upsert_page_stats( $dto );
            return;
        }

        $dto = ( new \OptinCraft\App\DTO\Analytics\PageStatDTO() )
            ->set_campaign_id( $campaign_id )
            ->set_stat_date( $stat_date )
            ->set_page_url( $page_url )
            ->set_views( $views )
            ->set_conversions( $conversions );
        $this->page_repo->upsert_page_stats( $dto );
    }

    private function update_browser_stats( int $campaign_id, string $stat_date, string $browser, int $views, int $conversions ): void {
        $row = $this->browser_repo->get_query_builder()
            ->where( 'campaign_id', '=', $campaign_id )
            ->where( 'stat_date', '=', $stat_date )
            ->where( 'browser', '=', $browser )
            ->first();

        if ( $row ) {
            $dto = ( new \OptinCraft\App\DTO\Analytics\BrowserStatDTO() )
                ->set_campaign_id( $campaign_id )
                ->set_stat_date( $stat_date )
                ->set_browser( $browser )
                ->set_views( (int) $row->views + $views )
                ->set_conversions( (int) $row->conversions + $conversions );
            $this->browser_repo->upsert_browser_stats( $dto );
            return;
        }

        $dto = ( new \OptinCraft\App\DTO\Analytics\BrowserStatDTO() )
            ->set_campaign_id( $campaign_id )
            ->set_stat_date( $stat_date )
            ->set_browser( $browser )
            ->set_views( $views )
            ->set_conversions( $conversions );
        $this->browser_repo->upsert_browser_stats( $dto );
    }
}


