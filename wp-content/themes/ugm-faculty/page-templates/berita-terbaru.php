<?php
/**
 * Template Name: Berita Terbaru
 * Template Post Type: page
 *
 * Halaman arsip berita terbaru UGM.
 *
 * Header & footer otomatis dipanggil via get_header() / get_footer().
 * Section yang ditampilkan dikontrol oleh blok Gutenberg
 * dari kategori "UGM — Berita Terbaru" yang ditambahkan
 * ke konten halaman ini di editor.
 *
 * Blok yang tersedia (kategori: UGM — Berita Terbaru):
 *  ugm/bt-featured-row       → baris utama: 1 besar kiri + 1 kecil kanan
 *  ugm/bt-news-grid          → grid N kolom berita
 *  ugm/bt-sidebar-promo      → sidebar: tombol update + poster
 *  ugm/bt-sidebar-news       → sidebar: daftar berita
 *  ugm/bt-sidebar-agenda     → sidebar: agenda
 *  ugm/bt-sidebar-categories → sidebar: daftar kategori
 *
 * Sidebar muncul otomatis jika minimal 1 blok sidebar ditambahkan.
 * Jika tidak ada blok sama sekali, semua section ditampilkan (fallback).
 *
 * Warna navy: PANTONE P111-16C (#004266)
 *
 * @package ugm-faculty
 */

get_header();

/* ==========================================================================
 * Baca konfigurasi blok dari konten halaman
 * ========================================================================== */

$_bt_page_id = (int) get_queried_object_id();
$_bt_cfg     = function_exists( 'ugmbt_get_block_config' )
	? ugmbt_get_block_config( $_bt_page_id )
	: array();

// Tentukan section mana yang aktif. Blok lama tetap didukung agar konten lama tidak rusak.
$bt_has_news_main = isset( $_bt_cfg['ugm/bt-news-section'] );
$bt_has_featured  = $bt_has_news_main || isset( $_bt_cfg['ugm/bt-featured-row'] );
$bt_has_grid      = $bt_has_news_main || isset( $_bt_cfg['ugm/bt-news-grid'] );
$bt_has_sb_promo  = isset( $_bt_cfg['ugm/bt-sidebar-promo'] );
$bt_has_sb_news   = isset( $_bt_cfg['ugm/bt-sidebar-news'] );
$bt_has_sb_agenda = isset( $_bt_cfg['ugm/bt-sidebar-agenda'] );
$bt_has_sb_cats   = isset( $_bt_cfg['ugm/bt-sidebar-categories'] );

// Fallback: jika tidak ada blok sama sekali, tampilkan semua section.
$bt_no_blocks = empty( $_bt_cfg );
if ( $bt_no_blocks ) {
	$bt_has_featured  = true;
	$bt_has_grid      = true;
	$bt_has_sb_promo  = true;
	$bt_has_sb_news   = true;
	$bt_has_sb_agenda = true;
	$bt_has_sb_cats   = true;
}

// Jika halaman hanya berisi blok sidebar, konten utama berita tetap tampil.
if ( ! $bt_has_featured && ! $bt_has_grid ) {
	$bt_has_featured = true;
	$bt_has_grid     = true;
}

// Sidebar aktif jika minimal satu widget sidebar hadir.
$bt_has_sidebar = $bt_has_sb_promo || $bt_has_sb_news || $bt_has_sb_agenda || $bt_has_sb_cats;

/* ==========================================================================
 * Ambil attribute per blok
 * ========================================================================== */

/**
 * Helper: baca nilai attribute blok, fallback ke $default.
 */
$_bt_attr = static function ( $block, $key, $default ) use ( $_bt_cfg ) {
	$val = $_bt_cfg[ $block ][ $key ] ?? null;
	return ( null !== $val && '' !== $val ) ? $val : $default;
};

// Judul halaman.
$page_title = get_theme_mod( 'ugm_latest_section_title', __( 'Berita Terbaru', 'ugm-faculty' ) );

