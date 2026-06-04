<?php
/**
 * Gallery page template helpers and block registration.
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ugm_get_default_gallery_page_blocks() {
	return '<!-- wp:ugm/gallery-page {"title":"Galeri"} /-->';
}

function ugm_has_gallery_page_block( $content ) {
	return false !== strpos( (string) $content, '<!-- wp:ugm/gallery-page' );
}

function ugm_get_gallery_block_template_blocks() {
	return '<!-- wp:ugm/gallery-template-preview /-->' . "\n" .
		'<!-- wp:post-content /-->';
}

function ugm_is_gallery_page_template_slug( $template ) {
	return in_array(
		(string) $template,
		array(
			'page-templates/template-gallery.php',
			'gallery-page',
		),
		true
	);
}

/**
 * Resolve the page ID currently being rendered by a Gallery block.
 *
 * @param WP_Block|null $block Block instance passed to the render callback.
 * @return int
 */
function ugm_get_gallery_render_post_id( $block = null ) {
	if ( $block instanceof WP_Block && ! empty( $block->context['postId'] ) ) {
		return absint( $block->context['postId'] );
	}

	$queried_id = absint( get_queried_object_id() );
	if ( $queried_id > 0 && 'page' === get_post_type( $queried_id ) ) {
		return $queried_id;
	}

	$post_id = absint( get_the_ID() );
	if ( $post_id > 0 && 'page' === get_post_type( $post_id ) ) {
		return $post_id;
	}

	return 0;
}

/**
 * Check whether the Gallery block is being rendered for a page using the
 * Gallery Page template. This protects both frontend the_content() and
 * Gutenberg server-side previews from leaked Gallery blocks in normal pages.
 *
 * @param WP_Block|null $block Block instance passed to the render callback.
 * @return bool
 */
function ugm_should_render_gallery_page_block( $block = null ) {
	$post_id = ugm_get_gallery_render_post_id( $block );

	if (
		defined( 'REST_REQUEST' ) &&
		REST_REQUEST &&
		$post_id > 0 &&
		current_user_can( 'edit_post', $post_id )
	) {
		return true;
	}

	return $post_id > 0 && ugm_is_gallery_page_template_slug( get_page_template_slug( $post_id ) );
}

function ugm_get_gallery_render_source( $page_content = '', $template_slug = '' ) {
	$page_content = (string) $page_content;

	if ( function_exists( 'ugm_has_management_page_blocks' ) && ugm_has_management_page_blocks( $page_content ) ) {
		return ugm_get_default_gallery_page_blocks();
	}

	if ( ugm_is_gallery_page_template_slug( $template_slug ) && ! ugm_has_gallery_page_block( $page_content ) ) {
		return ugm_get_default_gallery_page_blocks();
	}

	if ( false !== strpos( $page_content, '<!-- wp:' ) || '' !== trim( wp_strip_all_tags( strip_shortcodes( $page_content ) ) ) ) {
		return $page_content;
	}

	return ugm_get_default_gallery_page_blocks();
}

/**
 * Populate an empty Gallery Page with its editable listing block.
 *
 * @param int $post_id Page ID.
 * @return bool True when content was updated.
 */
function ugm_populate_empty_gallery_page( $post_id ) {
	$post_id = absint( $post_id );
	if ( $post_id <= 0 || 'page' !== get_post_type( $post_id ) ) {
		return false;
	}

	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		return false;
	}

	if ( ! ugm_is_gallery_page_template_slug( get_page_template_slug( $post_id ) ) ) {
		return false;
	}

	$content         = (string) $post->post_content;
	$has_text        = '' !== trim( wp_strip_all_tags( strip_shortcodes( $content ) ) );
	$should_populate = '' === trim( $content )
		|| ( ! ugm_has_gallery_page_block( $content ) && ! $has_text )
		|| ( function_exists( 'ugm_has_management_page_blocks' ) && ugm_has_management_page_blocks( $content ) );

	if ( ! $should_populate ) {
		return false;
	}

	remove_action( 'save_post_page', 'ugm_seed_gallery_page_on_save', 20 );
	wp_update_post(
		array(
			'ID'           => $post_id,
			'post_content' => ugm_get_default_gallery_page_blocks(),
		)
	);
	add_action( 'save_post_page', 'ugm_seed_gallery_page_on_save', 20, 3 );

	return true;
}

