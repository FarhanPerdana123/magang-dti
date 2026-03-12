<?php

namespace OptinCraft\App\Repositories;

defined( "ABSPATH" ) || exit;

use OptinCraft\App\DTO\Campaign\Read;
use OptinCraft\App\Models\Campaign\Campaign;
use OptinCraft\WpMVC\Repositories\Repository;
use OptinCraft\WpMVC\Database\Query\Builder;

/**
 * Repository for campaign data operations.
 * Handles CRUD operations for campaign entities with search and pagination.
 */
class CampaignRepository extends Repository {
    /**
     * Get the query builder for the campaigns table.
     *
     * @return Builder
     */
    public function get_query_builder(): Builder {
        return Campaign::query( 'campaign' );
    }

    /**
     * Get campaigns with search, filtering, and pagination.
     * Supports searching across title, description, type, and status fields.
     *
     * @param Read $dto Campaign read DTO with search and pagination parameters
     * @return array Array containing total count and paginated campaign items
     */
    public function get( Read $dto ) {
        $query = $this->get_query_builder();

        // Apply search filter across multiple fields
        if ( $dto->get_search() ) {
            $query->where(
                function( $query ) use ( $dto ) {
                    $query->where( 'title', 'like', '%' . $dto->get_search() . '%' )
                    ->or_where( 'description', 'like', '%' . $dto->get_search() . '%' )
                    ->or_where( 'type', 'like', '%' . $dto->get_search() . '%' )
                    ->or_where( 'status', 'like', '%' . $dto->get_search() . '%' );
                }
            );
        }

        // Clone query for total count calculation
        $total_query = clone $query;

        $query->select( 'campaign.id', 'campaign.title', 'campaign.description', 'campaign.type', 'campaign.status', 'campaign.updated_at' )->with( 'campaign_stats' );

        // Apply ordering
        if ( $dto->get_order_by() ) {
            $query->order_by( $dto->get_order_by(), $dto->get_order_direction() );
        } else {
            $query->order_by_desc( "campaign.id" );
        }

        do_action( 'optincraft_get_campaigns_query', $query );

        // Get paginated results
        $campaigns = array_map(
            function( $popup ) {
                $popup->status = (bool) $popup->status;
                $impressions   = 0;
                $conversions   = 0;
                foreach ( $popup->campaign_stats as $stat ) {
                    $impressions += $stat->impressions;
                    $conversions += $stat->conversions;
                }
                $popup->impressions = $impressions;
                $popup->conversions = $conversions;
                return apply_filters( 'optincraft_get_campaigns_item', $popup );
            }, $query->pagination( $dto->get_page(), $dto->get_per_page() )
        );

        return [
            'total' => $total_query->count( 'campaign.id' ),
            'items' => $campaigns
        ];
    }

    public function get_public_campaigns() {
        $current_date_and_time = new \DateTime( "now", new \DateTimeZone( "UTC" ) );
        
        $query = $this->get_query_builder()->where(
            function( $query ) use ( $current_date_and_time ) {
                $query->where_null( 'start_date' )->or_where( 'start_date', '<=', $current_date_and_time );
            }
        )->where(
            function( $query ) use ( $current_date_and_time ) {
                $query->where_null( 'end_date' )->or_where( 'end_date', '>=', $current_date_and_time );
            }
        )->where( 'status', 1 );

        do_action( 'optincraft_get_public_campaigns_query', $query );

        return $query->get();
    }

    public function get_by_id( int $id, $columns = ['*'] ) {
        $campaign = $this->get_by( 'id', $id, $columns );
        if ( ! $campaign ) {
            return null;
        }

        $campaign->content = $campaign->content ? json_decode( $campaign->content, true ) : null;

        $this->elements_to_steps( $campaign );

        return $campaign;
    }

    public function elements_to_steps( $campaign ) {
        if ( $campaign->content && ! isset( $campaign->content['steps'] ) ) {
            $campaign->content['steps'] = [
                [
                    'id'       => 'welcome',
                    'title'    => 'Welcome',
                    'elements' => $campaign->content['elements']
                ],
            ];

            unset( $campaign->content['elements'] );
        }
    }
}