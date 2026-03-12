<?php

namespace OptinCraft\App\Http\Controllers\Admin;

defined( "ABSPATH" ) || exit;

use OptinCraft\App\Http\Controllers\Controller;
use OptinCraft\App\Repositories\PageViewRepository;
use OptinCraft\App\Repositories\Analytics\DeviceStatRepository;
use OptinCraft\App\Repositories\Analytics\CampaignStatRepository;
use OptinCraft\WpMVC\Routing\Response;
use OptinCraft\WpMVC\RequestValidator\Validator;
use WP_REST_Request;

/**
 * Controller for KPI (Key Performance Indicators) endpoints.
 * Provides individual KPI calculations and trend analysis.
 */
class KpisController extends Controller {
    private DeviceStatRepository $device_stat_repository;

    private PageViewRepository $page_view_repository;

    private CampaignStatRepository $campaign_stat_repository;

    public function __construct(
        DeviceStatRepository $device_stat_repository,
        PageViewRepository $page_view_repository,
        CampaignStatRepository $campaign_stat_repository
      ) {
        $this->device_stat_repository   = $device_stat_repository;
        $this->page_view_repository     = $page_view_repository;
        $this->campaign_stat_repository = $campaign_stat_repository;
    }

    /**
     * Get all Key Performance Indicators (KPIs) with trend analysis.
     * Calculates current period metrics and compares with previous period for trend calculation.
     *
     * @param Validator $validator Request validation instance
     * @param WP_REST_Request $request WordPress REST request object
     * @return array JSON response with KPI data and trends
     */
    public function kpis( Validator $validator, WP_REST_Request $request ): array {
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

        [ $prev_start, $prev_end ] = $this->compute_previous_period_dates( $start_date, $end_date );

        // Get current and previous period data
        $current_data  = $this->get_current_period_data( $start_date, $end_date, $campaign_id );
        $previous_data = $this->get_previous_period_data( $prev_start, $prev_end, $campaign_id );

        // Calculate individual KPIs
        $page_views      = $this->calculate_page_views_kpi( $current_data, $previous_data );
        $impressions     = $this->calculate_impressions_kpi( $current_data, $previous_data );
        $conversions     = $this->calculate_conversions_kpi( $current_data, $previous_data );
        $conversion_rate = $this->calculate_conversion_rate_kpi( $current_data, $previous_data );
        $revenue         = $this->calculate_revenue_kpi( $current_data, $previous_data );

        return Response::send(
            [
                'page_views'      => $page_views['current'],
                'impressions'     => $impressions['current'],
                'conversions'     => $conversions['current'],
                'conversion_rate' => $conversion_rate['current'],
                'revenue'         => $revenue['current'],
                'trends'          => [
                    'page_views'      => $this->transform_trend_to_growth( $page_views['trend'] ),
                    'impressions'     => $this->transform_trend_to_growth( $impressions['trend'] ),
                    'conversions'     => $this->transform_trend_to_growth( $conversions['trend'] ),
                    'conversion_rate' => $this->transform_trend_to_growth( $conversion_rate['trend'] ),
                    'revenue'         => $this->transform_trend_to_growth( $revenue['trend'] ),
                ],
            ]
        );
    }

    /**
     * Get current period data for KPI calculations.
     *
     * @param string $start_date Start date
     * @param string $end_date End date
     * @param int $campaign_id Campaign ID
     * @return array Current period data
     */
    private function get_current_period_data( string $start_date, string $end_date, int $campaign_id ): array {
        $totals      = $this->campaign_stat_repository->get_kpis( $start_date, $end_date, $campaign_id );
        $page_views  = $this->page_view_repository->count_total_in_range( $start_date, $end_date );
        $device_rows = $this->device_stat_repository->get_device_breakdown( $start_date, $end_date, $campaign_id );

        return array_merge( $totals, [ 'page_views' => $page_views, 'device_rows' => $device_rows ] );
    }

    /**
     * Get previous period data for trend calculations.
     *
     * @param string $prev_start Previous start date
     * @param string $prev_end Previous end date
     * @param int $campaign_id Campaign ID
     * @return array Previous period data
     */
    private function get_previous_period_data( string $prev_start, string $prev_end, int $campaign_id ): array {
        $totals      = $this->campaign_stat_repository->get_kpis( $prev_start, $prev_end, $campaign_id );
        $device_rows = $this->device_stat_repository->get_device_breakdown( $prev_start, $prev_end, $campaign_id );
        $page_views  = $this->page_view_repository->count_total_in_range( $prev_start, $prev_end );

        return array_merge( $totals, [ 'page_views' => $page_views, 'device_rows' => $device_rows ] );
    }

    /**
     * Calculate page views KPI with trend.
     *
     * @param array $current_data Current period data
     * @param array $previous_data Previous period data
     * @return array Page views KPI with current value and trend
     */
    private function calculate_page_views_kpi( array $current_data, array $previous_data ): array {
        $current  = (int) ( $current_data['page_views'] ?? 0 );
        $previous = (int) ( $previous_data['page_views'] ?? 0 );
        $trend    = $this->calculate_trend( $current, $previous );

        return [
            'current' => $current,
            'trend'   => $trend,
        ];
    }

