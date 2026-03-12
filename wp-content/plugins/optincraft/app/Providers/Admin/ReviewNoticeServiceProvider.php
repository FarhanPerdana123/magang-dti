<?php

namespace OptinCraft\App\Providers\Admin;

defined( "ABSPATH" ) || exit;

use OptinCraft\WpMVC\Contracts\Provider;

class ReviewNoticeServiceProvider implements Provider {
    private const USER_META_KEY        = 'optincraft_review_notice_dismissed';
    private const AJAX_ACTION          = 'optincraft_dismiss_review_notice';
    private const PLUGIN_SLUG          = 'optincraft';
    private const WORDPRESS_REVIEW_URL = 'https://wordpress.org/support/plugin/' . self::PLUGIN_SLUG . '/reviews/#new-post';
    private const SUPPORT_URL          = 'https://crafium.com/optincraft';

    public function boot() {
        add_action( 'admin_notices', [ $this, 'display_review_notice' ], 100 );
        add_action( 'wp_ajax_' . self::AJAX_ACTION, [ $this, 'handle_dismiss' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'admin_footer', [ $this, 'enqueue_footer_scripts' ] );
    }

    /**
     * Check if the review notice should be displayed.
     *
     * @return bool
     */
    private function should_show_notice(): bool {
        if ( ! current_user_can( 'manage_options' ) ) {
            return false;
        }

        // Check if 1 week has passed since plugin activation
        $activate_time = get_option( 'optincraft_activate_time' );
        
        if ( ! $activate_time ) {
            return false;
        }

        $one_week_ago = time() - ( 7 * DAY_IN_SECONDS );
        
        if ( (int) $activate_time > $one_week_ago ) {
            return false;
        }

        $user_id   = get_current_user_id();
        $dismissed = get_user_meta( $user_id, self::USER_META_KEY, true );

        if ( $dismissed === 'permanent' || $dismissed === 'already_did' ) {
            return false;
        }

        // Show notice if not dismissed or if dismissed more than 2 weeks ago
        if ( $dismissed && is_numeric( $dismissed ) ) {
            $dismissed_time = (int) $dismissed;
            $two_weeks_ago  = time() - ( 14 * DAY_IN_SECONDS );
            
            if ( $dismissed_time > $two_weeks_ago ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the plugin logo URL.
     *
     * @return string
     */
    private function get_logo_url(): string {
        return optincraft_url( 'assets/img/logo.webp' );
    }

    /**
     * Display the review notice.
     *
     * @return void
     */
    public function display_review_notice(): void {
        if ( ! $this->should_show_notice() ) {
            return;
        }

        $logo_url = $this->get_logo_url();

        ?>
        <div class="optincraft-review-notice-wrapper notice notice-info" id="optincraft-review-notice">
            <div class="optincraft-review-notice">
                <div class="optincraft-review-notice-icon">
                    <img src="<?php echo esc_url( $logo_url ); ?>" alt="OptinCraft" />
                </div>
                <div class="optincraft-review-notice-content">
                    <p class="optincraft-review-notice-text">
                        Hello! It looks like you've been using OptinCraft to build this website — thank you so much!<br>
                        We'd really appreciate it if you could take a moment to give us a <strong>5-star</strong> rating on WordPress. Your feedback helps motivate us and assists other users in making an informed decision when choosing OptinCraft.
                    </p>
                    <div class="optincraft-review-notice-actions">
                        <a href="<?php echo esc_url( self::WORDPRESS_REVIEW_URL ); ?>" 
                           target="_blank" 
                           class="button button-primary optincraft-review-action" 
                           data-action="ok_deserved">
                            Ok, you deserved it
                        </a>
                        <a href="#" 
                           class="optincraft-review-link optincraft-review-action" 
                           data-action="already_did">
                            <span class="optincraft-review-icon">🙂</span>
                            I already did
                        </a>
                        <a href="<?php echo esc_url( self::SUPPORT_URL ); ?>" 
                           target="_blank" 
                           class="optincraft-review-link optincraft-review-action" 
                           data-action="need_support">
                            <span class="optincraft-review-icon dashicons dashicons-admin-tools"></span>
                            I need support
                        </a>
                        <a href="#" 
                           class="optincraft-review-link optincraft-review-action" 
                           data-action="never_ask">
                            <span class="optincraft-review-icon">💬</span>
                            Never ask again
                        </a>
                        <a href="#" 
                           class="optincraft-review-link optincraft-review-action" 
                           data-action="not_good">
                            <span class="optincraft-review-icon">👎</span>
                            No, not good enough
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Handle AJAX dismissal of the review notice.
     *
     * @return void
     */
    public function handle_dismiss(): void {
        check_ajax_referer( self::AJAX_ACTION, 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
            return;
        }

        $action = isset( $_POST['action_type'] ) ? sanitize_text_field( wp_unslash( $_POST['action_type'] ) ) : '';

        if ( empty( $action ) ) {
            wp_send_json_error( [ 'message' => 'Invalid action' ] );
            return;
        }

        $user_id = get_current_user_id();

        switch ( $action ) {
            case 'ok_deserved':
            case 'already_did':
                // Mark as permanently dismissed
                update_user_meta( $user_id, self::USER_META_KEY, 'already_did' );
                break;
            case 'never_ask':
                // Mark as permanently dismissed
                update_user_meta( $user_id, self::USER_META_KEY, 'permanent' );
                break;
            case 'need_support':
                // Don't dismiss for support - just track it
                break;
            case 'not_good':
            default:
                // Dismiss for 2 weeks
                update_user_meta( $user_id, self::USER_META_KEY, time() );
                break;
        }

        wp_send_json_success( [ 'message' => 'Notice dismissed' ] );
    }

    /**
     * Enqueue scripts and styles for the review notice.
     *
     * @return void
     */
    public function enqueue_scripts(): void {
        if ( ! $this->should_show_notice() ) {
            return;
        }

        // Enqueue jQuery if not already enqueued
        wp_enqueue_script( 'jquery' );

        ?>
        <style>
        .optincraft-review-notice-wrapper {
            padding: 12px;
        }

        .optincraft-review-notice {
            display: flex;
            align-items: center;
            gap: 20px;
            padding-bottom: 10px;
            border-radius: 8px;
        }

        .optincraft-review-notice-icon {
            flex-shrink: 0;
            width: 86px;
            height: 86px;
            padding: 8px;
            border-radius: 8px;
            background: linear-gradient(135deg, #fff 0%, #e8f0fe 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }

        .optincraft-review-notice-icon img {
            display: block;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .optincraft-review-notice-content {
            flex: 1;
            min-width: 0;
        }

        .optincraft-review-notice-text {
            margin: 0 0 15px;
            font-size: 13px;
            line-height: 1.6;
            color: #1d2327;
        }

        .optincraft-review-notice-text strong {
            font-weight: 600;
        }

        .optincraft-review-notice-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }

        .optincraft-review-notice-actions .button-primary {
            margin: 0;
        }

        .optincraft-review-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            line-height: 1.5;
            color: #2271b1 !important;
            text-decoration: none;
            transition: color .2s;
        }

        .optincraft-review-link:hover {
            color: #135e96 !important;
            text-decoration: underline;
        }

        .optincraft-review-icon {
            display: inline-flex;
            align-items: center;
            font-size: 16px;
            line-height: 1;
        }

        .optincraft-review-icon.dashicons {
            width: 16px;
            height: 16px;
        }

        @media (max-width: 782px) {
            .optincraft-review-notice-wrapper {
                padding: 10px;
            }

            .optincraft-review-notice {
                flex-direction: column;
                align-items: center;
                gap: 15px;
                padding: 15px 10px;
            }

            .optincraft-review-notice-icon {
                width: 64px;
                height: 64px;
            }

            .optincraft-review-notice-content {
                width: 100%;
                text-align: center;
            }

            .optincraft-review-notice-text {
                margin-bottom: 12px;
            }

            .optincraft-review-notice-actions {
                flex-direction: column;
                width: 100%;
                gap: 10px;
            }

            .optincraft-review-notice-actions .button-primary {
                width: 100%;
                text-align: center;
                justify-content: center;
            }

            .optincraft-review-link {
                width: 100%;
                justify-content: center;
                padding: 8px 0;
            }
        }

        @media (max-width: 480px) {
            .optincraft-review-notice-wrapper {
                padding: 8px;
            }

            .optincraft-review-notice {
                padding: 12px 8px;
                gap: 12px;
            }

            .optincraft-review-notice-icon {
                width: 56px;
                height: 56px;
            }

            .optincraft-review-notice-text {
                margin-bottom: 10px;
                font-size: 12px;
                line-height: 1.5;
            }

            .optincraft-review-notice-actions {
                gap: 8px;
            }

            .optincraft-review-link {
                font-size: 12px;
                padding: 6px 0;
            }
        }
        </style>
        <?php
    }

    /**
     * Enqueue footer scripts for the review notice.
     *
     * @return void
     */
    public function enqueue_footer_scripts(): void {
        if ( ! $this->should_show_notice() ) {
            return;
        }

        $nonce = wp_create_nonce( self::AJAX_ACTION );

        ?>
        <script type="text/javascript">
        (function($) {
            $(document).ready(function() {
                var $notice = $('#optincraft-review-notice').closest('.optincraft-review-notice-wrapper');

                // Handle all review action buttons
                $(document).on('click', '.optincraft-review-action', function(e) {
                    var $button = $(this);
                    var action = $button.data('action');
                    var href = $button.attr('href');
                    var isExternal = $button.attr('target') === '_blank';

                    e.preventDefault();

                    // Dismiss notice
                    $notice.fadeOut(300, function() {
                        $(this).remove();
                    });

                    // Open external links in new window for actions that have them
                    if (isExternal && (action === 'ok_deserved' || action === 'need_support')) {
                        window.open(href, '_blank');
                    }

                    // Send AJAX request
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: '<?php echo esc_js( self::AJAX_ACTION ); ?>',
                            nonce: '<?php echo esc_js( $nonce ); ?>',
                            action_type: action
                        }
                    });
                });
            });
        })(jQuery);
        </script>
        <?php
    }
}