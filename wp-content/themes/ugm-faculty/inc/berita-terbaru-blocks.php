<?php
/**
 * Custom Server-Side-Rendered blocks untuk template Berita Terbaru.
 *
 * Setiap blok merender konten nyata (berita, sidebar, dsb.) baik
 * di editor Gutenberg (via ServerSideRender/REST) maupun – jika
 * diperlukan – di frontend.
 *
 * PHP template (berita-terbaru.php) membaca blok mana yang hadir
 * di post_content untuk menentukan layout yang ditampilkan.
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gutenberg can request dynamic block previews with JSON-encoded attributes in
 * the query string. Normalize them before REST validates the block-renderer route.
 */
add_filter(
	'rest_pre_dispatch',
	static function ( $result, $server, $request ) {
		if ( 0 !== strpos( $request->get_route(), '/wp/v2/block-renderer/' ) ) {
			return $result;
		}

		$attributes = $request->get_param( 'attributes' );
		if ( ! is_string( $attributes ) ) {
			return $result;
		}

		$decoded = json_decode( $attributes, true );
		if ( is_array( $decoded ) ) {
			$request->set_param( 'attributes', $decoded );
		}

		return $result;
	},
	0,
	3
);

/* ==========================================================================
 * Kategori blok "UGM — Berita Terbaru"
 * ========================================================================== */

add_filter(
	'block_categories_all',
	static function ( $categories ) {
		foreach ( $categories as $cat ) {
			if ( 'ugm-berita-terbaru' === $cat['slug'] ) {
				return $categories;
			}
		}
		$new = array(
			'slug'  => 'ugm-berita-terbaru',
			'title' => __( 'UGM — Berita Terbaru', 'ugm-faculty' ),
			'icon'  => 'admin-post',
		);
		$pos = false;
		foreach ( $categories as $i => $cat ) {
			if ( 'ugm-sections' === $cat['slug'] ) { $pos = $i + 1; break; }
		}
		false !== $pos
			? array_splice( $categories, $pos, 0, array( $new ) )
			: array_unshift( $categories, $new );
		return $categories;
	},
	11
);

/* ==========================================================================
 * Helper: enqueue CSS di block editor (REST preview)
 * ========================================================================== */

function ugmbt_maybe_enqueue_styles(): void {
	static $done = false;
	if ( $done ) return;
	$done = true;
	if ( ! function_exists( 'wp_enqueue_style' ) ) return;
	wp_enqueue_style( 'ugm-style' );
	wp_enqueue_style(
		'ugm-berita-terbaru',
		get_template_directory_uri() . '/assets/css/berita-terbaru.css',
		array( 'ugm-style' ),
		filemtime( get_template_directory() . '/assets/css/berita-terbaru.css' ) ?: '1.0'
	);
}

/* ==========================================================================
 * Helper: editor preview layout wrapper
 * ========================================================================== */

function ugmbt_is_editor_request(): bool {
	return is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST );
}

function ugmbt_wrap_editor_layout( string $content, string $area ): string {
	// Simplified: just return content — no layout wrapper needed.
	// (Layout 2-kolom ditangani oleh PHP template, bukan di editor.)
	return $content;
}

/* ==========================================================================
 * Helper: ambil data post & render thumbnail
 * ========================================================================== */

/**
 * Ambil kicker (nama kategori non-default) dari global post.
 */
function ugmbt_get_kicker(): string {
	$cats       = get_the_category();
	$default_id = (int) get_option( 'default_category' );
	foreach ( $cats as $c ) {
		if ( $c->term_id !== $default_id && 'uncategorized' !== $c->slug ) {
			return $c->name;
		}
	}
	return '';
}

/**
 * Render thumbnail atau placeholder div.
 *
 * @param string $size Ukuran thumbnail WordPress.
 * @param string $class CSS class tambahan untuk wrapper.
 */
function ugmbt_the_thumb( string $size = 'large', string $class = '' ): void {
	if ( has_post_thumbnail() ) {
		printf(
			'<img src="%s" alt="%s" loading="lazy"%s>',
			esc_url( (string) get_the_post_thumbnail_url( null, $size ) ),
			esc_attr( (string) get_the_title() ),
			$class ? ' class="' . esc_attr( $class ) . '"' : ''
		);
	} else {
		// Fallback: coba ambil gambar pertama dari konten post.
		$content = (string) get_the_content();
		if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/', $content, $match ) ) {
			printf(
				'<img src="%s" alt="%s" loading="lazy"%s>',
				esc_url( $match[1] ),
				esc_attr( (string) get_the_title() ),
				$class ? ' class="' . esc_attr( $class ) . '"' : ''
			);
		} else {
			echo '<div class="ugmbt-placeholder" aria-hidden="true"></div>';
		}
	}
}

