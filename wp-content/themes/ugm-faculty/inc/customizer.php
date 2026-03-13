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

	// Landing Page UGM Section.
	$wp_customize->add_section(
		'ugm_hero_section',
		array(
			'title'    => __( 'Landing Page UGM', 'ugm-faculty' ),
			'priority' => 36,
		)
	);

	// Hero Background Image.
	$wp_customize->add_setting(
		'ugm_hero_background_image',
		array(
			'sanitize_callback' => 'ugm_sanitize_image_value',
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

	// Landing page latest news section.
	$wp_customize->add_section(
		'ugm_latest_news_section',
		array(
			'title'    => __( 'Berita Terbaru (Landing Page)', 'ugm-faculty' ),
			'priority' => 38,
		)
	);

	$wp_customize->add_setting(
		'ugm_latest_news_help',
		array(
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		new UGM_Customize_Note_Control(
			$wp_customize,
			'ugm_latest_news_help',
			array(
				'label'       => __( 'Panduan Kelola Berita', 'ugm-faculty' ),
				'description' => wp_kses_post(
					sprintf(
						/* translators: 1: posts list URL, 2: add new post URL, 3: categories URL */
						__( 'Konten berita diambil dari menu Postingan WordPress.<br><a href="%1$s" target="_blank" rel="noopener">Lihat semua postingan</a> | <a href="%2$s" target="_blank" rel="noopener">Tambah berita baru</a> | <a href="%3$s" target="_blank" rel="noopener">Kelola kategori berita</a>', 'ugm-faculty' ),
						esc_url( admin_url( 'edit.php' ) ),
						esc_url( admin_url( 'post-new.php' ) ),
						esc_url( admin_url( 'edit-tags.php?taxonomy=category' ) )
					)
				),
				'section'     => 'ugm_latest_news_section',
			)
		)
	);

	$wp_customize->add_setting(
		'ugm_latest_news_mode',
		array(
			'default'           => 'auto',
			'sanitize_callback' => 'ugm_sanitize_latest_news_mode',
		)
	);

	$wp_customize->add_control(
		'ugm_latest_news_mode',
		array(
			'label'   => __( 'Sumber Berita', 'ugm-faculty' ),
			'section' => 'ugm_latest_news_section',
			'type'    => 'radio',
			'choices' => array(
				'auto'   => __( 'Otomatis (terbaru dari semua kategori)', 'ugm-faculty' ),
				'manual' => __( 'Manual (pilih berita per kartu)', 'ugm-faculty' ),
			),
		)
	);

	$wp_customize->add_setting(
		'ugm_latest_section_title',
		array(
			'default'           => __( 'Berita Terbaru', 'ugm-faculty' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'ugm_latest_section_title',
		array(
			'label'   => __( 'Judul Bagian Berita', 'ugm-faculty' ),
			'section' => 'ugm_latest_news_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'ugm_latest_news_count',
		array(
			'default'           => 3,
			'sanitize_callback' => 'ugm_sanitize_latest_news_count',
		)
	);

	$wp_customize->add_control(
		'ugm_latest_news_count',
		array(
			'label'       => __( 'Jumlah Berita Ditampilkan', 'ugm-faculty' ),
			'description' => __( 'Rekomendasi: 3 agar layout tetap sesuai desain awal.', 'ugm-faculty' ),
			'section'     => 'ugm_latest_news_section',
			'type'        => 'number',
			'input_attrs' => array(
				'min' => 1,
				'max' => 6,
			),
		)
	);

	$manual_news_controls = array(
		'ugm_latest_news_post_1' => __( 'Berita Utama (Kartu Besar)', 'ugm-faculty' ),
		'ugm_latest_news_post_2' => __( 'Berita Kedua', 'ugm-faculty' ),
		'ugm_latest_news_post_3' => __( 'Berita Ketiga', 'ugm-faculty' ),
	);

	foreach ( $manual_news_controls as $setting_id => $control_label ) {
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => 0,
				'sanitize_callback' => 'absint',
			)
		);

		$wp_customize->add_control(
			new UGM_Customize_Dropdown_Posts_Control(
				$wp_customize,
				$setting_id,
				array(
					'label'           => $control_label,
					'section'         => 'ugm_latest_news_section',
					'active_callback' => function () {
						return 'manual' === get_theme_mod( 'ugm_latest_news_mode', 'auto' );
					},
				)
			)
		);
	}

	// Landing page academic news section.
	$wp_customize->add_section(
		'ugm_academic_news_section',
		array(
			'title'    => __( 'Berita Akademik (Landing Page)', 'ugm-faculty' ),
			'priority' => 39,
		)
	);

	$academic_category    = ugm_get_category_root_by_slugs( array( 'pendidikan' ) );
	$academic_category_id = $academic_category ? (int) $academic_category->term_id : 0;
	$academic_posts_url   = $academic_category_id > 0 ? admin_url( 'edit.php?cat=' . $academic_category_id ) : admin_url( 'edit.php' );

	$wp_customize->add_setting(
		'ugm_academic_news_help',
		array(
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		new UGM_Customize_Note_Control(
			$wp_customize,
			'ugm_academic_news_help',
			array(
				'label'       => __( 'Panduan Kelola Berita Akademik', 'ugm-faculty' ),
				'description' => wp_kses_post(
					sprintf(
						/* translators: 1: filtered posts list URL, 2: add new post URL, 3: categories URL */
						__( 'Section ini hanya menampilkan postingan kategori <strong>pendidikan</strong> beserta subkategorinya.<br><a href="%1$s" target="_blank" rel="noopener">Lihat berita pendidikan</a> | <a href="%2$s" target="_blank" rel="noopener">Tambah berita baru</a> | <a href="%3$s" target="_blank" rel="noopener">Kelola kategori berita</a>', 'ugm-faculty' ),
						esc_url( $academic_posts_url ),
						esc_url( admin_url( 'post-new.php' ) ),
						esc_url( admin_url( 'edit-tags.php?taxonomy=category' ) )
					)
				),
				'section'     => 'ugm_academic_news_section',
			)
		)
	);

	$wp_customize->add_setting(
		'ugm_academic_news_mode',
		array(
			'default'           => 'auto',
			'sanitize_callback' => 'ugm_sanitize_latest_news_mode',
		)
	);

	$wp_customize->add_control(
		'ugm_academic_news_mode',
		array(
			'label'   => __( 'Sumber Berita Akademik', 'ugm-faculty' ),
			'section' => 'ugm_academic_news_section',
			'type'    => 'radio',
			'choices' => array(
				'auto'   => __( 'Otomatis (terbaru kategori pendidikan + subkategori)', 'ugm-faculty' ),
				'manual' => __( 'Manual (pilih berita per kartu)', 'ugm-faculty' ),
			),
		)
	);

	$wp_customize->add_setting(
		'ugm_academic_section_title',
		array(
			'default'           => __( 'Berita Akademik', 'ugm-faculty' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'ugm_academic_section_title',
		array(
			'label'   => __( 'Judul Bagian Berita Akademik', 'ugm-faculty' ),
			'section' => 'ugm_academic_news_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'ugm_academic_news_count',
		array(
			'default'           => 2,
			'sanitize_callback' => 'ugm_sanitize_latest_news_count',
		)
	);

	$wp_customize->add_control(
		'ugm_academic_news_count',
		array(
			'label'       => __( 'Jumlah Berita Akademik Ditampilkan', 'ugm-faculty' ),
			'description' => __( 'Section ini tetap memfilter kategori pendidikan dan subkategori.', 'ugm-faculty' ),
			'section'     => 'ugm_academic_news_section',
			'type'        => 'number',
			'input_attrs' => array(
				'min' => 1,
				'max' => 6,
			),
		)
	);

	$manual_academic_controls = array(
		'ugm_academic_news_post_1' => __( 'Berita Akademik 1', 'ugm-faculty' ),
		'ugm_academic_news_post_2' => __( 'Berita Akademik 2', 'ugm-faculty' ),
		'ugm_academic_news_post_3' => __( 'Berita Akademik 3', 'ugm-faculty' ),
		'ugm_academic_news_post_4' => __( 'Berita Akademik 4', 'ugm-faculty' ),
		'ugm_academic_news_post_5' => __( 'Berita Akademik 5', 'ugm-faculty' ),
		'ugm_academic_news_post_6' => __( 'Berita Akademik 6', 'ugm-faculty' ),
	);

	foreach ( $manual_academic_controls as $setting_id => $control_label ) {
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => 0,
				'sanitize_callback' => 'absint',
			)
		);

		$wp_customize->add_control(
			new UGM_Customize_Dropdown_Posts_Control(
				$wp_customize,
				$setting_id,
				array(
					'label'           => $control_label,
					'section'         => 'ugm_academic_news_section',
					'active_callback' => function () {
						return 'manual' === get_theme_mod( 'ugm_academic_news_mode', 'auto' );
					},
				)
			)
		);
	}

	// Landing page profile section.
	$wp_customize->add_section(
		'ugm_profile_news_section',
		array(
			'title'    => __( 'Profile (Landing Page)', 'ugm-faculty' ),
			'priority' => 40,
		)
	);

	$profile_category    = ugm_get_category_root_by_slugs( array( 'profile', 'profil' ) );
	$profile_category_id = $profile_category ? (int) $profile_category->term_id : 0;
	$profile_posts_url   = $profile_category_id > 0 ? admin_url( 'edit.php?cat=' . $profile_category_id ) : admin_url( 'edit.php' );

	$wp_customize->add_setting(
		'ugm_profile_news_help',
		array(
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		new UGM_Customize_Note_Control(
			$wp_customize,
			'ugm_profile_news_help',
			array(
				'label'       => __( 'Panduan Kelola Profile', 'ugm-faculty' ),
				'description' => wp_kses_post(
					sprintf(
						/* translators: 1: filtered posts list URL, 2: add new post URL, 3: categories URL */
						__( 'Section ini menampilkan postingan kategori <strong>profile</strong>.<br><a href="%1$s" target="_blank" rel="noopener">Lihat konten profile</a> | <a href="%2$s" target="_blank" rel="noopener">Tambah postingan baru</a> | <a href="%3$s" target="_blank" rel="noopener">Kelola kategori</a>', 'ugm-faculty' ),
						esc_url( $profile_posts_url ),
						esc_url( admin_url( 'post-new.php' ) ),
						esc_url( admin_url( 'edit-tags.php?taxonomy=category' ) )
					)
				),
				'section'     => 'ugm_profile_news_section',
			)
		)
	);

	$wp_customize->add_setting(
		'ugm_profile_news_mode',
		array(
			'default'           => 'auto',
			'sanitize_callback' => 'ugm_sanitize_latest_news_mode',
		)
	);

	$wp_customize->add_control(
		'ugm_profile_news_mode',
		array(
			'label'   => __( 'Sumber Konten Profile', 'ugm-faculty' ),
			'section' => 'ugm_profile_news_section',
			'type'    => 'radio',
			'choices' => array(
				'auto'   => __( 'Otomatis (terbaru kategori profile)', 'ugm-faculty' ),
				'manual' => __( 'Manual (pilih postingan per kartu)', 'ugm-faculty' ),
			),
		)
	);

	$wp_customize->add_setting(
		'ugm_profile_section_title',
		array(
			'default'           => __( 'Profile', 'ugm-faculty' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'ugm_profile_section_title',
		array(
			'label'   => __( 'Judul Bagian Profile', 'ugm-faculty' ),
			'section' => 'ugm_profile_news_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'ugm_profile_news_count',
		array(
			'default'           => 3,
			'sanitize_callback' => 'ugm_sanitize_latest_news_count',
		)
	);

	$wp_customize->add_control(
		'ugm_profile_news_count',
		array(
			'label'       => __( 'Jumlah Konten Profile Ditampilkan', 'ugm-faculty' ),
			'description' => __( 'Maksimal 6 konten.', 'ugm-faculty' ),
			'section'     => 'ugm_profile_news_section',
			'type'        => 'number',
			'input_attrs' => array(
				'min' => 1,
				'max' => 6,
			),
		)
	);

	$manual_profile_controls = array(
		'ugm_profile_news_post_1' => __( 'Profile 1', 'ugm-faculty' ),
		'ugm_profile_news_post_2' => __( 'Profile 2', 'ugm-faculty' ),
		'ugm_profile_news_post_3' => __( 'Profile 3', 'ugm-faculty' ),
		'ugm_profile_news_post_4' => __( 'Profile 4', 'ugm-faculty' ),
		'ugm_profile_news_post_5' => __( 'Profile 5', 'ugm-faculty' ),
		'ugm_profile_news_post_6' => __( 'Profile 6', 'ugm-faculty' ),
	);

	foreach ( $manual_profile_controls as $setting_id => $control_label ) {
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => 0,
				'sanitize_callback' => 'absint',
			)
		);

		$wp_customize->add_control(
			new UGM_Customize_Dropdown_Posts_Control(
				$wp_customize,
				$setting_id,
				array(
					'label'           => $control_label,
					'section'         => 'ugm_profile_news_section',
					'active_callback' => function () {
						return 'manual' === get_theme_mod( 'ugm_profile_news_mode', 'auto' );
					},
				)
			)
		);
	}

	// Landing page achievement section.
	$wp_customize->add_section(
		'ugm_achievement_news_section',
		array(
			'title'    => __( 'Prestasi (Landing Page)', 'ugm-faculty' ),
			'priority' => 41,
		)
	);

	$achievement_category    = ugm_get_category_root_by_slugs( array( 'prestasi' ) );
	$achievement_category_id = $achievement_category ? (int) $achievement_category->term_id : 0;
	$achievement_posts_url   = $achievement_category_id > 0 ? admin_url( 'edit.php?cat=' . $achievement_category_id ) : admin_url( 'edit.php' );

	$wp_customize->add_setting(
		'ugm_achievement_news_help',
		array(
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		new UGM_Customize_Note_Control(
			$wp_customize,
			'ugm_achievement_news_help',
			array(
				'label'       => __( 'Panduan Kelola Prestasi', 'ugm-faculty' ),
				'description' => wp_kses_post(
					sprintf(
						/* translators: 1: filtered posts list URL, 2: add new post URL, 3: categories URL */
						__( 'Section ini menampilkan postingan kategori <strong>prestasi</strong> beserta subkategorinya.<br><a href="%1$s" target="_blank" rel="noopener">Lihat konten prestasi</a> | <a href="%2$s" target="_blank" rel="noopener">Tambah postingan baru</a> | <a href="%3$s" target="_blank" rel="noopener">Kelola kategori</a>', 'ugm-faculty' ),
						esc_url( $achievement_posts_url ),
						esc_url( admin_url( 'post-new.php' ) ),
						esc_url( admin_url( 'edit-tags.php?taxonomy=category' ) )
					)
				),
				'section'     => 'ugm_achievement_news_section',
			)
		)
	);

	$wp_customize->add_setting(
		'ugm_achievement_news_mode',
		array(
			'default'           => 'auto',
			'sanitize_callback' => 'ugm_sanitize_latest_news_mode',
		)
	);

	$wp_customize->add_control(
		'ugm_achievement_news_mode',
		array(
			'label'   => __( 'Sumber Konten Prestasi', 'ugm-faculty' ),
			'section' => 'ugm_achievement_news_section',
			'type'    => 'radio',
			'choices' => array(
				'auto'   => __( 'Otomatis (terbaru kategori prestasi + subkategori)', 'ugm-faculty' ),
				'manual' => __( 'Manual (pilih postingan per kartu)', 'ugm-faculty' ),
			),
		)
	);

	$wp_customize->add_setting(
		'ugm_achievement_section_title',
		array(
			'default'           => __( 'Prestasi', 'ugm-faculty' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'ugm_achievement_section_title',
		array(
			'label'   => __( 'Judul Bagian Prestasi', 'ugm-faculty' ),
			'section' => 'ugm_achievement_news_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'ugm_achievement_news_count',
		array(
			'default'           => 3,
			'sanitize_callback' => 'ugm_sanitize_latest_news_count',
		)
	);

	$wp_customize->add_control(
		'ugm_achievement_news_count',
		array(
			'label'       => __( 'Jumlah Konten Prestasi Ditampilkan', 'ugm-faculty' ),
			'description' => __( 'Maksimal 6 konten.', 'ugm-faculty' ),
			'section'     => 'ugm_achievement_news_section',
			'type'        => 'number',
			'input_attrs' => array(
				'min' => 1,
				'max' => 6,
			),
		)
	);

	$manual_achievement_controls = array(
		'ugm_achievement_news_post_1' => __( 'Prestasi 1', 'ugm-faculty' ),
		'ugm_achievement_news_post_2' => __( 'Prestasi 2', 'ugm-faculty' ),
		'ugm_achievement_news_post_3' => __( 'Prestasi 3', 'ugm-faculty' ),
		'ugm_achievement_news_post_4' => __( 'Prestasi 4', 'ugm-faculty' ),
		'ugm_achievement_news_post_5' => __( 'Prestasi 5', 'ugm-faculty' ),
		'ugm_achievement_news_post_6' => __( 'Prestasi 6', 'ugm-faculty' ),
	);

	foreach ( $manual_achievement_controls as $setting_id => $control_label ) {
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => 0,
				'sanitize_callback' => 'absint',
			)
		);

		$wp_customize->add_control(
			new UGM_Customize_Dropdown_Posts_Control(
				$wp_customize,
				$setting_id,
				array(
					'label'           => $control_label,
					'section'         => 'ugm_achievement_news_section',
					'active_callback' => function () {
						return 'manual' === get_theme_mod( 'ugm_achievement_news_mode', 'auto' );
					},
				)
			)
		);
	}

	// Number fields for home sections.
	$number_fields = array(
		'ugm_featured_post_id'          => __( 'Featured Post ID', 'ugm-faculty' ),
		'ugm_featured_category_id'      => __( 'Featured Category ID', 'ugm-faculty' ),
		'ugm_announcements_category_id' => __( 'Announcements Category ID', 'ugm-faculty' ),
		'ugm_events_category_id'        => __( 'Events Category ID', 'ugm-faculty' ),
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

	// Landing page digital magazine section.
	$wp_customize->add_section(
		'ugm_magazine_section',
		array(
			'title'    => __( 'Majalah Kabar Digital (Landing Page)', 'ugm-faculty' ),
			'priority' => 44,
		)
	);

	$wp_customize->add_setting(
		'ugm_magazine_help',
		array(
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		new UGM_Customize_Note_Control(
			$wp_customize,
			'ugm_magazine_help',
			array(
				'label'       => __( 'Panduan Kelola Majalah Digital', 'ugm-faculty' ),
				'description' => wp_kses_post(
					sprintf(
						/* translators: 1: majalah list URL, 2: add new majalah URL */
						__( 'Konten majalah dikelola lewat CPT <strong>Majalah Digital</strong>.<br><a href="%1$s" target="_blank" rel="noopener">Lihat semua majalah</a> | <a href="%2$s" target="_blank" rel="noopener">Tambah majalah baru</a><br><br>Di Customizer ini hanya untuk pengaturan tampilan section landing.', 'ugm-faculty' ),
						esc_url( admin_url( 'edit.php?post_type=majalah' ) ),
						esc_url( admin_url( 'post-new.php?post_type=majalah' ) )
					)
				),
				'section'     => 'ugm_magazine_section',
			)
		)
	);

	$wp_customize->add_setting(
		'ugm_magazine_section_title',
		array(
			'default'           => __( 'Majalah Kabar Digital', 'ugm-faculty' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'ugm_magazine_section_title',
		array(
			'label'   => __( 'Judul Section Majalah', 'ugm-faculty' ),
			'section' => 'ugm_magazine_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'ugm_magazine_item_count',
		array(
			'default'           => 4,
			'sanitize_callback' => 'ugm_sanitize_magazine_item_count',
		)
	);

	$wp_customize->add_control(
		'ugm_magazine_item_count',
		array(
			'label'       => __( 'Jumlah Majalah Ditampilkan', 'ugm-faculty' ),
			'description' => __( 'Jumlah majalah terbaru yang tampil otomatis di landing page (1-12).', 'ugm-faculty' ),
			'section'     => 'ugm_magazine_section',
			'type'        => 'number',
			'input_attrs' => array(
				'min' => 1,
				'max' => 12,
			),
		)
	);

	// Landing page faculty slider section.
	$wp_customize->add_section(
		'ugm_faculty_slider_section',
		array(
			'title'    => __( 'Fakultas (Landing Page)', 'ugm-faculty' ),
			'priority' => 45,
		)
	);

	$wp_customize->add_setting(
		'ugm_faculty_slider_help',
		array(
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		new UGM_Customize_Note_Control(
			$wp_customize,
			'ugm_faculty_slider_help',
			array(
				'label'       => __( 'Panduan Kelola Section Fakultas', 'ugm-faculty' ),
				'description' => __( 'Tambah jumlah kartu lalu isi nama dan gambar tiap fakultas. Jika gambar kosong, tema akan memakai latar bawaan.', 'ugm-faculty' ),
				'section'     => 'ugm_faculty_slider_section',
			)
		)
	);

	$wp_customize->add_setting(
		'ugm_faculty_section_title',
		array(
			'default'           => __( 'Fakultas', 'ugm-faculty' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'ugm_faculty_section_title',
		array(
			'label'   => __( 'Judul Section Fakultas', 'ugm-faculty' ),
			'section' => 'ugm_faculty_slider_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'ugm_faculty_item_count',
		array(
			'default'           => 0,
			'sanitize_callback' => 'ugm_sanitize_faculty_item_count',
		)
	);

	$wp_customize->add_control(
		'ugm_faculty_item_count',
		array(
			'label'       => __( 'Jumlah Kartu Fakultas', 'ugm-faculty' ),
			'description' => __( 'Atur jumlah kartu fakultas yang ditampilkan (0-20).', 'ugm-faculty' ),
			'section'     => 'ugm_faculty_slider_section',
			'type'        => 'number',
			'input_attrs' => array(
				'min' => 0,
				'max' => 20,
			),
		)
	);

	$max_faculty_items = 20;

	for ( $faculty_index = 1; $faculty_index <= $max_faculty_items; $faculty_index++ ) {
		$name_setting_id  = 'ugm_faculty_item_' . $faculty_index . '_name';
		$image_setting_id = 'ugm_faculty_item_' . $faculty_index . '_image';

		$wp_customize->add_setting(
			$name_setting_id,
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		$wp_customize->add_control(
			$name_setting_id,
			array(
				/* translators: %d: faculty item number */
				'label'           => sprintf( __( 'Nama Fakultas %d', 'ugm-faculty' ), $faculty_index ),
				'section'         => 'ugm_faculty_slider_section',
				'type'            => 'text',
				'active_callback' => function () use ( $faculty_index ) {
					return (int) get_theme_mod( 'ugm_faculty_item_count', 0 ) >= $faculty_index;
				},
			)
		);

		$wp_customize->add_setting(
			$image_setting_id,
			array(
				'default'           => '',
				'sanitize_callback' => 'ugm_sanitize_image_value',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				$image_setting_id,
				array(
					/* translators: %d: faculty item number */
					'label'           => sprintf( __( 'Gambar Fakultas %d', 'ugm-faculty' ), $faculty_index ),
					'section'         => 'ugm_faculty_slider_section',
					'active_callback' => function () use ( $faculty_index ) {
						return (int) get_theme_mod( 'ugm_faculty_item_count', 0 ) >= $faculty_index;
					},
				)
			)
		);
	}

	// UGM Footer Section.
	$wp_customize->add_section(
		'ugm_footer_section',
		array(
			'title'    => __( 'Footer Settings', 'ugm-faculty' ),
			'priority' => 46,
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
