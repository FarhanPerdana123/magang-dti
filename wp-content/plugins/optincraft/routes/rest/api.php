<?php

defined( 'ABSPATH' ) || exit;

use OptinCraft\WpMVC\Routing\Response;
use OptinCraft\App\Jobs\Queue;
use OptinCraft\WpMVC\Routing\Route;
use OptinCraft\App\Http\Controllers\EventsController;
use OptinCraft\App\Http\Controllers\ResponseController;

/**
 * Public routes
 */
Route::post( 'events', [EventsController::class, 'ingest'] );
Route::post( 'responses', [ResponseController::class, 'store'] );

/**
 * Admin routes
 */
Route::group(
    'admin', function () {
        require_once __DIR__ . '/admin.php';
        require_once __DIR__ . '/integrations.php';
    }, ['admin']
);

Route::post(
    'queue/dispatch', function() {
        $queue = optincraft_singleton( Queue::class );
        $queue->dispatch_queue();

        return Response::send( [] );
    } 
);