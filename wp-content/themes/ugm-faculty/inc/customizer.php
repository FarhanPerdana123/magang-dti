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
 * Sanitize image field from Customizer.
 *
 * Accepts either attachment ID or image URL.
 *
 * @param mixed $value Raw value from Customizer.
 * @return int|string
 */
function ugm_sanitize_image_value( $value ) {
	if ( is_numeric( $value ) ) {
		return absint( $value );
	}

	return esc_url_raw( (string) $value );
}

/**
 * Sanitize positive integer with minimum value 1.
 *
 * @param mixed $value Raw value from Customizer.
 * @return int
 */
function ugm_sanitize_positive_int( $value ) {
	$value = absint( $value );
	return max( 1, $value );
}

/**
 * Sanitize latest news count for landing page layout.
 *
 * @param mixed $value Raw value from Customizer.
 * @return int
 */
function ugm_sanitize_latest_news_count( $value ) {
	$value = absint( $value );
	if ( $value < 1 ) {
		return 1;
	}

	return min( 6, $value );
}

/**
 * Sanitize latest news source mode.
 *
 * @param mixed $value Raw value from Customizer.
 * @return string
 */
function ugm_sanitize_latest_news_mode( $value ) {
	$value = sanitize_key( (string) $value );
	return in_array( $value, array( 'auto', 'manual' ), true ) ? $value : 'auto';
}

/**
 * Sanitize faculty item count for landing page slider.
 *
 * @param mixed $value Raw value from Customizer.
 * @return int
 */
function ugm_sanitize_faculty_item_count( $value ) {
	$value = absint( $value );
	if ( $value < 0 ) {
		return 0;
	}

	return min( 20, $value );
}

/**
 * Sanitize digital magazine item count for landing page.
 *
 * @param mixed $value Raw value from Customizer.
 * @return int
 */
function ugm_sanitize_magazine_item_count( $value ) {
	$value = absint( $value );
	if ( $value < 1 ) {
		return 1;
	}

	return min( 12, $value );
}

/**
 * Simple note control for Customizer instructions.
 */
if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'UGM_Customize_Note_Control' ) ) {
	class UGM_Customize_Note_Control extends WP_Customize_Control {
		/**
		 * Render control content.
		 */
		public function render_content() {
			if ( ! empty( $this->label ) ) {
				?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php
			}

			if ( ! empty( $this->description ) ) {
				?>
				<p class="description customize-control-description"><?php echo wp_kses_post( $this->description ); ?></p>
				<?php
			}
		}
	}
}

/**
 * Category dropdown control for Customizer.
 */
if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'UGM_Customize_Dropdown_Categories_Control' ) ) {
	class UGM_Customize_Dropdown_Categories_Control extends WP_Customize_Control {
		/**
		 * Control type.
		 *
		 * @var string
		 */
		public $type = 'dropdown-categories';

		/**
		 * Render control content.
		 */
		public function render_content() {
			$categories = get_categories(
				array(
					'hide_empty' => false,
				)
			);
			?>
			<label>
				<?php if ( ! empty( $this->label ) ) : ?>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $this->description ) ) : ?>
					<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
				<?php endif; ?>
				<select <?php $this->link(); ?>>
					<option value="0"><?php esc_html_e( 'Semua Kategori', 'ugm-faculty' ); ?></option>
					<?php foreach ( $categories as $category ) : ?>
						<option value="<?php echo esc_attr( $category->term_id ); ?>" <?php selected( (int) $this->value(), (int) $category->term_id ); ?>>
							<?php echo esc_html( $category->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
			<?php
		}
	}
}

/**
 * Post dropdown control for Customizer.
 */
if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'UGM_Customize_Dropdown_Posts_Control' ) ) {
	class UGM_Customize_Dropdown_Posts_Control extends WP_Customize_Control {
		/**
		 * Control type.
		 *
		 * @var string
		 */
		public $type = 'dropdown-posts';

		/**
		 * Render control content.
		 */
		public function render_content() {
			$posts = get_posts(
				array(
					'post_type'           => 'post',
					'posts_per_page'      => 200,
					'post_status'         => 'publish',
					'orderby'             => 'date',
					'order'               => 'DESC',
					'ignore_sticky_posts' => true,
				)
			);
			?>
			<label>
				<?php if ( ! empty( $this->label ) ) : ?>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $this->description ) ) : ?>
					<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
				<?php endif; ?>
				<select <?php $this->link(); ?>>
					<option value="0"><?php esc_html_e( 'Pilih Postingan', 'ugm-faculty' ); ?></option>
					<?php foreach ( $posts as $post_item ) : ?>
						<option value="<?php echo esc_attr( $post_item->ID ); ?>" <?php selected( (int) $this->value(), (int) $post_item->ID ); ?>>
							<?php echo esc_html( $post_item->post_title ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
			<?php
		}
	}
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
