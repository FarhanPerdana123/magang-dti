<?php

namespace OptinCraft\App\Repositories;

defined( "ABSPATH" ) || exit;

use OptinCraft\WpMVC\Repositories\Repository;
use OptinCraft\WpMVC\Database\Query\Builder;
use OptinCraft\App\Models\Response;
use OptinCraft\App\Models\Campaign\Campaign;
use OptinCraft\App\DTO\Response\Read;

class ResponseRepository extends Repository {
    public function get_query_builder(): Builder {
        return Response::query( 'response' );
    }

    public function get( Read $dto ) {
        $query       = $this->get_query_builder()->where( 'response.campaign_id', $dto->get_campaign_id() );
        $count_query = clone $query;

        $query->select( 'response.*', 'campaign.title as campaign_title' )->with( 'answers' )
        ->left_join( Campaign::get_table_name() . " as campaign", "campaign.id", "response.campaign_id" )
        ->order_by( 'response.id',  'desc' );

        $responses = array_map(
            function( $response ) {
                $response->user_info = json_decode( $response->user_info, true );
                foreach ( $response->answers as $answer ) {
                      $response->{$answer->field_name} = $answer->value;
                }
                unset( $response->answers );
                return $response;
            }, $query->pagination( $dto->get_page(), $dto->get_per_page() ) 
        );

        return [
            'total' => $count_query->count(),
            'items' => $responses,
        ];
    }
}