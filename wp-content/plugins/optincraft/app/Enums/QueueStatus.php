<?php

namespace OptinCraft\App\Enums;

defined( 'ABSPATH' ) || exit;

class QueueStatus {
    const IN_QUEUE    = 'in_queue';
    const IN_PROGRESS = 'in_progress';
    const COMPLETED   = 'completed';
    const SKIPPED     = 'skipped';
    const FAILED      = 'failed';
}