/**
 * Ambil URL gambar untuk post (thumbnail atau gambar pertama di konten).
 *
 * @param int|null $post_id Post ID (null = current post).
 * @param string   $size    Ukuran thumbnail.
 * @return string URL gambar atau string kosong.
 */
function ugmbt_get_image_url( $post_id = null, string $size = 'large' ): string {
	$url = (string) ( get_the_post_thumbnail_url( $post_id, $size ) ?: '' );
	if ( '' !== $url ) return $url;

	// Fallback: gambar pertama dari konten.
	$post    = get_post( $post_id );
	$content = $post instanceof WP_Post ? (string) $post->post_content : '';
	if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/', $content, $match ) ) {
		return $match[1];
	}
	return '';
}

/**
 * Buat WP_Query berdasarkan categorySlug attribute.
 *
 * @param string $cat_slug Slug kategori (boleh kosong = semua).
 * @param int    $count    Jumlah post.
 * @param array  $extra    Argumen tambahan untuk WP_Query.
 */
function ugmbt_make_query( string $cat_slug, int $count, array $extra = array() ): WP_Query {
	$args = array_merge(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => max( 1, $count ),
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'no_found_rows'       => true,
		),
		$extra
	);

	if ( '' !== $cat_slug ) {
		$term = get_category_by_slug( $cat_slug );
		if ( $term instanceof WP_Term ) {
			$args['cat'] = (int) $term->term_id;
		} else {
			$args['post__in'] = array( 0 ); // paksa kosong
		}
	}

	if ( function_exists( 'ugm_apply_non_agenda_date_query' ) ) {
		$args = ugm_apply_non_agenda_date_query( $args );
	}

	return new WP_Query( $args );
}

/* ==========================================================================
 * 1. ugm/bt-news-section  —  Kumpulan Berita (Section)
 *
 * Satu blok merender N "group", tiap group terdiri dari:
 *   - Baris atas  : 1 artikel besar (hero, kiri) + 1 artikel kecil (kanan)
 *   - Baris bawah : N kartu grid
 *
 * Attribute:
 *   sectionsCount  integer  Jumlah group (1-8), default 4
 *   postsPerRow    integer  Jumlah kartu di baris bawah (2-4), default 3
 *   categorySlug   string   Filter kategori (kosong = semua)
 * ========================================================================== */

/**
 * Helper: render satu kartu hero.
 */
function ugmbt_render_hero_card( array $p ): void {
	?>
	<article class="ugmbt-hero" id="post-<?php echo esc_attr( (string) $p['id'] ); ?>">
		<a href="<?php echo esc_url( $p['permalink'] ); ?>"
		   class="ugmbt-card__img-wrap ugmbt-card__img-wrap--hero"
		   aria-label="<?php echo esc_attr( $p['title'] ); ?>">
			<?php if ( $p['thumb'] ) : ?>
			<img src="<?php echo esc_url( $p['thumb'] ); ?>" alt="<?php echo esc_attr( $p['title'] ); ?>" loading="lazy">
			<?php else : ?>
			<div class="ugmbt-placeholder" aria-hidden="true"></div>
			<?php endif; ?>
		</a>
		<h2 class="ugmbt-card__title ugmbt-card__title--hero">
			<a href="<?php echo esc_url( $p['permalink'] ); ?>"><?php echo esc_html( $p['title'] ); ?></a>
		</h2>
		<div class="ugmbt-card__meta-row">
			<?php if ( $p['kicker'] ) : ?>
			<span class="ugmbt-card__kicker"><?php echo esc_html( $p['kicker'] ); ?></span>
			<span class="ugmbt-card__sep" aria-hidden="true">&middot;</span>
			<?php endif; ?>
			<span class="ugmbt-card__date"><?php echo esc_html( $p['date_full'] ); ?></span>
		</div>
		<p class="ugmbt-card__excerpt"><?php echo esc_html( $p['excerpt'] ); ?></p>
	</article>
	<?php
}

/**
 * Helper: render satu kartu secondary (kanan atas).
 */
function ugmbt_render_sec_card( array $p ): void {
	?>
	<article class="ugmbt-secondary" id="post-<?php echo esc_attr( (string) $p['id'] ); ?>">
		<a href="<?php echo esc_url( $p['permalink'] ); ?>"
		   class="ugmbt-card__img-wrap ugmbt-card__img-wrap--sec"
		   aria-label="<?php echo esc_attr( $p['title'] ); ?>">
			<?php if ( $p['thumb'] ) : ?>
			<img src="<?php echo esc_url( $p['thumb'] ); ?>" alt="<?php echo esc_attr( $p['title'] ); ?>" loading="lazy">
			<?php else : ?>
			<div class="ugmbt-placeholder" aria-hidden="true"></div>
			<?php endif; ?>
		</a>
		<h2 class="ugmbt-card__title">
			<a href="<?php echo esc_url( $p['permalink'] ); ?>"><?php echo esc_html( $p['title'] ); ?></a>
		</h2>
		<div class="ugmbt-card__meta-row">
			<?php if ( $p['kicker'] ) : ?>
			<span class="ugmbt-card__kicker"><?php echo esc_html( $p['kicker'] ); ?></span>
			<span class="ugmbt-card__sep" aria-hidden="true">&middot;</span>
			<?php endif; ?>
			<span class="ugmbt-card__date"><?php echo esc_html( $p['date_full'] ); ?></span>
		</div>
		<p class="ugmbt-card__excerpt"><?php echo esc_html( wp_trim_words( $p['excerpt'], 22 ) ); ?></p>
	</article>
	<?php
}

