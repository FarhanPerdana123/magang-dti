<?php

namespace OptinCraft\App\Repositories\Analytics;

defined( 'ABSPATH' ) || exit;

use OptinCraft\App\Models\Analytics\CountryStat;
use OptinCraft\App\DTO\Analytics\CountryStatDTO;
use OptinCraft\WpMVC\Repositories\Repository;
use OptinCraft\WpMVC\Database\Query\Builder;

/**
 * Repository for country statistics analytics.
 * Handles country-specific analytics data aggregation and retrieval.
 */
class CountryStatRepository extends Repository {
    /**
     * Get the query builder for the country stats table.
     *
     * @return Builder
     */
    public function get_query_builder(): Builder {
        return CountryStat::query( 'c' );
    }

    /**
     * Insert or update country statistics.
     * If a record exists for the campaign, date, and country code, it updates the existing record.
     * Otherwise, it creates a new record.
     *
     * @param CountryStatDTO $dto The country stats data transfer object
     * @return void
     */
    public function upsert_country_stats( CountryStatDTO $dto ): void {
        // Check if a record already exists for this campaign, date, and country code
        $existing = $this->get_query_builder()
            ->where( 'campaign_id', '=', $dto->get_campaign_id() )
            ->where( 'stat_date', '=', $dto->get_stat_date() )
            ->where( 'country_code', '=', $dto->get_country_code() )
            ->first();

        if ( $existing ) {
            // Update existing record
            $this->get_query_builder()
                ->where( 'campaign_id', '=', $dto->get_campaign_id() )
                ->where( 'stat_date', '=', $dto->get_stat_date() )
                ->where( 'country_code', '=', $dto->get_country_code() )
                ->update( $dto->to_array() );
        } else {
            // Create new record
            $this->create( $dto );
        }
    }

    /**
     * Get country breakdown analytics.
     * Returns aggregated statistics grouped by country code.
     *
     * @param string $start_date Start date in Y-m-d format
     * @param string $end_date End date in Y-m-d format
     * @param int|null $campaign_id Optional campaign ID to filter by
     * @return array Country breakdown data with views and conversions
     */
    public function get_country_breakdown( string $start_date, string $end_date, ?int $campaign_id = null ): array {
        // Build query with date range filter
        $query = $this->get_query_builder()->where_between( 'stat_date', [$start_date, $end_date] );
        
        // Filter by campaign if specified
        if ( $campaign_id ) {
            $query->where( 'campaign_id', '=', $campaign_id );
        }
        
        // Aggregate data by country code and return top countries
        $results = $query->select( [ 'country_code', 'SUM(views) as views', 'SUM(conversions) as conversions' ] )->group_by( 'country_code' )->order_by_desc( 'views' )->get();
        
        // Add conversion rate to each result
        foreach ( $results as &$result ) {
            $result->conversion_rate = $result->views > 0 ? round( ( $result->conversions / $result->views ) * 100, 2 ) : 0;
            unset( $result->conversions );
        }
        
        return $results;
    }
}