// Attribute blok konten utama.
$bt_featured_cat = $bt_has_news_main
	? (string) $_bt_attr( 'ugm/bt-news-section', 'categorySlug', '' )
	: (string) $_bt_attr( 'ugm/bt-featured-row', 'categorySlug', '' );

// Grid.
$bt_sections_count = $bt_has_news_main
	? max( 1, min( 8, (int) $_bt_attr( 'ugm/bt-news-section', 'sectionsCount', 4 ) ) )
	: 4;
$bt_grid_count = $bt_has_news_main
	? max( 2, min( 4, (int) $_bt_attr( 'ugm/bt-news-section', 'postsPerRow', 3 ) ) )
	: max( 2, min( 4, (int) $_bt_attr( 'ugm/bt-news-grid', 'postsCount', 3 ) ) );
$bt_grid_cat = $bt_has_news_main
	? (string) $_bt_attr( 'ugm/bt-news-section', 'categorySlug', '' )
	: (string) $_bt_attr( 'ugm/bt-news-grid', 'categorySlug', '' );

// Sidebar: berita.
$sb_promo_button_text = (string) $_bt_attr( 'ugm/bt-sidebar-promo', 'buttonText', __( 'UGM Peduli Bencana - Update', 'ugm-faculty' ) );
$sb_promo_button_url  = (string) $_bt_attr( 'ugm/bt-sidebar-promo', 'buttonUrl', '/peduli-bencana/' );
$sb_promo_poster_1    = (string) $_bt_attr( 'ugm/bt-sidebar-promo', 'posterOneUrl', '' );
$sb_promo_poster_1_alt = (string) $_bt_attr( 'ugm/bt-sidebar-promo', 'posterOneAlt', __( 'Poster informasi kebencanaan', 'ugm-faculty' ) );
$sb_promo_poster_1_link = (string) $_bt_attr( 'ugm/bt-sidebar-promo', 'posterOneLink', '' );
$sb_promo_poster_2    = (string) $_bt_attr( 'ugm/bt-sidebar-promo', 'posterTwoUrl', '' );
$sb_promo_poster_2_alt = (string) $_bt_attr( 'ugm/bt-sidebar-promo', 'posterTwoAlt', __( 'Poster UGM Peduli Bencana', 'ugm-faculty' ) );
$sb_promo_poster_2_link = (string) $_bt_attr( 'ugm/bt-sidebar-promo', 'posterTwoLink', '' );

// Sidebar: berita.
$sb_news_title = (string) $_bt_attr( 'ugm/bt-sidebar-news', 'widgetTitle', __( 'Berita', 'ugm-faculty' ) );
if ( __( 'Berita Terbaru', 'ugm-faculty' ) === $sb_news_title ) {
	$sb_news_title = __( 'Berita', 'ugm-faculty' );
}
$sb_news_count = max( 1, (int) $_bt_attr( 'ugm/bt-sidebar-news', 'postsCount', 5 ) );

// Sidebar: agenda.
$sb_agenda_title = (string) $_bt_attr( 'ugm/bt-sidebar-agenda', 'widgetTitle', __( 'Agenda', 'ugm-faculty' ) );
if ( __( 'Agenda Terbaru', 'ugm-faculty' ) === $sb_agenda_title ) {
	$sb_agenda_title = __( 'Agenda', 'ugm-faculty' );
}
$sb_agenda_count = max( 1, (int) $_bt_attr( 'ugm/bt-sidebar-agenda', 'postsCount', 3 ) );
$sb_agenda_url   = (string) $_bt_attr( 'ugm/bt-sidebar-agenda', 'agendaUrl', '/agenda/' );

// Sidebar: kategori.
$sb_cats_title = (string) $_bt_attr( 'ugm/bt-sidebar-categories', 'widgetTitle', __( 'Kategori', 'ugm-faculty' ) );

$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

