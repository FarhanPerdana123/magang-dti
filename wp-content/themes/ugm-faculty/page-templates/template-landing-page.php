<?php
/**
 * Template Name: Landing Page
 * Template Post Type: page
 *
 * Full landing page design: latest news, academic news, profile, achievements,
 * categories, faculty slider, agenda, facilities, and digital magazine.
 *
 * @package ugm-faculty
 */

get_header();

// ── Block-based rendering (new approach) ─────────────────────────────────────
// Parse blocks manually so we can:
//  1. Render ugm/hero-section OUTSIDE .home-content (full-bleed / full-width).
//  2. Wrap the "triple" sections (academic, profile, achievement) inside
//     .home-sections-triple for the 3-column desktop layout.
//  3. Keep all other section blocks inside .home-content (max-width container).
// Urutan block di editor Gutenberg tercermin langsung di frontend.
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();

		$template_slug = (string) get_page_template_slug( get_the_ID() );
		$post_content  = function_exists( 'ugm_get_landing_page_render_content' )
			? ugm_get_landing_page_render_content( (string) get_the_content(), $template_slug )
			: (string) get_the_content();
		$blocks       = parse_blocks( $post_content );

		// Blok-blok yang dikumpulkan dalam .home-sections-triple (grid 3 kolom).
		$triple_names = array(
			'ugm/academic-news',
			'ugm/profile-section',
			'ugm/achievement-section',
		);

		// ugm/featured-category-column dikumpulkan dalam .featured-category-grid
		// (wrapper yang sama seperti ugm/featured-categories lama) agar semua CSS berlaku.
		$feat_col_name = 'ugm/featured-category-column';

		// Blocks that must render full-bleed (outside .home-content container).
		$fullbleed_names = array( 'ugm/hero-section' );

		$hero_html    = '';
		$content_html = '';
		$in_triple    = false;
		$in_feat_cols = false;

		foreach ( $blocks as $block ) {
			if ( null === $block['blockName'] ) {
				if ( '' !== $hero_html || $in_feat_cols ) {
					$content_html .= render_block( $block );
				}
				continue;
			}

			// ── Full-bleed blocks ─────────────────────────────────────────────
			if ( in_array( $block['blockName'], $fullbleed_names, true ) ) {
				if ( $in_triple ) {
					$content_html .= '</div><!-- /.home-sections-triple -->';
					$in_triple     = false;
				}
				if ( $in_feat_cols ) {
					$content_html .= '</div></section><!-- /.featured-category-grid -->';
					$in_feat_cols  = false;
				}
				$hero_html .= render_block( $block );
				continue;
			}

			// ── ugm/featured-category-column → .featured-category-grid ───────
			if ( $feat_col_name === $block['blockName'] ) {
				// Close triple first if open.
				if ( $in_triple ) {
					$content_html .= '</div><!-- /.home-sections-triple -->';
					$in_triple     = false;
				}
				// Open the same wrapper structure as the original ugm/featured-categories block.
				if ( ! $in_feat_cols ) {
					$content_html .= '<section class="home-section section-featured-categories" aria-label="' .
						esc_attr__( 'Sorotan kategori', 'ugm-faculty' ) . '">' .
						'<div class="featured-category-grid">';
					$in_feat_cols  = true;
				}
				// Render inner column HTML directly (identical to original markup function).
				$col_attrs     = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
				$content_html .= ugm_render_single_featured_column( $col_attrs );
				continue;
			}

			// Close featured-cols grid when a non-column block is encountered.
			if ( $in_feat_cols ) {
				$content_html .= '</div></section><!-- /.featured-category-grid -->';
				$in_feat_cols  = false;
			}

			// ── home-sections-triple ──────────────────────────────────────────
			$is_triple = in_array( $block['blockName'], $triple_names, true );
			if ( $is_triple && ! $in_triple ) {
				$content_html .= '<div class="home-sections-triple row g-4 g-lg-5">';
				$in_triple     = true;
			} elseif ( ! $is_triple && $in_triple ) {
				$content_html .= '</div><!-- /.home-sections-triple -->';
				$in_triple     = false;
			}

			$content_html .= render_block( $block );
		}

		// Close any trailing open wrappers.
		if ( $in_triple ) {
			$content_html .= '</div><!-- /.home-sections-triple -->';
		}
		if ( $in_feat_cols ) {
			$content_html .= '</div></section><!-- /.featured-category-grid -->';
		}

		?>
		<main id="primary" class="site-main ugm-home">
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $hero_html; // Full-bleed (no container).
			?>
			<div class="home-content container">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $content_html;
				?>
			</div>
		</main>
		<?php
	}
	get_footer();
	return; // ← stop here; do NOT run the legacy PHP queries below.
}

// ── Legacy PHP-rendered sections (backward compat) ───────────────────────────
// Resolve per-page section titles: post meta overrides Customizer value.
$ugm_page_id             = get_queried_object_id();
$ugm_landing_block_attrs = array();
$ugm_landing_post        = get_post( $ugm_page_id );

if ( $ugm_landing_post instanceof WP_Post ) {
	foreach ( parse_blocks( (string) $ugm_landing_post->post_content ) as $ugm_landing_block ) {
		if ( ! empty( $ugm_landing_block['blockName'] ) && 0 === strpos( $ugm_landing_block['blockName'], 'ugm/' ) ) {
			$ugm_landing_block_attrs[ $ugm_landing_block['blockName'] ] = isset( $ugm_landing_block['attrs'] ) && is_array( $ugm_landing_block['attrs'] )
				? $ugm_landing_block['attrs']
				: array();
		}
	}
}

$ugm_get_block_attr = static function ( $block_name, $attr_name ) use ( $ugm_landing_block_attrs ) {
	if ( isset( $ugm_landing_block_attrs[ $block_name ][ $attr_name ] ) ) {
		return trim( (string) $ugm_landing_block_attrs[ $block_name ][ $attr_name ] );
	}

	return '';
};
$ugm_get_category_by_slugs = static function ( array $slugs ) {
	foreach ( $slugs as $slug ) {
		$term = get_category_by_slug( sanitize_title( $slug ) );
		if ( $term instanceof WP_Term ) {
			return $term;
		}
	}

	return null;
};
$ugm_title_latest        = (string) get_post_meta( $ugm_page_id, 'ugm_page_latest_title', true );
$ugm_title_academic      = (string) get_post_meta( $ugm_page_id, 'ugm_page_academic_title', true );
$ugm_title_profile       = (string) get_post_meta( $ugm_page_id, 'ugm_page_profile_title', true );
$ugm_title_achievement   = (string) get_post_meta( $ugm_page_id, 'ugm_page_achievement_title', true );
$ugm_title_faculty       = (string) get_post_meta( $ugm_page_id, 'ugm_page_faculty_title', true );
$ugm_title_agenda        = (string) get_post_meta( $ugm_page_id, 'ugm_page_agenda_title', true );

