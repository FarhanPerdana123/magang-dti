<?php

namespace OptinCraft\App\Repositories\Analytics;

defined( 'ABSPATH' ) || exit;

use OptinCraft\App\Models\Analytics\CampaignStat;
use OptinCraft\App\DTO\Analytics\CampaignStatDTO;
use OptinCraft\WpMVC\Repositories\Repository;
use OptinCraft\WpMVC\Database\Query\Builder;

class CampaignStatRepository extends Repository {
    /**
     * Get the query builder for the daily campaign stats table.
     *
     * @return Builder
     */
    public function get_query_builder(): Builder {
        return CampaignStat::query( 's' );
    }

    /**
     * Insert or update daily campaign statistics.
     * If a record exists for the campaign and date, it updates the existing record.
     * Otherwise, it creates a new record.
     *
     * @param CampaignStatDTO $dto The daily stats data transfer object
     * @return void
     */
    public function upsert_daily_stats( CampaignStatDTO $dto ): void {
        // Check if a record already exists for this campaign and date
        $existing = $this->get_query_builder()
            ->where( 'campaign_id', '=', $dto->get_campaign_id() )
            ->where( 'stat_date', '=', $dto->get_stat_date() )
            ->first();

        if ( $existing ) {
            // Update existing record
            $this->get_query_builder()
                ->where( 'campaign_id', '=', $dto->get_campaign_id() )
                ->where( 'stat_date', '=', $dto->get_stat_date() )
                ->update( $dto->to_array() );
        } else {
            // Create new record
            $this->create( $dto );
        }
    }

    /**
     * Get Key Performance Indicators (KPIs) for analytics dashboard.
     * Calculates aggregated metrics over a date range for a specific campaign or all campaigns.
     *
     * @param string $start_date Start date in Y-m-d format
     * @param string $end_date End date in Y-m-d format
     * @param int|null $campaign_id Optional campaign ID to filter by
     * @return array KPI data including impressions, conversions, conversion rate, and revenue
     */
    public function get_kpis( string $start_date, string $end_date, ?int $campaign_id = null ): array {
        $query = $this->get_query_builder()->where_between( 'stat_date', [$start_date, $end_date] );
        
        // Filter by campaign if specified
        if ( $campaign_id ) {
            $query->where( 'campaign_id', '=', $campaign_id );
        }
        
        // Aggregate totals across the date range
        $totals = $query->select(
            [
                'SUM(impressions) as impressions',
                'SUM(conversions) as conversions',
                'SUM(revenue) as revenue',
            ]
        )->first();

        // Cast to appropriate types and provide defaults
        $impressions = (int) ( $totals->impressions ?? 0 );
        $conversions = (int) ( $totals->conversions ?? 0 );
        $revenue     = (float) ( $totals->revenue ?? 0 );

        return [
            'impressions'     => $impressions,
            'conversions'     => $conversions,
            'conversion_rate' => $impressions > 0 ? round( ( $conversions / $impressions ) * 100, 2 ) : 0,
            'revenue'         => $revenue,
        ];
    }

    /**
     * Get time series data for charts and graphs.
     * Returns daily metrics ordered by date for visualization.
     *
     * @param string $start_date Start date in Y-m-d format
     * @param string $end_date End date in Y-m-d format
     * @param int|null $campaign_id Optional campaign ID to filter by
     * @return array Time series data with daily metrics
     */
    public function get_series( string $start_date, string $end_date, ?int $campaign_id = null ): array {
        $query = $this->get_query_builder()->where_between( 'stat_date', [$start_date, $end_date] );
        
        // Filter by campaign if specified
        if ( $campaign_id ) {
            $query->where( 'campaign_id', '=', $campaign_id );
        }
        
        return $query->select( [ 'stat_date', 'impressions', 'conversions', 'revenue' ] )->order_by( 'stat_date', 'asc' )->get();
    }

    /**
     * Get top performing campaigns based on impressions.
     * Includes campaign titles and calculates performance scores.
     *
     * @param string $start_date Start date in Y-m-d format
     * @param string $end_date End date in Y-m-d format
     * @return array Top campaigns with metrics and performance scores
     */
    public function get_top_campaigns( string $start_date, string $end_date ): array {
        // Get aggregated campaign data ordered by impressions
        $rows = $this->get_query_builder()
            ->select( [ 'campaign_id', 'SUM(impressions) as impressions', 'SUM(conversions) as conversions', 'SUM(revenue) as revenue' ] )
            ->where_between( 'stat_date', [$start_date, $end_date] )
            ->group_by( 'campaign_id' )
            ->order_by_desc( 'impressions' )
            ->get();

        // Fetch campaign titles for display
        $campaign_ids = array_map( fn( $r ) => (int) $r->campaign_id, $rows );
        $titles_map   = [];
        if ( ! empty( $campaign_ids ) ) {
            $titles = \OptinCraft\App\Models\Campaign\Campaign::query( 'c' )
                ->select( [ 'id', 'title' ] )
                ->where_in( 'id', $campaign_ids )
                ->get();
            foreach ( $titles as $t ) {
                $titles_map[ (int) $t->id ] = $t->title;
            }
        }

        // Transform data and calculate performance metrics
        return array_map(
            function( $r ) use ( $titles_map ) {
                $impr = (int) $r->impressions;
                $conv = (int) $r->conversions;
                $cvr  = $impr > 0 ? round( ( $conv / $impr ) * 100, 2 ) : 0;
            
                // Performance score: weighted conversion rate + revenue bonus
                //TODO: rate weight and revenue bonus should be configurable
                $score = round( ( $cvr * 0.7 ) + ( (float) $r->revenue > 0 ? 30 : 0 ), 2 );
            
                return [
                    'campaign_id' => (int) $r->campaign_id,
                    'title'       => $titles_map[ (int) $r->campaign_id ] ?? '',
                    'impressions' => $impr,
                    'conversions' => $conv,
                    'rate'        => $cvr,
                    'revenue'     => (float) $r->revenue,
                    'performance' => $score,
                ];
            }, $rows 
        );
    }
}


