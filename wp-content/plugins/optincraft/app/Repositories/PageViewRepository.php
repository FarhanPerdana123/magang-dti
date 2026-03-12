<?php

namespace OptinCraft\App\Repositories;

defined( 'ABSPATH' ) || exit;

use OptinCraft\App\DTO\Analytics\PageViewDTO;
use OptinCraft\App\Models\Analytics\PageView;
use OptinCraft\WpMVC\Repositories\Repository;
use OptinCraft\WpMVC\Database\Query\Builder;

class PageViewRepository extends Repository {
    public function get_query_builder(): Builder {
        return PageView::query();
    }

    public function track_page_view( PageViewDTO $data ) {
        return $this->create( $data );
    }

    public function count_for_visitor_in_range( string $visitor_id, string $start_date, string $end_date ): int {
        return $this->get_query_builder()
            ->where( 'visitor_id', $visitor_id )
            ->where_between( 'created_at', [$start_date, $end_date] )
            ->count();
    }

    public function count_for_session_in_range( string $session_id, string $start_date, string $end_date ): int {
        return $this->get_query_builder()
            ->where( 'session_id', $session_id )
            ->where_between( 'created_at', [$start_date, $end_date] )
            ->count();
    }

    public function count_total_in_range( string $start_date, string $end_date ): int {
        return $this->get_query_builder()
            ->where_between( 'created_at', [$start_date, $end_date] )
            ->count();
    }

    public function get_series( string $start_date, string $end_date ): array {
        return $this->get_query_builder()
            ->select( [ 'DATE(created_at) as stat_date', 'COUNT(*) as views' ] )
            ->where_between( 'created_at', [$start_date, $end_date] )
            ->group_by( 'stat_date' )
            ->order_by( 'stat_date', 'asc' )
            ->get();
    }
}
