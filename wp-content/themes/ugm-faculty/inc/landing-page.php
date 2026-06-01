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
	$blocks .= '<!-- wp:ugm/academic-news {"title":"Informasi Akademik","categorySlug":"pendidikan"} /-->' . "\n";
	$blocks .= '<!-- wp:ugm/profile-section {"title":"Informasi Umum","categorySlug":"profile"} /-->' . "\n";
	$blocks .= '<!-- wp:ugm/achievement-section {"title":"Pencapaian","categorySlug":"prestasi"} /-->' . "\n";
	foreach ( $featured_columns as $featured_column ) {
		$blocks .= '<!-- wp:ugm/featured-category-column ' . wp_json_encode( $featured_column ) . ' /-->' . "\n";
	}
	$blocks .= '<!-- wp:ugm/category-section {"title":"Kategori"} /-->' . "\n";
	$blocks .= '<!-- wp:ugm/faculty-list {"overline":"Seputar UGM","title":"Fakultas dan Sekolah","items":[]} /-->' . "\n";
	$blocks .= '<!-- wp:ugm/agenda-only {"title":"Agenda Kegiatan","categorySlug":"agenda","visibility":"desktop"} /-->' . "\n";
	$blocks .= '<!-- wp:ugm/facility-only {"title":"Fasilitas Mahasiswa","categorySlug":"fasilitas-mahasiswa","visibility":"desktop"} /-->' . "\n";
	$blocks .= '<!-- wp:ugm/magazine-section /-->' . "\n";
	$blocks .= '<!-- wp:ugm/video-section {"title":"Video","categorySlug":"video","visibility":"desktop"} /-->' . "\n";
	$blocks .= '<!-- wp:ugm/template-links {"title":"Tautan Layanan","items":[],"visibility":"desktop"} /-->';

	return $blocks;
}

/**
 * Check whether serialized content contains UGM landing blocks.
 *
 * @param string $content Serialized block content.
 * @return bool
 */
function ugm_landing_page_has_ugm_blocks( $content ) {
	return false !== strpos( (string) $content, '<!-- wp:ugm/' );
}

/**
 * Build a serialized dynamic block comment.
 *
 * @param string $block_name Block name without the "wp:" prefix.
 * @param array  $attrs      Block attributes.
 * @return string
 */
function ugm_landing_page_block_comment( $block_name, $attrs = array() ) {
	$attrs = is_array( $attrs ) ? array_filter(
		$attrs,
		static function ( $value ) {
			return null !== $value;
		}
	) : array();

	return '<!-- wp:' . $block_name . ( empty( $attrs ) ? '' : ' ' . wp_json_encode( $attrs ) ) . ' /-->';
}

/**
 * Parse serialized block comments into block arrays.
 *
 * @param string $content Serialized block content.
 * @return array[]
 */
function ugm_landing_page_parse_block_comments( $content ) {
	$parsed = parse_blocks( (string) $content );
	return is_array( $parsed ) ? $parsed : array();
}

/**
 * Replace legacy landing-page block sections with the current block set.
 *
 * Keeps user configured titles/category slugs where the new block has an
 * equivalent setting. Blocks that are already current are left untouched.
 *
 * @param string $content Serialized block content.
 * @return string Normalized serialized content.
 */
function ugm_normalize_landing_page_blocks( $content ) {
	$content = (string) $content;

	if ( ! ugm_landing_page_has_ugm_blocks( $content ) ) {
		return $content;
	}

	$blocks     = ugm_landing_page_parse_block_comments( $content );
	$normalized = array();
	$changed    = false;

	foreach ( $blocks as $block ) {
		$block_name = isset( $block['blockName'] ) ? (string) $block['blockName'] : '';
		$attrs      = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();

		if ( 'ugm/featured-categories' === $block_name ) {
			$changed = true;
			$normalized = array_merge(
				$normalized,
				ugm_landing_page_parse_block_comments(
					ugm_landing_page_block_comment(
						'ugm/featured-category-column',
						array(
							'title'        => $attrs['campusTitle'] ?? __( 'Seputar Kampus', 'ugm-faculty' ),
							'categorySlug' => $attrs['campusCategorySlug'] ?? 'seputar-kampus',
							'visibility'   => $attrs['visibility'] ?? null,
						)
					) . "\n" .
					ugm_landing_page_block_comment(
						'ugm/featured-category-column',
						array(
							'title'        => $attrs['facultyTitle'] ?? __( 'Kabar Fakultas', 'ugm-faculty' ),
							'categorySlug' => $attrs['facultyCategorySlug'] ?? 'kabar-fakultas',
							'visibility'   => $attrs['visibility'] ?? null,
						)
					) . "\n" .
					ugm_landing_page_block_comment(
						'ugm/featured-category-column',
						array(
							'title'        => $attrs['partnershipTitle'] ?? __( 'Kerjasama', 'ugm-faculty' ),
							'categorySlug' => $attrs['partnershipCategorySlug'] ?? 'kerjasama',
							'visibility'   => $attrs['visibility'] ?? null,
						)
					)
				)
			);
			continue;
		}

		if ( 'ugm/faculty-section' === $block_name ) {
			$changed = true;
			$normalized = array_merge(
				$normalized,
				ugm_landing_page_parse_block_comments(
					ugm_landing_page_block_comment(
						'ugm/faculty-list',
						array(
							'overline'   => $attrs['overline'] ?? __( 'Seputar UGM', 'ugm-faculty' ),
							'title'      => $attrs['title'] ?? __( 'Fakultas dan Sekolah', 'ugm-faculty' ),
							'items'      => array(),
							'visibility' => $attrs['visibility'] ?? null,
						)
					)
				)
			);
			continue;
		}

		if ( 'ugm/agenda-section' === $block_name ) {
			$changed = true;
			$normalized = array_merge(
				$normalized,
				ugm_landing_page_parse_block_comments(
					ugm_landing_page_block_comment(
						'ugm/agenda-only',
						array(
							'title'        => $attrs['title'] ?? __( 'Agenda Kegiatan', 'ugm-faculty' ),
							'categorySlug' => $attrs['categorySlug'] ?? 'agenda',
							'visibility'   => $attrs['visibility'] ?? null,
						)
					) . "\n" .
					ugm_landing_page_block_comment(
						'ugm/facility-only',
						array(
							'title'        => $attrs['facilityTitle'] ?? __( 'Fasilitas Mahasiswa', 'ugm-faculty' ),
							'categorySlug' => $attrs['facilityCategorySlug'] ?? 'fasilitas-mahasiswa',
							'visibility'   => $attrs['visibility'] ?? null,
						)
					)
				)
			);
			continue;
		}

		if ( 'ugm/facility-section' === $block_name ) {
			$changed = true;
			$normalized = array_merge(
				$normalized,
				ugm_landing_page_parse_block_comments(
					ugm_landing_page_block_comment(
						'ugm/facility-only',
						array(
							'title'        => $attrs['title'] ?? __( 'Fasilitas Mahasiswa', 'ugm-faculty' ),
							'categorySlug' => $attrs['categorySlug'] ?? 'fasilitas-mahasiswa',
							'visibility'   => $attrs['visibility'] ?? null,
						)
					)
				)
			);
			continue;
		}

		$normalized[] = $block;
	}

	if ( ! $changed ) {
		return $content;
	}

	return trim( serialize_blocks( $normalized ) ) . "\n";
}

