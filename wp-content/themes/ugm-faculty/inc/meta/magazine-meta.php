<?php
/**
 * Majalah post type and PDF meta box registration.
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Majalah custom post type.
 */
function ugm_register_majalah_post_type() {
	$labels = array(
		'name'                  => __( 'Majalah Digital', 'ugm-faculty' ),
		'singular_name'         => __( 'Majalah', 'ugm-faculty' ),
		'menu_name'             => __( 'Majalah Digital', 'ugm-faculty' ),
		'name_admin_bar'        => __( 'Majalah Digital', 'ugm-faculty' ),
		'add_new'               => __( 'Tambah Baru', 'ugm-faculty' ),
		'add_new_item'          => __( 'Tambah Majalah', 'ugm-faculty' ),
		'new_item'              => __( 'Majalah Baru', 'ugm-faculty' ),
		'edit_item'             => __( 'Edit Majalah', 'ugm-faculty' ),
		'view_item'             => __( 'Lihat Majalah', 'ugm-faculty' ),
		'all_items'             => __( 'Semua Majalah', 'ugm-faculty' ),
		'search_items'          => __( 'Cari Majalah', 'ugm-faculty' ),
		'not_found'             => __( 'Tidak ada majalah.', 'ugm-faculty' ),
		'not_found_in_trash'    => __( 'Tidak ada majalah di sampah.', 'ugm-faculty' ),
		'featured_image'        => __( 'Gambar Sampul', 'ugm-faculty' ),
		'set_featured_image'    => __( 'Set gambar sampul', 'ugm-faculty' ),
		'remove_featured_image' => __( 'Hapus gambar sampul', 'ugm-faculty' ),
		'use_featured_image'    => __( 'Gunakan sebagai gambar sampul', 'ugm-faculty' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'show_in_rest'       => true,
		'menu_icon'          => 'dashicons-media-document',
		'has_archive'        => 'majalah',
		'rewrite'            => array(
			'slug'       => 'majalah',
			'with_front' => false,
		),
		'supports'           => array( 'title', 'thumbnail' ),
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
	);

	register_post_type( 'majalah', $args );
}
add_action( 'init', 'ugm_register_majalah_post_type' );

/**
 * Register magazine PDF & edition meta box.
 */
function ugm_register_magazine_pdf_meta_box() {
	add_meta_box(
		'ugm-majalah-pdf-meta-box',
		__( 'Informasi & File PDF Majalah', 'ugm-faculty' ),
		'ugm_render_magazine_pdf_meta_box',
		'majalah',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'ugm_register_magazine_pdf_meta_box' );

/**
 * Render magazine PDF & edition meta box.
 *
 * @param WP_Post $post Post object.
 */
function ugm_render_magazine_pdf_meta_box( $post ) {
	$pdf_value     = get_post_meta( $post->ID, '_ugm_majalah_pdf', true );
	$edition_value = get_post_meta( $post->ID, '_ugm_majalah_edisi', true );
	wp_nonce_field( 'ugm_majalah_pdf_meta_box', 'ugm_majalah_pdf_meta_box_nonce' );
	?>
	<p>
		<label for="ugm_majalah_edisi_field"><strong><?php esc_html_e( 'Nomor Edisi / Terbitan:', 'ugm-faculty' ); ?></strong></label>
		<input type="text" id="ugm_majalah_edisi_field" name="ugm_majalah_edisi_field" value="<?php echo esc_attr( (string) $edition_value ); ?>" placeholder="<?php esc_attr_e( 'Contoh: Edisi 12 / 2024', 'ugm-faculty' ); ?>" style="width:100%; margin-top:4px;" />
		<span class="description" style="display:block; margin-top:4px; font-size:12px; color:#666;"><?php esc_html_e( 'Masukkan nomor edisi atau periode terbit majalah/buletin.', 'ugm-faculty' ); ?></span>
	</p>
	<hr style="margin: 12px 0; border: 0; border-top: 1px solid #ddd;" />
	<p>
		<label for="ugm_majalah_pdf_field"><strong><?php esc_html_e( 'File PDF Majalah:', 'ugm-faculty' ); ?></strong></label>
		<input type="text" id="ugm_majalah_pdf_field" name="ugm_majalah_pdf_field" value="<?php echo esc_attr( (string) $pdf_value ); ?>" style="width:100%; margin-top:4px;" />
	</p>
	<p>
		<button type="button" class="button ugm-majalah-pdf-upload"><?php esc_html_e( 'Upload/Pilih PDF', 'ugm-faculty' ); ?></button>
		<button type="button" class="button ugm-majalah-pdf-remove"><?php esc_html_e( 'Hapus', 'ugm-faculty' ); ?></button>
	</p>
	<p class="description"><?php esc_html_e( 'Cover majalah di landing dan arsip akan membuka file PDF ini.', 'ugm-faculty' ); ?></p>
	<?php
}

/**
 * Save magazine PDF & edition post meta.
 *
 * @param int $post_id Post ID.
 */
function ugm_save_magazine_pdf_meta_box( $post_id ) {
	if ( ! isset( $_POST['ugm_majalah_pdf_meta_box_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ugm_majalah_pdf_meta_box_nonce'] ) ), 'ugm_majalah_pdf_meta_box' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Save Nomor Edisi
	if ( isset( $_POST['ugm_majalah_edisi_field'] ) ) {
		$edition = sanitize_text_field( wp_unslash( $_POST['ugm_majalah_edisi_field'] ) );
		if ( '' !== $edition ) {
			update_post_meta( $post_id, '_ugm_majalah_edisi', $edition );
		} else {
			delete_post_meta( $post_id, '_ugm_majalah_edisi' );
		}
	}

	// Save File PDF
	if ( ! isset( $_POST['ugm_majalah_pdf_field'] ) ) {
		delete_post_meta( $post_id, '_ugm_majalah_pdf' );
		return;
	}

	$raw_value = wp_unslash( $_POST['ugm_majalah_pdf_field'] );
	$value     = trim( (string) $raw_value );

	if ( '' === $value ) {
		delete_post_meta( $post_id, '_ugm_majalah_pdf' );
		return;
	}

	$sanitized = is_numeric( $value ) ? (string) absint( $value ) : esc_url_raw( $value );
	update_post_meta( $post_id, '_ugm_majalah_pdf', $sanitized );
}
add_action( 'save_post_majalah', 'ugm_save_magazine_pdf_meta_box' );

/**
 * Enqueue media uploader script for magazine PDF meta box.
 *
 * @param string $hook_suffix Current admin page.
 */
function ugm_enqueue_magazine_pdf_meta_box_assets( $hook_suffix ) {
	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();

	if ( ! $screen || 'majalah' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_media();

	$script = <<<JS
jQuery(function($) {
	var frame;
	var input = $('#ugm_majalah_pdf_field');

	$(document).on('click', '.ugm-majalah-pdf-upload', function(e) {
		e.preventDefault();
		if (!input.length) {
			return;
		}

		if (frame) {
			frame.open();
			return;
		}

		frame = wp.media({
			title: 'Pilih File PDF Majalah',
			button: { text: 'Gunakan PDF' },
			library: { type: 'application/pdf' },
			multiple: false
		});

		frame.on('select', function() {
			var attachment = frame.state().get('selection').first().toJSON();
			if (!attachment) {
				return;
			}
			input.val(attachment.id ? attachment.id : attachment.url);
		});

		frame.open();
	});

	$(document).on('click', '.ugm-majalah-pdf-remove', function(e) {
		e.preventDefault();
		if (input.length) {
			input.val('');
		}
	});
});
JS;

	wp_add_inline_script( 'jquery-core', $script );
}
add_action( 'admin_enqueue_scripts', 'ugm_enqueue_magazine_pdf_meta_box_assets' );
