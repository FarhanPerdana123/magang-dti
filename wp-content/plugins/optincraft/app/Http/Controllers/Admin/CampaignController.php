<?php

namespace OptinCraft\App\Http\Controllers\Admin;

defined( "ABSPATH" ) || exit;

use OptinCraft\App\DTO\Campaign\DTO;
use OptinCraft\App\DTO\Campaign\Read;
use OptinCraft\App\Http\Controllers\Controller;
use OptinCraft\App\Repositories\CampaignRepository;
use OptinCraft\WpMVC\Routing\Response;
use OptinCraft\WpMVC\RequestValidator\Validator;
use WP_REST_Request;
use OptinCraft\App\Helpers\DateTime;

class CampaignController extends Controller {
    private CampaignRepository $repository;

    public function __construct( CampaignRepository $repository ) {
        $this->repository = $repository;
    }

    public function index( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                "page"            => "required|numeric",
                "perPage"         => "required|numeric",
                "search"          => "string",
                "order_by"        => "string",
                "order_direction" => "string|accepted:asc,desc",
            ]
        );

        $dto = ( new Read )->set_page( $request->get_param( "page" ) )
            ->set_per_page( (int) $request->get_param( "perPage" ) )
            ->set_search( (string) $request->get_param( "search" ) )
            ->set_order_by( (string) $request->get_param( "order_by" ) ?? 'id' )
            ->set_order_direction( (string) $request->get_param( "order_direction" ) ?? 'desc' );

        return Response::send( $this->repository->get( $dto ) );
    }

    public function store( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'title' => 'required|string',
                'type'  => 'required|string|accepted:popup,floating_bar,slide_in',
            ]
        );

        $dto = ( new DTO )->set_title( $request->get_param( "title" ) )->set_type( $request->get_param( "type" ) );

        $id = $this->repository->create( $dto );

        return Response::send(
            [
                "message" => esc_html__( "Campaign was created successfully", 'optincraft' ),
                "data"    => [
                    "id" => $id
                ]
            ], 201
        );
    }

    public function update( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'id'      => 'required|numeric',
                // 'title'              => 'required|string',
                // 'description'        => 'required|string',
                'content' => 'required|array',
                // 'type'               => 'required|string|accepted:popup,floating_bar,slide_in,full_screen',
                // 'open_event'         => 'required|string|accepted:on_load,on_scroll,on_exit_intent,on_click',
                // 'display_conditions' => 'required|array',
                // 'device_visibility'  => 'required|array',
                'status'  => 'required|boolean',
                // 'start_date'         => 'required|date',
                // 'end_date'           => 'required|date',
                // 'budget'             => 'required|numeric',
            ]
        );

        $content  = $request->get_param( "content" );
        $settings = $content['settings'];

        $dto = ( new DTO )->set_id( $request->get_param( "id" ) )
            // ->set_title( $request->get_param( "title" ) )
            // ->set_description( $request->get_param( "description" ) )
            ->set_content( $content )
            // ->set_type( $request->get_param( "type" ) )
            // ->set_open_event( $request->get_param( "open_event" ) )
            // ->set_display_conditions( $request->get_param( "display_conditions" ) )
            // ->set_device_visibility( $request->get_param( "device_visibility" ) )
            ->set_status( $request->get_param( "status" ) )
            ->set_start_date( ! empty( $settings['schedule_start'] ) ? new DateTime( $settings['schedule_start'] ) : null )
            ->set_end_date( ! empty( $settings['schedule_end'] ) ? new DateTime( $settings['schedule_end'] ) : null )
            ->set_geolocation_action( $settings['geolocation_action'] ?? 'show' )
            ->set_geolocation_countries( ! empty( $settings['geolocation_country'] ) ? $settings['geolocation_country'] : null )
            // ->set_budget( $request->get_param( "budget" ) )
            ;
        do_action( 'optincraft_before_update_campaign', $dto );

        $this->repository->update( $dto );

        do_action( 'optincraft_after_update_campaign', $dto->get_id() );

        return Response::send(
            [
                "message" => esc_html__( "Campaign updated successfully", 'optincraft' ),
            ]
        );
    }

    public function show( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'id' => 'required|numeric',
            ]
        );

        $id = (int) $request->get_param( "id" );

        $campaign = $this->repository->get_by_id( $id );

        if ( ! $campaign ) {
            return Response::send(
                [
                    "message" => esc_html__( "Campaign not found", 'optincraft' ),
                ], 404
            );
        }

        $campaign->status = (bool) $campaign->status;

        return Response::send(
            [
                "item" => $campaign,
            ] 
        );
    }

    public function update_status( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'value' => 'required|boolean',
            ]
        );

        $id    = (int) $request->get_param( "id" );
        $value = intval( $request->get_param( "value" ) );

        $dto = ( new DTO )->set_id( $id )->set_status( $value );

        $this->repository->update( $dto );

        return Response::send(
            [
                "message" => esc_html__( "Campaign status updated successfully", 'optincraft' ),
            ]
        );
    }

    public function update_title( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'id'    => 'required|numeric',
                'title' => 'required|string|max:255|min:3',
            ]
        );

        $id    = (int) $request->get_param( "id" );
        $title = $request->get_param( "title" );

        $dto = ( new DTO )->set_id( $id )->set_title( $title );

        $this->repository->update( $dto );

        return Response::send(
            [
                "message" => esc_html__( "Campaign title updated successfully", 'optincraft' ),
            ]
        );
    }

    public function delete( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'id' => 'required|numeric',
            ]
        );

        $id = (int) $request->get_param( "id" );

        $this->repository->delete_by_id( $id );

        return Response::send(
            [
                "message" => esc_html__( "Campaign deleted successfully", 'optincraft' ),
            ]
        );
    }

    public function select( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'all' => 'numeric',
            ]
        );

        $all = (int) $request->get_param( "all" ) ?? 0;

        $campaigns = $this->repository->get_query_builder()->select( 'id as value', 'title as label' )->order_by_desc( 'id' )->get();

        if ( $all ) {
            array_unshift(
                $campaigns, [
                    'value' => 0,
                    'label' => esc_html__( 'All Campaigns', 'optincraft' ),
                ] 
            );
        }

        return Response::send( $campaigns );
    }

    public function duplicate( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'id' => 'required|numeric',
            ]
        );

        $id       = (int) $request->get_param( "id" );
        $campaign = $this->repository->get_by_id( $id );

        if ( ! $campaign ) {
            return Response::send(
                [
                    "message" => esc_html__( "Campaign not found", 'optincraft' ),
                ], 404
            );
        }

        $dto = ( new DTO )->set_title( $campaign->title . ' - Copy' )
            ->set_type( $campaign->type )
            ->set_start_date( $campaign->start_date )
            ->set_end_date( $campaign->end_date )
            ->set_geolocation_action( $campaign->geolocation_action )
            ->set_geolocation_countries( $campaign->geolocation_countries );

        if ( ! empty( $campaign->content ) ) {
            $dto->set_content( $campaign->content );
        }

        $id = $this->repository->create( $dto );

        return Response::send(
            [
                "message" => esc_html__( "Campaign duplicated successfully", 'optincraft' ),
                "data"    => [
                    "id" => $id
                ]
            ]
        );
    }
}