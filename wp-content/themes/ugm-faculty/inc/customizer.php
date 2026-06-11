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
 * Get the header social-media field definitions.
 *
 * Header social links are managed separately from the footer social block.
 *
 * @return array[]
 */
function ugm_get_header_social_definitions() {
	return array(
		array( 'label' => __( 'Instagram', 'ugm-faculty' ), 'setting' => 'ugm_header_social_instagram_url', 'icon' => 'Component Instagram.png' ),
		array( 'label' => __( 'YouTube', 'ugm-faculty' ), 'setting' => 'ugm_header_social_youtube_url', 'icon' => 'Component YouTube.png' ),
		array( 'label' => __( 'Facebook', 'ugm-faculty' ), 'setting' => 'ugm_header_social_facebook_url', 'icon' => 'Component Facebook.png' ),
		array( 'label' => __( 'X/Twitter', 'ugm-faculty' ), 'setting' => 'ugm_header_social_x_url', 'icon' => 'Component Twitter.png' ),
		array( 'label' => __( 'LinkedIn', 'ugm-faculty' ), 'setting' => 'ugm_header_social_linkedin_url', 'icon' => 'Component LinkedIn.png' ),
		array( 'label' => __( 'TikTok', 'ugm-faculty' ), 'setting' => 'ugm_header_social_tiktok_url', 'icon' => 'Component TikTok.png' ),
	);
}

/**
 * Get enabled header social-media items from Customizer settings.
 *
 * @return array[]
 */
function ugm_get_header_social_items() {
	$items = array();

	foreach ( ugm_get_header_social_definitions() as $definition ) {
		$url = trim( (string) get_theme_mod( $definition['setting'], '' ) );

		if ( '' === $url ) {
			continue;
		}

		$items[] = array(
			'label' => $definition['label'],
			'file'  => $definition['icon'],
			'url'   => $url,
		);
	}

	return $items;
}

/**
 * Sanitize the landing page navbar style choice.
 *
 * @param string $value Selected Customizer value.
 * @return string
 */
function ugm_sanitize_landing_navbar_style( $value ) {
	$allowed = array( 'solid', 'transparent' );

	return in_array( $value, $allowed, true ) ? $value : 'solid';
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

	// Branding Text Lines.
	$wp_customize->add_setting(
		'ugm_branding_line_1',
		array(
			'default'           => 'UNIVERSITAS',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'ugm_branding_line_1',
		array(
			'label'       => __( 'Branding Text Line 1', 'ugm-faculty' ),
			'description' => __( 'Maksimal 3 baris. Kosongkan baris yang tidak dipakai.', 'ugm-faculty' ),
			'section'     => 'ugm_branding',
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'ugm_branding_line_2',
		array(
			'default'           => 'GADJAH MADA',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'ugm_branding_line_2',
		array(
			'label'   => __( 'Branding Text Line 2', 'ugm-faculty' ),
			'section' => 'ugm_branding',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'ugm_branding_line_3',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'ugm_branding_line_3',
		array(
			'label'   => __( 'Branding Text Line 3', 'ugm-faculty' ),
			'section' => 'ugm_branding',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'ugm_landing_navbar_style',
		array(
			'default'           => 'solid',
			'sanitize_callback' => 'ugm_sanitize_landing_navbar_style',
		)
	);

	$wp_customize->add_control(
		'ugm_landing_navbar_style',
		array(
			'label'       => __( 'Landing Page Navbar Style', 'ugm-faculty' ),
			'description' => __( 'Mengatur tampilan navbar khusus landing page saat posisi halaman paling atas.', 'ugm-faculty' ),
			'section'     => 'ugm_branding',
			'type'        => 'radio',
			'choices'     => array(
				'solid'       => __( 'Full background', 'ugm-faculty' ),
				'transparent' => __( 'Transparent on top', 'ugm-faculty' ),
			),
		)
	);

	// Header Social Media Section.
	$wp_customize->add_section(
		'ugm_header_social_media',
		array(
			'title'       => __( 'Header Social Media', 'ugm-faculty' ),
			'description' => __( 'Isi URL untuk menampilkan ikon media sosial di header. Kosongkan URL untuk menyembunyikan ikon.', 'ugm-faculty' ),
			'priority'    => 36,
		)
	);

	foreach ( ugm_get_header_social_definitions() as $definition ) {
		$wp_customize->add_setting(
			$definition['setting'],
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			)
		);

		$wp_customize->add_control(
			$definition['setting'],
			array(
				'label'       => sprintf(
					/* translators: %s: social media name. */
					__( '%s URL', 'ugm-faculty' ),
					$definition['label']
				),
				'description' => __( 'Kosongkan jika ikon ini tidak ingin ditampilkan di header.', 'ugm-faculty' ),
				'section'     => 'ugm_header_social_media',
				'type'        => 'url',
			)
		);
	}

}
add_action( 'customize_register', 'ugm_customize_register' );
