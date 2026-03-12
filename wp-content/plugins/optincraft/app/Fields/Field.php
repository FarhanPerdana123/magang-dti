<?php

namespace OptinCraft\App\Fields;

defined( 'ABSPATH' ) || exit;

use OptinCraft\App\Repositories\AnswerRepository;
use OptinCraft\WpMVC\RequestValidator\Validator;
use OptinCraft\App\DTO\Answer\DTO;
use WP_REST_Request;
use stdClass;

abstract class Field {
    public AnswerRepository $repository;

    public $has_children = false;

    public function __construct( AnswerRepository $repository ) {
        $this->repository = $repository;
    }

    abstract public static function get_key(): string;

    protected function get_validation_rules( array $field ): array {
        return [];
    }

    public function validate( array $field, WP_REST_Request $request, Validator $validator, stdClass $campaign ) {
        $rules = $this->get_validation_rules( $field );

        if ( isset( $field["required"] ) && $field["required"] ) {
            $rules[] = 'required';
        }

        if ( ! empty( $rules ) ) {

            $validator->validate(
                [
                    $field['field_name'] => implode( '|', $rules ),
                ]
            );
        }
    }

    public function get_field_dto( array $field, WP_REST_Request $request, stdClass $campaign ): DTO {
        return ( new DTO() )->set_campaign_id( $campaign->id )
        ->set_field_type( $field['field_type'] )
        ->set_field_name( $field['field_name'] )
        ->set_value( $request->get_param( $field['field_name'] ) );
    }
}