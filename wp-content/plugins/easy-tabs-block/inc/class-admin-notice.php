<?php

/**
 * Admin Notice Handler
 * 
 * Handles promotional notices in the WordPress admin dashboard
 * 
 * @package Easy_Tabs_Block
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class Easy_Tabs_Block_Admin_Notice
 */
class Easy_Tabs_Block_Admin_Notice {

    /**
     * API URL for fetching promotions
     */
    const API_URL = 'https://easytabsblock.com/wp-json/org/promotions/';

    /**
     * Transient key for caching API response
     */
    const CACHE_KEY = 'etb_promotions_notice_cache';

    /**
     * Cache duration (12 hours)
     */
    const CACHE_DURATION = 12 * HOUR_IN_SECONDS;

    /**
     * Option key for storing dismissed notices
     */
    const DISMISSED_KEY = 'etb_dismissed_notice';

    /**
     * Flag to prevent duplicate asset output
     */
    private static $assets_printed = false;

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_notices', [$this, 'display_notice']);
        add_action('wp_ajax_etb_dismiss_notice', [$this, 'handle_dismiss']);
    }

    /**
     * Fetch promotions from API
     * 
     * @return array|false Array of promotions or false on failure
     */
    private function fetch_promotions() {
        // Try to get cached data
        $cached = get_transient(self::CACHE_KEY);
        if (false !== $cached) {
            return $cached;
        }

        // Fetch from API
        $response = wp_remote_get(self::API_URL, [
            'timeout' => 15,
            'sslverify' => true,
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        // Check HTTP response code
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Validate data structure
        if (!is_array($data) || empty($data)) {
            return false;
        }

        // Cache the response
        set_transient(self::CACHE_KEY, $data, self::CACHE_DURATION);

        return $data;
    }

    /**
     * Get dismissed notice keys for current user
     * 
     * @return array Array of dismissed notice keys
     */
    private function get_dismissed_notices() {
        $dismissed = get_option(self::DISMISSED_KEY, []);
        return is_array($dismissed) ? $dismissed : [];
    }

    /**
     * Check if a notice has been dismissed
     * 
     * @param string $key Notice key
     * @return bool True if dismissed, false otherwise
     */
    private function is_notice_dismissed($key) {
        $dismissed = $this->get_dismissed_notices();
        return in_array($key, $dismissed, true);
    }

    /**
     * Get active promotion to display
     * 
     * @return array|null Promotion data or null if none active
     */
    private function get_active_promotion() {
        $promotions = $this->fetch_promotions();

        if (!$promotions || !is_array($promotions)) {
            return null;
        }

        $current_time = current_time('timestamp');

        foreach ($promotions as $promotion) {
            // Validate promotion is an array
            if (!is_array($promotion)) {
                continue;
            }

            // Validate required fields are strings
            if (!isset($promotion['key']) || !is_string($promotion['key']) || empty($promotion['key'])) {
                continue;
            }
            if (!isset($promotion['title']) || !is_string($promotion['title']) || empty($promotion['title'])) {
                continue;
            }

            // Check if dismissed
            if ($this->is_notice_dismissed($promotion['key'])) {
                continue;
            }

            // Check date range
            $start_date = !empty($promotion['start_date']) ? strtotime($promotion['start_date']) : 0;
            $end_date = !empty($promotion['end_date']) ? strtotime($promotion['end_date']) : PHP_INT_MAX;

            // Validate dates
            if ($start_date === false || $end_date === false) {
                continue;
            }

            if ($current_time >= $start_date && $current_time <= $end_date) {
                return $promotion;
            }
        }

        return null;
    }

    /**
     * Display the admin notice
     */
    public function display_notice() {
        // Only show to users who can manage options
        if (!current_user_can('manage_options')) {
            return;
        }

        // Don't show if Easy Tabs Block Pro is active
        if (defined('ETBP_VERSION')) {
            return;
        }

        $promotion = $this->get_active_promotion();

        if (!$promotion) {
            return;
        }

        $key = sanitize_key($promotion['key']);
        $title = $promotion['title'];
        $content = !empty($promotion['content']) && is_string($promotion['content']) ? $promotion['content'] : '';
        $action_url = !empty($promotion['action_url']) && is_string($promotion['action_url']) ? $promotion['action_url'] : '';
        $action_title = !empty($promotion['action_title']) && is_string($promotion['action_title']) ? $promotion['action_title'] : __('Get Now', 'easy-tabs-block');

        // Output notice only once per page load
        if (self::$assets_printed) {
            return;
        }
        self::$assets_printed = true;

?>
        <div class="notice notice-info easy-tabs-block-sale-notice" data-notice-key="<?php echo esc_attr($key); ?>">
            <div class="easy-tabs-block-sale-notice-container">
                <div class="easy-tabs-block-sale-notice-logo">
                    <img width="82" src="<?php echo esc_url(plugins_url('../assets/images/easy-tabs-block-logo.png', __FILE__)); ?>" alt="Easy Tabs Block">
                </div>
                <div class="easy-tabs-block-sale-notice-content">
                    <h3 class="easy-tabs-block-sale-notice-title"><?php echo esc_html($title); ?></h3>
                    <?php if ($content): ?>
                        <p class="easy-tabs-block-sale-notice-description"><?php echo esc_html($content); ?></p>
                    <?php endif; ?>
                    <div class="easy-tabs-block-sale-notice-actions">
                        <?php if ($action_url): ?>
                            <a class="easy-tabs-block-btn easy-tabs-block-btn-primary" href="<?php echo esc_url($action_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($action_title); ?></a>
                        <?php endif; ?>
                        <button type="button" class="easy-tabs-block-btn easy-tabs-block-btn-secondary" data-key="<?php echo esc_attr($key); ?>"><?php esc_html_e('Dismiss', 'easy-tabs-block'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <style>
            .easy-tabs-block-sale-notice {
                border-left-color: #154618;
            }

            .easy-tabs-block-sale-notice-container {
                display: flex;
                align-items: center;
                padding: 0.625rem 0;
                gap: 1rem;
            }

            .easy-tabs-block-sale-notice-logo img {
                display: block;
            }

            .easy-tabs-block-sale-notice-title {
                color: #000000;
                font-size: 14px;
                font-weight: 700;
                margin: 0 0 4px !important;
            }

            .easy-tabs-block-sale-notice-description {
                color: #888888;
                font-weight: 400;
                font-size: 13px;
                margin: 0 !important;
            }

            .easy-tabs-block-sale-notice-actions {
                display: flex;
                gap: 0.5rem;
                margin-top: 6px;
            }

            .easy-tabs-block-btn {
                font-size: 12px;
                font-weight: 600;
                line-height: 1.3;
                padding: 6px 12px;
                border-radius: 3px;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
                transition: all 0.2s ease-in-out;
            }

            .easy-tabs-block-btn:focus {
                outline: none;
                box-shadow: none;
            }

            .easy-tabs-block-btn-primary {
                color: #fff;
                background: #154618;
                border: 1px solid #154618;

            }

            .easy-tabs-block-btn-secondary {
                color: #154618;
                background: #fff;
                border: 1px solid #154618;
            }

            .easy-tabs-block-btn-primary:hover {
                background: transparent;
                color: #154618;
            }

            .easy-tabs-block-btn-secondary:hover {
                background: #154618;
                color: #fff;
            }
        </style>
        <script>
            (function() {
                var noticeKey = <?php echo json_encode($key, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                var nonce = <?php echo json_encode(wp_create_nonce('etb_dismiss_notice'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                var ajaxUrl = <?php echo json_encode(admin_url('admin-ajax.php'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

                var notice = document.querySelector('.notice[data-notice-key="' + noticeKey + '"]');
                if (notice) {
                    var dismissBtn = notice.querySelector('.easy-tabs-block-btn-secondary');
                    if (dismissBtn) {
                        dismissBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            var formData = new FormData();
                            formData.append('action', 'etb_dismiss_notice');
                            formData.append('key', noticeKey);
                            formData.append('nonce', nonce);
                            fetch(ajaxUrl, {
                                method: 'POST',
                                body: formData,
                                credentials: 'same-origin'
                            }).then(function(response) {
                                if (response.ok) {
                                    notice.remove();
                                }
                            }).catch(function(error) {
                                console.error('ETB Notice: Dismiss failed', error);
                            });
                        });
                    }
                }
            })();
        </script>
<?php
    }

    /**
     * Handle AJAX dismiss request
     */
    public function handle_dismiss() {
        // Verify nonce
        check_ajax_referer('etb_dismiss_notice', 'nonce');

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'easy-tabs-block')], 403);
        }

        // Don't allow dismissing if Pro is active (notice shouldn't show anyway)
        if (defined('ETBP_VERSION')) {
            wp_send_json_error(['message' => __('Invalid request', 'easy-tabs-block')], 400);
        }

        // Get and validate notice key
        $key = isset($_POST['key']) ? sanitize_key($_POST['key']) : '';

        if (empty($key)) {
            wp_send_json_error(['message' => __('Invalid notice key', 'easy-tabs-block')], 400);
        }

        // Add to dismissed notices
        $dismissed = $this->get_dismissed_notices();
        if (!in_array($key, $dismissed, true)) {
            $dismissed[] = $key;
            $updated = update_option(self::DISMISSED_KEY, $dismissed, false);

            if (!$updated && !in_array($key, get_option(self::DISMISSED_KEY, []))) {
                wp_send_json_error(['message' => __('Failed to save', 'easy-tabs-block')], 500);
            }
        }

        wp_send_json_success(['message' => __('Notice dismissed', 'easy-tabs-block')]);
    }
}
