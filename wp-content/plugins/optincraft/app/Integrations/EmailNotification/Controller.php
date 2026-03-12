<?php

namespace OptinCraft\App\Integrations\EmailNotification;

defined( "ABSPATH" ) || exit;

use OptinCraft\App\DTO\TaskDTO;
use OptinCraft\WpMVC\RequestValidator\Validator;
use OptinCraft\App\Http\Controllers\Admin\TaskController;
use WP_REST_Request;

class Controller extends TaskController {
    protected function get_type(): string {
        return 'email_notification';
    }

    public function get_store_dto( Validator $validator, WP_REST_Request $request ): TaskDTO {
        $validator->validate(
            [
                'title'                  => 'required|string|max:255',
                'subject'                => 'required|string|max:255',
                'send_to'                => 'required|string|max:255',
                'body'                   => 'required|string',
                'is_conditional_trigger' => 'boolean',
                'conditions'             => 'array',
            ]
        );

        $data = [
            'title'                  => $request->get_param( "title" ),
            'subject'                => $request->get_param( "subject" ),
            'send_to'                => $request->get_param( "send_to" ),
            'body'                   => $request->get_param( "body" ),
            'is_conditional_trigger' => $request->has_param( "is_conditional_trigger" ) ? $request->get_param( "is_conditional_trigger" ) : false,
            'conditions'             => $request->get_param( "conditions" ) ?? [],
        ];

        return ( new TaskDTO() )->set_data( $data );
    }

    public function get_update_dto( Validator $validator, WP_REST_Request $request ): TaskDTO {
        return $this->get_store_dto( $validator, $request );
    }
}