/**
 * Get customized Site Editor content for the landing-page block template.
 *
 * @return string
 */
function ugm_get_landing_page_block_template_content() {
	if ( ! function_exists( 'get_block_template' ) ) {
		return '';
	}

	$template = get_block_template( get_stylesheet() . '//landing-page', 'wp_template' );
	if ( ! $template || empty( $template->content ) ) {
		return '';
	}

	return (string) $template->content;
}

/**
 * Resolve the frontend/editor source of truth for landing-page blocks.
 *
 * Page content wins because normal page edits are per-page. If a page is
 * empty, use a customized Site Editor template before falling back to theme
 * defaults so template editor saves are reflected on the site.
 *
 * @param string $post_content  Page post_content.
 * @param string $template_slug Page template slug.
 * @return string Serialized block content.
 */
function ugm_get_landing_page_render_content( $post_content = '', $template_slug = '' ) {
	$post_content = (string) $post_content;
	$template_slug = (string) $template_slug;

	if ( 'landing-page' === $template_slug ) {
		$template_content = ugm_get_landing_page_block_template_content();
		if ( ugm_landing_page_has_ugm_blocks( $template_content ) ) {
			return ugm_normalize_landing_page_blocks( $template_content );
		}
	}

	if ( ugm_landing_page_has_ugm_blocks( $post_content ) ) {
		return ugm_normalize_landing_page_blocks( $post_content );
	}

	$template_content = ugm_get_landing_page_block_template_content();
	if ( ugm_landing_page_has_ugm_blocks( $template_content ) ) {
		return ugm_normalize_landing_page_blocks( $template_content );
	}

	return ugm_get_default_landing_page_blocks();
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

/**
 * Normalize stored landing-page content that still uses legacy section blocks.
 *
 * This runs idempotently in admin requests. It only writes when a page or a
 * customized Site Editor template actually changes, so old migrated installs
 * are repaired without requiring an option reset.
 *
 * @return void
 */
function ugm_normalize_existing_landing_page_content() {
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
		$page = get_post( $page_id );
		if ( ! $page instanceof WP_Post ) {
			continue;
		}

		$content    = (string) $page->post_content;
		$normalized = ugm_normalize_landing_page_blocks( $content );

		if ( $normalized === $content ) {
			continue;
		}

		remove_action( 'save_post_page', 'ugm_seed_landing_page_on_save', 20 );
		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => $normalized,
			)
		);
		add_action( 'save_post_page', 'ugm_seed_landing_page_on_save', 20, 3 );
	}

	$template_posts = get_posts(
		array(
			'post_type'      => 'wp_template',
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => -1,
			's'              => 'landing-page',
		)
	);

	foreach ( $template_posts as $template_post ) {
		if ( ! $template_post instanceof WP_Post ) {
			continue;
		}

		$post_name = (string) $template_post->post_name;
		$is_landing_template = 'landing-page' === $post_name || false !== strpos( $post_name, 'landing-page' );
		if ( ! $is_landing_template ) {
			continue;
		}

		$content    = (string) $template_post->post_content;
		$normalized = ugm_normalize_landing_page_blocks( $content );

		if ( $normalized === $content ) {
			continue;
		}

		wp_update_post(
			array(
				'ID'           => $template_post->ID,
				'post_content' => $normalized,
			)
		);
	}
}
add_action( 'admin_init', 'ugm_normalize_existing_landing_page_content', 30 );
