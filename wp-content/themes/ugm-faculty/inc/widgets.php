<?php
/**
 * Widget areas registration
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register widget areas.
 */
function ugm_footer_widgets_init() {
	// Footer Quick Links Widget Area.
	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer Quick Links Widget', 'ugm-faculty' ),
			'id'            => 'footer-quick-links-widget',
			'description'   => esc_html__( 'Optional widget area for additional quick links in footer.', 'ugm-faculty' ),
			'before_widget' => '<section class="widget %2$s" id="%1$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		)
	);

	// Footer Institutional Widget Area.
	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer Institutional Widget', 'ugm-faculty' ),
			'id'            => 'footer-institutional-widget',
			'description'   => esc_html__( 'Optional widget area for accreditation badges or institutional notes.', 'ugm-faculty' ),
			'before_widget' => '<section class="widget %2$s" id="%1$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h4 class="widget-title">',
			'after_title'   => '</h4>',
		)
	);
}
add_action( 'widgets_init', 'ugm_footer_widgets_init' );