if ( '' === $ugm_title_latest ) {
	$ugm_title_latest = $ugm_get_block_attr( 'ugm/latest-news', 'title' );
}
if ( '' === $ugm_title_latest ) {
	$ugm_title_latest = get_theme_mod( 'ugm_latest_section_title', __( 'Berita Terbaru', 'ugm-faculty' ) );
}
if ( '' === $ugm_title_academic ) {
	$ugm_title_academic = $ugm_get_block_attr( 'ugm/academic-news', 'title' );
}
if ( '' === $ugm_title_academic ) {
	$ugm_title_academic = get_theme_mod( 'ugm_academic_section_title', __( 'Berita Akademik', 'ugm-faculty' ) );
}
if ( '' === $ugm_title_profile ) {
	$ugm_title_profile = $ugm_get_block_attr( 'ugm/profile-section', 'title' );
}
if ( '' === $ugm_title_profile ) {
	$ugm_title_profile = get_theme_mod( 'ugm_profile_section_title', __( 'Profile', 'ugm-faculty' ) );
}
if ( '' === $ugm_title_achievement ) {
	$ugm_title_achievement = $ugm_get_block_attr( 'ugm/achievement-section', 'title' );
}
if ( '' === $ugm_title_achievement ) {
	$ugm_title_achievement = get_theme_mod( 'ugm_achievement_section_title', __( 'Prestasi', 'ugm-faculty' ) );
}
if ( '' === $ugm_title_faculty ) {
	$ugm_title_faculty = $ugm_get_block_attr( 'ugm/faculty-section', 'title' );
}
if ( '' === $ugm_title_faculty ) {
	$ugm_title_faculty = get_theme_mod( 'ugm_faculty_section_title', __( 'Fakultas dan Sekolah', 'ugm-faculty' ) );
}
if ( '' === $ugm_title_agenda ) {
	$ugm_title_agenda = $ugm_get_block_attr( 'ugm/agenda-section', 'title' );
}
if ( '' === $ugm_title_agenda ) {
	$ugm_title_agenda = get_theme_mod( 'ugm_events_section_title', __( 'Agenda Kegiatan', 'ugm-faculty' ) );
}

// Per-page category slugs: determines which WordPress category each section reads from.
// Kosongkan field di meta box = gunakan slug default bawaan.
$ugm_cat_academic    = trim( (string) get_post_meta( $ugm_page_id, 'ugm_page_academic_category', true ) );
$ugm_cat_profile     = trim( (string) get_post_meta( $ugm_page_id, 'ugm_page_profile_category', true ) );
$ugm_cat_achievement = trim( (string) get_post_meta( $ugm_page_id, 'ugm_page_achievement_category', true ) );
$ugm_cat_agenda      = trim( (string) get_post_meta( $ugm_page_id, 'ugm_page_agenda_category', true ) );

if ( '' === $ugm_cat_academic ) {
	$ugm_cat_academic = 'pendidikan';
}
if ( '' === $ugm_cat_profile ) {
	$ugm_cat_profile = 'profile';
}
if ( '' === $ugm_cat_achievement ) {
	$ugm_cat_achievement = 'prestasi';
}

// Berita Terbaru: opsional — kosong = semua post terbaru (default).
$ugm_cat_latest = trim( (string) get_post_meta( $ugm_page_id, 'ugm_page_latest_category', true ) );

// Resolve agenda term IDs lebih awal agar bisa di-exclude dari Berita Terbaru.
// Prioritas: per-page meta → Customizer category ID → auto-detect by slug.
$ugm_agenda_exclude_ids = array();
if ( '' !== $ugm_cat_agenda ) {
	// Slug diisi di meta box — gunakan itu.
	$ugm_agenda_root_early = get_category_by_slug( $ugm_cat_agenda );
	if ( $ugm_agenda_root_early ) {
		$ugm_agenda_exclude_ids = ugm_get_category_tree_ids( (int) $ugm_agenda_root_early->term_id );
	}
} else {
	// Fallback: cek Customizer category ID, lalu auto-detect slug umum.
	$ugm_agenda_cid_early = absint( get_theme_mod( 'ugm_events_category_id', 0 ) );
	if ( $ugm_agenda_cid_early > 0 ) {
		$ugm_agenda_exclude_ids = ugm_get_category_tree_ids( $ugm_agenda_cid_early );
	} else {
		$ugm_agenda_root_early = ugm_get_category_root_by_slugs( array( 'agenda', 'kegiatan', 'events', 'event' ) );
		if ( $ugm_agenda_root_early ) {
			$ugm_agenda_exclude_ids = ugm_get_category_tree_ids( (int) $ugm_agenda_root_early->term_id );
		}
	}
}

// Resolve faculty section term IDs agar tidak muncul di Berita Terbaru.
// Baca slug dari block attributes halaman ini, fallback ke default 'fakultas-dan-sekolah'.
$ugm_faculty_cat_slug    = trim( (string) ( $ugm_landing_block_attrs['ugm/faculty-section']['categorySlug'] ?? '' ) );
if ( '' === $ugm_faculty_cat_slug ) {
	$ugm_faculty_cat_slug = 'fakultas-dan-sekolah';
}
$ugm_faculty_exclude_ids = array();
$ugm_faculty_root_early  = get_category_by_slug( $ugm_faculty_cat_slug );
if ( $ugm_faculty_root_early instanceof WP_Term ) {
	$ugm_faculty_exclude_ids = ugm_get_category_tree_ids( (int) $ugm_faculty_root_early->term_id );
}

// Gabungkan semua category IDs yang perlu di-exclude dari Berita Terbaru.
$ugm_latest_exclude_ids = array_unique( array_merge( $ugm_agenda_exclude_ids, $ugm_faculty_exclude_ids ) );
?>

