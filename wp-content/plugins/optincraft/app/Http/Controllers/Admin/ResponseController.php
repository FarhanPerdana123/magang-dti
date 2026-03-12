<?php

namespace OptinCraft\App\Http\Controllers\Admin;

defined( "ABSPATH" ) || exit;

use OptinCraft\App\Http\Controllers\Controller;
use OptinCraft\WpMVC\Exceptions\Exception;
use OptinCraft\WpMVC\Routing\Response;
use OptinCraft\WpMVC\RequestValidator\Validator;
use WP_REST_Request;
use OptinCraft\App\Repositories\ResponseRepository;
use OptinCraft\App\DTO\Response\DTO;
use OptinCraft\App\DTO\Response\Read;

class ResponseController extends Controller {
    public ResponseRepository $repository;

    public function __construct( ResponseRepository $repository ) {
        $this->repository = $repository;
    }

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
                "page"            => "required|numeric",
                "perPage"         => "required|numeric",
                "campaign_id"     => "required|numeric",
                "search"          => "string",
                "order_by"        => "string",
                "order_direction" => "string|accepted:asc,desc",
            ]
        );

        $dto = ( new Read )->set_page( $request->get_param( "page" ) )
            ->set_per_page( (int) $request->get_param( "perPage" ) )
            ->set_search( (string) $request->get_param( "search" ) )
            ->set_order_by( (string) $request->get_param( "order_by" ) ?? 'id' )
            ->set_order_direction( (string) $request->get_param( "order_direction" ) ?? 'desc' )
            ->set_campaign_id( (int) $request->get_param( "campaign_id" ) );

        return Response::send( $this->repository->get( $dto ) );
    }

    public function get_columns( Validator $validator, WP_REST_Request $request ) {
        $validator->validate(
            [
                "campaign_id" => "required|numeric",
            ]
        );

        $columns = [
            [
                'id'    => 'id',
                'label' => 'ID',
            ],
            [
                'id'    => 'campaign_title',
                'label' => 'Campaign',
            ]
        ];

        $fields = optincraft_get_campaign_form( (int) $request->get_param( "campaign_id" ) );

        foreach ( $fields as $field ) {
            $columns[] = [
                'id'    => $field['field_name'],
                'label' => $field['label'],
            ];
        }

        $columns[] = [
            'id'    => 'created_at',
            'label' => 'Submitted At',
        ];

        return Response::send(
            [
                "columns" => $columns
            ]
        );
    }
}