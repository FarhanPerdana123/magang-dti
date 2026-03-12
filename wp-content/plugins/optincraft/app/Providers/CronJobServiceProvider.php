<?php

namespace OptinCraft\App\Providers;

defined( "ABSPATH" ) || exit;

use OptinCraft\App\Models\Analytics\Event;
use OptinCraft\App\Models\Analytics\PageStat;
use OptinCraft\App\Models\Analytics\PageView;
use OptinCraft\App\Models\Analytics\ReferrerStat;
use OptinCraft\App\Models\Analytics\BrowserStat;
use OptinCraft\App\Models\Analytics\CampaignStat;
use OptinCraft\App\Models\Analytics\CountryStat;
use OptinCraft\App\Models\Analytics\DeviceStat;
use OptinCraft\WpMVC\Contracts\Provider;
use OptinCraft\WpMVC\Exceptions\Exception;

class CronJobServiceProvider implements Provider {
    public function boot() {
        if ( ! wp_next_scheduled( 'optincraft_daily_cron' ) ) {
            wp_schedule_event( time(), 'daily', 'optincraft_daily_cron' );
        }

        add_action( 'optincraft_daily_cron', [ $this, 'handle_daily_cron' ] );
    }

    /**
     * Handle the daily cron job execution.
     *
     * @return void
     */
    public function handle_daily_cron() {
        $settings                = optincraft_settings_repository()->get();
        $analytics_data_duration = $settings['analytics_data_duration'] ?? 'forever';

        if ( $analytics_data_duration === 'forever' ) {
            return;
        }

        // Map duration values to days
        $duration_days = [
            '30_days'  => 30,
            '60_days'  => 60,
            '90_days'  => 90,
            '180_days' => 180,
            '1_year'   => 365,
            '2_years'  => 730,
            '5_years'  => 1825,
        ];

        if ( ! isset( $duration_days[ $analytics_data_duration ] ) ) {
            return;
        }

        $days        = $duration_days[ $analytics_data_duration ];
        $cutoff_date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        try {
            // Delete records from tables that use stat_date
            BrowserStat::query()
                ->where( 'stat_date', '<', $cutoff_date )
                ->delete();

            CampaignStat::query()
                ->where( 'stat_date', '<', $cutoff_date )
                ->delete();

            CountryStat::query()
                ->where( 'stat_date', '<', $cutoff_date )
                ->delete();

            DeviceStat::query()
                ->where( 'stat_date', '<', $cutoff_date )
                ->delete();

            PageStat::query()
                ->where( 'stat_date', '<', $cutoff_date )
                ->delete();

            ReferrerStat::query()
                ->where( 'stat_date', '<', $cutoff_date )
                ->delete();

            // Delete records from tables that use created_at
            Event::query()
                ->where( 'created_at', '<', $cutoff_date )
                ->delete();

            PageView::query()
                ->where( 'created_at', '<', $cutoff_date )
                ->delete();
        } catch ( Exception $e ) {
            //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log( 'OptinCraft daily cron error: ' . $e->getMessage() );
        }
    }
}