<?php

namespace OptinCraft\App\Repositories\Analytics;

defined( 'ABSPATH' ) || exit;

use OptinCraft\App\Models\Analytics\PageStat;
use OptinCraft\App\DTO\Analytics\PageStatDTO;
use OptinCraft\WpMVC\Repositories\Repository;
use OptinCraft\WpMVC\Database\Query\Builder;

/**
 * Repository for page statistics analytics.
 * Handles page-specific analytics data aggregation and retrieval.
 */
class PageStatRepository extends Repository {
    /**
     * Get the query builder for the page stats table.
     *
     * @return Builder
     */
    public function get_query_builder(): Builder {
        return PageStat::query( 'p' );
    }

    /**
     * Insert or update page statistics.
     * If a record exists for the campaign, date, and page URL, it updates the existing record.
     * Otherwise, it creates a new record.
     *
     * @param PageStatDTO $dto The page stats data transfer object
     * @return void
     */
    public function upsert_page_stats( PageStatDTO $dto ): void {
        // Check if a record already exists for this campaign, date, and page URL
        $existing = $this->get_query_builder()
            ->where( 'campaign_id', '=', $dto->get_campaign_id() )
            ->where( 'stat_date', '=', $dto->get_stat_date() )
            ->where( 'page_url', '=', $dto->get_page_url() )
            ->first();

        if ( $existing ) {
            // Update existing record
            $this->get_query_builder()
                ->where( 'campaign_id', '=', $dto->get_campaign_id() )
                ->where( 'stat_date', '=', $dto->get_stat_date() )
                ->where( 'page_url', '=', $dto->get_page_url() )
                ->update( $dto->to_array() );
        } else {
            // Create new record
            $this->create( $dto );
        }
    }

    /**
     * Get page breakdown analytics.
     * Returns aggregated statistics grouped by page URL with optional limit.
     *
     * @param string $start_date Start date in Y-m-d format
     * @param string $end_date End date in Y-m-d format
     * @param int|null $campaign_id Optional campaign ID to filter by
     * @return array Page breakdown data with views and conversions
     */
    public function get_page_breakdown( string $start_date, string $end_date, ?int $campaign_id = null ): array {
        // Build query with date range filter
        $query = $this->get_query_builder()->where_between( 'stat_date', [$start_date, $end_date] );
        
        // Filter by campaign if specified
        if ( $campaign_id ) {
            $query->where( 'campaign_id', '=', $campaign_id );
        }
        
        // Aggregate data by page URL and return top pages
        return $query->select( [ 'page_url', 'SUM(views) as views', 'SUM(conversions) as conversions' ] )->group_by( 'page_url' )->order_by_desc( 'views' )->get();
    }
}
