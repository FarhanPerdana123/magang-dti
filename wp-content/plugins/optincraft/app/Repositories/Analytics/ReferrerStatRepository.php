<?php

namespace OptinCraft\App\Repositories\Analytics;

defined( 'ABSPATH' ) || exit;

use OptinCraft\App\Models\Analytics\ReferrerStat;
use OptinCraft\App\DTO\Analytics\ReferrerStatDTO;
use OptinCraft\WpMVC\Repositories\Repository;
use OptinCraft\WpMVC\Database\Query\Builder;

/**
 * Repository for referrer statistics analytics.
 * Handles referrer-specific analytics data aggregation and retrieval.
 */
class ReferrerStatRepository extends Repository {
    /**
     * Get the query builder for the referrer stats table.
     *
     * @return Builder
     */
    public function get_query_builder(): Builder {
        return ReferrerStat::query( 'r' );
    }

    /**
     * Insert or update referrer statistics.
     * If a record exists for the campaign, date, and referrer, it updates the existing record.
     * Otherwise, it creates a new record.
     *
     * @param ReferrerStatDTO $dto The referrer stats data transfer object
     * @return void
     */
    public function upsert_referrer_stats( ReferrerStatDTO $dto ): void {
        // Check if a record already exists for this campaign, date, and referrer
        $existing = $this->get_query_builder()
            ->where( 'campaign_id', '=', $dto->get_campaign_id() )
            ->where( 'stat_date', '=', $dto->get_stat_date() )
            ->where( 'referrer', '=', $dto->get_referrer() )
            ->first();

        if ( $existing ) {
            // Update existing record
            $this->get_query_builder()
                ->where( 'campaign_id', '=', $dto->get_campaign_id() )
                ->where( 'stat_date', '=', $dto->get_stat_date() )
                ->where( 'referrer', '=', $dto->get_referrer() )
                ->update( $dto->to_array() );
        } else {
            // Create new record
            $this->create( $dto );
        }
    }

    /**
     * Get referrer breakdown analytics.
     * Returns aggregated statistics grouped by referrer with optional limit.
     *
     * @param string $start_date Start date in Y-m-d format
     * @param string $end_date End date in Y-m-d format
     * @param int|null $campaign_id Optional campaign ID to filter by
     * @return array Referrer breakdown data with views and conversions
     */
    public function get_referrer_breakdown( string $start_date, string $end_date, ?int $campaign_id = null ): array {
        // Build query with date range filter
        $query = $this->get_query_builder()->where_between( 'stat_date', [$start_date, $end_date] );
        
        // Filter by campaign if specified
        if ( $campaign_id ) {
            $query->where( 'campaign_id', '=', $campaign_id );
        }
        
        // Aggregate data by referrer and return top referrers
        return $query->select( [ 'referrer', 'SUM(views) as views', 'SUM(conversions) as conversions' ] )->group_by( 'referrer' )->order_by_desc( 'views' )->get();
    }
}