/* ==========================================================================
 * Query utama: hitung berapa post yang dibutuhkan
 * ========================================================================== */

$bt_need_posts = 0;
if ( $bt_has_featured ) $bt_need_posts += 2; // 1 hero + 1 secondary dalam satu blok
if ( $bt_has_grid )     $bt_need_posts += $bt_grid_count;
$bt_need_posts *= $bt_sections_count;
$bt_need_posts = max( 1, $bt_need_posts );

// Kumpulkan category ID yang di-exclude jika categorySlug dispesifikasi.
function ugmbt_resolve_cat_ids( string $slug ): array {
	if ( '' === $slug ) return array();
	$term = get_category_by_slug( $slug );
	if ( ! $term ) return array( 0 );
	return function_exists( 'ugm_get_category_tree_ids' )
		? ugm_get_category_tree_ids( (int) $term->term_id )
		: array( (int) $term->term_id );
}

$main_query_args = array(
	'post_type'           => 'post',
	'posts_per_page'      => $bt_need_posts,
	'paged'               => $paged,
	'ignore_sticky_posts' => true,
	'post_status'         => 'publish',
	'orderby'             => 'date',
	'order'               => 'DESC',
);

$main_cat_slug = '' !== $bt_featured_cat ? $bt_featured_cat : $bt_grid_cat;
if ( '' !== $main_cat_slug ) {
	$main_query_args['category__in'] = ugmbt_resolve_cat_ids( $main_cat_slug );
}
if ( function_exists( 'ugmbt_exclude_non_news_categories' ) ) {
	$main_query_args = ugmbt_exclude_non_news_categories( $main_query_args );
}
if ( function_exists( 'ugm_apply_non_agenda_date_query' ) ) {
	$main_query_args = ugm_apply_non_agenda_date_query( $main_query_args );
}

$main_query = new WP_Query( $main_query_args );

// Kumpulkan post ke array agar mudah diakses per-index.
$posts_pool = array();
if ( $main_query->have_posts() ) {
	while ( $main_query->have_posts() ) {
		$main_query->the_post();
		$cats  = get_the_category();
		$kicker = '';
		foreach ( $cats as $c ) {
			if ( (int) $c->term_id !== (int) get_option( 'default_category' ) && 'uncategorized' !== $c->slug ) {
				$kicker = $c->name;
				break;
			}
		}
		$posts_pool[] = array(
			'id'        => get_the_ID(),
			'title'     => get_the_title(),
			'permalink' => get_permalink(),
			'date'      => get_the_date( 'l, j F Y' ),
			'date_loc'  => get_the_date( 'j F Y' ),
			'kicker'    => $kicker,
			'excerpt'   => get_the_excerpt(),
			'thumb_url' => get_the_post_thumbnail_url( null, 'large' ) ?: '',
		);
	}
	wp_reset_postdata();
}

$pool_idx   = 0;
$news_sections = array();

for ( $section_idx = 0; $section_idx < $bt_sections_count; $section_idx++ ) {
	$section_hero = null;
	$section_sec  = null;
	$section_grid = array();

	if ( $bt_has_featured ) {
		if ( isset( $posts_pool[ $pool_idx ] ) ) {
			$section_hero = $posts_pool[ $pool_idx++ ];
		}
		if ( isset( $posts_pool[ $pool_idx ] ) ) {
			$section_sec = $posts_pool[ $pool_idx++ ];
		}
	}

	if ( $bt_has_grid ) {
		$section_grid = array_slice( $posts_pool, $pool_idx, $bt_grid_count );
		$pool_idx    += count( $section_grid );
	}

	if ( ! $section_hero && ! $section_sec && empty( $section_grid ) ) {
		break;
	}

	$news_sections[] = array(
		'hero' => $section_hero,
		'sec'  => $section_sec,
		'grid' => $section_grid,
	);
}

/* ==========================================================================
 * Query sidebar
 * ========================================================================== */