/**
 * Helper: render satu kartu grid.
 */
function ugmbt_render_grid_card( array $p ): void {
	?>
	<article class="ugmbt-grid-card" id="post-<?php echo esc_attr( (string) $p['id'] ); ?>">
		<a href="<?php echo esc_url( $p['permalink'] ); ?>" class="ugmbt-card__img-wrap ugmbt-card__img-wrap--grid"
		   aria-label="<?php echo esc_attr( $p['title'] ); ?>">
			<?php if ( $p['thumb'] ) : ?>
			<img src="<?php echo esc_url( $p['thumb'] ); ?>" alt="<?php echo esc_attr( $p['title'] ); ?>" loading="lazy">
			<?php else : ?>
			<div class="ugmbt-placeholder" aria-hidden="true"></div>
			<?php endif; ?>
		</a>
		<h2 class="ugmbt-card__title">
			<a href="<?php echo esc_url( $p['permalink'] ); ?>"><?php echo esc_html( $p['title'] ); ?></a>
		</h2>
		<div class="ugmbt-card__meta-row">
			<?php if ( $p['kicker'] ) : ?>
			<span class="ugmbt-card__kicker"><?php echo esc_html( $p['kicker'] ); ?></span>
			<span class="ugmbt-card__sep" aria-hidden="true">&middot;</span>
			<?php endif; ?>
			<span class="ugmbt-card__date"><?php echo esc_html( $p['date_full'] ); ?></span>
		</div>
		<p class="ugmbt-card__excerpt"><?php echo esc_html( $p['excerpt'] ); ?></p>
	</article>
	<?php
}

/**
 * Helper: ambil data array untuk satu post (global post harus sudah di-setup).
 */
function ugmbt_collect_post_data(): array {
	$cats       = get_the_category();
	$default_id = (int) get_option( 'default_category' );
	$kicker     = '';
	foreach ( $cats as $c ) {
		if ( $c->term_id !== $default_id && 'uncategorized' !== $c->slug ) {
			$kicker = $c->name;
			break;
		}
	}
	return array(
		'id'        => (int) get_the_ID(),
		'title'     => (string) get_the_title(),
		'permalink' => (string) get_permalink(),
		'date_full' => (string) get_the_date( 'l, j F Y' ),
		'kicker'    => $kicker,
		'excerpt'   => (string) wp_trim_words( (string) get_the_excerpt(), 18 ),
		'thumb'     => ugmbt_get_image_url( null, 'large' ),
	);
}

function ugmbt_render_block_news_section( array $attrs ): string {
	ugmbt_maybe_enqueue_styles();

	$sections_count  = max( 1, min( 8, (int) ( $attrs['sectionsCount'] ?? 4 ) ) );
	$posts_per_row   = max( 2, min( 4, (int) ( $attrs['postsPerRow']   ?? 3 ) ) );
	$cat_slug        = sanitize_key( $attrs['categorySlug'] ?? '' );
	$per_section     = 2 + $posts_per_row;  // hero + sec + kartu grid
	$total_posts     = $sections_count * $per_section;

	$q = ugmbt_make_query( $cat_slug, $total_posts );

	if ( ! $q->have_posts() ) {
		return '<div class="ugmbt-empty-block"><p>' .
			esc_html__( 'Belum ada berita untuk ditampilkan.', 'ugm-faculty' ) . '</p></div>';
	}

	// Kumpulkan semua post ke array.
	$all_posts = array();
	while ( $q->have_posts() ) {
		$q->the_post();
		$all_posts[] = ugmbt_collect_post_data();
	}
	wp_reset_postdata();

	ob_start();
	for ( $i = 0; $i < $sections_count; $i++ ) {
		$offset     = $i * $per_section;
		$hero       = $all_posts[ $offset ]     ?? null;
		$sec        = $all_posts[ $offset + 1 ] ?? null;
		$grid_posts = array_slice( $all_posts, $offset + 2, $posts_per_row );

		if ( ! $hero && empty( $grid_posts ) ) break;

		if ( $i > 0 ) {
			echo '<hr class="ugmbt-section-divider" aria-hidden="true">';
		}
		?>
		<div class="ugmbt-section-group">
			<?php if ( $hero || $sec ) : ?>
			<div class="ugmbt-row1">
				<?php
				if ( $hero ) ugmbt_render_hero_card( $hero );
				if ( $sec )  ugmbt_render_sec_card( $sec );
				?>
			</div><!-- /ugmbt-row1 -->
			<?php endif; ?>

			<?php if ( ! empty( $grid_posts ) ) : ?>
			<hr class="ugmbt-divider" aria-hidden="true">
			<div class="ugmbt-row2 ugmbt-row2--<?php echo esc_attr( (string) count( $grid_posts ) ); ?>col">
				<?php foreach ( $grid_posts as $gp ) ugmbt_render_grid_card( $gp ); ?>
			</div><!-- /ugmbt-row2 -->
			<?php endif; ?>
		</div><!-- /ugmbt-section-group -->
		<?php
	}

	return (string) ob_get_clean();
}

