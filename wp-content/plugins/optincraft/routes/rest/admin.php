<?php

defined( 'ABSPATH' ) || exit;

use OptinCraft\App\Http\Controllers\Admin\CampaignController;
use OptinCraft\App\Http\Controllers\Admin\AnalyticsController;
use OptinCraft\App\Http\Controllers\Admin\ResponseController;
use OptinCraft\App\Http\Controllers\Admin\KpisController;
use OptinCraft\App\Http\Controllers\Admin\SettingsController;
use OptinCraft\App\Http\Controllers\Admin\TemplateController;
use OptinCraft\App\Http\Controllers\Admin\DisplayConditionController;
use OptinCraft\WpMVC\Routing\Route;

Route::group(
    'campaigns', function() {
        Route::get( 'select', [CampaignController::class, 'select'] );
        Route::group(
            '{id}', function() {
                Route::post( 'status', [CampaignController::class, 'update_status'] );
                Route::patch( 'title', [CampaignController::class, 'update_title'] );
                Route::post( 'duplicate', [CampaignController::class, 'duplicate'] );
            }
        );
        Route::resource( '/', CampaignController::class );
    }
);

// Analytics endpoints
Route::group(
    'analytics', function() {
        Route::get( 'kpis', [KpisController::class, 'kpis'] );
        Route::get( 'timeseries', [AnalyticsController::class, 'timeseries'] );
        Route::get( 'campaigns/top', [AnalyticsController::class, 'top_campaigns'] );
        Route::get( 'devices', [AnalyticsController::class, 'devices'] );
        Route::get( 'countries', [AnalyticsController::class, 'countries'] );
        Route::get( 'referrers', [AnalyticsController::class, 'referrers'] );
        Route::get( 'pages', [AnalyticsController::class, 'pages'] );
        Route::get( 'browsers', [AnalyticsController::class, 'browsers'] );
    } 
);

Route::group(
    'campaigns/{campaign_id}/responses', function() {
        Route::get( '/', [ResponseController::class, 'index'] );
        Route::get( '/columns', [ResponseController::class, 'get_columns'] );
    } 
);

// Settings endpoints
Route::group(
    'settings', function() {
        Route::get( '/', [SettingsController::class, 'index'] );
        Route::post( '/', [SettingsController::class, 'update'] );
    } 
);

Route::post( 'templates/insert-attachment', [TemplateController::class, 'insert_attachment'] );

Route::group(
    'display-conditions', function() {
        Route::get( 'select/{field}', [DisplayConditionController::class, 'select'] );
    } 
);