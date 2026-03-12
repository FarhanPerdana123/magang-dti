<?php

namespace OptinCraft\App\Repositories;

defined( "ABSPATH" ) || exit;

use OptinCraft\WpMVC\Repositories\Repository;
use OptinCraft\WpMVC\Database\Query\Builder;
use OptinCraft\WpMVC\Exceptions\Exception;
use OptinCraft\App\DTO\Answer\DTO;
use OptinCraft\App\Models\Answer;

class AnswerRepository extends Repository {
    public function get_query_builder(): Builder {
        return Answer::query();
    }

    public function creates( int $response_id, array $items ) {
        return Answer::query()->insert(
            array_map(
                function( DTO $field ) use( $response_id ) {
                    return $this->process_values( $field->set_response_id( $response_id )->to_array() );
                }, $items
            )
        );
    }

    public function get_by_response_id( int $response_id, bool $key_field_name = false ) {
        $answers = $this->get_query_builder()->where( 'response_id', $response_id )->get();

        if ( $key_field_name ) {
            $processed_answers = [];
            foreach ( $answers as $answer ) {
                $processed_answers[ $answer->field_name ] = $answer;
            }
            return $processed_answers;
        }

        return $answers;
    }
}