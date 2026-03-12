<?php

namespace OptinCraft\App\Repositories\Analytics;

defined( 'ABSPATH' ) || exit;

use OptinCraft\App\Models\Analytics\DeviceStat;
use OptinCraft\App\DTO\Analytics\DeviceStatDTO;
use OptinCraft\WpMVC\Repositories\Repository;
use OptinCraft\WpMVC\Database\Query\Builder;

/**
 * Repository for device statistics analytics.
 * Handles device-specific analytics data aggregation and retrieval.
 */
class DeviceStatRepository extends Repository {
    /**
     * Get the query builder for the device stats table.
     *
     * @return Builder
     */
    public function get_query_builder(): Builder {
        return DeviceStat::query( 'd' );
    }

    /**
     * Insert or update device statistics.
     * If a record exists for the campaign, date, and device, it updates the existing record.
     * Otherwise, it creates a new record.
     *
     * @param DeviceStatDTO $dto The device stats data transfer object
     * @return void
     */
    public function upsert_device_stats( DeviceStatDTO $dto ): void {
        // Check if a record already exists for this campaign, date, and device
        $existing = $this->get_query_builder()
            ->where( 'campaign_id', '=', $dto->get_campaign_id() )
            ->where( 'stat_date', '=', $dto->get_stat_date() )
            ->where( 'device', '=', $dto->get_device() )
            ->first();

        if ( $existing ) {
            // Update existing record
            $this->get_query_builder()
                ->where( 'campaign_id', '=', $dto->get_campaign_id() )
                ->where( 'stat_date', '=', $dto->get_stat_date() )
                ->where( 'device', '=', $dto->get_device() )
                ->update( $dto->to_array() );
        } else {
            // Create new record
            $this->create( $dto );
        }
    }

    /**
     * Get device breakdown analytics.
     * Returns aggregated statistics grouped by device type.
     *
     * @param string $start_date Start date in Y-m-d format
     * @param string $end_date End date in Y-m-d format
     * @param int|null $campaign_id Optional campaign ID to filter by
     * @return array Device breakdown data with views, conversions, and unique visitors
     */
    public function get_device_breakdown( string $start_date, string $end_date, ?int $campaign_id = null ): array {
        // Build query with date range filter
        $query = $this->get_query_builder()->where_between( 'stat_date', [$start_date, $end_date] );
        
        // Filter by campaign if specified
        if ( $campaign_id ) {
            $query->where( 'campaign_id', '=', $campaign_id );
        }
        
        // Aggregate data by device type
        return $query->select( [ 'device', 'SUM(views) as views', 'SUM(conversions) as conversions', 'SUM(unique_visitors) as visitors' ] )
            ->group_by( 'device' )
            ->order_by_desc( 'views' )
            ->get();
    }
}


