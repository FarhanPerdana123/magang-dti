<?php

namespace OptinCraft\App\Http\Controllers\Admin;

defined( "ABSPATH" ) || exit;

use OptinCraft\App\DTO\TaskRead;
use OptinCraft\App\DTO\TaskDTO;
use OptinCraft\App\Repositories\TaskRepository;
use OptinCraft\App\Repositories\CampaignRepository;
use OptinCraft\App\Http\Controllers\Controller;
use OptinCraft\WpMVC\Exceptions\Exception;
use OptinCraft\WpMVC\Routing\Response;
use OptinCraft\WpMVC\RequestValidator\Validator;
use WP_REST_Request;

abstract class TaskController extends Controller {
    public TaskRepository $repository;

    public CampaignRepository $campaign_repository;

    public function __construct( TaskRepository $repository, CampaignRepository $campaign_repository ) {
        $this->repository          = $repository;
        $this->campaign_repository = $campaign_repository;
    }

    abstract protected function get_type(): string;

    /**
     * Display a listing of the resource.
     *
     * @param Validator $validator Instance of the Validator.
     * @param WP_REST_Request $request The REST request instance.
     * @return array
     */
    public function index( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'campaign_id'     => 'required|numeric',
                "page"            => "required|numeric",
                "perPage"         => "required|numeric",
                "search"          => "string",
                "order_by"        => "string",
                "order_direction" => "string|accepted:asc,desc",
            ]
        );

        $dto = ( new TaskRead() )->set_campaign_id( $request->get_param( "campaign_id" ) )
            ->set_page( $request->get_param( "page" ) )
            ->set_per_page( (int) $request->get_param( "perPage" ) )
            ->set_search( (string) $request->get_param( "search" ) )
            ->set_order_by( (string) $request->get_param( "order_by" ) ?? 'id' )
            ->set_order_direction( (string) $request->get_param( "order_direction" ) ?? 'desc' )
            ->set_type( $this->get_type() );

        return Response::send( $this->repository->get( $dto ) );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Validator $validator Instance of the Validator.
     * @param WP_REST_Request $request The REST request instance.
     * @return array
     */
    public function store( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'campaign_id' => 'required|numeric',
                'status'      => 'required|boolean',
            ]
        );

        $campaign_id = $request->get_param( "campaign_id" );
        $campaign    = $this->campaign_repository->get_by_id( $campaign_id );

        if ( ! $campaign ) {
            throw new Exception( esc_html__( "Campaign not found", 'optincraft' ) );
        }

        $dto = $this->get_store_dto( $validator, $request );
        $dto->set_campaign_id( $request->get_param( "campaign_id" ) )->set_status( $request->get_param( "status" ) )->set_type( $this->get_type() );
        $id = $this->repository->create( $dto );
        $dto->set_id( $id );

        do_action( 'optincraft_after_create_task', $dto );

        return Response::send(
            [
                "message" => esc_html__( "Item was created successfully", 'optincraft' ),
                "data"    => [
                    "id" => $id
                ]
            ], 201
        );
    }
    
    public abstract function get_store_dto( Validator $validator, WP_REST_Request $request ): TaskDTO;

    /**
     * Display the specified resource.
     *
     * @param Validator $validator Instance of the Validator.
     * @param WP_REST_Request $request The REST request instance.
     * @return array
     * @throws Exception
     */
    public function show( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                "id" => "required|numeric"
            ]
        );

        $item = $this->repository->get_by_id( $request->get_param( "id" ) );

        if ( ! $item ) {
            throw new Exception( esc_html__( "Item not found", 'optincraft' ) );
        }

        $data = json_decode( $item->data, true );
        foreach ( $data as $key => $value ) {
            $item->{$key} = $value;
        }
        unset( $item->data );
        unset( $item->type );

        return Response::send(
            [
                "data" => $item
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Validator $validator Instance of the Validator.
     * @param WP_REST_Request $request The REST request instance.
     * @return array
     */
    public function update( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                "id"          => "required|numeric",
                'campaign_id' => 'required|numeric',
                'status'      => 'required|boolean',
            ]
        );

        $id   = $request->get_param( "id" );
        $task = $this->repository->get_by_id( $id );

        if ( ! $task ) {
            throw new Exception( esc_html__( "Task not found", 'optincraft' ) );
        }

        $dto = $this->get_update_dto( $validator, $request );

        $existing_data = json_decode( $task->data, true );
        $data          = array_merge( $existing_data, $dto->get_data() );
        $dto->set_data( $data );

        $dto->set_id( $request->get_param( "id" ) )->set_campaign_id( $request->get_param( "campaign_id" ) )->set_status( $request->get_param( "status" ) )->set_type( $this->get_type() );

        $this->repository->update( $dto );

        do_action( 'optincraft_after_update_task', $dto );

        return Response::send(
            [
                "message" => esc_html__( "Item was updated successfully", 'optincraft' )
            ]
        );
    }

    public abstract function get_update_dto( Validator $validator, WP_REST_Request $request ): TaskDTO;

    /**
     * Remove the specified resource from storage.
     *
     * @param Validator $validator Instance of the Validator.
     * @param WP_REST_Request $request The REST request instance.
     * @return array
     */
    public function delete( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                "id" => "required|numeric"
            ]
        );

        $this->repository->delete_by_id( $request->get_param( "id" ) );

        return Response::send(
            [
                "message" => esc_html__( "Item was deleted successfully", 'optincraft' )
            ]
        );
    }

    public function update_status( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'id'    => 'required|numeric',
                'value' => 'required|boolean'
            ]
        );

        $id   = $request->get_param( "id" );
        $task = $this->repository->get_by_id( $id );

        if ( ! $task ) {
            throw new Exception( esc_html__( "Task not found", 'optincraft' ) );
        }

        $value = $request->get_param( "value" );
        $this->repository->update_status( $id, $value );

        $task->status = $value;

        do_action( 'optincraft_after_update_task_status', $id, $task );

        return Response::send(
            [
                'message' => esc_html__( 'The status has been updated successfully.', 'optincraft' )
            ]
        );
    }
}