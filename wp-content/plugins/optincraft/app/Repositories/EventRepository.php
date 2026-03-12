<?php

namespace OptinCraft\App\Repositories;

defined( 'ABSPATH' ) || exit;

use OptinCraft\App\Models\Analytics\Event as AnalyticsEvent;
use OptinCraft\App\DTO\Analytics\EventDTO;
use OptinCraft\WpMVC\Repositories\Repository;
use OptinCraft\WpMVC\Database\Query\Builder;

class EventRepository extends Repository {
    /**
     * Get the query builder for the events table.
     */
    public function get_query_builder(): Builder {
        return AnalyticsEvent::query();
    }

    /**
     * Ingest a new event into the database.
     *
     * @param EventDTO $dto The event data transfer object
     * @return int The ID of the created event
     */
    public function ingest_event( EventDTO $dto ): int {
        return $this->create( $dto );
    }

    /**
     * Count events for a specific visitor and device within a time range.
     * Used for unique visitor tracking in analytics.
     *
     * @param int $campaign_id The campaign ID
     * @param string $device The device type (desktop, mobile, tablet)
     * @param string $visitor_id The visitor identifier
     * @param string $start_datetime Start of time range (Y-m-d H:i:s)
     * @param string $end_datetime End of time range (Y-m-d H:i:s)
     * @return int Number of events found
     */
    public function count_for_visitor_device_in_range( int $campaign_id, string $device, string $visitor_id, string $start_datetime, string $end_datetime ): int {
        return $this->get_query_builder()
            ->where( 'campaign_id', '=', $campaign_id )
            ->where( 'device', '=', $device )
            ->where( 'visitor_id', '=', $visitor_id )
            ->where_between( 'created_at', [ $start_datetime, $end_datetime ] )
            ->count( 'id' );
    }
}