/**
 * Check whether the page content only contains the Gallery Page block.
 *
 * @param string $content Page content.
 * @return bool
 */
function ugm_content_is_only_gallery_page_block( $content ) {
	$blocks      = parse_blocks( (string) $content );
	$has_gallery = false;

	foreach ( $blocks as $block ) {
		$block_name = $block['blockName'] ?? null;

		if ( in_array( $block_name, array( 'ugm/gallery-page', 'ugm/gallery-template-preview' ), true ) ) {
			$has_gallery = true;
			continue;
		}

		$inner_html = trim( wp_strip_all_tags( (string) ( $block['innerHTML'] ?? '' ) ) );
		if ( null === $block_name && '' === $inner_html ) {
			continue;
		}

		return false;
	}

	return $has_gallery;
}

/**
 * Remove Gallery Page blocks from parsed block trees while preserving other
 * page content.
 *
 * @param array[] $blocks Parsed blocks.
 * @return array[]
 */
function ugm_filter_gallery_page_blocks( $blocks ) {
	$filtered = array();

	foreach ( is_array( $blocks ) ? $blocks : array() as $block ) {
		$block_name = $block['blockName'] ?? null;

		if ( in_array( $block_name, array( 'ugm/gallery-page', 'ugm/gallery-template-preview' ), true ) ) {
			continue;
		}

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			$block['innerBlocks'] = ugm_filter_gallery_page_blocks( $block['innerBlocks'] );
		}

		$filtered[] = $block;
	}

	return $filtered;
}

/**
 * Strip Gallery Page blocks from saved content.
 *
 * @param string $content Page content.
 * @return string
 */
function ugm_remove_gallery_page_blocks_from_content( $content ) {
	$content = (string) $content;

	if (
		false === strpos( $content, '<!-- wp:ugm/gallery-page' ) &&
		false === strpos( $content, '<!-- wp:ugm/gallery-template-preview' )
	) {
		return $content;
	}

	return trim( serialize_blocks( ugm_filter_gallery_page_blocks( parse_blocks( $content ) ) ) );
}

/**
 * Remove the auto-seeded Gallery block when the page is switched away from the
 * Gallery template.
 *
 * @param int     $post_id Page ID.
 * @param WP_Post $post    Post object.
 * @param bool    $update  Whether this is an update.
 * @return void
 */
function ugm_clear_gallery_page_on_default_template( $post_id, $post, $update ) {
	unset( $update );

	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) || 'page' !== get_post_type( $post_id ) ) {
		return;
	}

	if ( ugm_is_gallery_page_template_slug( get_page_template_slug( $post_id ) ) ) {
		return;
	}

	$content = $post instanceof WP_Post ? (string) $post->post_content : (string) get_post_field( 'post_content', $post_id );
	$cleaned = ugm_remove_gallery_page_blocks_from_content( $content );
	if ( $cleaned === $content ) {
		return;
	}

	remove_action( 'save_post_page', 'ugm_clear_gallery_page_on_default_template', 25 );
	wp_update_post(
		array(
			'ID'           => $post_id,
			'post_content' => $cleaned,
		)
	);
	add_action( 'save_post_page', 'ugm_clear_gallery_page_on_default_template', 25, 3 );
}
add_action( 'save_post_page', 'ugm_clear_gallery_page_on_default_template', 25, 3 );

/**
 * REST saves update the page template meta after wp_update_post(), so the
 * save_post hook can still see the old template. Run once more after the REST
 * controller has persisted the selected template.
 *
 * @param WP_Post         $post     Inserted or updated post object.
 * @param WP_REST_Request $request  Request object.
 * @param bool            $creating Whether the post was created.
 * @return void
 */
function ugm_clear_gallery_page_after_rest_save( $post, $request, $creating ) {
	unset( $request, $creating );

	if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
		return;
	}

	if ( ugm_is_gallery_page_template_slug( get_page_template_slug( $post->ID ) ) ) {
		ugm_populate_empty_gallery_page( $post->ID );
		return;
	}

	ugm_clear_gallery_page_on_default_template( $post->ID, get_post( $post->ID ), true );
}
add_action( 'rest_after_insert_page', 'ugm_clear_gallery_page_after_rest_save', 20, 3 );


