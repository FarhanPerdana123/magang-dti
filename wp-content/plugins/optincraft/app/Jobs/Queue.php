<?php

namespace OptinCraft\App\Jobs;

defined( "ABSPATH" ) || exit;

use OptinCraft\App\Enums\QueueStatus;
use OptinCraft\App\Repositories\QueueRepository;
use OptinCraft\App\Repositories\SettingsRepository;
use OptinCraft\WpMVC\Queue\Sequence;

class Queue extends Sequence {
    protected $prefix = 'optincraft';

    protected $action = 'queue'; 

    protected function sleep_on_rest_time() {
        return true;
    }

    protected function triggered_error( ?array $error ) {
        update_option( 'optincraft_lock_queue', 1 );
    }

    protected function get_item( $item ) {
        $repository = optincraft_singleton( QueueRepository::class );
        $queue      = $repository->get_by_first_queue();

        if ( ! $queue ) {
            return false;
        }

        $queue->data = json_decode( $queue->data, true );

        $repository->update_status( $queue->id, QueueStatus::IN_PROGRESS );

        $item['queue'] = $queue;

        return $item;
    }

    protected function perform_sequence_task( $item ) {
        if ( empty( $item['queue'] ) ) {
            return true;
        }

        $queue               = $item['queue'];
        $repository          = optincraft_singleton( QueueRepository::class );
        $settings_repository = optincraft_singleton( SettingsRepository::class );

        $integration_status = $settings_repository->get_by_key( 'integrations.' . $queue->task_type . '.status' );

        if ( ! $integration_status ) {
            $repository->delete_by_id( $queue->id );
            return $item;
        }

        $answers                = optincraft_answer_repository()->get_by_response_id( $queue->response_id, true );
        $queue->data['answers'] = $answers;

        $is_allowed = apply_filters( 'optincraft_is_allowed_queue', true, $queue );

        if ( ! $is_allowed ) {
            $repository->delete_by_id( $queue->id );
            return $item;
        }

        $is_queue_processed = false;

        try {
            do_action(
                "optincraft_process_{$queue->task_type}", $queue, function( $status ) use ( $repository, $queue, &$is_queue_processed ) {
                    if ( QueueStatus::COMPLETED === $status ) {
                        $repository->delete_by_id( $queue->id );
                    } else {
                        $repository->update_status( $queue->id, $status );
                    }
                    $is_queue_processed = true;
                } 
            );
        } catch ( \Throwable $th ) {
            $repository->update_status( $queue->id, QueueStatus::FAILED );
        }

        if ( ! $is_queue_processed ) {
            $repository->update_status( $queue->id, QueueStatus::FAILED );
        }

        return $item;
    }

    public function dispatch_queue() {
        if ( $this->is_active() ) {
            $lock = get_option( 'optincraft_lock_queue', 0 );

            if ( 1 == $lock ) {
                update_option( 'optincraft_lock_queue', 0 );
                $this->unlock_process()->dispatch();
            }

            return;
        }

        $this->push_to_queue( [] )->save()->dispatch();
    }
}