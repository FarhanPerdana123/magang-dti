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
	return '<!-- wp:ugm/gallery-page {"title":"Galeri","categorySlug":"galeri","postsPerPage":12} /-->';
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

function ugm_get_gallery_block_template_content() {
	if ( ! function_exists( 'get_block_template' ) ) {
		return '';
	}

	$template = get_block_template( get_stylesheet() . '//gallery-page', 'wp_template' );
	if ( ! $template || empty( $template->content ) || ! is_string( $template->content ) ) {
		return '';
	}

	return trim( $template->content );
}

function ugm_get_gallery_render_source( $page_content = '', $template_slug = '' ) {
	$page_content  = (string) $page_content;
	$template_slug = (string) $template_slug;

	if ( 'gallery-page' === $template_slug ) {
		$template_content = ugm_get_gallery_block_template_content();
		if ( '' !== $template_content && false !== strpos( $template_content, 'ugm/gallery-page' ) ) {
			return $template_content;
		}
	}

	if ( false !== strpos( $page_content, '<!-- wp:' ) || '' !== trim( wp_strip_all_tags( strip_shortcodes( $page_content ) ) ) ) {
		return $page_content;
	}

	return ugm_get_default_gallery_page_blocks();
}

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

		if ( false !== strpos( (string) $template_post->post_content, 'ugm/gallery-page' ) ) {
			continue;
		}

		wp_update_post(
			array(
				'ID'           => $template_post->ID,
				'post_content' => ugm_get_default_gallery_page_blocks(),
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

function ugm_gallery_term_ids_from_slugs( $slug_attr ) {
	$slugs = array_filter(
		array_map(
			'trim',
			explode( ',', (string) $slug_attr )
		)
	);
	$ids   = array();

	foreach ( $slugs as $slug ) {
		$term = get_category_by_slug( sanitize_title( $slug ) );
		if ( $term instanceof WP_Term ) {
			$ids[] = (int) $term->term_id;
		}
	}

	return array_values( array_unique( array_filter( $ids ) ) );
}

function ugm_gallery_get_posts( $category_slug, $posts_per_page, $offset = 0 ) {
	$args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => max( 1, absint( $posts_per_page ) ),
		'offset'              => max( 0, absint( $offset ) ),
		'ignore_sticky_posts' => true,
	);

	$term_ids = ugm_gallery_term_ids_from_slugs( $category_slug );
	if ( ! empty( $term_ids ) ) {
		$args['category__in'] = $term_ids;
	}

	return get_posts( $args );
}

function ugm_gallery_card_image( $post_id, $size = 'medium_large' ) {
	$post_id = absint( $post_id );
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail( $post_id, $size, array( 'loading' => 'lazy', 'decoding' => 'async' ) );
	}

	return '<span class="ugm-gallery-placeholder" aria-hidden="true"></span>';
}

function ugm_gallery_card_date( $post_id ) {
	return get_the_date( 'l, j F Y', $post_id );
}

function ugm_render_block_gallery_page( $attrs ) {
	$attrs = wp_parse_args(
		is_array( $attrs ) ? $attrs : array(),
		array(
			'title'        => 'Galeri',
			'categorySlug' => 'galeri',
			'postsPerPage' => 12,
			'buttonLabel'  => 'Selengkapnya',
		)
	);

	$title          = trim( (string) $attrs['title'] );
	$category_slug  = trim( (string) $attrs['categorySlug'] );
	$posts_per_page = max( 1, absint( $attrs['postsPerPage'] ) );
	$button_label   = trim( (string) $attrs['buttonLabel'] );
	$hero_posts     = ugm_gallery_get_posts( $category_slug, 4, 0 );
	$grid_posts     = ugm_gallery_get_posts( $category_slug, $posts_per_page, 0 );
	$hero_post      = ! empty( $hero_posts ) && $hero_posts[0] instanceof WP_Post ? $hero_posts[0] : null;
	$hero_images    = array_slice( $hero_posts, 0, 4 );

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
					<?php if ( $hero_post instanceof WP_Post ) : ?>
						<time><?php echo esc_html( ugm_gallery_card_date( $hero_post->ID ) ); ?></time>
						<h2><?php echo esc_html( get_the_title( $hero_post ) ); ?></h2>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt( $hero_post ), 18, '...' ) ); ?></p>
						<a class="ugm-gallery-button" href="<?php echo esc_url( get_permalink( $hero_post ) ); ?>"><?php echo esc_html( '' !== $button_label ? $button_label : __( 'Selengkapnya', 'ugm-faculty' ) ); ?></a>
					<?php else : ?>
						<time><?php echo esc_html( date_i18n( 'l, j F Y' ) ); ?></time>
						<h2><?php esc_html_e( 'Belum ada galeri', 'ugm-faculty' ); ?></h2>
						<p><?php esc_html_e( 'Tambahkan post dengan kategori galeri dan gambar unggulan untuk mengisi halaman ini.', 'ugm-faculty' ); ?></p>
					<?php endif; ?>
					<div class="ugm-gallery-hero__dots" aria-hidden="true">
						<span></span>
						<span></span>
						<span></span>
					</div>
				</div>
				<div class="ugm-gallery-hero__media-grid">
					<?php for ( $i = 0; $i < 4; $i++ ) : ?>
						<?php $image_post = isset( $hero_images[ $i ] ) && $hero_images[ $i ] instanceof WP_Post ? $hero_images[ $i ] : null; ?>
						<figure>
							<?php echo $image_post instanceof WP_Post ? ugm_gallery_card_image( $image_post->ID, 'large' ) : '<span class="ugm-gallery-placeholder" aria-hidden="true"></span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</figure>
					<?php endfor; ?>
				</div>
			</div>
		</section>

		<section class="ugm-gallery-list" aria-labelledby="ugm-gallery-list-title">
			<header class="ugm-gallery-section-head">
				<h2 id="ugm-gallery-list-title"><?php echo esc_html( $title ); ?></h2>
				<span aria-hidden="true"></span>
			</header>
			<div class="ugm-gallery-grid">
				<?php if ( ! empty( $grid_posts ) ) : ?>
					<?php foreach ( $grid_posts as $gallery_post ) : ?>
						<article class="ugm-gallery-card">
							<a class="ugm-gallery-card__media" href="<?php echo esc_url( get_permalink( $gallery_post ) ); ?>">
								<?php echo ugm_gallery_card_image( $gallery_post->ID, 'medium_large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
							<div class="ugm-gallery-card__body">
								<h3><a href="<?php echo esc_url( get_permalink( $gallery_post ) ); ?>"><?php echo esc_html( get_the_title( $gallery_post ) ); ?></a></h3>
								<time><?php echo esc_html( strtoupper( ugm_gallery_card_date( $gallery_post->ID ) ) ); ?></time>
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

function ugm_register_gallery_page_blocks() {
	register_block_type(
		'ugm/gallery-page',
		array(
			'api_version'     => 2,
			'render_callback' => 'ugm_render_block_gallery_page',
			'category'        => 'ugm-gallery-page-sections',
			'attributes'      => array(
				'title'        => array( 'type' => 'string', 'default' => 'Galeri' ),
				'categorySlug' => array( 'type' => 'string', 'default' => 'galeri' ),
				'postsPerPage' => array( 'type' => 'number', 'default' => 12 ),
				'buttonLabel'  => array( 'type' => 'string', 'default' => 'Selengkapnya' ),
			),
		)
	);
}
add_action( 'init', 'ugm_register_gallery_page_blocks' );