register_block_type( 'ugm/bt-news-section', array(
	'title'           => __( 'Kumpulan Berita (Section)', 'ugm-faculty' ),
	'description'     => __( 'N group berita: tiap group = 1 besar + 1 kecil (atas) + N kartu grid (bawah).', 'ugm-faculty' ),
	'category'        => 'ugm-berita-terbaru',
	'icon'            => 'layout',
	'render_callback' => 'ugmbt_render_block_news_section',
	'supports'        => array( 'html' => false, 'multiple' => false ),
	'attributes'      => array(
		'sectionsCount' => array( 'type' => 'integer', 'default' => 4 ),
		'postsPerRow'   => array( 'type' => 'integer', 'default' => 3 ),
		'categorySlug'  => array( 'type' => 'string',  'default' => '' ),
	),
) );

/**
 * Back-compat: blok lama yang pernah dipakai halaman Berita.
 */
function ugmbt_render_block_featured_row( array $attrs ): string {
	ugmbt_maybe_enqueue_styles();

	$cat_slug = sanitize_key( $attrs['categorySlug'] ?? '' );
	$q        = ugmbt_make_query( $cat_slug, 2 );

	if ( ! $q->have_posts() ) {
		return '<div class="ugmbt-empty-block"><p>' .
			esc_html__( 'Belum ada berita untuk ditampilkan.', 'ugm-faculty' ) . '</p></div>';
	}

	$posts = array();
	while ( $q->have_posts() ) {
		$q->the_post();
		$posts[] = ugmbt_collect_post_data();
	}
	wp_reset_postdata();

	ob_start();
	?>
	<div class="ugmbt-row1">
		<?php
		if ( isset( $posts[0] ) ) {
			ugmbt_render_hero_card( $posts[0] );
		}
		if ( isset( $posts[1] ) ) {
			ugmbt_render_sec_card( $posts[1] );
		}
		?>
	</div>
	<?php
	return (string) ob_get_clean();
}

