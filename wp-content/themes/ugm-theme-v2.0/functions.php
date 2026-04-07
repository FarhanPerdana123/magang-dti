<?php

define( 'UGM_THEME_NAME',       get_template() );

/* Directory path */
define( 'UGM_THEME_DIR',        get_template_directory() );
define( 'UGM_THEME_DIR_ASSETS', get_template_directory() . '/assets' );
define( 'UGM_THEME_DIR_INC',    get_template_directory() . '/inc' );

/* Directory uri */
define( 'UGM_THEME_URI',        get_template_directory_uri() );
define( 'UGM_THEME_URI_ASSETS', get_template_directory_uri() . '/assets' );
define( 'UGM_THEME_URI_INC',    get_template_directory_uri() . '/inc' );

// load libraries.
require_once UGM_THEME_DIR_INC . '/libs/advanced-custom-fields-pro/acf.php';
require_once UGM_THEME_DIR_INC . '/libs/acf-code-field/acf-code-field.php';
require_once UGM_THEME_DIR_INC . '/libs/acf-cf7/acf-cf7.php';
require_once UGM_THEME_DIR_INC . '/libs/tgm-plugin-activation/class-tgm-plugin-activation.php';
require_once UGM_THEME_DIR_INC . '/libs/ugm-blocks/ugm-blocks.php';

// load functions.
require_once UGM_THEME_DIR_INC . '/function-helpers.php';
require_once UGM_THEME_DIR_INC . '/function-template.php';

// load walkers.
require_once UGM_THEME_DIR_INC . '/walker/class-ugm-walker-comment.php';
require_once UGM_THEME_DIR_INC . '/walker/class-ugm-walker-nav.php';
require_once UGM_THEME_DIR_INC . '/walker/class-ugm-walker-nav-burger.php';

// load widgets.
require_once UGM_THEME_DIR_INC . '/widget/class-ugm-widget-recent-posts.php';
require_once UGM_THEME_DIR_INC . '/widget/class-ugm-widget-recent-events.php';
require_once UGM_THEME_DIR_INC . '/widget/class-ugm-widget-related-posts.php';
require_once UGM_THEME_DIR_INC . '/widget/class-ugm-widget-rss.php';

// theme core.
require_once UGM_THEME_DIR_INC . '/class-ugm-post-types.php';
require_once UGM_THEME_DIR_INC . '/class-ugm-custom-fields.php';
require_once UGM_THEME_DIR_INC . '/class-ugm-scripts.php';
require_once UGM_THEME_DIR_INC . '/class-ugm-hooks-front.php';
require_once UGM_THEME_DIR_INC . '/class-ugm-hooks-admin.php';
require_once UGM_THEME_DIR_INC . '/class-ugm-config-writer.php';
require_once UGM_THEME_DIR_INC . '/class-ugm-blocks-importer.php';

class UGM_Theme {

    public function __construct() {
        add_action( 'after_setup_theme', [ $this, 'theme_setup' ] );
        add_action( 'after_setup_theme', [ $this, 'load_translation' ] );
        add_filter( 'excerpt_length', [ $this, 'set_excerpt_size' ] );
        add_action( 'widgets_init', [ $this, 'register_custom_widgets' ] );
        add_action( 'tgmpa_register', [ $this, 'register_required_plugins' ] );
        register_activation_hook( 'wp-maintenance-mode/wp-maintenance-mode.php', [ $this, 'set_maintenance_page' ] );

        add_shortcode( 'ugm_breadcrumb', [ $this, 'shortcode_breadcrumb' ] );
    }

    public function theme_setup() {
        // Set title will be set by WordPress.
        add_theme_support( 'title-tag' );

        // Enable support for Post Thumbnails on posts and pages.
        add_theme_support( 'post-thumbnails' );

        // Set navigation menus on themes.
        register_nav_menus( array(
            'primary' => __( 'Primary Menu'),
            'top' => __( 'Top Menu'),
            'footer' => __( 'Footer Menu')
        ) );

        // Set sidebar.
        register_sidebar( array(
            'name'          => __( 'Sidebar Widget'),
            'id'            => 'sidebar-widget',
            'description'   => __( 'Ditampilkan pada sidebar utama'),
            'before_widget' => '<div id="%1$s" class="col-md-12 widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<div class="widget-header"><h2 class="widget-title">',
            'after_title'   => '</h2></div>',
        ) );
        register_sidebar( array(
            'name'          => __( 'Secondary Sidebar Widget'),
            'id'            => 'secondary-sidebar-widget',
            'description'   => __( 'Ditampilkan hanya pada layout 3 kolom'),
            'before_widget' => '<div id="%1$s" class="col-md-12 widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<div class="widget-header"><h2 class="widget-title">',
            'after_title'   => '</h2></div>',
        ) );
        register_sidebar( array(
            'name'          => __( 'Footer Widget'),
            'id'            => 'footer-widget',
            'description'   => __( 'Ditampilkan pada footer'),
            'before_widget' => '<div id="%1$s" class="col-md-3 widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<div class="widget-header"><h2 class="widget-title">',
            'after_title'   => '</h2></div>',
        ) );

        // Core markup for this part as valid HTML4.
        add_theme_support( 'html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
        ) );

