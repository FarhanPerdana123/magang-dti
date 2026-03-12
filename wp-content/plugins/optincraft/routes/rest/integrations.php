<?php

defined( 'ABSPATH' ) || exit;

use OptinCraft\App\Integrations\EmailNotification\Controller as EmailNotificationController;
use OptinCraft\WpMVC\Routing\Route;

Route::group(
    'integrations/{campaign_id}', function() {
        Route::post( 'email-notification/{id}/status', [EmailNotificationController::class, 'update_status'] );
        Route::resource( 'email-notification', EmailNotificationController::class );
    }, ['admin']
);