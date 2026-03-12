<?php

namespace OptinCraft\App\Http\Controllers\Admin;

defined( 'ABSPATH' ) || exit;

use OptinCraft\App\Repositories\Analytics\DeviceStatRepository;
use OptinCraft\App\Http\Controllers\Controller;
use OptinCraft\App\Repositories\Analytics\ReferrerStatRepository;
use OptinCraft\App\Repositories\Analytics\CountryStatRepository;
use OptinCraft\App\Repositories\Analytics\PageStatRepository;
use OptinCraft\App\Repositories\Analytics\BrowserStatRepository;
use OptinCraft\App\Repositories\Analytics\CampaignStatRepository;
use OptinCraft\App\Repositories\PageViewRepository;
use OptinCraft\WpMVC\Routing\Response;
use OptinCraft\WpMVC\RequestValidator\Validator;
use WP_REST_Request;

/**
 * Controller for analytics dashboard endpoints.
 * Provides KPIs, time series data, and breakdown analytics for the admin dashboard.
 */
class AnalyticsController extends Controller {
    private DeviceStatRepository $device_stat_repository;

    private CountryStatRepository $country_stat_repository;

    private ReferrerStatRepository $referrer_stat_repository;

    private PageStatRepository $page_stat_repository;

    private BrowserStatRepository $browser_stat_repository;

    private CampaignStatRepository $campaign_stat_repository;

    private PageViewRepository $page_view_repository;

    /**
     * Constructor for AnalyticsController.
     *
     * @param DeviceStatRepository $device_stat_repository Device stat repository for device stats data
     * @param ReferrerStatRepository $referrer_stat_repository Referrer stat repository for referrer stats data
     */
    public function __construct(
        DeviceStatRepository $device_stat_repository,
        CountryStatRepository $country_stat_repository,
        ReferrerStatRepository $referrer_stat_repository,
        PageStatRepository $page_stat_repository,
        BrowserStatRepository $browser_stat_repository,
        CampaignStatRepository $campaign_stat_repository,
        PageViewRepository $page_view_repository
    ) {
        $this->device_stat_repository   = $device_stat_repository;
        $this->country_stat_repository  = $country_stat_repository;
        $this->referrer_stat_repository = $referrer_stat_repository;
        $this->page_stat_repository     = $page_stat_repository;
        $this->browser_stat_repository  = $browser_stat_repository;
        $this->campaign_stat_repository = $campaign_stat_repository;
        $this->page_view_repository     = $page_view_repository;
    }