$sidebar_news_query = null;
if ( $bt_has_sb_news ) {
	$sidebar_news_args = array(
		'post_type'      => 'post',
		'posts_per_page' => $sb_news_count,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	);
	if ( function_exists( 'ugmbt_exclude_non_news_categories' ) ) {
		$sidebar_news_args = ugmbt_exclude_non_news_categories( $sidebar_news_args );
	}
	if ( function_exists( 'ugm_apply_non_agenda_date_query' ) ) {
		$sidebar_news_args = ugm_apply_non_agenda_date_query( $sidebar_news_args );
	}
	$sidebar_news_query = new WP_Query( $sidebar_news_args );
}

$sidebar_agenda_query = null;
if ( $bt_has_sb_agenda ) {
	$sidebar_agenda_args = array(
		'post_type'      => 'post',
		'posts_per_page' => $sb_agenda_count,
		'post_status'    => 'publish',
		'meta_key'       => 'agenda_event_date',
		'orderby'        => array(
			'meta_value' => 'ASC',
			'date'       => 'DESC',
		),
		'order'          => 'ASC',
		'no_found_rows'  => true,
	);
	if ( function_exists( 'ugm_apply_agenda_date_query' ) ) {
		$sidebar_agenda_args = ugm_apply_agenda_date_query( $sidebar_agenda_args );
	}
	$sidebar_agenda_query = new WP_Query( $sidebar_agenda_args );
}

$all_categories = array();
if ( $bt_has_sb_cats ) {
	$exclude_category_ids = function_exists( 'ugmbt_get_non_news_category_ids' )
		? ugmbt_get_non_news_category_ids()
		: array();
	$all_categories = get_categories( array(
		'orderby'    => 'name',
		'order'      => 'ASC',
		'hide_empty' => true,
		'exclude'    => $exclude_category_ids,
	) );
}

/* ==========================================================================
 * Helper: thumbnail atau placeholder
 * ========================================================================== */

function ugmbt_render_thumb( array $post_data ): void {
	if ( ! empty( $post_data['thumb_url'] ) ) {
		printf(
			'<img src="%s" alt="%s" loading="lazy">',
			esc_url( $post_data['thumb_url'] ),
			esc_attr( $post_data['title'] )
		);
	} else {
		echo '<div class="ugmbt-placeholder" aria-hidden="true"></div>';
	}
}

function ugmbt_render_pagination( int $current_page, int $total_pages ): void {
	$total_pages  = max( 1, $total_pages );
	$current_page = max( 1, min( $current_page, $total_pages ) );

	$page_url = static function ( int $page ) {
		return esc_url( add_query_arg( 'paged', $page ) );
	};

	$items = array( 1 );

	for ( $i = $current_page - 1; $i <= $current_page + 1; $i++ ) {
		if ( $i > 1 && $i < $total_pages ) {
			$items[] = $i;
		}
	}

	if ( $total_pages > 1 ) {
		$items[] = $total_pages;
	}

	$items = array_values( array_unique( $items ) );
	sort( $items );
	?>
	<nav class="ugmbt-pagination" aria-label="<?php esc_attr_e( 'Navigasi halaman', 'ugm-faculty' ); ?>">
		<ul class="page-numbers">
			<?php if ( $current_page > 1 ) : ?>
			<li><a class="prev page-numbers" href="<?php echo $page_url( $current_page - 1 ); ?>" aria-label="<?php esc_attr_e( 'Halaman sebelumnya', 'ugm-faculty' ); ?>">&larr;</a></li>
			<?php endif; ?>

			<?php $previous = 0; ?>
			<?php foreach ( $items as $item ) : ?>
				<?php if ( $previous && $item > $previous + 1 ) : ?>
				<li><span class="page-numbers dots">&hellip;</span></li>
				<?php endif; ?>

				<?php if ( $item === $current_page ) : ?>
				<li><span aria-current="page" class="page-numbers current"><?php echo esc_html( (string) $item ); ?></span></li>
				<?php else : ?>
				<li><a class="page-numbers" href="<?php echo $page_url( $item ); ?>"><?php echo esc_html( (string) $item ); ?></a></li>
				<?php endif; ?>

				<?php $previous = $item; ?>
			<?php endforeach; ?>

			<?php if ( $current_page < $total_pages ) : ?>
			<li><a class="next page-numbers" href="<?php echo $page_url( $current_page + 1 ); ?>" aria-label="<?php esc_attr_e( 'Halaman berikutnya', 'ugm-faculty' ); ?>">&rarr;</a></li>
			<?php endif; ?>
		</ul>
	</nav>
	<?php
}
?>