/**
 * Remove leaked Gallery blocks from the global "Pages" block template.
 *
 * @return void
 */
function ugm_repair_default_page_template_gallery_leak() {
	if ( ! is_admin() ) {
		return;
	}

	$page_templates = get_posts(
		array(
			'post_type'      => 'wp_template',
			'post_status'    => array( 'publish', 'draft' ),
			'name'           => 'page',
			'posts_per_page' => 1,
		)
	);

	foreach ( $page_templates as $template_post ) {
		if ( ! $template_post instanceof WP_Post ) {
			continue;
		}

		$template_content = (string) $template_post->post_content;
		if (
			! ugm_content_is_only_gallery_page_block( $template_content ) &&
			false === strpos( $template_content, '<!-- wp:ugm/gallery-page' ) &&
			false === strpos( $template_content, '<!-- wp:ugm/gallery-template-preview' )
		) {
			continue;
		}

		wp_update_post(
			array(
				'ID'           => $template_post->ID,
				'post_content' => '<!-- wp:post-content {"layout":{"type":"default"}} /-->',
			)
		);
	}
}
add_action( 'admin_init', 'ugm_repair_default_page_template_gallery_leak', 26 );

/**
 * Seed Gallery Page content after its template is selected.
 *
 * @param int     $post_id Page ID.
 * @param WP_Post $post    Post object.
 * @param bool    $update  Whether this is an update.
 * @return void
 */
function ugm_seed_gallery_page_on_save( $post_id, $post, $update ) {
	unset( $post, $update );

	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	ugm_populate_empty_gallery_page( $post_id );
}
add_action( 'save_post_page', 'ugm_seed_gallery_page_on_save', 20, 3 );

/**
 * Repair existing empty pages that already use a Gallery Page template.
 *
 * @return void
 */
function ugm_seed_existing_empty_gallery_pages() {
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
					'value' => 'page-templates/template-gallery.php',
				),
				array(
					'key'   => '_wp_page_template',
					'value' => 'gallery-page',
				),
			),
		)
	);

	foreach ( $pages as $page_id ) {
		ugm_populate_empty_gallery_page( $page_id );
	}
}
add_action( 'admin_init', 'ugm_seed_existing_empty_gallery_pages' );

function ugm_repair_gallery_block_template() {
	if ( ! is_admin() ) {
		return;
	}

	$template_posts = get_posts(
		array(
			'post_type'      => 'wp_template',
			'post_status'    => array( 'publish', 'draft' ),
			'name'           => 'gallery-page',
			'posts_per_page' => 1,
		)
	);

	foreach ( $template_posts as $template_post ) {
		if ( ! $template_post instanceof WP_Post ) {
			continue;
		}

		if ( ugm_get_gallery_block_template_blocks() === trim( (string) $template_post->post_content ) ) {
			continue;
		}

		wp_update_post(
			array(
				'ID'           => $template_post->ID,
				'post_content' => ugm_get_gallery_block_template_blocks(),
			)
		);
	}
}
add_action( 'admin_init', 'ugm_repair_gallery_block_template', 25 );

function ugm_use_php_gallery_template_on_frontend( $template ) {
	if ( is_admin() || ! is_page() ) {
		return $template;
	}

	$page_id = (int) get_queried_object_id();
	if ( $page_id <= 0 || ! ugm_is_gallery_page_template_slug( get_page_template_slug( $page_id ) ) ) {
		return $template;
	}

	$php_template = get_theme_file_path( 'page-templates/template-gallery.php' );
	return file_exists( $php_template ) ? $php_template : $template;
}
add_filter( 'template_include', 'ugm_use_php_gallery_template_on_frontend', 20 );

function ugm_gallery_page_body_class( $classes ) {
	if ( is_page() && ugm_is_gallery_page_template_slug( get_page_template_slug( get_queried_object_id() ) ) ) {
		$classes[] = 'ugm-is-gallery-page';
	}

	return $classes;
}
add_filter( 'body_class', 'ugm_gallery_page_body_class' );

/**
 * Normalize manually configured gallery cards.
 *
 * @param mixed $items Gallery items block attribute.
 * @return array[]
 */