    /**
     * Calculate impressions KPI with trend.
     *
     * @param array $current_data Current period data
     * @param array $previous_data Previous period data
     * @return array Impressions KPI with current value and trend
     */
    private function calculate_impressions_kpi( array $current_data, array $previous_data ): array {
        $current  = (int) ( $current_data['impressions'] ?? 0 );
        $previous = (int) ( $previous_data['impressions'] ?? 0 );
        $trend    = $this->calculate_trend( $current, $previous );

        return [
            'current' => $current,
            'trend'   => $trend,
        ];
    }

    /**
     * Calculate conversions KPI with trend.
     *
     * @param array $current_data Current period data
     * @param array $previous_data Previous period data
     * @return array Conversions KPI with current value and trend
     */
    private function calculate_conversions_kpi( array $current_data, array $previous_data ): array {
        $current  = (int) ( $current_data['conversions'] ?? 0 );
        $previous = (int) ( $previous_data['conversions'] ?? 0 );
        $trend    = $this->calculate_trend( $current, $previous );

        return [
            'current' => $current,
            'trend'   => $trend,
        ];
    }

    /**
     * Calculate conversion rate KPI with trend.
     *
     * @param array $current_data Current period data
     * @param array $previous_data Previous period data
     * @return array Conversion rate KPI with current value and trend
     */
    private function calculate_conversion_rate_kpi( array $current_data, array $previous_data ): array {
        // Calculate current conversion rate
        $current_visitors    = $this->calculate_total_visitors( $current_data['device_rows'] );
        $current_conversions = (int) ( $current_data['conversions'] ?? 0 );
        $current             = $current_visitors > 0 ? round( ( $current_conversions / $current_visitors ) * 100, 2 ) : 0.0;

        // Calculate previous conversion rate
        $previous_visitors    = $this->calculate_total_visitors( $previous_data['device_rows'] );
        $previous_conversions = (int) ( $previous_data['conversions'] ?? 0 );
        $previous             = $previous_visitors > 0 ? ( $previous_conversions / $previous_visitors ) * 100 : 0.0;

        $trend = $this->calculate_trend( $current, $previous );

        return [
            'current' => $current,
            'trend'   => $trend,
        ];
    }

    /**
     * Calculate revenue KPI with trend.
     *
     * @param array $current_data Current period data
     * @param array $previous_data Previous period data
     * @return array Revenue KPI with current value and trend
     */
    private function calculate_revenue_kpi( array $current_data, array $previous_data ): array {
        $current  = (float) ( $current_data['revenue'] ?? 0 );
        $previous = (float) ( $previous_data['revenue'] ?? 0 );
        $trend    = $this->calculate_trend( $current, $previous );

        return [
            'current' => $current,
            'trend'   => $trend,
        ];
    }

    /**
     * Calculate total visitors from device breakdown.
     *
     * @param array $device_rows Device breakdown data
     * @return int Total unique visitors
     */
    private function calculate_total_visitors( array $device_rows ): int {
        $total_visitors = 0;
        foreach ( $device_rows as $row ) {
            $total_visitors += (int) ( $row->unique_visitors ?? 0 );
        }
        return $total_visitors;
    }

    /**
     * Calculate trend percentage between current and previous values.
     * Handles division by zero cases.
     *
     * @param float|int $current Current value
     * @param float|int $previous Previous value
     * @return float Trend percentage
     */
    private function calculate_trend( $current, $previous ): float {
        if ( $previous == 0 ) {
            return $current > 0 ? 100.0 : 0.0;
        }
        return round( ( ( $current - $previous ) / $previous ) * 100, 2 );
    }

    /**
     * Compute previous period date range based on the selected custom range.
     * Always uses equal-duration immediately before the current start.
     *
     * @param string $start_date
     * @param string $end_date
     * @return array{string, string} [prev_start, prev_end]
     */
    private function compute_previous_period_dates( string $start_date, string $end_date ): array {
        $period_seconds = strtotime( $end_date ) - strtotime( $start_date );
        $prev_start     = gmdate( 'Y-m-d H:i:s', strtotime( $start_date ) - $period_seconds );
        $prev_end       = gmdate( 'Y-m-d H:i:s', strtotime( $start_date ) - 1 );

        return [ $prev_start, $prev_end ];
    }

    /**
     * Convert a trend percentage to growth data.
     * Growth rate is clamped between 0 and 100.
     * Direction is one of 'up', 'down', or 'flat'.
     *
     * @param float $trend Trend percentage value (may be negative or > 100)
     * @return array{rate: float, direction: string} Growth data with rate and direction
     */
    private function transform_trend_to_growth( float $trend ): array {
        $rate      = min( 100.0, max( 0.0, abs( $trend ) ) );
        $direction = $trend > 0 ? 'up' : ( $trend < 0 ? 'down' : 'flat' );
        return [
            'rate'      => $rate,
            'direction' => $direction,
        ];
    }
}