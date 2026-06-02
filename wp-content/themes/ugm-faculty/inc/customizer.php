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

}
add_action( 'customize_register', 'ugm_customize_register' );
