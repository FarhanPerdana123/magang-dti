<?php

namespace OptinCraft\App\Http\Controllers;


defined( "ABSPATH" ) || exit;

use OptinCraft\App\Http\Controllers\Controller;
use OptinCraft\WpMVC\Exceptions\Exception;
use OptinCraft\WpMVC\Routing\Response;
use OptinCraft\WpMVC\RequestValidator\Validator;
use OptinCraft\App\Repositories\ResponseRepository;
use OptinCraft\App\Repositories\AnswerRepository;
use OptinCraft\App\Repositories\CampaignRepository;
use OptinCraft\App\DTO\Response\DTO;
use WP_REST_Request;
use stdClass;

class ResponseController extends Controller {
    public ResponseRepository $repository;

    public AnswerRepository $answer_repository;

    public CampaignRepository $campaign_repository;

    public function __construct( ResponseRepository $repository, AnswerRepository $answer_repository, CampaignRepository $campaign_repository ) {
        $this->repository          = $repository;
        $this->answer_repository   = $answer_repository;
        $this->campaign_repository = $campaign_repository;
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
                'form_data'   => 'required|array',
            ]
        );

        $campaign = $this->campaign_repository->get_by_id( $request->get_param( 'campaign_id' ) );

        if ( ! $campaign ) {
            throw new Exception( esc_html__( 'Campaign not found', 'optincraft' ) );
        }

        // Validate form data and create DTOs.
        $validate_data = $this->validate_form_data( $campaign, $validator, $request );

        if ( ! empty( $validate_data['errors'] ) ) {
            return Response::send( ['messages' => $validate_data['errors']], 422 );
        }

        
        if ( empty( $validate_data['field_dtos'] ) ) {
            return Response::send( ['messages' => esc_html__( 'No form data submitted.', 'optincraft' )], 422 );
        }

        $response_dto = ( new DTO() )->set_campaign_id( $campaign->id );        
        $ip           = optincraft_get_user_ip();
        $browser_info = optincraft_get_browser_info( $request );

        if ( ! empty( $ip ) ) {
            $response_dto->set_ip( $ip );
            $response_dto->set_user_info( optincraft_get_user_infoby_ip( $ip ) );
        }

        if ( ! empty( $browser_info ) ) {
            $response_dto->set_browser( $browser_info['browser'] )->set_browser_version( $browser_info['browser_version'] )->set_device( $browser_info['device'] );
        }

        if ( is_user_logged_in() ) {
            $response_dto->set_user_id( get_current_user_id() );
        }

        $response_id = $this->repository->create( $response_dto );
        $this->answer_repository->creates( $response_id, $validate_data['field_dtos'] );

        do_action( "optincraft_after_create_response", $response_id, $campaign );

        return Response::send(
            [
                "message" => esc_html__( "Item was created successfully", 'optincraft' ),
            ], 201
        );
    }

    public function validate_form_data( stdClass $campaign, Validator $validator, WP_REST_Request $request ): array {
        $form_data = $request->get_param( 'form_data' );
        $request->set_body_params( $form_data );

        $registered_fields = optincraft_config( "fields" );
        $fields            = optincraft_get_campaign_form( $campaign->id );

        $errors        = [];
        $field_dtos    = [];
        $children_dtos = [];

        foreach ( $form_data as $field_name => $field_data ) {
            // Skip if the field is not found in the form's field settings.
            if ( empty( $fields[$field_name] ) ) {
                unset( $form_data[$field_name] );
                continue;
            }

            $field = $fields[$field_name];

            try {
                // Get the field handler for this field type.
                $field_handler = optincraft_field_handler( $field['field_type'] );

                // Validate the field and create its DTO.
                $field_handler->validate( $field, $request, $validator, $campaign );
                $dto = $field_handler->get_field_dto( $field, $request, $campaign );

                $field_dtos[$field['field_name']] = $dto;

            } catch ( Exception $exception ) {
                // Merge any validation errors from the field handler.
                $errors = array_merge( $errors, $exception->get_messages() );
            }
        }

        return compact( 'field_dtos', 'errors' );
    }
}