        // Add image size.
        add_image_size( 'ugm-post-content', 825, 510 );
        add_image_size( 'ugm-archive-thumbnail-small', 270, 182, true );
        add_image_size( 'ugm-archive-thumbnail-medium', 360, 242, true );
        add_image_size( 'ugm-archive-thumbnail-large', 560, 376, true );
        add_image_size( 'ugm-post-slider-wide', 1140, 400, true );
    }

    public function load_translation() {
        load_theme_textdomain( 'ugm-theme', UGM_THEME_DIR . '/languages' );
    }

    public function set_excerpt_size( $length ) {
        return 35;
    }

    public function register_custom_widgets() {
        unregister_widget( 'WP_Widget_Recent_Posts' );
        unregister_widget( 'WP_Widget_RSS' );
        register_widget( 'UGM_Widget_Recent_Posts' );
        register_widget( 'UGM_Widget_Recent_Events' );
        register_widget( 'UGM_Widget_Related_Posts' );
        register_widget( 'UGM_Widget_RSS' );
    }

    public function register_required_plugins() {
        $plugins = array(
            array(
                'name'               => 'Classic Widgets',
                'slug'               => 'classic-widgets',
                'required'           => true,
                'force_activation'   => true,
                'force_deactivation' => false,
            ),
            array(
                'name'               => 'One Click Accessibility',
                'slug'               => 'pojo-accessibility',
                'required'           => true,
                'force_activation'   => true,
                'force_deactivation' => false,
            ),
            array(
                'name'               => 'Polylang',
                'slug'               => 'polylang',
                'required'           => false,
                'force_activation'   => false,
                'force_deactivation' => false,
            ),
            array(
                'name'               => 'Contact Form 7',
                'slug'               => 'contact-form-7',
                'required'           => true,
                'force_activation'   => true,
                'force_deactivation' => false,
            ),
            array(
                'name'               => 'WP Google Maps',
                'slug'               => 'wp-google-maps',
                'source'             => UGM_THEME_DIR_INC . '/plugins/wp-google-maps.zip',
                'required'           => false,
                'force_activation'   => false,
                'force_deactivation' => false,
            ),
            array(
                'name'               => 'Advanced Tabs',
                'slug'               => 'advanced-tabs-block',
                'required'           => false,
                'force_activation'   => false,
                'force_deactivation' => false,
            ),
            array(
                'name'               => 'TablePress',
                'slug'               => 'tablepress',
                'required'           => false,
                'force_activation'   => false,
                'force_deactivation' => false,
            ),
            array(
                'name'               => 'WP Maintenance Mode',
                'slug'               => 'wp-maintenance-mode',
                'source'             => UGM_THEME_DIR_INC . '/plugins/wp-maintenance-mode.zip',
                'required'           => false,
                'force_activation'   => false,
                'force_deactivation' => false,
            )
        );

        $config = array(
            'id'           => 'ugm_tgm',                // Unique ID for hashing notices for multiple instances of TGMPA.
            'default_path' => '',                       // Default absolute path to bundled plugins.
            'menu'         => 'ugm-install-plugins',    // Menu slug.
            'parent_slug'  => 'themes.php',             // Parent menu slug.
            'capability'   => 'edit_theme_options',     // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
            'has_notices'  => true,                     // Show admin notices or not.
            'dismissable'  => true,                     // If false, a user cannot dismiss the nag message.
            'dismiss_msg'  => '',                       // If 'dismissable' is false, this message will be output at top of nag.
            'is_automatic' => true,                     // Automatically activate plugins after installation or not.
            'message'      => '',                       // Message to output right before the plugins table.
        );

        tgmpa( $plugins, $config );
    }

    public function set_maintenance_page() {
        copy( UGM_THEME_DIR_INC . '\plugins\wp-maintenance-mode.php', WP_CONTENT_DIR . '\wp-maintenance-mode.php' );
    }

    public function shortcode_breadcrumb() {
        ob_start();
        ugm_breadcrumbs();
        return ob_get_clean();
    }

}
new UGM_Theme();