function ugm_gallery_manual_items( $items ) {
	$normalized = array();

	foreach ( is_array( $items ) ? $items : array() as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}

		$images = array();
		foreach ( isset( $item['images'] ) && is_array( $item['images'] ) ? $item['images'] : array() as $image ) {
			if ( ! is_array( $image ) ) {
				continue;
			}

			$image_id  = absint( $image['id'] ?? 0 );
			$image_url = $image_id > 0 ? (string) wp_get_attachment_image_url( $image_id, 'large' ) : '';
			if ( '' === $image_url && ! empty( $image['url'] ) ) {
				$image_url = esc_url_raw( (string) $image['url'] );
			}

			if ( '' !== $image_url ) {
				$images[] = array(
					'id'  => $image_id,
					'url' => $image_url,
				);
			}
		}

		$normalized[] = array(
			'title'       => trim( (string) ( $item['title'] ?? '' ) ),
			'date'        => trim( (string) ( $item['date'] ?? '' ) ),
			'description' => trim( (string) ( $item['description'] ?? '' ) ),
			'images'      => $images,
		);
	}

	return $normalized;
}

/**
 * Render the cover image for a manually configured gallery card.
 *
 * @param array $item Gallery item.
 * @return string
 */
function ugm_gallery_manual_card_image( $item ) {
	$image_url = isset( $item['images'][0]['url'] ) ? (string) $item['images'][0]['url'] : '';
	if ( '' === $image_url ) {
		return '<span class="ugm-gallery-placeholder" aria-hidden="true"></span>';
	}

	return '<img src="' . esc_url( $image_url ) . '" alt="" loading="lazy" decoding="async">';
}

function ugm_gallery_detail_url( $item_index ) {
	return add_query_arg( 'ugm_gallery_item', absint( $item_index ) + 1, get_permalink() );
}

function ugm_render_gallery_detail( $title, $item, $item_index ) {
	$images      = isset( $item['images'] ) && is_array( $item['images'] ) ? $item['images'] : array();
	$detail_url  = ugm_gallery_detail_url( $item_index );
	$author_id   = absint( get_post_field( 'post_author', get_the_ID() ) );
	$author_name = $author_id > 0 ? get_the_author_meta( 'display_name', $author_id ) : '';

	ob_start();
	?>
	<div class="ugm-gallery-detail" data-gallery-detail>
		<nav class="ugm-gallery-detail__breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'ugm-faculty' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Berita', 'ugm-faculty' ); ?></a>
			<span aria-hidden="true">&gt;</span>
			<a href="<?php echo esc_url( remove_query_arg( 'ugm_gallery_item', $detail_url ) ); ?>"><?php echo esc_html( $title ); ?></a>
			<span aria-hidden="true">&gt;</span>
			<span><?php echo esc_html( '' !== $item['title'] ? $item['title'] : __( 'Galeri', 'ugm-faculty' ) ); ?></span>
		</nav>
		<article class="ugm-gallery-detail__article">
			<h1><?php echo esc_html( '' !== $item['title'] ? $item['title'] : __( 'Galeri', 'ugm-faculty' ) ); ?></h1>
			<div class="ugm-gallery-detail__meta">
				<time><?php echo esc_html( '' !== $item['date'] ? $item['date'] : date_i18n( 'l, j F Y' ) ); ?></time>
				<?php if ( '' !== $author_name ) : ?><span><?php esc_html_e( 'Oleh:', 'ugm-faculty' ); ?> <strong><?php echo esc_html( $author_name ); ?></strong></span><?php endif; ?>
			</div>
			<?php if ( '' !== $item['description'] ) : ?><p class="ugm-gallery-detail__description"><?php echo esc_html( $item['description'] ); ?></p><?php endif; ?>
			<?php if ( ! empty( $images ) ) : ?>
				<div class="ugm-gallery-detail__viewer">
					<button type="button" class="ugm-gallery-detail__arrow ugm-gallery-detail__arrow--prev" data-gallery-detail-prev aria-label="<?php esc_attr_e( 'Gambar sebelumnya', 'ugm-faculty' ); ?>">&larr;</button>
					<div class="ugm-gallery-detail__stage">
						<?php foreach ( $images as $image_index => $image ) : ?>
							<img class="<?php echo 0 === $image_index ? 'is-active' : ''; ?>" data-gallery-detail-image="<?php echo esc_attr( $image_index ); ?>" src="<?php echo esc_url( $image['url'] ); ?>" alt="" loading="<?php echo 0 === $image_index ? 'eager' : 'lazy'; ?>" decoding="async">
						<?php endforeach; ?>
					</div>
					<button type="button" class="ugm-gallery-detail__arrow ugm-gallery-detail__arrow--next" data-gallery-detail-next aria-label="<?php esc_attr_e( 'Gambar berikutnya', 'ugm-faculty' ); ?>">&rarr;</button>
				</div>
				<div class="ugm-gallery-detail__thumbs">
					<?php foreach ( $images as $image_index => $image ) : ?>
						<button type="button" class="<?php echo 0 === $image_index ? 'is-active' : ''; ?>" data-gallery-detail-thumb="<?php echo esc_attr( $image_index ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Tampilkan gambar %d', 'ugm-faculty' ), $image_index + 1 ) ); ?>">
							<img src="<?php echo esc_url( $image['url'] ); ?>" alt="" loading="lazy" decoding="async">
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</article>
	</div>
	<?php
	return ob_get_clean();
}