function ugmbt_render_block_news_grid( array $attrs ): string {
	ugmbt_maybe_enqueue_styles();

	$count    = max( 3, min( 12, (int) ( $attrs['postsCount'] ?? 3 ) ) );
	$cat_slug = sanitize_key( $attrs['categorySlug'] ?? '' );
	$offset   = max( 0, (int) ( $attrs['offset'] ?? 2 ) );
	$q        = ugmbt_make_query( $cat_slug, $count, array( 'offset' => $offset ) );

	if ( ! $q->have_posts() ) {
		return '<div class="ugmbt-empty-block"><p>' .
			esc_html__( 'Belum ada berita untuk ditampilkan.', 'ugm-faculty' ) . '</p></div>';
	}

	$posts = array();
	while ( $q->have_posts() ) {
		$q->the_post();
		$posts[] = ugmbt_collect_post_data();
	}
	wp_reset_postdata();

	ob_start();
	?>
	<div class="ugmbt-row2 ugmbt-row2--<?php echo esc_attr( (string) min( 4, count( $posts ) ) ); ?>col">
		<?php foreach ( $posts as $post_data ) : ?>
			<?php ugmbt_render_grid_card( $post_data ); ?>
		<?php endforeach; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

register_block_type( 'ugm/bt-featured-row', array(
	'title'           => __( 'Baris Utama Berita', 'ugm-faculty' ),
	'description'     => __( 'Baris utama berita: 1 besar kiri dan 1 kecil kanan.', 'ugm-faculty' ),
	'category'        => 'ugm-berita-terbaru',
	'icon'            => 'align-wide',
	'render_callback' => 'ugmbt_render_block_featured_row',
	'supports'        => array( 'html' => false, 'multiple' => false ),
	'attributes'      => array(
		'categorySlug' => array( 'type' => 'string', 'default' => '' ),
		'dateLocation' => array( 'type' => 'string', 'default' => '' ),
		'area'         => array( 'type' => 'string', 'default' => '' ),
	),
) );

register_block_type( 'ugm/bt-news-grid', array(
	'title'           => __( 'Grid Berita', 'ugm-faculty' ),
	'description'     => __( 'Grid berita di bawah baris utama.', 'ugm-faculty' ),
	'category'        => 'ugm-berita-terbaru',
	'icon'            => 'grid-view',
	'render_callback' => 'ugmbt_render_block_news_grid',
	'supports'        => array( 'html' => false, 'multiple' => false ),
	'attributes'      => array(
		'postsCount'   => array( 'type' => 'integer', 'default' => 3 ),
		'categorySlug' => array( 'type' => 'string',  'default' => '' ),
		'offset'       => array( 'type' => 'integer', 'default' => 2 ),
		'area'         => array( 'type' => 'string',  'default' => '' ),
	),
) );

/* ==========================================================================
 * 4. ugm/bt-sidebar-promo  —  Sidebar: Tombol update + poster
 * ========================================================================== */

function ugmbt_render_block_sidebar_promo( array $attrs ): string {
	ugmbt_maybe_enqueue_styles();

	$button_text = isset( $attrs['buttonText'] ) && '' !== $attrs['buttonText']
		? sanitize_text_field( $attrs['buttonText'] )
		: __( 'UGM Peduli Bencana - Update', 'ugm-faculty' );
	$button_url  = isset( $attrs['buttonUrl'] ) && '' !== $attrs['buttonUrl']
		? esc_url_raw( $attrs['buttonUrl'] )
		: '/peduli-bencana/';
	$button_href = preg_match( '#^https?://#i', $button_url ) ? $button_url : home_url( $button_url );

	$posters = array(
		array(
			'url'  => isset( $attrs['posterOneUrl'] ) ? esc_url_raw( $attrs['posterOneUrl'] ) : '',
			'alt'  => isset( $attrs['posterOneAlt'] ) && '' !== $attrs['posterOneAlt'] ? sanitize_text_field( $attrs['posterOneAlt'] ) : __( 'Poster informasi kebencanaan', 'ugm-faculty' ),
			'link' => isset( $attrs['posterOneLink'] ) ? esc_url_raw( $attrs['posterOneLink'] ) : '',
		),
		array(
			'url'  => isset( $attrs['posterTwoUrl'] ) ? esc_url_raw( $attrs['posterTwoUrl'] ) : '',
			'alt'  => isset( $attrs['posterTwoAlt'] ) && '' !== $attrs['posterTwoAlt'] ? sanitize_text_field( $attrs['posterTwoAlt'] ) : __( 'Poster UGM Peduli Bencana', 'ugm-faculty' ),
			'link' => isset( $attrs['posterTwoLink'] ) ? esc_url_raw( $attrs['posterTwoLink'] ) : '',
		),
	);

	ob_start();
	?>
	<section class="ugmbt-promo" aria-label="<?php esc_attr_e( 'Informasi UGM Peduli Bencana', 'ugm-faculty' ); ?>">
		<?php if ( '' !== $button_text ) : ?>
		<a class="ugmbt-promo__button" href="<?php echo esc_url( $button_href ); ?>">
			<?php echo esc_html( $button_text ); ?>
		</a>
		<?php endif; ?>

		<?php foreach ( $posters as $poster ) : ?>
			<?php if ( '' === $poster['url'] ) : ?>
			<div class="ugmbt-promo__placeholder">
				<?php esc_html_e( 'Pilih gambar poster di pengaturan block.', 'ugm-faculty' ); ?>
			</div>
				<?php continue; ?>
			<?php endif; ?>
			<?php if ( '' !== $poster['link'] ) : ?>
			<a class="ugmbt-promo__poster" href="<?php echo esc_url( $poster['link'] ); ?>">
				<img src="<?php echo esc_url( $poster['url'] ); ?>" alt="<?php echo esc_attr( $poster['alt'] ); ?>" loading="lazy">
			</a>
			<?php else : ?>
			<div class="ugmbt-promo__poster">
				<img src="<?php echo esc_url( $poster['url'] ); ?>" alt="<?php echo esc_attr( $poster['alt'] ); ?>" loading="lazy">
			</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</section>
	<?php
	return ugmbt_wrap_editor_layout( (string) ob_get_clean(), 'sidebar' );
}

register_block_type( 'ugm/bt-sidebar-promo', array(
	'title'           => __( 'Sidebar: Poster & Update', 'ugm-faculty' ),
	'description'     => __( 'Tombol update UGM Peduli Bencana dan poster informasi di sidebar.', 'ugm-faculty' ),
	'category'        => 'ugm-berita-terbaru',
	'icon'            => 'format-image',
	'render_callback' => 'ugmbt_render_block_sidebar_promo',
	'supports'        => array( 'html' => false, 'multiple' => false ),
	'attributes'      => array(
		'buttonText'    => array( 'type' => 'string',  'default' => 'UGM Peduli Bencana - Update' ),
		'buttonUrl'     => array( 'type' => 'string',  'default' => '/peduli-bencana/' ),
		'posterOneId'   => array( 'type' => 'integer', 'default' => 0 ),
		'posterOneUrl'  => array( 'type' => 'string',  'default' => '' ),
		'posterOneAlt'  => array( 'type' => 'string',  'default' => '' ),
		'posterOneLink' => array( 'type' => 'string',  'default' => '' ),
		'posterTwoId'   => array( 'type' => 'integer', 'default' => 0 ),
		'posterTwoUrl'  => array( 'type' => 'string',  'default' => '' ),
		'posterTwoAlt'  => array( 'type' => 'string',  'default' => '' ),
		'posterTwoLink' => array( 'type' => 'string',  'default' => '' ),
	),
) );

/* ==========================================================================
 * 4. ugm/bt-sidebar-news  —  Sidebar: Daftar Berita Terbaru
 * ========================================================================== */

function ugmbt_render_block_sidebar_news( array $attrs ): string {
	ugmbt_maybe_enqueue_styles();

	$title = ( isset( $attrs['widgetTitle'] ) && '' !== $attrs['widgetTitle'] )
		? sanitize_text_field( $attrs['widgetTitle'] )
		: __( 'Berita Terbaru', 'ugm-faculty' );
	$count = max( 1, (int) ( $attrs['postsCount'] ?? 5 ) );

	$query_args = array(
		'post_type'           => 'post',
		'posts_per_page'      => $count,
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'no_found_rows'       => true,
	);
	if ( function_exists( 'ugm_apply_non_agenda_date_query' ) ) {
		$query_args = ugm_apply_non_agenda_date_query( $query_args );
	}
	$q = new WP_Query( $query_args );

	ob_start();
	?>
	<section class="ugmbt-widget">
		<h2 class="ugmbt-widget__title"><?php echo esc_html( $title ); ?></h2>
		<?php if ( $q->have_posts() ) : ?>
		<ul class="ugmbt-news-list" role="list">
			<?php while ( $q->have_posts() ) : $q->the_post(); ?>
			<li>
				<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			</li>
			<?php endwhile; wp_reset_postdata(); ?>
		</ul>
		<?php else : ?>
		<p class="ugmbt-empty"><?php esc_html_e( 'Belum ada berita.', 'ugm-faculty' ); ?></p>
		<?php endif; ?>
	</section>
	<?php
	return ugmbt_wrap_editor_layout( (string) ob_get_clean(), 'sidebar' );
}

register_block_type( 'ugm/bt-sidebar-news', array(
	'title'           => __( 'Sidebar: Berita Terbaru', 'ugm-faculty' ),
	'description'     => __( 'Widget sidebar berisi daftar judul berita terbaru.', 'ugm-faculty' ),
	'category'        => 'ugm-berita-terbaru',
	'icon'            => 'list-view',
	'render_callback' => 'ugmbt_render_block_sidebar_news',
	'supports'        => array( 'html' => false, 'multiple' => false ),
	'attributes'      => array(
		'widgetTitle' => array( 'type' => 'string',  'default' => '' ),
		'postsCount'  => array( 'type' => 'integer', 'default' => 5 ),
	),
) );

/* ==========================================================================
 * 5. ugm/bt-sidebar-agenda  —  Sidebar: Agenda Terbaru
 * ========================================================================== */

function ugmbt_render_block_sidebar_agenda( array $attrs ): string {
	ugmbt_maybe_enqueue_styles();

	$title      = ( isset( $attrs['widgetTitle'] ) && '' !== $attrs['widgetTitle'] )
		? sanitize_text_field( $attrs['widgetTitle'] )
		: __( 'Agenda Terbaru', 'ugm-faculty' );
	$count      = max( 1, (int) ( $attrs['postsCount'] ?? 3 ) );
	$agenda_url = ( isset( $attrs['agendaUrl'] ) && '' !== $attrs['agendaUrl'] )
		? sanitize_text_field( $attrs['agendaUrl'] ) : '/agenda/';

	$query_args = array(
		'post_type'           => 'post',
		'posts_per_page'      => $count,
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'meta_key'            => 'agenda_event_date',
		'orderby'             => array(
			'meta_value' => 'ASC',
			'date'       => 'DESC',
		),
		'order'               => 'ASC',
		'no_found_rows'       => true,
	);
	if ( function_exists( 'ugm_apply_agenda_date_query' ) ) {
		$query_args = ugm_apply_agenda_date_query( $query_args );
	}
	$q = new WP_Query( $query_args );

	ob_start();
	?>
	<section class="ugmbt-widget">
		<h2 class="ugmbt-widget__title"><?php echo esc_html( $title ); ?></h2>
		<?php if ( $q->have_posts() ) : ?>
		<div class="ugmbt-agenda-list">
			<?php while ( $q->have_posts() ) : $q->the_post(); ?>
			<?php
			$agenda_timestamp = function_exists( 'ugm_get_agenda_event_timestamp' )
				? ugm_get_agenda_event_timestamp( get_the_ID() )
				: (int) get_post_timestamp( get_the_ID() );
			?>
			<div class="ugmbt-agenda-item">
				<div class="ugmbt-agenda-date" aria-label="<?php echo esc_attr( wp_date( 'j F Y', $agenda_timestamp ) ); ?>">
					<span class="ugmbt-agenda-date__day"><?php echo esc_html( wp_date( 'j', $agenda_timestamp ) ); ?></span>
					<span class="ugmbt-agenda-date__mon"><?php echo esc_html( wp_date( 'M', $agenda_timestamp ) ); ?></span>
				</div>
				<div class="ugmbt-agenda-body">
					<a class="ugmbt-agenda-title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</div>
			</div>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
		<a class="ugmbt-btn-agenda" href="<?php echo esc_url( home_url( $agenda_url ) ); ?>">
			<?php esc_html_e( 'Semua Agenda', 'ugm-faculty' ); ?> &rarr;
		</a>
		<?php else : ?>
		<p class="ugmbt-empty"><?php esc_html_e( 'Belum ada agenda.', 'ugm-faculty' ); ?></p>
		<?php endif; ?>
	</section>
	<?php
	return ugmbt_wrap_editor_layout( (string) ob_get_clean(), 'sidebar' );
}

register_block_type( 'ugm/bt-sidebar-agenda', array(
	'title'           => __( 'Sidebar: Agenda Terbaru', 'ugm-faculty' ),
	'description'     => __( 'Widget agenda dengan kotak tanggal navy dan tombol "Semua Agenda".', 'ugm-faculty' ),
	'category'        => 'ugm-berita-terbaru',
	'icon'            => 'calendar-alt',
	'render_callback' => 'ugmbt_render_block_sidebar_agenda',
	'supports'        => array( 'html' => false, 'multiple' => false ),
	'attributes'      => array(
		'widgetTitle' => array( 'type' => 'string',  'default' => '' ),
		'postsCount'  => array( 'type' => 'integer', 'default' => 3 ),
		'agendaUrl'   => array( 'type' => 'string',  'default' => '/agenda/' ),
	),
) );

/* ==========================================================================
 * 6. ugm/bt-sidebar-categories  —  Sidebar: Kategori
 * ========================================================================== */

function ugmbt_render_block_sidebar_categories( array $attrs ): string {
	ugmbt_maybe_enqueue_styles();

	$title = ( isset( $attrs['widgetTitle'] ) && '' !== $attrs['widgetTitle'] )
		? sanitize_text_field( $attrs['widgetTitle'] )
		: __( 'Kategori', 'ugm-faculty' );

	$cats = get_categories( array(
		'orderby'    => 'name',
		'order'      => 'ASC',
		'hide_empty' => true,
	) );

	ob_start();
	?>
	<section class="ugmbt-widget">
		<h2 class="ugmbt-widget__title"><?php echo esc_html( $title ); ?></h2>
		<?php if ( ! empty( $cats ) ) : ?>
		<ul class="ugmbt-cat-list" role="list">
			<?php foreach ( $cats as $cat ) : ?>
			<li>
				<a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>">
					<?php echo esc_html( $cat->name ); ?>
				</a>
				<span><?php echo esc_html( $cat->count . ' ' . __( 'Artikel Total', 'ugm-faculty' ) ); ?></span>
			</li>
			<?php endforeach; ?>
		</ul>
		<?php else : ?>
		<p class="ugmbt-empty"><?php esc_html_e( 'Belum ada kategori.', 'ugm-faculty' ); ?></p>
		<?php endif; ?>
	</section>
	<?php
	return ugmbt_wrap_editor_layout( (string) ob_get_clean(), 'sidebar' );
}

register_block_type( 'ugm/bt-sidebar-categories', array(
	'title'           => __( 'Sidebar: Kategori', 'ugm-faculty' ),
	'description'     => __( 'Widget daftar kategori berita beserta jumlah artikelnya.', 'ugm-faculty' ),
	'category'        => 'ugm-berita-terbaru',
	'icon'            => 'category',
	'render_callback' => 'ugmbt_render_block_sidebar_categories',
	'supports'        => array( 'html' => false, 'multiple' => false ),
	'attributes'      => array(
		'widgetTitle' => array( 'type' => 'string', 'default' => '' ),
	),
) );

/* ==========================================================================
 * Block Pattern: Halaman Berita Terbaru Lengkap
 * ========================================================================== */

add_action( 'init', static function () {
	if ( ! function_exists( 'register_block_pattern_category' ) || ! function_exists( 'register_block_pattern' ) ) {
		return;
	}

	register_block_pattern_category( 'ugm-berita-terbaru', array(
		'label' => __( 'UGM Berita Terbaru', 'ugm-faculty' ),
	) );

	$pattern_content =
		'<!-- wp:group {"className":"ugmbt-editor-shell ugmbt-page","layout":{"type":"default"}} -->' . "\n" .
		'<div class="wp-block-group ugmbt-editor-shell ugmbt-page">' . "\n" .
		'<!-- wp:group {"className":"ugmbt-editor-main","layout":{"type":"default"}} -->' . "\n" .
		'<div class="wp-block-group ugmbt-editor-main">' . "\n" .
		'<!-- wp:ugm/bt-news-section {"sectionsCount":4,"postsPerRow":3,"categorySlug":""} /-->' . "\n" .
		'</div>' . "\n" .
		'<!-- /wp:group -->' . "\n\n" .
		'<!-- wp:group {"className":"ugmbt-editor-sidebar","layout":{"type":"default"}} -->' . "\n" .
		'<div class="wp-block-group ugmbt-editor-sidebar">' . "\n" .
		'<!-- wp:ugm/bt-sidebar-promo {"buttonText":"UGM Peduli Bencana - Update","buttonUrl":"/peduli-bencana/","posterOneId":0,"posterOneUrl":"","posterOneAlt":"","posterOneLink":"","posterTwoId":0,"posterTwoUrl":"","posterTwoAlt":"","posterTwoLink":""} /-->' . "\n" .
		'<!-- wp:ugm/bt-sidebar-news {"widgetTitle":"Berita Terbaru","postsCount":5} /-->' . "\n" .
		'<!-- wp:ugm/bt-sidebar-agenda {"widgetTitle":"Agenda Terbaru","postsCount":3,"agendaUrl":"/agenda/"} /-->' . "\n" .
		'<!-- wp:ugm/bt-sidebar-categories {"widgetTitle":"Kategori"} /-->' . "\n" .
		'</div>' . "\n" .
		'<!-- /wp:group -->' . "\n" .
		'</div>' . "\n" .
		'<!-- /wp:group -->';

	register_block_pattern( 'ugm/berita-terbaru-complete', array(
		'title'       => __( 'Halaman Berita Terbaru Lengkap', 'ugm-faculty' ),
		'description' => __( 'Layout berita kiri dan sidebar kanan untuk template Berita Terbaru.', 'ugm-faculty' ),
		'categories'  => array( 'ugm-berita-terbaru' ),
		'content'     => $pattern_content,
	) );
} );

/* ==========================================================================
 * Helper: baca config blok ugm/bt-* dari halaman
 * Mencari secara REKURSIF agar blok yang ada di dalam wp:row / wp:group
 * tetap terdeteksi.
 * Dipakai oleh page-templates/berita-terbaru.php
 * ========================================================================== */

/**
 * Flatten blok bersarang secara rekursif ke satu array datar.
 *
 * @param array $blocks Hasil parse_blocks().
 * @return array Array datar semua blok (termasuk innerBlocks).
 */
function ugmbt_flatten_blocks( array $blocks ): array {
	$flat = array();
	foreach ( $blocks as $block ) {
		$flat[] = $block;
		if ( ! empty( $block['innerBlocks'] ) ) {
			$flat = array_merge( $flat, ugmbt_flatten_blocks( $block['innerBlocks'] ) );
		}
	}
	return $flat;
}

function ugmbt_get_block_config( int $page_id ): array {
	static $cache = array();
	if ( isset( $cache[ $page_id ] ) ) return $cache[ $page_id ];

	$post = get_post( $page_id );
	if ( ! $post instanceof WP_Post ) return array();

	// Flatten semua blok termasuk yang bersarang di wp:row, wp:group, dll.
	$all_blocks = ugmbt_flatten_blocks( parse_blocks( (string) $post->post_content ) );

	$result = array();
	foreach ( $all_blocks as $block ) {
		if ( empty( $block['blockName'] ) || 0 !== strpos( $block['blockName'], 'ugm/bt-' ) ) {
			continue;
		}
		$result[ $block['blockName'] ] = is_array( $block['attrs'] ) ? $block['attrs'] : array();
	}

	$cache[ $page_id ] = $result;
	return $result;
}

/**
 * Ambil daftar blok ugm/bt-* sesuai urutan di post_content.
 * Dipakai untuk menyesuaikan urutan render di template.
 */
function ugmbt_get_block_list( int $page_id ): array {
	$post = get_post( $page_id );
	if ( ! $post instanceof WP_Post ) return array();

	$all_blocks = ugmbt_flatten_blocks( parse_blocks( (string) $post->post_content ) );
	$blocks     = array();

	foreach ( $all_blocks as $block ) {
		if ( empty( $block['blockName'] ) || 0 !== strpos( $block['blockName'], 'ugm/bt-' ) ) {
			continue;
		}
		$blocks[] = array(
			'name'  => $block['blockName'],
			'attrs' => is_array( $block['attrs'] ) ? $block['attrs'] : array(),
		);
	}

	return $blocks;
}
