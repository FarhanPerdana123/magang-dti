<?php
/**
 * Landing page template defaults and editor seeding.
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the default landing-page block order.
 *
 * @return string Serialized block markup.
 */
function ugm_get_default_landing_page_blocks() {
	$hero_attrs = array(
		'imageId'     => 0,
		'imageUrl'    => '',
		'title'       => "UNIVERSITAS\nGADJAH MADA",
		'description' => __( 'Universitas Gadjah Mada merupakan universitas negeri terkemuka di Indonesia yang unggul dalam bidang pendidikan, penelitian, dan pengabdian kepada masyarakat, serta berperan aktif dalam menghasilkan lulusan berkualitas dan berdaya saing global.', 'ugm-faculty' ),
	);

	$featured_columns = array(
		array(
			'title'        => __( 'Seputar Kampus', 'ugm-faculty' ),
			'categorySlug' => 'seputar-kampus',
		),
		array(
			'title'        => __( 'Kabar Fakultas', 'ugm-faculty' ),
			'categorySlug' => 'kabar-fakultas',
		),
		array(
			'title'        => __( 'Kerjasama', 'ugm-faculty' ),
			'categorySlug' => 'kerjasama',
		),
	);

	$blocks  = '<!-- wp:ugm/hero-section ' . wp_json_encode( $hero_attrs ) . ' /-->' . "\n";
	$blocks .= '<!-- wp:ugm/latest-news {"title":"Berita Terbaru","categorySlug":""} /-->' . "\n";
	$blocks .= '<!-- wp:ugm/academic-news {"title":"Berita Akademik","categorySlug":"pendidikan"} /-->' . "\n";
	$blocks .= '<!-- wp:ugm/profile-section {"title":"Profile","categorySlug":"profile"} /-->' . "\n";
	$blocks .= '<!-- wp:ugm/achievement-section {"title":"Prestasi","categorySlug":"prestasi"} /-->' . "\n";
	foreach ( $featured_columns as $featured_column ) {
		$blocks .= '<!-- wp:ugm/featured-category-column ' . wp_json_encode( $featured_column ) . ' /-->' . "\n";
	}
	$blocks .= '<!-- wp:ugm/category-section {"title":"Kategori"} /-->' . "\n";
	$blocks .= '<!-- wp:ugm/faculty-section {"overline":"Seputar UGM","title":"Fakultas dan Sekolah","categorySlug":"fakultas-dan-sekolah"} /-->' . "\n";
	$blocks .= '<!-- wp:ugm/agenda-section {"title":"Agenda Kegiatan","categorySlug":"agenda"} /-->' . "\n";
	$blocks .= '<!-- wp:ugm/magazine-section /-->' . "\n";
	$blocks .= '<!-- wp:ugm/video-section {"title":"Video","categorySlug":"video"} /-->' . "\n";
	$blocks .= '<!-- wp:ugm/template-links {"title":"Tautan Layanan","items":[]} /-->';

	return $blocks;
}

/**
 * Check whether a template slug refers to a Landing Page template variant.
 *
 * @param string $template Template slug/path.
 * @return bool
 */
function ugm_is_landing_page_template_slug( $template ) {
	return in_array(
		(string) $template,
		array(
			'page-templates/template-landing-page.php',
			'landing-page',
		),
		true
	);
}

/**
 * Populate an empty Landing Page with default section blocks.
 *
 * @param int $post_id Page ID.
 * @return bool True when content was updated.
 */
function ugm_populate_empty_landing_page( $post_id ) {
	$post_id = absint( $post_id );
	if ( $post_id <= 0 || 'page' !== get_post_type( $post_id ) ) {
		return false;
	}

	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post || '' !== trim( (string) $post->post_content ) ) {
		return false;
	}

	if ( ! ugm_is_landing_page_template_slug( get_page_template_slug( $post_id ) ) ) {
		return false;
	}

	remove_action( 'save_post_page', 'ugm_seed_landing_page_on_save', 20 );
	wp_update_post(
		array(
			'ID'           => $post_id,
			'post_content' => ugm_get_default_landing_page_blocks(),
		)
	);
	add_action( 'save_post_page', 'ugm_seed_landing_page_on_save', 20, 3 );

	return true;
}

/**
 * Seed Landing Page content after template selection is saved.
 *
 * @param int     $post_id Page ID.
 * @param WP_Post $post    Post object.
 * @param bool    $update  Whether this is an update.
 * @return void
 */
function ugm_seed_landing_page_on_save( $post_id, $post, $update ) {
	unset( $post, $update );

	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	ugm_populate_empty_landing_page( $post_id );
}
add_action( 'save_post_page', 'ugm_seed_landing_page_on_save', 20, 3 );

/**
 * Repair existing empty pages that already use the Landing Page template.
 *
 * @return void
 */
function ugm_seed_existing_empty_landing_pages() {
	if ( ! is_admin() ) {
		return;
	}

	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'private', 'pending' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'   => '_wp_page_template',
					'value' => 'page-templates/template-landing-page.php',
				),
				array(
					'key'   => '_wp_page_template',
					'value' => 'landing-page',
				),
			),
		)
	);

	foreach ( $pages as $page_id ) {
		ugm_populate_empty_landing_page( $page_id );
	}
}
add_action( 'admin_init', 'ugm_seed_existing_empty_landing_pages' );
