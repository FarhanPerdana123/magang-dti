<?php
/**
 * Theme Customizer settings
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add customizer settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function ugm_customize_register( $wp_customize ) {
	// UGM Branding Section.
	$wp_customize->add_section(
		'ugm_branding',
		array(
			'title'    => __( 'UGM Branding', 'ugm-faculty' ),
			'priority' => 35,
		)
	);

	// Dark Logo Setting.
	$wp_customize->add_setting(
		'ugm_logo_dark',
		array(
			'sanitize_callback' => 'absint',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'ugm_logo_dark',
			array(
				'label'     => __( 'Dark Logo (solid navbar)', 'ugm-faculty' ),
				'section'   => 'ugm_branding',
				'mime_type' => 'image',
			)
		)
	);

	// Light Logo Setting.
	$wp_customize->add_setting(
		'ugm_logo_light',
		array(
			'sanitize_callback' => 'absint',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'ugm_logo_light',
			array(
				'label'     => __( 'Light Logo (transparent navbar)', 'ugm-faculty' ),
				'section'   => 'ugm_branding',
				'mime_type' => 'image',
			)
		)
	);

	// Front Page Hero Section.
	$wp_customize->add_section(
		'ugm_hero_section',
		array(
			'title'    => __( 'Front Page Hero', 'ugm-faculty' ),
			'priority' => 36,
		)
	);

	// Hero Background Image.
	$wp_customize->add_setting(
		'ugm_hero_background_image',
		array(
			'sanitize_callback' => 'absint',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'ugm_hero_background_image',
			array(
				'label'   => __( 'Hero Background Image', 'ugm-faculty' ),
				'section' => 'ugm_hero_section',
			)
		)
	);

	// Hero Headline.
	$wp_customize->add_setting(
		'ugm_hero_headline',
		array(
			'default'           => get_bloginfo( 'name' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'ugm_hero_headline',
		array(
			'label'   => __( 'Hero Headline', 'ugm-faculty' ),
			'section' => 'ugm_hero_section',
			'type'    => 'text',
		)
	);

	// Hero Description.
	$wp_customize->add_setting(
		'ugm_hero_description',
		array(
			'default'           => get_bloginfo( 'description' ),
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);

	$wp_customize->add_control(
		'ugm_hero_description',
		array(
			'label'   => __( 'Hero Description', 'ugm-faculty' ),
			'section' => 'ugm_hero_section',
			'type'    => 'textarea',
		)
	);

	// Front Page Sections.
	$wp_customize->add_section(
		'ugm_home_sections',
		array(
			'title'    => __( 'Front Page Sections', 'ugm-faculty' ),
			'priority' => 37,
		)
	);

	// Number fields for home sections.
	$number_fields = array(
		'ugm_featured_post_id'          => __( 'Featured Post ID', 'ugm-faculty' ),
		'ugm_featured_category_id'      => __( 'Featured Category ID', 'ugm-faculty' ),
		'ugm_news_category_id'          => __( 'Latest News Category ID', 'ugm-faculty' ),
		'ugm_announcements_category_id' => __( 'Announcements Category ID', 'ugm-faculty' ),
		'ugm_events_category_id'        => __( 'Events Category ID', 'ugm-faculty' ),
		'ugm_latest_news_count'         => __( 'Latest News Count', 'ugm-faculty' ),
		'ugm_announcements_count'       => __( 'Announcements Count', 'ugm-faculty' ),
		'ugm_events_count'              => __( 'Events Count', 'ugm-faculty' ),
	);

	foreach ( $number_fields as $id => $label ) {
		$wp_customize->add_setting(
			$id,
			array(
				'sanitize_callback' => 'absint',
			)
		);

		$wp_customize->add_control(
			$id,
			array(
				'label'   => $label,
				'section' => 'ugm_home_sections',
				'type'    => 'number',
			)
		);
	}

	// Text fields for home sections.
	$text_fields = array(
		'ugm_featured_section_title'      => __( 'Featured Section Title', 'ugm-faculty' ),
		'ugm_latest_section_title'        => __( 'Latest News Section Title', 'ugm-faculty' ),
		'ugm_announcements_section_title' => __( 'Announcements Section Title', 'ugm-faculty' ),
		'ugm_events_section_title'        => __( 'Events Section Title', 'ugm-faculty' ),
		'ugm_categories_section_title'    => __( 'Categories Section Title', 'ugm-faculty' ),
		'ugm_content_category_ids'        => __( 'Content Category IDs (comma separated)', 'ugm-faculty' ),
	);

	foreach ( $text_fields as $id => $label ) {
		$wp_customize->add_setting(
			$id,
			array(
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		$wp_customize->add_control(
			$id,
			array(
				'label'   => $label,
				'section' => 'ugm_home_sections',
				'type'    => 'text',
			)
		);
	}

	// UGM Footer Section.
	$wp_customize->add_section(
		'ugm_footer_section',
		array(
			'title'    => __( 'Footer Settings', 'ugm-faculty' ),
			'priority' => 45,
		)
	);

	// Footer fields.
	$footer_fields = array(
		'ugm_faculty_name'       => array( 
			'label'    => __( 'Faculty Name', 'ugm-faculty' ), 
			'type'     => 'text', 
			'sanitize' => 'sanitize_text_field',
			'default'  => 'Universitas Gadjah Mada',
		),
		'ugm_faculty_address'    => array( 
			'label'    => __( 'Faculty Address', 'ugm-faculty' ), 
			'type'     => 'textarea', 
			'sanitize' => 'sanitize_textarea_field',
			'default'  => "Bulaksumur, Caturtunggal, Kec. Depok,\nKabupaten Sleman, Daerah Istimewa\nYogyakarta 55281",
		),
		'ugm_faculty_phone'      => array( 
			'label'    => __( 'Faculty Phone', 'ugm-faculty' ), 
			'type'     => 'text', 
			'sanitize' => 'sanitize_text_field',
			'default'  => '+62(274)588688',
		),
		'ugm_faculty_email'      => array( 
			'label'    => __( 'Faculty Email', 'ugm-faculty' ), 
			'type'     => 'email', 
			'sanitize' => 'sanitize_email',
			'default'  => 'info@ugm.ac.id',
		),
		'ugm_faculty_fax'        => array(
			'label'    => __( 'Faculty Fax', 'ugm-faculty' ),
			'type'     => 'text',
			'sanitize' => 'sanitize_text_field',
			'default'  => '+62(274)565223',
		),
		'ugm_faculty_whatsapp'   => array(
			'label'    => __( 'Faculty WhatsApp', 'ugm-faculty' ),
			'type'     => 'text',
			'sanitize' => 'sanitize_text_field',
			'default'  => '+628112869988',
		),
		'ugm_faculty_hours'      => array( 
			'label'    => __( 'Office Hours', 'ugm-faculty' ), 
			'type'     => 'text', 
			'sanitize' => 'sanitize_text_field' 
		),
		'ugm_institutional_info' => array( 
			'label'    => __( 'Institutional Information', 'ugm-faculty' ), 
			'type'     => 'textarea', 
			'sanitize' => 'sanitize_textarea_field',
			'default'  => 'Universitas Gadjah Mada telah mendapatkan Akreditasi Institusi Unggul dari Badan Akreditasi Nasional Perguruan Tinggi (BAN-PT) untuk periode 2022-2027.',
		),
		'ugm_accreditation_info' => array( 
			'label'    => __( 'Accreditation Information', 'ugm-faculty' ), 
			'type'     => 'textarea', 
			'sanitize' => 'sanitize_textarea_field' 
		),
		'ugm_footer_copyright'   => array( 
			'label'    => __( 'Copyright Text', 'ugm-faculty' ), 
			'type'     => 'text', 
			'sanitize' => 'sanitize_text_field' 
		),
	);

	foreach ( $footer_fields as $id => $field ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => isset( $field['default'] ) ? $field['default'] : '',
				'sanitize_callback' => $field['sanitize'],
			)
		);

		$wp_customize->add_control(
			$id,
			array(
				'label'   => $field['label'],
				'section' => 'ugm_footer_section',
				'type'    => $field['type'],
			)
		);
	}

	// Footer banner image upload.
	$wp_customize->add_setting(
		'ugm_footer_banner_image',
		array(
			'sanitize_callback' => 'absint',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'ugm_footer_banner_image',
			array(
				'label'       => __( 'Footer Bottom Banner Image', 'ugm-faculty' ),
				'description' => __( 'Displayed at the very bottom of the footer.', 'ugm-faculty' ),
				'section'     => 'ugm_footer_section',
			)
		)
	);
}
add_action( 'customize_register', 'ugm_customize_register' );
