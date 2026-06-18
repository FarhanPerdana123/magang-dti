<?php
/**
 * Custom meta box for "Halaman Landing" page template.
 *
 * Provides per-page editable section titles directly in the WordPress page editor.
 * When a field is left empty, it falls back to the Customizer value.
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register REST-visible post meta so Gutenberg can read/write them.
 */
function ugm_register_landing_page_meta() {
	$meta_keys = array(
		'ugm_page_latest_title',
		'ugm_page_latest_category',
		'ugm_page_academic_title',
		'ugm_page_academic_category',
		'ugm_page_profile_title',
		'ugm_page_profile_category',
		'ugm_page_achievement_title',
		'ugm_page_achievement_category',
		'ugm_page_faculty_title',
		'ugm_page_agenda_title',
		'ugm_page_agenda_category',
	);

	foreach ( $meta_keys as $meta_key ) {
		register_post_meta(
			'page',
			$meta_key,
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
add_action( 'init', 'ugm_register_landing_page_meta' );

/**
 * Register the meta box on page edit screens.
 */
function ugm_add_landing_page_meta_box() {
	add_meta_box(
		'ugm-landing-section-titles',
		__( '⚙ Judul Section (Halaman Landing)', 'ugm-faculty' ),
		'ugm_render_landing_page_meta_box',
		'page',
		'side',
		'default'
	);
}
// Disabled: section titles and category slugs are edited from each UGM block.
// add_action( 'add_meta_boxes', 'ugm_add_landing_page_meta_box' );

/**
 * Render meta box HTML.
 *
 * @param WP_Post $post Current post.
 */
function ugm_render_landing_page_meta_box( $post ) {
	wp_nonce_field( 'ugm_landing_meta_save', 'ugm_landing_meta_nonce' );

	// Sections config: each entry groups title + optional category-slug field.
	$sections = array(
		array(
			'heading' => __( 'Berita Terbaru', 'ugm-faculty' ),
			'fields'  => array(
				'ugm_page_latest_title' => array(
					'label'   => __( 'Judul', 'ugm-faculty' ),
					'default' => get_theme_mod( 'ugm_latest_section_title', __( 'Berita Terbaru', 'ugm-faculty' ) ),
				),
				'ugm_page_latest_category' => array(
					'label'   => __( 'Slug Kategori', 'ugm-faculty' ),
					'default' => '',
					'is_slug' => true,
				),
			),
		),
		array(
			'heading' => __( 'Berita Akademik', 'ugm-faculty' ),
			'fields'  => array(
				'ugm_page_academic_title' => array(
					'label'   => __( 'Judul', 'ugm-faculty' ),
					'default' => get_theme_mod( 'ugm_academic_section_title', __( 'Berita Akademik', 'ugm-faculty' ) ),
				),
				'ugm_page_academic_category' => array(
					'label'   => __( 'Slug Kategori', 'ugm-faculty' ),
					'default' => 'pendidikan',
					'is_slug' => true,
				),
			),
		),
		array(
			'heading' => __( 'Profile', 'ugm-faculty' ),
			'fields'  => array(
				'ugm_page_profile_title' => array(
					'label'   => __( 'Judul', 'ugm-faculty' ),
					'default' => get_theme_mod( 'ugm_profile_section_title', __( 'Profile', 'ugm-faculty' ) ),
				),
				'ugm_page_profile_category' => array(
					'label'   => __( 'Slug Kategori', 'ugm-faculty' ),
					'default' => 'profile',
					'is_slug' => true,
				),
			),
		),
		array(
			'heading' => __( 'Prestasi', 'ugm-faculty' ),
			'fields'  => array(
				'ugm_page_achievement_title' => array(
					'label'   => __( 'Judul', 'ugm-faculty' ),
					'default' => get_theme_mod( 'ugm_achievement_section_title', __( 'Prestasi', 'ugm-faculty' ) ),
				),
				'ugm_page_achievement_category' => array(
					'label'   => __( 'Slug Kategori', 'ugm-faculty' ),
					'default' => 'prestasi',
					'is_slug' => true,
				),
			),
		),
		array(
			'heading' => __( 'Fakultas dan Sekolah', 'ugm-faculty' ),
			'fields'  => array(
				'ugm_page_faculty_title' => array(
					'label'   => __( 'Judul', 'ugm-faculty' ),
					'default' => get_theme_mod( 'ugm_faculty_section_title', __( 'Fakultas dan Sekolah', 'ugm-faculty' ) ),
				),
			),
		),
		array(
			'heading' => __( 'Agenda Kegiatan', 'ugm-faculty' ),
			'fields'  => array(
				'ugm_page_agenda_title' => array(
					'label'   => __( 'Judul', 'ugm-faculty' ),
					'default' => get_theme_mod( 'ugm_events_section_title', __( 'Agenda Kegiatan', 'ugm-faculty' ) ),
				),
				'ugm_page_agenda_category' => array(
					'label'   => __( 'Slug Kategori', 'ugm-faculty' ),
					'default' => 'agenda',
					'is_slug' => true,
				),
			),
		),
	);

	$current_template = get_page_template_slug( $post->ID );
	$is_landing       = 'page-templates/template-landing-page.php' === $current_template;
	?>
	<style>
		#ugm-landing-section-titles .inside { padding: 8px 12px 12px; }
		.ugm-meta-section { margin-bottom: 14px; border: 1px solid #e2e4e7; border-radius: 4px; overflow: hidden; }
		.ugm-meta-section__heading { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #fff; background: #2271b1; padding: 4px 8px; margin: 0; }
		.ugm-meta-section__body { padding: 8px 10px 4px; }
		.ugm-meta-field { margin-bottom: 8px; }
		.ugm-meta-field label { display: block; font-size: 11px; font-weight: 600; color: #3c434a; margin-bottom: 3px; }
		.ugm-meta-field input[type="text"] { width: 100%; box-sizing: border-box; }
		.ugm-meta-field--slug label { color: #787c82; font-weight: 400; }
		.ugm-meta-field--slug input { font-family: monospace; font-size: 11px; background: #f9f9f9; }
		.ugm-meta-slug-hint { display: block; font-size: 10px; color: #999; margin-top: 2px; font-style: italic; }
		.ugm-meta-hint { font-size: 11px; color: #757575; margin: 0 0 10px; padding: 8px 10px; background: #f6f7f7; border-left: 3px solid #2271b1; }
		.ugm-meta-inactive { font-size: 11px; color: #757575; font-style: italic; padding: 8px; background: #f9f9f9; border-radius: 3px; margin: 0; }
	</style>

	<?php if ( $is_landing ) : ?>
		<p class="ugm-meta-hint">
			<?php esc_html_e( 'Judul: label tampilan. Slug Kategori: kategori WordPress yang ditampilkan; bisa lebih dari satu slug dipisah koma (kosongkan = default).', 'ugm-faculty' ); ?>
		</p>
		<?php foreach ( $sections as $section ) : ?>
			<div class="ugm-meta-section">
				<p class="ugm-meta-section__heading"><?php echo esc_html( $section['heading'] ); ?></p>
				<div class="ugm-meta-section__body">
					<?php foreach ( $section['fields'] as $meta_key => $field ) : ?>
						<?php $is_slug = ! empty( $field['is_slug'] ); ?>
						<div class="ugm-meta-field<?php echo $is_slug ? ' ugm-meta-field--slug' : ''; ?>">
							<label for="<?php echo esc_attr( $meta_key ); ?>">
								<?php echo esc_html( $field['label'] ); ?>
							</label>
							<input
								type="text"
								id="<?php echo esc_attr( $meta_key ); ?>"
								name="<?php echo esc_attr( $meta_key ); ?>"
								value="<?php echo esc_attr( (string) get_post_meta( $post->ID, $meta_key, true ) ); ?>"
								placeholder="<?php echo esc_attr( $field['default'] ); ?>"
							/>
							<?php if ( $is_slug ) : ?>
								<span class="ugm-meta-slug-hint"><?php esc_html_e( 'Slug kategori WordPress, bukan judul. Bisa lebih dari satu dipisah koma. Contoh: prestasi, profile', 'ugm-faculty' ); ?></span>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
	<?php else : ?>
		<p class="ugm-meta-inactive">
			<?php esc_html_e( 'Pilih template "Halaman Landing" untuk mengedit judul section halaman ini.', 'ugm-faculty' ); ?>
		</p>
	<?php endif; ?>
	<?php
}

/**
 * Save meta box values on page save.
 *
 * @param int $post_id Post ID being saved.
 */
function ugm_save_landing_page_meta( $post_id ) {
	// Verify nonce.
	if ( ! isset( $_POST['ugm_landing_meta_nonce'] )
		|| ! wp_verify_nonce( sanitize_key( $_POST['ugm_landing_meta_nonce'] ), 'ugm_landing_meta_save' )
	) {
		return;
	}

	// Skip autosaves.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$meta_keys = array(
		'ugm_page_latest_title',
		'ugm_page_latest_category',
		'ugm_page_academic_title',
		'ugm_page_academic_category',
		'ugm_page_profile_title',
		'ugm_page_profile_category',
		'ugm_page_achievement_title',
		'ugm_page_achievement_category',
		'ugm_page_faculty_title',
		'ugm_page_agenda_title',
		'ugm_page_agenda_category',
	);

	foreach ( $meta_keys as $meta_key ) {
		if ( array_key_exists( $meta_key, $_POST ) ) {
			$value = sanitize_text_field( wp_unslash( (string) $_POST[ $meta_key ] ) );
			update_post_meta( $post_id, $meta_key, $value );
		}
	}
}
add_action( 'save_post_page', 'ugm_save_landing_page_meta' );