<main id="primary" class="site-main ugmbt-page">
<div class="ugmbt-container">

	<!-- Breadcrumb -->
	<nav class="ugmbt-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'ugm-faculty' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Berita', 'ugm-faculty' ); ?></a>
		<span aria-hidden="true">&#8250;</span>
		<span aria-current="page"><?php echo esc_html( $page_title ); ?></span>
	</nav>

	<!-- Judul Halaman -->
	<h1 class="ugmbt-page-title"><?php echo esc_html( $page_title ); ?></h1>

	<!-- Layout Utama -->
	<div class="ugmbt-layout<?php echo $bt_has_sidebar ? '' : ' ugmbt-layout--no-sidebar'; ?>">

		<!-- ═══════════════════════════════ MAIN CONTENT ═══════════════════════════════ -->
		<div class="ugmbt-main">

			<?php if ( ! empty( $news_sections ) ) : ?>

				<?php foreach ( $news_sections as $section_index => $news_section ) : ?>
					<?php
					$hero_data  = $news_section['hero'];
					$sec_data   = $news_section['sec'];
					$grid_posts = $news_section['grid'];
					?>

					<?php if ( $section_index > 0 ) : ?>
					<hr class="ugmbt-section-divider" aria-hidden="true">
					<?php endif; ?>

					<section class="ugmbt-section-group">

				<?php if ( $bt_has_featured && ( $hero_data || $sec_data ) ) : ?>
			<!-- ROW 1: Hero + Secondary (Baris Utama Berita) -->
			<div class="ugmbt-row1">

					<?php if ( $hero_data ) : ?>
					<article class="ugmbt-hero" id="post-<?php echo esc_attr( $hero_data['id'] ); ?>">
						<a href="<?php echo esc_url( $hero_data['permalink'] ); ?>"
						   class="ugmbt-card__img-wrap ugmbt-card__img-wrap--hero"
						   aria-label="<?php echo esc_attr( $hero_data['title'] ); ?>">
							<?php ugmbt_render_thumb( $hero_data ); ?>
						</a>
						<h2 class="ugmbt-card__title ugmbt-card__title--hero">
							<a href="<?php echo esc_url( $hero_data['permalink'] ); ?>"><?php echo esc_html( $hero_data['title'] ); ?></a>
						</h2>
						<div class="ugmbt-card__meta-row">
							<?php if ( $hero_data['kicker'] ) : ?>
							<span class="ugmbt-card__kicker"><?php echo esc_html( $hero_data['kicker'] ); ?></span>
							<span class="ugmbt-card__sep" aria-hidden="true">&middot;</span>
							<?php endif; ?>
							<span class="ugmbt-card__date"><?php echo esc_html( $hero_data['date'] ); ?></span>
						</div>
						<p class="ugmbt-card__excerpt"><?php echo esc_html( wp_trim_words( $hero_data['excerpt'], 28 ) ); ?></p>
					</article>
					<?php endif; ?>

					<?php if ( $sec_data ) : ?>
					<article class="ugmbt-secondary" id="post-<?php echo esc_attr( $sec_data['id'] ); ?>">
						<a href="<?php echo esc_url( $sec_data['permalink'] ); ?>"
						   class="ugmbt-card__img-wrap ugmbt-card__img-wrap--sec"
						   aria-label="<?php echo esc_attr( $sec_data['title'] ); ?>">
							<?php ugmbt_render_thumb( $sec_data ); ?>
						</a>
						<h2 class="ugmbt-card__title">
							<a href="<?php echo esc_url( $sec_data['permalink'] ); ?>"><?php echo esc_html( $sec_data['title'] ); ?></a>
						</h2>
						<div class="ugmbt-card__meta-row">
							<?php if ( $sec_data['kicker'] ) : ?>
							<span class="ugmbt-card__kicker"><?php echo esc_html( $sec_data['kicker'] ); ?></span>
							<span class="ugmbt-card__sep" aria-hidden="true">&middot;</span>
							<?php endif; ?>
							<span class="ugmbt-card__date"><?php echo esc_html( $sec_data['date'] ); ?></span>
						</div>
						<p class="ugmbt-card__excerpt ugmbt-card__excerpt--secondary"><?php echo esc_html( wp_trim_words( $sec_data['excerpt'], 72 ) ); ?></p>
					</article>
					<?php endif; ?>

				</div><!-- /row1 -->
			<?php endif; ?>

				<?php if ( $bt_has_featured && $bt_has_grid && ! empty( $grid_posts ) ) : ?>
				<hr class="ugmbt-divider" aria-hidden="true">
				<?php endif; ?>

				<?php if ( $bt_has_grid && ! empty( $grid_posts ) ) : ?>
				<!-- ROW 2: Grid kolom -->
				<div class="ugmbt-row2 ugmbt-row2--<?php echo esc_attr( count( $grid_posts ) ); ?>col">
					<?php foreach ( $grid_posts as $gp ) : ?>
					<article class="ugmbt-grid-card" id="post-<?php echo esc_attr( $gp['id'] ); ?>">
						<a href="<?php echo esc_url( $gp['permalink'] ); ?>"
						   class="ugmbt-card__img-wrap ugmbt-card__img-wrap--grid"
						   aria-label="<?php echo esc_attr( $gp['title'] ); ?>">
							<?php ugmbt_render_thumb( $gp ); ?>
						</a>
						<h2 class="ugmbt-card__title">
							<a href="<?php echo esc_url( $gp['permalink'] ); ?>"><?php echo esc_html( $gp['title'] ); ?></a>
						</h2>
						<div class="ugmbt-card__meta-row">
							<?php if ( $gp['kicker'] ) : ?>
							<span class="ugmbt-card__kicker"><?php echo esc_html( $gp['kicker'] ); ?></span>
							<span class="ugmbt-card__sep" aria-hidden="true">&middot;</span>
							<?php endif; ?>
							<span class="ugmbt-card__date"><?php echo esc_html( $gp['date'] ); ?></span>
						</div>
						<p class="ugmbt-card__excerpt"><?php echo esc_html( wp_trim_words( $gp['excerpt'], 18 ) ); ?></p>
					</article>
					<?php endforeach; ?>
				</div><!-- /row2 -->
				<?php endif; ?>

					</section>

				<?php endforeach; ?>

			<?php else : ?>
				<p class="ugmbt-empty"><?php esc_html_e( 'Belum ada berita terbaru.', 'ugm-faculty' ); ?></p>
			<?php endif; ?>

			<!-- Pagination -->
			<?php ugmbt_render_pagination( $paged, (int) $main_query->max_num_pages ); ?>

		</div><!-- /ugmbt-main -->

		<?php if ( $bt_has_sidebar ) : ?>
		<!-- ═══════════════════════════════ SIDEBAR ═══════════════════════════════ -->
		<aside class="ugmbt-sidebar" aria-label="<?php esc_attr_e( 'Sidebar', 'ugm-faculty' ); ?>">

			<?php if ( $bt_has_sb_promo ) : ?>
			<!-- Widget: Promo Poster -->
			<section class="ugmbt-promo" aria-label="<?php esc_attr_e( 'Informasi UGM Peduli Bencana', 'ugm-faculty' ); ?>">
				<?php if ( '' !== $sb_promo_button_text ) : ?>
				<a class="ugmbt-promo__button" href="<?php echo esc_url( preg_match( '#^https?://#i', $sb_promo_button_url ) ? $sb_promo_button_url : home_url( $sb_promo_button_url ) ); ?>">
					<?php echo esc_html( $sb_promo_button_text ); ?>
				</a>
				<?php endif; ?>

				<?php
				$promo_posters = array(
					array(
						'url'  => $sb_promo_poster_1,
						'alt'  => $sb_promo_poster_1_alt,
						'link' => $sb_promo_poster_1_link,
					),
					array(
						'url'  => $sb_promo_poster_2,
						'alt'  => $sb_promo_poster_2_alt,
						'link' => $sb_promo_poster_2_link,
					),
				);
				?>
				<?php foreach ( $promo_posters as $poster ) : ?>
					<?php if ( '' === $poster['url'] ) : ?>
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
			<?php endif; ?>

			<?php if ( $bt_has_sb_news && $sidebar_news_query ) : ?>
			<!-- Widget: Berita Terbaru -->
			<section class="ugmbt-widget" aria-labelledby="ugmbt-widget-news">
				<h2 class="ugmbt-widget__title" id="ugmbt-widget-news"><?php echo esc_html( $sb_news_title ); ?></h2>
				<?php if ( $sidebar_news_query->have_posts() ) : ?>
				<ul class="ugmbt-news-list" role="list">
					<?php while ( $sidebar_news_query->have_posts() ) : $sidebar_news_query->the_post(); ?>
					<li>
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</li>
					<?php endwhile; wp_reset_postdata(); ?>
				</ul>
				<?php endif; ?>
			</section>
			<?php endif; ?>

			<?php if ( $bt_has_sb_agenda && $sidebar_agenda_query ) : ?>
			<!-- Widget: Agenda Terbaru -->
			<section class="ugmbt-widget" aria-labelledby="ugmbt-widget-agenda">
				<h2 class="ugmbt-widget__title" id="ugmbt-widget-agenda"><?php echo esc_html( $sb_agenda_title ); ?></h2>
				<?php if ( $sidebar_agenda_query->have_posts() ) : ?>
				<div class="ugmbt-agenda-list">
					<?php while ( $sidebar_agenda_query->have_posts() ) : $sidebar_agenda_query->the_post(); ?>
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
				<a class="ugmbt-btn-agenda" href="<?php echo esc_url( home_url( $sb_agenda_url ) ); ?>">
					<?php esc_html_e( 'Semua Agenda', 'ugm-faculty' ); ?> &rarr;
				</a>
				<?php endif; ?>
			</section>
			<?php endif; ?>

			<?php if ( $bt_has_sb_cats && ! empty( $all_categories ) ) : ?>
			<!-- Widget: Kategori -->
			<section class="ugmbt-widget" aria-labelledby="ugmbt-widget-cats">
				<h2 class="ugmbt-widget__title" id="ugmbt-widget-cats"><?php echo esc_html( $sb_cats_title ); ?></h2>
				<ul class="ugmbt-cat-list" role="list">
					<?php foreach ( $all_categories as $cat ) : ?>
					<li>
						<a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>">
							<?php echo esc_html( $cat->name ); ?>
						</a>
						<span><?php echo esc_html( $cat->count . ' ' . __( 'Artikel Total', 'ugm-faculty' ) ); ?></span>
					</li>
					<?php endforeach; ?>
				</ul>
			</section>
			<?php endif; ?>

		</aside><!-- /ugmbt-sidebar -->
		<?php endif; ?>

	</div><!-- /ugmbt-layout -->
</div><!-- /ugmbt-container -->
</main>

<?php get_footer(); ?>
