<?php

namespace OptinCraft\App\Http\Controllers\Admin;

defined( "ABSPATH" ) || exit;

use OptinCraft\App\Http\Controllers\Controller;
use OptinCraft\WpMVC\Exceptions\Exception;
use OptinCraft\WpMVC\Routing\Response;
use OptinCraft\WpMVC\RequestValidator\Validator;
use WP_REST_Request;
use OptinCraft\App\Repositories\SettingsRepository;

class SettingsController extends Controller {
    public SettingsRepository $repository;

    public function __construct( SettingsRepository $repository ) {
        $this->repository = $repository;
    }

    public function index( Validator $validator, WP_REST_Request $request ): array {
        return Response::send( $this->repository->get() );
    }

    public function update( Validator $validator, WP_REST_Request $request ) {
        // $validator->validate(
        //     [
        //         'settings' => 'required|array'
        //     ]
        // );

        $this->repository->update_settings( $request->get_params() );

        return Response::send(
            [
                'message' => esc_html__( 'Settings have been saved successfully.', 'optincraft' )
            ]
        );
    }
}