function ugm_render_block_gallery_page( $attrs, $content = '', $block = null ) {
	unset( $content );

	if ( ! ugm_should_render_gallery_page_block( $block ) ) {
		return '';
	}

	return ugm_render_gallery_page_markup( $attrs );
}

function ugm_render_block_gallery_template_preview( $attrs ) {
	return ugm_render_gallery_page_markup( $attrs );
}

function ugm_render_gallery_page_markup( $attrs ) {
	$attrs = wp_parse_args(
		is_array( $attrs ) ? $attrs : array(),
		array(
			'title'        => 'Galeri',
			'buttonLabel'  => 'Selengkapnya',
			'galleryItems' => array(),
		)
	);

	$title          = trim( (string) $attrs['title'] );
	$button_label   = trim( (string) $attrs['buttonLabel'] );
	$manual_items   = ugm_gallery_manual_items( $attrs['galleryItems'] );
	$detail_index   = max( 0, absint( get_query_var( 'ugm_gallery_item' ) ) - 1 );
	$has_detail     = '' !== (string) get_query_var( 'ugm_gallery_item' ) && isset( $manual_items[ $detail_index ] );

	if ( $has_detail ) {
		return ugm_render_gallery_detail( $title, $manual_items[ $detail_index ], $detail_index );
	}

	$hero_slides    = array();

	foreach ( $manual_items as $manual_index => $manual_item ) {
		$item_images = array_column( $manual_item['images'], 'url' );
		foreach ( ! empty( $item_images ) ? array_chunk( $item_images, 4 ) : array( array() ) as $item_image_chunk ) {
			$hero_slides[] = array(
				'item'       => $manual_item,
				'item_index' => $manual_index,
				'images'     => $item_image_chunk,
			);
		}
	}

	if ( empty( $hero_slides ) ) {
		$hero_slides[] = array(
			'item'       => null,
			'item_index' => 0,
			'images'     => array(),
		);
	}

	$hero_page_count = count( $hero_slides );

	ob_start();
	?>
	<div class="ugm-gallery-template">
		<header class="ugm-gallery-page-head">
			<nav class="ugm-gallery-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'ugm-faculty' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Berita', 'ugm-faculty' ); ?></a>
				<span aria-hidden="true">&gt;</span>
				<span><?php echo esc_html( $title ); ?></span>
			</nav>
			<h1><?php echo esc_html( $title ); ?></h1>
		</header>

		<section class="ugm-gallery-hero" aria-label="<?php esc_attr_e( 'Sorotan galeri', 'ugm-faculty' ); ?>">
			<div class="ugm-gallery-hero__inner">
				<div class="ugm-gallery-hero__copy">
					<?php foreach ( $hero_slides as $page_index => $hero_slide ) : ?>
						<?php $manual_hero = $hero_slide['item']; ?>
						<?php $hero_item_index = absint( $hero_slide['item_index'] ); ?>
						<div class="ugm-gallery-hero__copy-slide<?php echo 0 === $page_index ? ' is-active' : ''; ?>" data-gallery-copy>
							<?php if ( is_array( $manual_hero ) ) : ?>
								<time><?php echo esc_html( '' !== $manual_hero['date'] ? $manual_hero['date'] : date_i18n( 'l, j F Y' ) ); ?></time>
								<h2><?php echo esc_html( '' !== $manual_hero['title'] ? $manual_hero['title'] : $title ); ?></h2>
								<?php if ( '' !== $manual_hero['description'] ) : ?><p><?php echo esc_html( wp_trim_words( $manual_hero['description'], 18, '...' ) ); ?></p><?php endif; ?>
								<?php if ( ! empty( $manual_hero['images'] ) ) : ?><a class="ugm-gallery-button" href="<?php echo esc_url( ugm_gallery_detail_url( $hero_item_index ) ); ?>"><?php echo esc_html( '' !== $button_label ? $button_label : __( 'Selengkapnya', 'ugm-faculty' ) ); ?></a><?php endif; ?>
							<?php else : ?>
								<time><?php echo esc_html( date_i18n( 'l, j F Y' ) ); ?></time>
								<h2><?php esc_html_e( 'Belum ada galeri', 'ugm-faculty' ); ?></h2>
								<p><?php esc_html_e( 'Tambahkan kartu galeri manual melalui editor untuk mengisi halaman ini.', 'ugm-faculty' ); ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
					<div class="ugm-gallery-hero__dots" aria-label="<?php esc_attr_e( 'Navigasi gambar sorotan', 'ugm-faculty' ); ?>">
						<?php for ( $page_index = 0; $page_index < $hero_page_count; $page_index++ ) : ?>
							<button type="button" class="<?php echo 0 === $page_index ? 'is-active' : ''; ?>" data-gallery-slide="<?php echo esc_attr( $page_index ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Tampilkan kumpulan gambar %d', 'ugm-faculty' ), $page_index + 1 ) ); ?>"<?php echo 0 === $page_index ? ' aria-current="true"' : ''; ?>></button>
						<?php endfor; ?>
					</div>
				</div>
				<div class="ugm-gallery-hero__media-slider" data-gallery-slider>
					<div class="ugm-gallery-hero__media-track">
						<?php foreach ( $hero_slides as $page_index => $hero_slide ) : ?>
							<div class="ugm-gallery-hero__media-grid" data-gallery-page>
								<?php for ( $item_index = 0; $item_index < 4; $item_index++ ) : ?>
									<?php $media_item = $hero_slide['images'][ $item_index ] ?? null; ?>
									<figure>
										<?php if ( is_string( $media_item ) && '' !== $media_item ) : ?>
											<img src="<?php echo esc_url( $media_item ); ?>" alt="" loading="lazy" decoding="async">
										<?php else : ?>
											<span class="ugm-gallery-placeholder" aria-hidden="true"></span>
										<?php endif; ?>
									</figure>
								<?php endfor; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>

		<section class="ugm-gallery-list" aria-labelledby="ugm-gallery-list-title">
			<header class="ugm-gallery-section-head">
				<h2 id="ugm-gallery-list-title"><?php echo esc_html( $title ); ?></h2>
				<span aria-hidden="true"></span>
			</header>
			<div class="ugm-gallery-grid">
				<?php if ( ! empty( $manual_items ) ) : ?>
					<?php foreach ( $manual_items as $manual_index => $manual_item ) : ?>
						<?php $detail_url = ugm_gallery_detail_url( $manual_index ); ?>
						<article class="ugm-gallery-card">
							<?php $has_manual_images = ! empty( $manual_item['images'] ); ?>
							<<?php echo $has_manual_images ? 'a' : 'div'; ?>
								class="ugm-gallery-card__media"
								<?php echo $has_manual_images ? ' href="' . esc_url( $detail_url ) . '"' : ''; ?>
							>
								<?php echo ugm_gallery_manual_card_image( $manual_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</<?php echo $has_manual_images ? 'a' : 'div'; ?>>
							<div class="ugm-gallery-card__body">
								<h3>
									<?php if ( $has_manual_images ) : ?>
										<a href="<?php echo esc_url( $detail_url ); ?>">
											<?php echo esc_html( '' !== $manual_item['title'] ? $manual_item['title'] : __( 'Galeri', 'ugm-faculty' ) ); ?>
										</a>
									<?php else : ?>
										<?php echo esc_html( '' !== $manual_item['title'] ? $manual_item['title'] : __( 'Galeri', 'ugm-faculty' ) ); ?>
									<?php endif; ?>
								</h3>
								<?php if ( '' !== $manual_item['date'] ) : ?><time><?php echo esc_html( strtoupper( $manual_item['date'] ) ); ?></time><?php endif; ?>
							</div>
						</article>
					<?php endforeach; ?>
				<?php else : ?>
					<?php for ( $i = 0; $i < 6; $i++ ) : ?>
						<article class="ugm-gallery-card ugm-gallery-card--skeleton" aria-hidden="true">
							<div class="ugm-gallery-card__media"><span class="ugm-gallery-placeholder"></span></div>
							<div class="ugm-gallery-card__body">
								<span class="ugm-skeleton-line ugm-skeleton-line--title"></span>
								<span class="ugm-skeleton-line ugm-skeleton-line--meta"></span>
							</div>
						</article>
					<?php endfor; ?>
				<?php endif; ?>
			</div>
		</section>
	</div>
	<?php

	return ob_get_clean();
}

function ugm_get_gallery_page_block_attributes() {
	return array(
		'title'        => array( 'type' => 'string', 'default' => 'Galeri' ),
		'buttonLabel'  => array( 'type' => 'string', 'default' => 'Selengkapnya' ),
		'galleryItems' => array( 'type' => 'array', 'default' => array() ),
	);
}

function ugm_register_gallery_page_blocks() {
	$attributes = ugm_get_gallery_page_block_attributes();

	register_block_type(
		'ugm/gallery-page',
		array(
			'api_version'     => 2,
			'render_callback' => 'ugm_render_block_gallery_page',
			'category'        => 'ugm-gallery-page-sections',
			'attributes'      => $attributes,
			'uses_context'    => array( 'postId', 'postType' ),
		)
	);

	register_block_type(
		'ugm/gallery-template-preview',
		array(
			'api_version'     => 2,
			'render_callback' => 'ugm_render_block_gallery_template_preview',
			'attributes'      => $attributes,
			'uses_context'    => array( 'postId', 'postType' ),
		)
	);
}
add_action( 'init', 'ugm_register_gallery_page_blocks' );

add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'ugm_gallery_item';
	return $vars;
} );

