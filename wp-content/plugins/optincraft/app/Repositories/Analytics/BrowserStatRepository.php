<?php

namespace OptinCraft\App\Repositories\Analytics;

defined( 'ABSPATH' ) || exit;

use OptinCraft\App\Models\Analytics\BrowserStat;
use OptinCraft\App\DTO\Analytics\BrowserStatDTO;
use OptinCraft\WpMVC\Repositories\Repository;
use OptinCraft\WpMVC\Database\Query\Builder;

/**
 * Repository for browser statistics analytics.
 * Handles browser-specific analytics data aggregation and retrieval.
 */
class BrowserStatRepository extends Repository {
    /**
     * Get the query builder for the browser stats table.
     *
     * @return Builder
     */
    public function get_query_builder(): Builder {
        return BrowserStat::query( 'b' );
    }

    /**
     * Insert or update browser statistics.
     * If a record exists for the campaign, date, and browser, it updates the existing record.
     * Otherwise, it creates a new record.
     *
     * @param BrowserStatDTO $dto The browser stats data transfer object
     * @return void
     */
    public function upsert_browser_stats( BrowserStatDTO $dto ): void {
        // Check if a record already exists for this campaign, date, and browser
        $existing = $this->get_query_builder()
            ->where( 'campaign_id', '=', $dto->get_campaign_id() )
            ->where( 'stat_date', '=', $dto->get_stat_date() )
            ->where( 'browser', '=', $dto->get_browser() )
            ->first();

        if ( $existing ) {
            // Update existing record
            $this->get_query_builder()
                ->where( 'campaign_id', '=', $dto->get_campaign_id() )
                ->where( 'stat_date', '=', $dto->get_stat_date() )
                ->where( 'browser', '=', $dto->get_browser() )
                ->update( $dto->to_array() );
        } else {
            // Create new record
            $this->create( $dto );
        }
    }

    /**
     * Get browser breakdown analytics.
     * Returns aggregated statistics grouped by browser type with optional limit.
     *
     * @param string $start_date Start date in Y-m-d format
     * @param string $end_date End date in Y-m-d format
     * @param int|null $campaign_id Optional campaign ID to filter by
     * @return array Browser breakdown data with views and conversions
     */
    public function get_browser_breakdown( string $start_date, string $end_date, ?int $campaign_id = null ): array {
        // Build query with date range filter
        $query = $this->get_query_builder()->where_between( 'stat_date', [$start_date, $end_date] );
        
        // Filter by campaign if specified
        if ( $campaign_id ) {
            $query->where( 'campaign_id', '=', $campaign_id );
        }
        
        $total_views_query = clone $query; 
        // Get total views for percentage calculation
        $total_views = $total_views_query->select( [ 'SUM(views) as total_views' ] )->first();
        $total_views = $total_views ? (int) $total_views->total_views : 0;
        
        // Aggregate data by browser type and return top browsers
        $results = $query->select( [ 'browser', 'SUM(views) as views', 'SUM(conversions) as conversions' ] )->group_by( 'browser' )->order_by_desc( 'views' )->get();
        
        // Add views percentage to each result
        foreach ( $results as &$result ) {
            $result->views_percent = $total_views > 0 ? round( ( $result->views / $total_views ) * 100, 2 ) : 0;
        }
        
        return $results;
    }
}
