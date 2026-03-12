<?php

namespace OptinCraft\App\Repositories;

defined( "ABSPATH" ) || exit;

use OptinCraft\WpMVC\Repositories\Repository;
use OptinCraft\WpMVC\Database\Query\Builder;
use OptinCraft\WpMVC\Exceptions\Exception;
use OptinCraft\App\Models\Task;
use OptinCraft\App\DTO\TaskRead;

class TaskRepository extends Repository {
    public function get_query_builder(): Builder {
        return Task::query();
    }

    public function get( TaskRead $dto ) {
        $query = $this->get_query_builder()->where( 'type', $dto->get_type() )->where( 'campaign_id', $dto->get_campaign_id() );

        $total_query = clone $query;

        $tasks = array_map(
            function( $task ) {
                $task->status = (bool) $task->status;
                $data         = json_decode( $task->data, true );
                foreach ( $data as $key => $value ) {
                    $task->{$key} = $value;
                }
                unset( $task->data );
                unset( $task->type );
                
                return $task;
            }, $query->order_by_desc( "id" )->pagination( $dto->get_page(), $dto->get_per_page() )
        );

        return [
            'total' => $total_query->count( 'id' ),
            'items' => $tasks
        ];
    }

    public function update_status( $id, $value ) {
        $this->get_query_builder()->where( 'id', $id )->update( [ 'status' => $value ] );
    }

    public function update_data( $id, $data ) {
        $task = $this->get_by_id( $id );
        $data = array_merge( json_decode( $task->data, true ), $data );
        $this->get_query_builder()->where( 'id', $id )->update( $this->process_values( [ 'data' => $data ] ) );
    }
}