    /**
     * Get time series data for charts and graphs.
     * Returns daily metrics formatted for frontend visualization.
     *
     * @param Validator $validator Request validation instance
     * @param WP_REST_Request $request WordPress REST request object
     * @return array JSON response with time series data
     */
    public function timeseries( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'start_date'  => 'required',
                'end_date'    => 'required',
                'campaign_id' => 'numeric',
            ]
        );

        // Extract request parameters
        $start_date  = $request->get_param( 'start_date' );
        $end_date    = $request->get_param( 'end_date' );
        $campaign_id = (int) $request->get_param( 'campaign_id' );

        // Get raw time series data from repository
        $rows           = $this->campaign_stat_repository->get_series( $start_date, $end_date, $campaign_id );
        $page_view_rows = $this->page_view_repository->get_series( $start_date, $end_date );

        // Transform raw data into date-indexed array
        $series = [];

        try {
            // Initialize zero data for the full range first
            $current_date = new \DateTime( $start_date );
            $end_date_obj = new \DateTime( $end_date );

            while ( $current_date <= $end_date_obj ) {
                $date_str            = $current_date->format( 'Y-m-d' );
                $series[ $date_str ] = [
                    'impressions'     => 0,
                    'conversions'     => 0,
                    'page_views'      => 0,
                    'conversion_rate' => 0,
                ];
                $current_date->modify( '+1 day' );
            }
        } catch ( \Exception $e ) {
            // Fallback
        }

        // Overlay actual data
        foreach ( $rows as $row ) {
            $date = substr( $row->stat_date, 0, 10 );
            if ( isset( $series[ $date ] ) ) {
                $series[ $date ]['impressions']     = (int) $row->impressions;
                $series[ $date ]['conversions']     = (int) $row->conversions;
                $series[ $date ]['conversion_rate'] = (int) $row->impressions > 0 ? round( ( (int) $row->conversions / (int) $row->impressions ) * 100, 2 ) : 0;
            }
        }

        foreach ( $page_view_rows as $row ) {
            $date = substr( $row->stat_date, 0, 10 );
            if ( isset( $series[ $date ] ) ) {
                $series[ $date ]['page_views'] = (int) $row->views;
            }
        }

        // Format data for chart visualization
        $impressions_data     = [];
        $conversions_data     = [];
        $page_views_data      = [];
        $conversion_rate_data = [];

        foreach ( $series as $date => $vals ) {
            $impressions_data[]     = [
                'date'  => $date,
                'value' => (int) $vals['impressions']
            ];
            $conversions_data[]     = [
                'date'  => $date,
                'value' => (int) $vals['conversions']
            ];
            $page_views_data[]      = [
                'date'  => $date,
                'value' => (int) ( $vals['page_views'] ?? 0 )
            ];
            $conversion_rate_data[] = [
                'date'  => $date,
                'value' => (float) ( $vals['conversion_rate'] ?? 0 )
            ];
        }

        $response = [
            'impressions'     => $impressions_data,
            'conversions'     => $conversions_data,
            'page_views'      => $page_views_data,
            'conversion_rate' => $conversion_rate_data,
        ];

        return Response::send( $response );
    }

    /**
     * Get top performing campaigns based on impressions.
     * Returns campaigns ordered by performance with metrics and scores.
     *
     * @param Validator $validator Request validation instance
     * @param WP_REST_Request $request WordPress REST request object
     * @return array JSON response with top campaigns data
     */
    public function top_campaigns( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'start_date' => 'required',
                'end_date'   => 'required'
            ]
        );

        // Extract request parameters
        $start_date = $request->get_param( 'start_date' );
        $end_date   = $request->get_param( 'end_date' );

        // Get top campaigns from repository
        $rows = $this->campaign_stat_repository->get_top_campaigns( $start_date, $end_date );

        return Response::send( [ 'items' => $rows ] );
    }

    /**
     * Get device breakdown analytics.
     * Returns statistics grouped by device type (desktop, mobile, tablet).
     *
     * @param Validator $validator Request validation instance
     * @param WP_REST_Request $request WordPress REST request object
     * @return array JSON response with device breakdown data
     */
    public function devices( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'start_date'  => 'required',
                'end_date'    => 'required',
                'campaign_id' => 'numeric', 
            ]
        );

        // Extract request parameters
        $start_date  = $request->get_param( 'start_date' );
        $end_date    = $request->get_param( 'end_date' );
        $campaign_id = (int) $request->get_param( 'campaign_id' );

        // Get device breakdown from repository
        $rows = $this->device_stat_repository->get_device_breakdown( $start_date, $end_date, $campaign_id );

        return Response::send( [ 'items' => $rows ] );
    }

    /**
     * Get country breakdown analytics.
     * Returns statistics grouped by country code with optional limit.
     *
     * @param Validator $validator Request validation instance
     * @param WP_REST_Request $request WordPress REST request object
     * @return array JSON response with country breakdown data
     */
    public function countries( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'start_date'  => 'required',
                'end_date'    => 'required',
                'campaign_id' => 'numeric'
            ]
        );

        // Extract request parameters
        $start_date  = $request->get_param( 'start_date' );
        $end_date    = $request->get_param( 'end_date' );
        $campaign_id = (int) $request->get_param( 'campaign_id' );

        // Get country breakdown from repository
        $rows = $this->country_stat_repository->get_country_breakdown( $start_date, $end_date, $campaign_id );

        return Response::send( [ 'items' => $rows ] );
    }

    /**
     * Get referrer breakdown analytics.
     * Returns statistics grouped by traffic source with optional limit.
     *
     * @param Validator $validator Request validation instance
     * @param WP_REST_Request $request WordPress REST request object
     * @return array JSON response with referrer breakdown data
     */
    public function referrers( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'start_date'  => 'required',
                'end_date'    => 'required',
                'campaign_id' => 'numeric'
            ]
        );

        // Extract request parameters
        $start_date  = $request->get_param( 'start_date' );
        $end_date    = $request->get_param( 'end_date' );
        $campaign_id = (int) $request->get_param( 'campaign_id' );

        // Get referrer breakdown from repository
        $rows = $this->referrer_stat_repository->get_referrer_breakdown( $start_date, $end_date, $campaign_id );

        return Response::send( [ 'items' => $rows ] );
    }

    /**
     * Get page breakdown analytics.
     * Returns statistics grouped by page URL with optional limit.
     *
     * @param Validator $validator Request validation instance
     * @param WP_REST_Request $request WordPress REST request object
     * @return array JSON response with page breakdown data
     */
    public function pages( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'start_date'  => 'required',
                'end_date'    => 'required',
                'campaign_id' => 'numeric'
            ]
        );

        // Extract request parameters
        $start_date  = $request->get_param( 'start_date' );
        $end_date    = $request->get_param( 'end_date' );
        $campaign_id = (int) $request->get_param( 'campaign_id' );

        // Get page breakdown from repository
        $rows = $this->page_stat_repository->get_page_breakdown( $start_date, $end_date, $campaign_id );

        return Response::send( [ 'items' => $rows ] );
    }

    /**
     * Get browser breakdown analytics.
     * Returns statistics grouped by browser type with optional limit.
     *
     * @param Validator $validator Request validation instance
     * @param WP_REST_Request $request WordPress REST request object
     * @return array JSON response with browser breakdown data
     */
    public function browsers( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'start_date'  => 'required',
                'end_date'    => 'required',
                'campaign_id' => 'numeric'
            ]
        );

        // Extract request parameters
        $start_date  = $request->get_param( 'start_date' );
        $end_date    = $request->get_param( 'end_date' );
        $campaign_id = (int) $request->get_param( 'campaign_id' );

        // Get browser breakdown from repository
        $rows = $this->browser_stat_repository->get_browser_breakdown( $start_date, $end_date, $campaign_id );

        return Response::send( [ 'items' => $rows ] );
    }
}