/**
 * Find the Gallery Page block attributes inside nested page content.
 *
 * @param array[] $blocks Parsed blocks.
 * @return array
 */
function ugm_find_gallery_page_block_attrs( $blocks ) {
	foreach ( is_array( $blocks ) ? $blocks : array() as $block ) {
		if ( isset( $block['blockName'] ) && 'ugm/gallery-page' === $block['blockName'] ) {
			return isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
		}

		$inner_attrs = ugm_find_gallery_page_block_attrs( $block['innerBlocks'] ?? array() );
		if ( ! empty( $inner_attrs ) ) {
			return $inner_attrs;
		}
	}

	return array();
}

/**
 * Use the opened gallery-card title in the browser tab.
 *
 * @param string $title Existing document title.
 * @return string
 */
function ugm_gallery_detail_document_title( $title ) {
	$detail_item = absint( get_query_var( 'ugm_gallery_item' ) );
	if ( $detail_item <= 0 || ! is_page() ) {
		return $title;
	}

	$attrs = ugm_find_gallery_page_block_attrs( parse_blocks( (string) get_post_field( 'post_content', get_queried_object_id() ) ) );
	$items = isset( $attrs['galleryItems'] ) && is_array( $attrs['galleryItems'] ) ? $attrs['galleryItems'] : array();
	$item  = $items[ $detail_item - 1 ] ?? array();
	$name  = trim( (string) ( $item['title'] ?? '' ) );

	if ( '' === $name ) {
		$name = __( 'Galeri', 'ugm-faculty' );
	}

	return $name . ' - ' . __( 'Galeri Page', 'ugm-faculty' ) . ' - ' . get_bloginfo( 'name' );
}
add_filter( 'pre_get_document_title', 'ugm_gallery_detail_document_title' );
