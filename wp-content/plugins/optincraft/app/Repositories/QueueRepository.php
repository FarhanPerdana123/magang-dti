<?php

namespace OptinCraft\App\Repositories;

defined( "ABSPATH" ) || exit;

use OptinCraft\App\Models\Task;
use OptinCraft\WpMVC\Repositories\Repository;
use OptinCraft\WpMVC\Database\Query\Builder;
use OptinCraft\WpMVC\Exceptions\Exception;
use OptinCraft\App\Models\Queue;

class QueueRepository extends Repository {
    public function get_query_builder(): Builder {
        return Queue::query( 'queue' );
    }

    public function get_by_first_queue() {
        return $this->get_query_builder()->select(
            'queue.id',
            'queue.campaign_id',
            'queue.response_id',
            'queue.queue_type',
            'queue.status',
            'queue.task_id',
            'task.type as task_type',
            'task.data'
        )->join( Task::get_table_name() . ' as task', 'queue.task_id', '=', 'task.id' )->where( 'queue.status', 'in_queue' )->first();
    }

    public function update_status( $id, $status ) {
        return $this->get_query_builder()->where( 'id', $id )->update( [ 'status' => $status ] );
    }
}