<main id="primary" class="site-main ugm-home">
	<div class="home-content container">
		<section class="home-section section-news" aria-labelledby="section-news-title">
			<?php $latest_news_archive_link = add_query_arg( 'ugm_latest_news', '1', home_url( '/' ) ); ?>
			<header class="section-header section-header--news">
				<h2 id="section-news-title" class="section-title">
					<?php echo esc_html( $ugm_title_latest ); ?>
				</h2>
				<span class="section-line" aria-hidden="true"></span>
				<a class="section-view-all" href="<?php echo esc_url( $latest_news_archive_link ); ?>" aria-label="<?php esc_attr_e( 'Lihat semua berita terbaru', 'ugm-faculty' ); ?>">
					<?php esc_html_e( 'Lihat Semua', 'ugm-faculty' ); ?>
					<span aria-hidden="true">&rarr;</span>
				</a>
			</header>

			<?php
			// Post terbaru untuk section berita landing page.
			$latest_news_count = max( 1, absint( get_theme_mod( 'ugm_latest_news_count', 4 ) ) );
			$latest_news_mode  = get_theme_mod( 'ugm_latest_news_mode', 'auto' );
			$latest_news_args  = array();

			if ( 'manual' === $latest_news_mode ) {
				$manual_news_ids = array_values(
					array_filter(
						array(
							absint( get_theme_mod( 'ugm_latest_news_post_1', 0 ) ),
							absint( get_theme_mod( 'ugm_latest_news_post_2', 0 ) ),
							absint( get_theme_mod( 'ugm_latest_news_post_3', 0 ) ),
							absint( get_theme_mod( 'ugm_latest_news_post_4', 0 ) ),
						)
					)
				);

				if ( ! empty( $manual_news_ids ) ) {
					$latest_news_args = array(
						'post_type'           => 'post',
						'post__in'            => $manual_news_ids,
						'orderby'             => 'post__in',
						'posts_per_page'      => count( $manual_news_ids ),
						'ignore_sticky_posts' => true,
						'post_status'         => 'publish',
						'no_found_rows'       => true,
					);
				}
			}

			if ( empty( $latest_news_args ) ) {
				$latest_news_args = array(
					'post_type'           => 'post',
					'posts_per_page'      => $latest_news_count,
					'ignore_sticky_posts' => true,
					'post_status'         => 'publish',
					'orderby'             => 'date',
					'order'               => 'DESC',
					'no_found_rows'       => true,
				);

				// Jika slug kategori diisi, filter by kategori tersebut.
				// Jika kosong, tampilkan semua post terbaru (perilaku default).
				if ( '' !== $ugm_cat_latest ) {
					$latest_category = get_category_by_slug( $ugm_cat_latest );
					if ( $latest_category ) {
						$latest_term_ids = ugm_get_category_tree_ids( (int) $latest_category->term_id );
						if ( ! empty( $latest_term_ids ) ) {
							$latest_news_args['category__in'] = $latest_term_ids;
						}
					} else {
						// Slug tidak ditemukan → paksa hasil kosong.
						$latest_news_args['post__in'] = array( 0 );
					}
				}

				// Selalu exclude kategori agenda dan fakultas dari berita terbaru.
				// Konten ini punya section sendiri — tidak boleh muncul di section ini.
				if ( ! empty( $ugm_latest_exclude_ids ) ) {
					$latest_news_args['category__not_in'] = $ugm_latest_exclude_ids;
				}
			}

			$latest_news_query = new WP_Query( $latest_news_args );
			?>

			<?php if ( $latest_news_query->have_posts() ) : ?>
				<?php
				$latest_news_posts = $latest_news_query->posts;
				$featured_news     = array_shift( $latest_news_posts );
				$side_news         = array_shift( $latest_news_posts );
				$list_news_posts   = array_slice( $latest_news_posts, 0, 2 );
				$news_side_items   = array_merge( $side_news ? array( $side_news ) : array(), $list_news_posts );
				$news_side_missing = max( 0, 3 - count( $news_side_items ) );
				$get_news_category_label = static function () {
					$categories          = get_the_category();
					$default_category_id = (int) get_option( 'default_category' );
					$category_label      = __( 'Kepakaran', 'ugm-faculty' );

					if ( ! empty( $categories ) ) {
						foreach ( $categories as $category ) {
							if ( $default_category_id !== (int) $category->term_id && 'uncategorized' !== $category->slug ) {
								$category_label = $category->name;
								break;
							}
						}
					}

					return $category_label;
				};
				?>
				<div class="news-layout">
					<?php if ( $featured_news instanceof WP_Post ) : ?>
						<div class="news-area news-area--featured">
							<?php
							$post = $featured_news; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
							setup_postdata( $post );
							$featured_category = $get_news_category_label();
							$featured_excerpt  = trim( wp_strip_all_tags( get_the_excerpt() ) );
							if ( '' === $featured_excerpt ) {
								$featured_excerpt = wp_trim_words( wp_strip_all_tags( get_the_content( null, false ) ), 30, '...' );
							}
							?>
							<article <?php post_class( 'news-featured' ); ?>>
								<?php if ( has_post_thumbnail() ) : ?>
									<a class="news-featured__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
										<?php the_post_thumbnail( 'large' ); ?>
									</a>
								<?php else : ?>
									<div class="news-featured__media news-featured__media--placeholder" aria-hidden="true">
										<span class="card-placeholder__text"><?php esc_html_e( 'Tulisan Bebas Area Utama', 'ugm-faculty' ); ?></span>
									</div>
								<?php endif; ?>

								<div class="news-featured__body">
									<p class="card-kicker"><?php echo esc_html( $featured_category ); ?></p>
									<h3 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
									<p class="card-date"><?php echo esc_html( get_the_date( 'j F Y, H.i' ) ); ?></p>
									<?php if ( '' !== $featured_excerpt ) : ?>
										<p class="news-featured__excerpt"><?php echo esc_html( $featured_excerpt ); ?></p>
									<?php endif; ?>
								</div>
							</article>
						</div>
					<?php endif; ?>

						<div class="news-area news-area--list news-list berita-list">
							<?php foreach ( $news_side_items as $news_index => $news_post ) : ?>
								<?php
								$post = $news_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
								setup_postdata( $post );
								$news_category = $get_news_category_label();
								$news_card_class = 0 === $news_index ? 'news-grid-card news-grid-card--side' : 'news-grid-card news-grid-card--list';
								?>
								<article <?php post_class( $news_card_class ); ?>>
									<?php if ( has_post_thumbnail() ) : ?>
										<a class="news-grid-card__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
											<?php the_post_thumbnail( 'medium_large' ); ?>
										</a>
									<?php else : ?>
										<div class="news-grid-card__media news-grid-card__media--placeholder" aria-hidden="true">
											<span class="card-placeholder__text"><?php esc_html_e( 'Tulisan Bebas', 'ugm-faculty' ); ?></span>
										</div>
									<?php endif; ?>

									<div class="news-grid-card__body">
										<p class="card-kicker"><?php echo esc_html( $news_category ); ?></p>
										<h3 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
										<p class="card-date"><?php echo esc_html( get_the_date( 'j F Y, H.i' ) ); ?></p>
									</div>
								</article>
							<?php endforeach; ?>
							<?php echo ugm_render_partial_skeleton_items( 'latest-news-card', $news_side_missing ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<?php echo ugm_render_empty_skeleton( 'latest-news', __( 'Belum ada berita terbaru.', 'ugm-faculty' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
		</section>

		<div class="home-sections-triple row g-4 g-lg-5">
		<section class="home-section section-academic col-12 col-lg-4" aria-labelledby="section-academic-title">
			<header class="section-header">
				<h2 id="section-academic-title" class="section-title">
					<?php echo esc_html( $ugm_title_academic ); ?>
				</h2>
				<span class="section-line" aria-hidden="true"></span>
			</header>

			<?php
			$academic_news_count = max( 1, absint( get_theme_mod( 'ugm_academic_news_count', 3 ) ) );
			$academic_news_mode  = get_theme_mod( 'ugm_academic_news_mode', 'auto' );
			$academic_news_args  = array();
			$academic_cat_slugs  = ugm_parse_category_slug_list( $ugm_cat_academic, array( 'pendidikan' ) );
			$academic_cat_ids    = ugm_resolve_multiple_slugs_to_ids( $academic_cat_slugs );
			$academic_archive_link = ugm_get_category_archive_url_from_slugs( $academic_cat_slugs );

			if ( 'manual' === $academic_news_mode ) {
				$manual_academic_ids = array_values(
					array_filter(
						array(
							absint( get_theme_mod( 'ugm_academic_news_post_1', 0 ) ),
							absint( get_theme_mod( 'ugm_academic_news_post_2', 0 ) ),
							absint( get_theme_mod( 'ugm_academic_news_post_3', 0 ) ),
							absint( get_theme_mod( 'ugm_academic_news_post_4', 0 ) ),
							absint( get_theme_mod( 'ugm_academic_news_post_5', 0 ) ),
							absint( get_theme_mod( 'ugm_academic_news_post_6', 0 ) ),
						)
					)
				);

				if ( ! empty( $manual_academic_ids ) ) {
					$academic_news_args = array(
						'post_type'           => 'post',
						'post__in'            => $manual_academic_ids,
						'orderby'             => 'post__in',
						'posts_per_page'      => count( $manual_academic_ids ),
						'ignore_sticky_posts' => true,
						'post_status'         => 'publish',
						'no_found_rows'       => true,
					);

					if ( ! empty( $academic_cat_ids ) ) {
						$academic_news_args['category__in'] = $academic_cat_ids;
					} else {
						// Kategori tidak ditemukan → paksa hasil kosong agar tidak menampilkan semua post.
						$academic_news_args = array( 'post__in' => array( 0 ) );
					}
				}
			}

			if ( empty( $academic_news_args ) ) {
				if ( ! empty( $academic_cat_ids ) ) {
					$academic_news_args = array(
						'post_type'           => 'post',
						'posts_per_page'      => $academic_news_count,
						'ignore_sticky_posts' => true,
						'post_status'         => 'publish',
						'orderby'             => 'date',
						'order'               => 'DESC',
						'no_found_rows'       => true,
						'category__in'        => $academic_cat_ids,
					);
				} else {
					// Kategori tidak ditemukan → paksa hasil kosong agar tidak menampilkan semua post.
					$academic_news_args = array( 'post__in' => array( 0 ) );
				}
			}

			$academic_query = new WP_Query( $academic_news_args );
			?>

			<?php if ( $academic_query->have_posts() ) : ?>
				<?php
				$academic_posts      = $academic_query->posts;
				$academic_featured   = array_shift( $academic_posts );
				$academic_list_posts = array_slice( $academic_posts, 0, 2 );
				$academic_missing    = max( 0, 2 - count( $academic_list_posts ) );
				$get_academic_category_label = static function () {
					$categories          = get_the_category();
					$default_category_id = (int) get_option( 'default_category' );
					$category_label      = __( 'Kepakaran', 'ugm-faculty' );

					if ( ! empty( $categories ) ) {
						foreach ( $categories as $category ) {
							if ( $default_category_id !== (int) $category->term_id && 'uncategorized' !== $category->slug ) {
								$category_label = $category->name;
								break;
							}
						}
					}

					return $category_label;
				};
				?>
				<div class="portal-column">
					<?php if ( $academic_featured instanceof WP_Post ) : ?>
						<?php
						$post = $academic_featured; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						setup_postdata( $post );
						$academic_featured_label = $get_academic_category_label();
						$academic_featured_excerpt = trim( wp_strip_all_tags( get_the_excerpt() ) );
						if ( '' === $academic_featured_excerpt ) {
							$academic_featured_excerpt = wp_trim_words( wp_strip_all_tags( get_the_content( null, false ) ), 30, '...' );
						}
						?>
						<article <?php post_class( 'portal-card portal-card--featured' ); ?>>
							<?php if ( has_post_thumbnail() ) : ?>
								<a class="portal-card__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
									<?php the_post_thumbnail( 'medium_large' ); ?>
								</a>
							<?php else : ?>
								<div class="portal-card__media portal-card__media--placeholder" aria-hidden="true">
									<span class="card-placeholder__text"><?php esc_html_e( 'Tulisan Bebas', 'ugm-faculty' ); ?></span>
								</div>
							<?php endif; ?>
							<div class="portal-card__body">
								<p class="card-kicker"><?php echo esc_html( $academic_featured_label ); ?></p>
								<h3 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<?php if ( '' !== $academic_featured_excerpt ) : ?>
									<p class="card-excerpt card-excerpt--mobile"><?php echo esc_html( $academic_featured_excerpt ); ?></p>
								<?php endif; ?>
								<p class="card-date"><?php echo esc_html( get_the_date( 'j F Y, H.i' ) ); ?></p>
							</div>
						</article>
					<?php endif; ?>

						<div class="portal-column__list">
							<?php foreach ( $academic_list_posts as $academic_post ) : ?>
								<?php
								$post = $academic_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
								setup_postdata( $post );
								$academic_label = $get_academic_category_label();
								$academic_excerpt = trim( wp_strip_all_tags( get_the_excerpt() ) );
								if ( '' === $academic_excerpt ) {
									$academic_excerpt = wp_trim_words( wp_strip_all_tags( get_the_content( null, false ) ), 30, '...' );
								}
								?>
								<article <?php post_class( 'portal-list-card' ); ?>>
									<?php if ( has_post_thumbnail() ) : ?>
										<a class="portal-list-card__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
											<?php the_post_thumbnail( 'thumbnail' ); ?>
										</a>
									<?php else : ?>
										<div class="portal-list-card__media portal-list-card__media--placeholder" aria-hidden="true"></div>
									<?php endif; ?>
									<div class="portal-list-card__body">
										<p class="card-kicker"><?php echo esc_html( $academic_label ); ?></p>
										<h3 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
										<?php if ( '' !== $academic_excerpt ) : ?>
											<p class="card-excerpt card-excerpt--mobile"><?php echo esc_html( $academic_excerpt ); ?></p>
										<?php endif; ?>
										<p class="card-date"><?php echo esc_html( get_the_date( 'j F Y, H.i' ) ); ?></p>
									</div>
								</article>
							<?php endforeach; ?>
							<?php echo ugm_render_partial_skeleton_items( 'portal-list-card', $academic_missing ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<?php echo ugm_render_empty_skeleton( 'portal-column', __( 'Belum ada berita akademik.', 'ugm-faculty' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>

			<a class="section-arrow-link" href="<?php echo esc_url( $academic_archive_link ); ?>" aria-label="<?php esc_attr_e( 'Lihat semua berita akademik', 'ugm-faculty' ); ?>">&#8594;</a>
		</section>

		<section class="home-section section-profile col-12 col-lg-4" aria-labelledby="section-profile-title">
			<header class="section-header">
				<h2 id="section-profile-title" class="section-title">
					<?php echo esc_html( $ugm_title_profile ); ?>
				</h2>
				<span class="section-line" aria-hidden="true"></span>
			</header>

			<?php
			$profile_news_count   = max( 1, absint( get_theme_mod( 'ugm_profile_news_count', 3 ) ) );
			$profile_news_mode    = get_theme_mod( 'ugm_profile_news_mode', 'auto' );
			$profile_news_args    = array();
			$profile_cat_slugs    = ugm_parse_category_slug_list( $ugm_cat_profile, array( 'profile' ) );
			$profile_term_ids     = ugm_resolve_multiple_slugs_to_ids( $profile_cat_slugs );
			$profile_archive_link = ugm_get_category_archive_url_from_slugs( $profile_cat_slugs );

			if ( 'manual' === $profile_news_mode ) {
				$manual_profile_ids = array_values(
					array_filter(
						array(
							absint( get_theme_mod( 'ugm_profile_news_post_1', 0 ) ),
							absint( get_theme_mod( 'ugm_profile_news_post_2', 0 ) ),
							absint( get_theme_mod( 'ugm_profile_news_post_3', 0 ) ),
							absint( get_theme_mod( 'ugm_profile_news_post_4', 0 ) ),
							absint( get_theme_mod( 'ugm_profile_news_post_5', 0 ) ),
							absint( get_theme_mod( 'ugm_profile_news_post_6', 0 ) ),
						)
					)
				);

				if ( ! empty( $manual_profile_ids ) ) {
					$profile_news_args = array(
						'post_type'           => 'post',
						'post__in'            => $manual_profile_ids,
						'orderby'             => 'post__in',
						'posts_per_page'      => count( $manual_profile_ids ),
						'ignore_sticky_posts' => true,
						'post_status'         => 'publish',
						'no_found_rows'       => true,
					);

					if ( ! empty( $profile_term_ids ) ) {
						$profile_news_args['category__in'] = $profile_term_ids;
					}
				}
			}

			if ( empty( $profile_news_args ) ) {
				if ( ! empty( $profile_term_ids ) ) {
					// Kategori ditemukan → filter by category.
					$profile_news_args = array(
						'post_type'           => 'post',
						'posts_per_page'      => $profile_news_count,
						'ignore_sticky_posts' => true,
						'post_status'         => 'publish',
						'orderby'             => 'date',
						'order'               => 'DESC',
						'no_found_rows'       => true,
						'category__in'        => $profile_term_ids,
					);
				} else {
					// Kategori tidak ada di WordPress →
					// paksa hasil kosong agar tidak menampilkan semua post.
					$profile_news_args = array( 'post__in' => array( 0 ) );
				}
			}

			$profile_query = new WP_Query( $profile_news_args );
			?>

			<?php if ( $profile_query->have_posts() ) : ?>
				<?php
				$profile_posts      = $profile_query->posts;
				$profile_featured   = array_shift( $profile_posts );
				$profile_list_posts = array_slice( $profile_posts, 0, 2 );
				$profile_missing    = max( 0, 2 - count( $profile_list_posts ) );
				$get_profile_category_label = static function () {
					$categories          = get_the_category();
					$default_category_id = (int) get_option( 'default_category' );
					$category_label      = __( 'Kepakaran', 'ugm-faculty' );

					if ( ! empty( $categories ) ) {
						foreach ( $categories as $category ) {
							if ( $default_category_id !== (int) $category->term_id && 'uncategorized' !== $category->slug ) {
								$category_label = $category->name;
								break;
							}
						}
					}

					return $category_label;
				};
				?>
				<div class="portal-column">
					<?php if ( $profile_featured instanceof WP_Post ) : ?>
						<?php
						$post = $profile_featured; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						setup_postdata( $post );
						$profile_featured_label = $get_profile_category_label();
						$profile_featured_excerpt = trim( wp_strip_all_tags( get_the_excerpt() ) );
						if ( '' === $profile_featured_excerpt ) {
							$profile_featured_excerpt = wp_trim_words( wp_strip_all_tags( get_the_content( null, false ) ), 30, '...' );
						}
						?>
						<article <?php post_class( 'portal-card portal-card--featured' ); ?>>
							<?php if ( has_post_thumbnail() ) : ?>
								<a class="portal-card__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
									<?php the_post_thumbnail( 'medium_large' ); ?>
								</a>
							<?php else : ?>
								<div class="portal-card__media portal-card__media--placeholder" aria-hidden="true">
									<span class="card-placeholder__text"><?php esc_html_e( 'Tulisan Bebas', 'ugm-faculty' ); ?></span>
								</div>
							<?php endif; ?>
							<div class="portal-card__body">
								<p class="card-kicker"><?php echo esc_html( $profile_featured_label ); ?></p>
								<h3 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<?php if ( '' !== $profile_featured_excerpt ) : ?>
									<p class="card-excerpt card-excerpt--mobile"><?php echo esc_html( $profile_featured_excerpt ); ?></p>
								<?php endif; ?>
								<p class="card-date"><?php echo esc_html( get_the_date( 'j F Y, H.i' ) ); ?></p>
							</div>
						</article>
					<?php endif; ?>

						<div class="portal-column__list">
							<?php foreach ( $profile_list_posts as $profile_post ) : ?>
								<?php
								$post = $profile_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
								setup_postdata( $post );
								$profile_label = $get_profile_category_label();
								$profile_excerpt = trim( wp_strip_all_tags( get_the_excerpt() ) );
								if ( '' === $profile_excerpt ) {
									$profile_excerpt = wp_trim_words( wp_strip_all_tags( get_the_content( null, false ) ), 30, '...' );
								}
								?>
								<article <?php post_class( 'portal-list-card' ); ?>>
									<?php if ( has_post_thumbnail() ) : ?>
										<a class="portal-list-card__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
											<?php the_post_thumbnail( 'thumbnail' ); ?>
										</a>
									<?php else : ?>
										<div class="portal-list-card__media portal-list-card__media--placeholder" aria-hidden="true"></div>
									<?php endif; ?>
									<div class="portal-list-card__body">
										<p class="card-kicker"><?php echo esc_html( $profile_label ); ?></p>
										<h3 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
										<?php if ( '' !== $profile_excerpt ) : ?>
											<p class="card-excerpt card-excerpt--mobile"><?php echo esc_html( $profile_excerpt ); ?></p>
										<?php endif; ?>
										<p class="card-date"><?php echo esc_html( get_the_date( 'j F Y, H.i' ) ); ?></p>
									</div>
								</article>
							<?php endforeach; ?>
							<?php echo ugm_render_partial_skeleton_items( 'portal-list-card', $profile_missing ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<?php echo ugm_render_empty_skeleton( 'portal-column', __( 'Belum ada konten profile.', 'ugm-faculty' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>

			<a class="section-arrow-link" href="<?php echo esc_url( $profile_archive_link ); ?>" aria-label="<?php esc_attr_e( 'Lihat semua profile', 'ugm-faculty' ); ?>">&#8594;</a>
		</section>

		<section class="home-section section-achievement col-12 col-lg-4" aria-labelledby="section-achievement-title">
			<header class="section-header">
				<h2 id="section-achievement-title" class="section-title">
					<?php echo esc_html( $ugm_title_achievement ); ?>
				</h2>
				<span class="section-line" aria-hidden="true"></span>
			</header>

			<?php
			$achievement_news_count  = max( 1, absint( get_theme_mod( 'ugm_achievement_news_count', 3 ) ) );
			$achievement_news_mode   = get_theme_mod( 'ugm_achievement_news_mode', 'auto' );
			$achievement_news_args   = array();
			$achievement_cat_slugs   = ugm_parse_category_slug_list( $ugm_cat_achievement, array( 'prestasi' ) );
			$achievement_term_ids     = ugm_resolve_multiple_slugs_to_ids( $achievement_cat_slugs );
			$achievement_archive_link = ugm_get_category_archive_url_from_slugs( $achievement_cat_slugs );

			if ( 'manual' === $achievement_news_mode ) {
				$manual_achievement_ids = array_values(
					array_filter(
						array(
							absint( get_theme_mod( 'ugm_achievement_news_post_1', 0 ) ),
							absint( get_theme_mod( 'ugm_achievement_news_post_2', 0 ) ),
							absint( get_theme_mod( 'ugm_achievement_news_post_3', 0 ) ),
							absint( get_theme_mod( 'ugm_achievement_news_post_4', 0 ) ),
							absint( get_theme_mod( 'ugm_achievement_news_post_5', 0 ) ),
							absint( get_theme_mod( 'ugm_achievement_news_post_6', 0 ) ),
						)
					)
				);

				if ( ! empty( $manual_achievement_ids ) ) {
					$achievement_news_args = array(
						'post_type'           => 'post',
						'post__in'            => $manual_achievement_ids,
						'orderby'             => 'post__in',
						'posts_per_page'      => count( $manual_achievement_ids ),
						'ignore_sticky_posts' => true,
						'post_status'         => 'publish',
						'no_found_rows'       => true,
					);

					if ( ! empty( $achievement_term_ids ) ) {
						$achievement_news_args['category__in'] = $achievement_term_ids;
					} else {
						$achievement_news_args = array( 'post__in' => array( 0 ) );
					}
				}
			}

			if ( empty( $achievement_news_args ) ) {
				if ( ! empty( $achievement_term_ids ) ) {
					// Kategori ditemukan → filter by category.
					$achievement_news_args = array(
						'post_type'           => 'post',
						'posts_per_page'      => $achievement_news_count,
						'ignore_sticky_posts' => true,
						'post_status'         => 'publish',
						'orderby'             => 'date',
						'order'               => 'DESC',
						'no_found_rows'       => true,
						'category__in'        => $achievement_term_ids,
					);
				} else {
					// Kategori 'prestasi' tidak ada di WordPress →
					// paksa hasil kosong agar tidak menampilkan semua post.
					$achievement_news_args = array( 'post__in' => array( 0 ) );
				}
			}

			$achievement_query = new WP_Query( $achievement_news_args );
			?>

			<?php if ( $achievement_query->have_posts() ) : ?>
				<?php
				$achievement_posts      = $achievement_query->posts;
				$achievement_featured   = array_shift( $achievement_posts );
				$achievement_list_posts = array_slice( $achievement_posts, 0, 2 );
				$achievement_missing    = max( 0, 2 - count( $achievement_list_posts ) );
				$get_achievement_category_label = static function () {
					$categories          = get_the_category();
					$default_category_id = (int) get_option( 'default_category' );
					$category_label      = __( 'Kepakaran', 'ugm-faculty' );

					if ( ! empty( $categories ) ) {
						foreach ( $categories as $category ) {
							if ( 'prestasi' === $category->slug ) {
								$category_label = $category->name;
								break;
							}
						}

						if ( __( 'Kepakaran', 'ugm-faculty' ) === $category_label ) {
							foreach ( $categories as $category ) {
								if ( $default_category_id !== (int) $category->term_id && 'uncategorized' !== $category->slug ) {
									$category_label = $category->name;
									break;
								}
							}
						}
					}

					return $category_label;
				};
				?>
				<div class="portal-column">
					<?php if ( $achievement_featured instanceof WP_Post ) : ?>
						<?php
						$post = $achievement_featured; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						setup_postdata( $post );
						$achievement_featured_label = $get_achievement_category_label();
						$achievement_featured_excerpt = trim( wp_strip_all_tags( get_the_excerpt() ) );
						if ( '' === $achievement_featured_excerpt ) {
							$achievement_featured_excerpt = wp_trim_words( wp_strip_all_tags( get_the_content( null, false ) ), 30, '...' );
						}
						?>
						<article <?php post_class( 'portal-card portal-card--featured' ); ?>>
							<?php if ( has_post_thumbnail() ) : ?>
								<a class="portal-card__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
									<?php the_post_thumbnail( 'medium_large' ); ?>
								</a>
							<?php else : ?>
								<div class="portal-card__media portal-card__media--placeholder" aria-hidden="true">
									<span class="card-placeholder__text"><?php esc_html_e( 'Tulisan Bebas', 'ugm-faculty' ); ?></span>
								</div>
							<?php endif; ?>
							<div class="portal-card__body">
								<p class="card-kicker"><?php echo esc_html( $achievement_featured_label ); ?></p>
								<h3 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<?php if ( '' !== $achievement_featured_excerpt ) : ?>
									<p class="card-excerpt card-excerpt--mobile"><?php echo esc_html( $achievement_featured_excerpt ); ?></p>
								<?php endif; ?>
								<p class="card-date"><?php echo esc_html( get_the_date( 'j F Y, H.i' ) ); ?></p>
							</div>
						</article>
					<?php endif; ?>

						<div class="portal-column__list">
							<?php foreach ( $achievement_list_posts as $achievement_post ) : ?>
								<?php
								$post = $achievement_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
								setup_postdata( $post );
								$achievement_label = $get_achievement_category_label();
								$achievement_excerpt = trim( wp_strip_all_tags( get_the_excerpt() ) );
								if ( '' === $achievement_excerpt ) {
									$achievement_excerpt = wp_trim_words( wp_strip_all_tags( get_the_content( null, false ) ), 30, '...' );
								}
								?>
								<article <?php post_class( 'portal-list-card' ); ?>>
									<?php if ( has_post_thumbnail() ) : ?>
										<a class="portal-list-card__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
											<?php the_post_thumbnail( 'thumbnail' ); ?>
										</a>
									<?php else : ?>
										<div class="portal-list-card__media portal-list-card__media--placeholder" aria-hidden="true"></div>
									<?php endif; ?>
									<div class="portal-list-card__body">
										<p class="card-kicker"><?php echo esc_html( $achievement_label ); ?></p>
										<h3 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
										<?php if ( '' !== $achievement_excerpt ) : ?>
											<p class="card-excerpt card-excerpt--mobile"><?php echo esc_html( $achievement_excerpt ); ?></p>
										<?php endif; ?>
										<p class="card-date"><?php echo esc_html( get_the_date( 'j F Y, H.i' ) ); ?></p>
									</div>
								</article>
							<?php endforeach; ?>
							<?php echo ugm_render_partial_skeleton_items( 'portal-list-card', $achievement_missing ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<?php echo ugm_render_empty_skeleton( 'portal-column', __( 'Belum ada konten prestasi.', 'ugm-faculty' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>

			<a class="section-arrow-link" href="<?php echo esc_url( $achievement_archive_link ); ?>" aria-label="<?php esc_attr_e( 'Lihat semua prestasi', 'ugm-faculty' ); ?>">&#8594;</a>
		</section>
		</div>

		<?php
		$featured_category_attrs = isset( $ugm_landing_block_attrs['ugm/featured-categories'] ) && is_array( $ugm_landing_block_attrs['ugm/featured-categories'] )
			? $ugm_landing_block_attrs['ugm/featured-categories']
			: array();
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ugm_render_featured_categories_markup( $featured_category_attrs );
		?>

		<?php
		$category_section_attrs = isset( $ugm_landing_block_attrs['ugm/category-section'] ) && is_array( $ugm_landing_block_attrs['ugm/category-section'] )
			? $ugm_landing_block_attrs['ugm/category-section']
			: array();
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ugm_render_category_section_markup( $category_section_attrs );
		?>

		<?php if ( false ) : ?>
		<section class="home-section section-faculty" aria-labelledby="section-faculty-title">
			<p class="section-overline section-overline--faculty"><?php esc_html_e( 'Seputar UGM', 'ugm-faculty' ); ?></p>
			<header class="section-header section-header--faculty">
				<h2 id="section-faculty-title" class="section-title">
					<?php echo esc_html( $ugm_title_faculty ); ?>
				</h2>
				<span class="section-line" aria-hidden="true"></span>
			</header>

			<?php
			$faculty_count = max( 0, min( 20, absint( get_theme_mod( 'ugm_faculty_item_count', 0 ) ) ) );
			$faculty_list  = array();

			for ( $faculty_index = 1; $faculty_index <= $faculty_count; $faculty_index++ ) {
				$faculty_name_setting = trim( (string) get_theme_mod( 'ugm_faculty_item_' . $faculty_index . '_name', '' ) );
				if ( '' === $faculty_name_setting ) {
					continue;
				}

				$faculty_image_value = get_theme_mod( 'ugm_faculty_item_' . $faculty_index . '_image', '' );
				$faculty_image_url   = '';

				if ( is_numeric( $faculty_image_value ) && (int) $faculty_image_value > 0 ) {
					$faculty_image_url = wp_get_attachment_image_url( (int) $faculty_image_value, 'large' );
				} elseif ( is_string( $faculty_image_value ) ) {
					$faculty_image_url = esc_url_raw( $faculty_image_value );
				}

				$faculty_list[] = array(
					'name'      => $faculty_name_setting,
					'image_url' => $faculty_image_url,
				);
			}

			$items_per_page = wp_is_mobile() ? 3 : 8;
			$total_pages    = ceil( count( $faculty_list ) / $items_per_page );
			?>
			<?php if ( ! empty( $faculty_list ) ) : ?>
				<div class="faculty-slider-wrapper">
					<button class="faculty-nav faculty-nav--prev" aria-label="<?php esc_attr_e( 'Previous page', 'ugm-faculty' ); ?>">&#8249;</button>
					<button class="faculty-nav faculty-nav--next" aria-label="<?php esc_attr_e( 'Next page', 'ugm-faculty' ); ?>">&#8250;</button>

					<div class="faculty-slider" role="list">
						<?php
						$page_index = 0;
						foreach ( $faculty_list as $index => $faculty_item ) :
							// Start new page every N items.
							if ( 0 === $index % $items_per_page ) :
								if ( $index > 0 ) :
									echo '</div>'; // Close previous page.
								endif;
								?>
								<div class="faculty-page" data-page="<?php echo esc_attr( $page_index ); ?>">
								<?php
								$page_index++;
							endif;
							?>
							<article class="faculty-card" role="listitem">
								<div class="faculty-card__image" aria-hidden="true"<?php if ( $faculty_item['image_url'] ) : ?> style="background-image: url('<?php echo esc_url( $faculty_item['image_url'] ); ?>');"<?php endif; ?>></div>
								<div class="faculty-card__overlay">
									<h3 class="faculty-card__title"><?php echo esc_html( $faculty_item['name'] ); ?></h3>
								</div>
							</article>
							<?php
						endforeach;
						echo '</div>'; // Close last page.
						?>
					</div>

					<div class="faculty-pagination" aria-hidden="true">
						<?php for ( $i = 0; $i < $total_pages; $i++ ) : ?>
							<span class="pagination-dot <?php echo 0 === $i ? 'active' : ''; ?>" data-page="<?php echo esc_attr( $i ); ?>"></span>
						<?php endfor; ?>
					</div>
				</div>
			<?php else : ?>
				<p class="section-empty"><?php esc_html_e( 'Belum ada data fakultas.', 'ugm-faculty' ); ?></p>
			<?php endif; ?>
		</section>
		<?php endif; ?>

		<?php
		$faculty_section_attrs = isset( $ugm_landing_block_attrs['ugm/faculty-section'] ) && is_array( $ugm_landing_block_attrs['ugm/faculty-section'] )
			? $ugm_landing_block_attrs['ugm/faculty-section']
			: array();
		if ( '' === trim( (string) ( $faculty_section_attrs['title'] ?? '' ) ) ) {
			$faculty_section_attrs['title'] = $ugm_title_faculty;
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ugm_render_block_faculty_section( $faculty_section_attrs );

		$agenda_section_attrs = isset( $ugm_landing_block_attrs['ugm/agenda-section'] ) && is_array( $ugm_landing_block_attrs['ugm/agenda-section'] )
			? $ugm_landing_block_attrs['ugm/agenda-section']
			: array();
		if ( '' === trim( (string) ( $agenda_section_attrs['title'] ?? '' ) ) ) {
			$agenda_section_attrs['title'] = $ugm_title_agenda;
		}
		if ( '' === trim( (string) ( $agenda_section_attrs['categorySlug'] ?? '' ) ) && '' !== $ugm_cat_agenda ) {
			$agenda_section_attrs['categorySlug'] = $ugm_cat_agenda;
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ugm_render_block_agenda_section( $agenda_section_attrs );
		?>

		<?php if ( false ) : ?>
		<section class="home-section section-campus-desktop" aria-label="<?php esc_attr_e( 'Agenda dan fasilitas kampus', 'ugm-faculty' ); ?>">
			<?php
			$agenda_posts_count  = max( 1, min( 6, absint( get_theme_mod( 'ugm_events_count', 3 ) ) ) );
			$agenda_archive_link = home_url( '/' );
			$agenda_term_ids     = $ugm_agenda_exclude_ids; // Sudah di-resolve di atas.

			if ( ! empty( $ugm_agenda_exclude_ids ) ) {
				// Ambil link archive dari term pertama yang sudah di-resolve.
				$agenda_first_id = reset( $ugm_agenda_exclude_ids );
				$agenda_link     = get_category_link( $agenda_first_id );
				if ( ! is_wp_error( $agenda_link ) ) {
					$agenda_archive_link = $agenda_link;
				}
			}

			$agenda_query_args = array(
				'post_type'           => 'post',
				'posts_per_page'      => $agenda_posts_count,
				'ignore_sticky_posts' => true,
				'post_status'         => 'publish',
				'meta_key'            => 'agenda_event_date',
				'orderby'             => array(
					'meta_value' => 'ASC',
					'date'       => 'DESC',
				),
				'order'               => 'ASC',
				'no_found_rows'       => true,
			);

			if ( ! empty( $agenda_term_ids ) ) {
				$agenda_query_args['category__in'] = $agenda_term_ids;
			}

			$agenda_query = new WP_Query( $agenda_query_args );
			?>
			<section class="section-campus-desktop__agenda" aria-labelledby="section-agenda-title">
				<header class="section-header section-header--desktop-agenda">
					<h2 id="section-agenda-title" class="section-title"><?php echo esc_html( $ugm_title_agenda ); ?></h2>
					<span class="section-line" aria-hidden="true"></span>
				</header>

				<?php if ( $agenda_query->have_posts() ) : ?>
					<div class="desktop-agenda-list">
						<?php while ( $agenda_query->have_posts() ) : ?>
							<?php
							$agenda_query->the_post();
							$agenda_timestamp = function_exists( 'ugm_get_agenda_event_timestamp' )
								? ugm_get_agenda_event_timestamp( get_the_ID() )
								: (int) get_post_timestamp( get_the_ID() );
							?>
							<article <?php post_class( 'desktop-agenda-card' ); ?>>
								<a class="desktop-agenda-card__date" href="<?php the_permalink(); ?>" aria-label="<?php esc_attr_e( 'Buka agenda', 'ugm-faculty' ); ?>">
									<span class="desktop-agenda-card__day"><?php echo esc_html( wp_date( 'd', $agenda_timestamp ) ); ?></span>
									<span class="desktop-agenda-card__month"><?php echo esc_html( wp_date( 'M', $agenda_timestamp ) ); ?></span>
								</a>
								<div class="desktop-agenda-card__body">
									<h3 class="desktop-agenda-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
									<p class="desktop-agenda-card__meta"><?php echo esc_html( get_the_author() ); ?></p>
								</div>
							</article>
						<?php endwhile; ?>
					</div>
				<?php else : ?>
					<p class="section-empty"><?php esc_html_e( 'Belum ada agenda kegiatan.', 'ugm-faculty' ); ?></p>
				<?php endif; ?>
				<?php wp_reset_postdata(); ?>

				<a class="desktop-agenda-arrow" href="<?php echo esc_url( $agenda_archive_link ); ?>" aria-label="<?php esc_attr_e( 'Lihat semua agenda kegiatan', 'ugm-faculty' ); ?>">&#8594;</a>
			</section>

			<?php
			$facility_root         = ugm_get_category_root_by_slugs( array( 'fasilitas-mahasiswa', 'fasilitas', 'sarana-prasarana', 'sarana', 'prasarana' ) );
			$facility_term_ids     = array();
			$facility_archive_link = home_url( '/' );

			if ( $facility_root ) {
				$facility_root_id     = (int) $facility_root->term_id;
				$facility_term_ids    = ugm_get_category_tree_ids( $facility_root_id );
				$facility_link        = get_category_link( $facility_root_id );
				if ( ! is_wp_error( $facility_link ) ) {
					$facility_archive_link = $facility_link;
				}
			}

			if ( ! empty( $facility_term_ids ) ) {
				// Kategori ditemukan → filter by category.
				$facility_query_args = array(
					'post_type'           => 'post',
					'posts_per_page'      => 4,
					'ignore_sticky_posts' => true,
					'post_status'         => 'publish',
					'orderby'             => 'date',
					'order'               => 'DESC',
					'no_found_rows'       => true,
					'category__in'        => $facility_term_ids,
				);
			} else {
				// Kategori fasilitas tidak ada di WordPress →
				// paksa hasil kosong agar tidak menampilkan semua post.
				$facility_query_args = array( 'post__in' => array( 0 ) );
			}

			$facility_query = new WP_Query( $facility_query_args );
			?>
			<section class="section-campus-desktop__facility" aria-labelledby="section-facility-title">
				<header class="section-header section-header--desktop-facility">
					<h2 id="section-facility-title" class="section-title"><?php esc_html_e( 'Fasilitas Mahasiswa', 'ugm-faculty' ); ?></h2>
					<span class="section-line" aria-hidden="true"></span>
					<a class="section-view-all section-view-all--desktop-facility" href="<?php echo esc_url( $facility_archive_link ); ?>" aria-label="<?php esc_attr_e( 'Lihat semua fasilitas mahasiswa', 'ugm-faculty' ); ?>">
						<?php esc_html_e( 'Lihat Semua', 'ugm-faculty' ); ?>
						<span aria-hidden="true">&rarr;</span>
					</a>
				</header>

				<?php if ( $facility_query->have_posts() ) : ?>
					<?php
					$facility_posts = $facility_query->posts;
					$facility_total = count( $facility_posts );
					$facility_grid_class = 'desktop-facility-grid';

					if ( $facility_total >= 4 ) {
						$facility_grid_class .= ' desktop-facility-grid--count-4';
					} elseif ( 3 === $facility_total ) {
						$facility_grid_class .= ' desktop-facility-grid--count-3';
					} elseif ( 2 === $facility_total ) {
						$facility_grid_class .= ' desktop-facility-grid--count-2';
					} else {
						$facility_grid_class .= ' desktop-facility-grid--count-1';
					}
					?>
					<div class="<?php echo esc_attr( $facility_grid_class ); ?>">
						<?php foreach ( $facility_posts as $facility_post ) : ?>
							<?php
							$post = $facility_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
							setup_postdata( $post );
							?>
							<article <?php post_class( 'desktop-facility-card' ); ?>>
								<a class="desktop-facility-card__link" href="<?php the_permalink(); ?>">
									<div class="desktop-facility-card__media"<?php if ( has_post_thumbnail() ) : ?> style="background-image: url('<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'large' ) ); ?>');"<?php endif; ?>>
										<?php if ( ! has_post_thumbnail() ) : ?>
											<span class="card-placeholder__text"><?php esc_html_e( 'Fasilitas', 'ugm-faculty' ); ?></span>
										<?php endif; ?>
										<h3 class="desktop-facility-card__title"><?php the_title(); ?></h3>
									</div>
								</a>
							</article>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<p class="section-empty"><?php esc_html_e( 'Belum ada konten fasilitas.', 'ugm-faculty' ); ?></p>
				<?php endif; ?>
				<?php wp_reset_postdata(); ?>
			</section>
		</section>
		<?php endif; ?>

		<?php get_template_part( 'template-parts/section-majalah' ); ?>

		<?php
		// ── Video Section ──────────────────────────────────────────────────────
		$ugm_video_title    = $ugm_get_block_attr( 'ugm/video-section', 'title' );
		$ugm_video_cat_slug = $ugm_get_block_attr( 'ugm/video-section', 'categorySlug' );
		if ( '' === $ugm_video_title ) {
			$ugm_video_title = __( 'Video', 'ugm-faculty' );
		}
		if ( '' === $ugm_video_cat_slug ) {
			$ugm_video_cat_slug = 'video';
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ugm_render_block_video_section( array(
			'title'        => $ugm_video_title,
			'categorySlug' => $ugm_video_cat_slug,
		) );
		?>
	</div>
</main>
<?php
get_footer();
