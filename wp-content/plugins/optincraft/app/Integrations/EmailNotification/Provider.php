<?php

namespace OptinCraft\App\Integrations\EmailNotification;

defined( "ABSPATH" ) || exit;

use OptinCraft\App\Enums\QueueStatus;
use OptinCraft\WpMVC\Contracts\Provider as WpMVCProvider;

class Provider implements WpMVCProvider {
    const TASK_TYPE = 'email_notification';

    public function boot() {
        $task_type = self::TASK_TYPE;
        add_action( "optincraft_process_{$task_type}", [$this, 'process_task'], 10, 2 );
    }

    public function process_task( \stdClass $queue, callable $callback ) {
        $headers = [
            'Content-Type: text/html; charset=UTF-8'
        ];

        if ( ! empty( $queue->data['cc'] ) ) {
            $headers[] = 'Cc: ' . $queue->data['cc'];
        }

        if ( ! empty( $queue->data['bcc'] ) ) {
            $headers[] = 'Bcc: ' . $queue->data['bcc'];
        }

        if ( ! empty( $queue->data['reply_to'] ) ) {
            $headers[] = 'Reply-To: ' . $queue->data['reply_to'];
        }

        wp_mail( $queue->data['send_to'], $queue->data['subject'], $queue->data['body'], $headers );

        $callback( QueueStatus::COMPLETED );
    }
}