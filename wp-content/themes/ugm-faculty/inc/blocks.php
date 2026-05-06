<?php
/**
 * Custom Server-Side-Rendered blocks for the Landing Page template.
 *
 * Each section of the landing page is registered as a Gutenberg block with
 * a PHP render callback, allowing WordPress Full Site Editor to preview and
 * rearrange sections while keeping all dynamic content queries server-side.
 *
 * Blocks registered:
 *  - ugm/site-header       — theme header (for template parts)
 *  - ugm/site-footer       — theme footer (for template parts)
 *  - ugm/latest-news       — Berita Terbaru section
 *  - ugm/academic-news     — Berita Akademik section
 *  - ugm/profile-section   — Profile section
 *  - ugm/achievement-section — Prestasi section
 *  - ugm/facility-section  — Fasilitas section
 *  - ugm/faculty-section   — Fakultas dan Sekolah section
 *  - ugm/agenda-section    — Agenda Kegiatan section
 *  - ugm/magazine-section  — Majalah Digital section
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper: enqueue theme CSS for SSR block previews in the editor REST requests.
 * Called once per page load, not per block.
 */
function ugm_blocks_maybe_enqueue_styles() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;
	// Ensure block previews inherit theme styles inside the Site Editor iframe.
	if ( function_exists( 'wp_enqueue_style' ) ) {
		wp_enqueue_style( 'ugm-style' );
	}
}

/* --------------------------------------------------------------------------
 * Section helper shared across blocks
 * -------------------------------------------------------------------------- */

/**
 * Build a section header HTML string.
 *
 * @param string $title      Section title.
 * @param string $id         Heading element ID.
 * @param string $view_url   Optional "View all" URL.
 * @param string $view_label Optional "View all" aria-label.
 * @return string
 */
function ugm_block_section_header( $title, $id, $view_url = '', $view_label = '' ) {
	$out  = '<header class="section-header">';
	$out .= '<h2 id="' . esc_attr( $id ) . '" class="section-title">' . esc_html( $title ) . '</h2>';
	$out .= '<span class="section-line" aria-hidden="true"></span>';
	if ( '' !== $view_url ) {
		$out .= '<a class="section-view-all" href="' . esc_url( $view_url ) . '" aria-label="' . esc_attr( $view_label ) . '">';
		$out .= esc_html__( 'Lihat Semua', 'ugm-faculty' ) . ' <span aria-hidden="true">&rarr;</span>';
		$out .= '</a>';
	}
	$out .= '</header>';
	return $out;
}

/**
 * Find the first existing category term from a list of candidate slugs.
 *
 * @param string[] $slugs Candidate category slugs.
 * @return WP_Term|null
 */
function ugm_get_category_by_candidate_slugs( array $slugs ) {
	foreach ( $slugs as $slug ) {
		$term = get_category_by_slug( sanitize_title( $slug ) );
		if ( $term instanceof WP_Term ) {
			return $term;
		}
	}

	return null;
}

/**
 * Render the "featured category columns" section shared by template and block preview.
 *
 * @param array $attrs Block attributes.
 * @return string
 */
function ugm_render_featured_categories_markup( $attrs = array() ) {
	$defaults = array(
		'campusTitle'       => __( 'Seputar Kampus', 'ugm-faculty' ),
		'campusCategorySlug' => 'seputar-kampus',
		'facultyTitle'      => __( 'Kabar Fakultas', 'ugm-faculty' ),
		'facultyCategorySlug' => 'kabar-fakultas',
		'partnershipTitle'  => __( 'Kerjasama', 'ugm-faculty' ),
		'partnershipCategorySlug' => 'kerjasama',
	);
	$attrs = wp_parse_args( is_array( $attrs ) ? $attrs : array(), $defaults );

	$sections = array(
		array(
			'title'      => trim( (string) $attrs['campusTitle'] ) ?: $defaults['campusTitle'],
			'slugs'      => array_filter( array( trim( (string) $attrs['campusCategorySlug'] ) ) ),
			'empty_text' => __( 'Belum ada artikel seputar kampus.', 'ugm-faculty' ),
			'fallback'   => home_url( '/category/' ),
		),
		array(
			'title'      => trim( (string) $attrs['facultyTitle'] ) ?: $defaults['facultyTitle'],
			'slugs'      => array_filter( array( trim( (string) $attrs['facultyCategorySlug'] ) ) ),
			'empty_text' => __( 'Belum ada kabar fakultas.', 'ugm-faculty' ),
			'fallback'   => home_url( '/category/' ),
		),
		array(
			'title'      => trim( (string) $attrs['partnershipTitle'] ) ?: $defaults['partnershipTitle'],
			'slugs'      => array_filter( array( trim( (string) $attrs['partnershipCategorySlug'] ) ) ),
			'empty_text' => __( 'Belum ada artikel kerjasama.', 'ugm-faculty' ),
			'fallback'   => home_url( '/category/' ),
		),
	);

	ob_start();
	?>
	<section class="home-section section-featured-categories" aria-label="<?php esc_attr_e( 'Sorotan kategori', 'ugm-faculty' ); ?>">
		<div class="featured-category-grid">
			<?php foreach ( $sections as $section ) : ?>
				<?php
				$term = ugm_get_category_by_candidate_slugs( $section['slugs'] );
				$link = $term instanceof WP_Term ? get_category_link( $term->term_id ) : $section['fallback'];
				$args = array(
					'post_type'           => 'post',
					'posts_per_page'      => 1,
					'ignore_sticky_posts' => true,
					'post_status'         => 'publish',
					'no_found_rows'       => true,
				);

				if ( $term instanceof WP_Term ) {
					$args['cat'] = (int) $term->term_id;
				} else {
					$args['post__in'] = array( 0 );
				}

				$q = new WP_Query( $args );
				?>
				<section class="featured-category-column" aria-label="<?php echo esc_attr( $section['title'] ); ?>">
					<header class="section-header">
						<h2 class="section-title"><?php echo esc_html( $section['title'] ); ?></h2>
						<span class="section-line" aria-hidden="true"></span>
					</header>

					<?php if ( $q->have_posts() ) : ?>
						<?php
						$q->the_post();
						$has_thumb = has_post_thumbnail();
						?>
						<article <?php post_class( 'featured-category-card' ); ?>>
							<a class="featured-category-card__link" href="<?php the_permalink(); ?>">
								<div class="featured-category-card__media"<?php echo $has_thumb ? '' : ' aria-hidden="true"'; ?>>
									<?php if ( $has_thumb ) : ?>
										<?php the_post_thumbnail( 'large' ); ?>
									<?php else : ?>
										<div class="featured-category-card__placeholder">
											<span class="card-placeholder__text"><?php echo esc_html( $section['title'] ); ?></span>
										</div>
									<?php endif; ?>
								</div>
								<div class="featured-category-card__overlay">
									<h3 class="featured-category-card__title"><?php the_title(); ?></h3>
									<p class="featured-category-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
									<p class="featured-category-card__meta"><?php echo esc_html( get_the_date( 'j F Y, H.i' ) ); ?></p>
									<span class="featured-category-card__cta"><?php esc_html_e( 'Cari tahu lebih lanjut', 'ugm-faculty' ); ?></span>
								</div>
							</a>
						</article>
					<?php else : ?>
						<?php echo ugm_render_empty_skeleton( 'featured-category', $section['empty_text'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endif; ?>
					<?php wp_reset_postdata(); ?>

					<a class="section-arrow-link" href="<?php echo esc_url( $link ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Lihat semua artikel %s', 'ugm-faculty' ), $section['title'] ) ); ?>">&#8594;</a>
				</section>
			<?php endforeach; ?>
		</div>
	</section>
	<?php

	return ob_get_clean();
}

/**
 * Render category section markup shared by template and block preview.
 *
 * @param array $attrs Block attributes.
 * @return string
 */
function ugm_render_category_section_markup( $attrs = array() ) {
	$title = isset( $attrs['title'] ) && '' !== trim( (string) $attrs['title'] )
		? trim( (string) $attrs['title'] )
		: __( 'Kategori', 'ugm-faculty' );

	$top_level_category_terms = get_categories(
		array(
			'hide_empty' => true,
			'parent'     => 0,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);
	$category_preview_count = 9;
	$category_terms         = array_slice( $top_level_category_terms, 0, $category_preview_count );
	$category_remaining     = array_slice( $top_level_category_terms, count( $category_terms ) );
	$category_archive_link = home_url( '/category/' );
	$category_missing      = max( 0, $category_preview_count - count( $category_terms ) );

	ob_start();
	?>
	<section class="home-section section-category" aria-labelledby="section-category-title">
		<header class="section-header">
			<h2 id="section-category-title" class="section-title"><?php echo esc_html( $title ); ?></h2>
			<span class="section-line" aria-hidden="true"></span>
		</header>

		<?php if ( ! empty( $top_level_category_terms ) ) : ?>
			<details class="category-mobile-list">
				<summary class="category-mobile-list__trigger">
					<span class="category-mobile-list__label"><?php esc_html_e( 'Kategori Lists', 'ugm-faculty' ); ?></span>
					<span class="category-mobile-list__icon" aria-hidden="true">
						<span></span>
						<span></span>
						<span></span>
					</span>
				</summary>
				<ul class="category-mobile-list__menu">
					<?php foreach ( $top_level_category_terms as $category_term ) : ?>
						<?php
						$descendant_terms = get_terms(
							array(
								'taxonomy'   => 'category',
								'hide_empty' => true,
								'child_of'   => (int) $category_term->term_id,
								'orderby'    => 'name',
								'order'      => 'ASC',
							)
						);
						if ( is_wp_error( $descendant_terms ) ) {
							$descendant_terms = array();
						}

						$parent_total_count = (int) $category_term->count;
						if ( ! empty( $descendant_terms ) ) {
							foreach ( $descendant_terms as $descendant_term ) {
								$parent_total_count += (int) $descendant_term->count;
							}
						}
						?>
						<li class="category-mobile-group">
							<?php if ( ! empty( $descendant_terms ) ) : ?>
								<details class="category-mobile-group__details">
									<summary class="category-mobile-group__summary">
										<span class="category-mobile-group__meta">
											<span class="category-mobile-list__name"><?php echo esc_html( $category_term->name ); ?></span>
											<span class="category-mobile-list__count">
												<?php
												printf(
													esc_html__( '%s Artikel Total', 'ugm-faculty' ),
													esc_html( number_format_i18n( $parent_total_count ) )
												);
												?>
											</span>
										</span>
										<span class="category-mobile-group__chevron" aria-hidden="true"></span>
									</summary>

									<ul class="category-mobile-group__children">
										<?php foreach ( $descendant_terms as $descendant_term ) : ?>
											<li>
												<a href="<?php echo esc_url( get_category_link( $descendant_term->term_id ) ); ?>">
													<span class="category-mobile-list__name"><?php echo esc_html( $descendant_term->name ); ?></span>
													<span class="category-mobile-list__count">
														<?php
														printf(
															esc_html__( '%s Artikel', 'ugm-faculty' ),
															esc_html( number_format_i18n( (int) $descendant_term->count ) )
														);
														?>
													</span>
												</a>
											</li>
										<?php endforeach; ?>
									</ul>
								</details>
							<?php else : ?>
								<a class="category-mobile-group__link" href="<?php echo esc_url( get_category_link( $category_term->term_id ) ); ?>">
									<span class="category-mobile-group__meta">
										<span class="category-mobile-list__name"><?php echo esc_html( $category_term->name ); ?></span>
										<span class="category-mobile-list__count">
											<?php
											printf(
												esc_html__( '%s Artikel Total', 'ugm-faculty' ),
												esc_html( number_format_i18n( (int) $category_term->count ) )
											);
											?>
										</span>
									</span>
								</a>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</details>

			<div class="category-grid">
				<?php foreach ( $category_terms as $category_term ) : ?>
					<a class="category-card" href="<?php echo esc_url( get_category_link( $category_term->term_id ) ); ?>">
						<span class="category-card__label"><?php echo esc_html( $category_term->name ); ?></span>
						<span class="category-card__count">
							<?php
							printf(
								esc_html__( '%s Artikel Total', 'ugm-faculty' ),
								esc_html( number_format_i18n( (int) $category_term->count ) )
							);
							?>
						</span>
					</a>
				<?php endforeach; ?>
				<?php echo ugm_render_partial_skeleton_items( 'category-card', $category_missing ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>

			<?php if ( ! empty( $category_remaining ) ) : ?>
				<details class="category-desktop-dropdown">
					<summary class="section-view-all section-view-all--category" aria-label="<?php esc_attr_e( 'Lihat semua kategori', 'ugm-faculty' ); ?>">
						<?php esc_html_e( 'Lihat Semua', 'ugm-faculty' ); ?>
						<span class="category-desktop-dropdown__arrow" aria-hidden="true">&rarr;</span>
					</summary>
					<div class="category-desktop-dropdown__panel">
						<div class="category-desktop-dropdown__grid">
							<?php foreach ( $category_remaining as $category_term ) : ?>
								<a class="category-card category-card--dropdown" href="<?php echo esc_url( get_category_link( $category_term->term_id ) ); ?>">
									<span class="category-card__label"><?php echo esc_html( $category_term->name ); ?></span>
									<span class="category-card__count">
										<?php
										printf(
											esc_html__( '%s Artikel Total', 'ugm-faculty' ),
											esc_html( number_format_i18n( (int) $category_term->count ) )
										);
										?>
									</span>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				</details>
			<?php endif; ?>
		<?php else : ?>
			<?php echo ugm_render_empty_skeleton( 'category-grid', __( 'Belum ada kategori.', 'ugm-faculty' ), array( 'count' => 6 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endif; ?>
	</section>
	<?php

	return ob_get_clean();
}

/* ==========================================================================
 * 1. ugm/site-header
 * ========================================================================== */

function ugm_render_block_site_header( $attrs ) {
	// Single ob_start/ob_get_clean — everything echoed into one buffer.
	// IMPORTANT: do NOT use nested ob_start() without ob_get_clean(), and do NOT
	// return a manually-built string while a buffer is still open — that leaks
	// extra output into the REST JSON response making it invalid.
	ob_start();

	echo '<header id="masthead" class="site-header is-solid" style="position:relative;top:auto;">';
	echo '<div class="site-header__inner">';
	echo '<div class="site-header__branding">';
	get_template_part( 'template-parts/header/site-branding' );
	echo '</div>';
	echo '<nav class="site-header__nav">';
	wp_nav_menu( array(
		'theme_location' => 'menu-1',
		'menu_class'     => 'primary-menu',
		'container'      => false,
		'fallback_cb'    => false,
		'depth'          => 2,
	) );
	echo '</nav>';
	echo '</div></header>';

	return ob_get_clean();
}

register_block_type( 'ugm/site-header', array(
	'title'           => __( 'Header Situs', 'ugm-faculty' ),
	'description'     => __( 'Menampilkan header tema UGM Faculty.', 'ugm-faculty' ),
	'category'        => 'theme',
	'render_callback' => 'ugm_render_block_site_header',
	'supports'        => array( 'html' => false ),
) );

/* ==========================================================================
 * 2. ugm/hero-section — Landing Page Hero
 * ========================================================================== */

/**
 * Render the hero section block.
 *
 * Attributes:
 *  - imageId   (int)    — WP Media library attachment ID for background image.
 *  - imageUrl  (string) — Direct URL fallback (if imageId is 0).
 *  - title     (string) — Hero headline (supports newline for line breaks).
 *  - description (string) — Hero sub-description text.
 */
function ugm_render_block_hero_section( $attrs ) {
	$image_id  = isset( $attrs['imageId'] ) ? absint( $attrs['imageId'] ) : 0;
	$image_url = '';

	if ( $image_id > 0 ) {
		$image_url = (string) wp_get_attachment_image_url( $image_id, 'full' );
	} elseif ( ! empty( $attrs['imageUrl'] ) ) {
		$image_url = esc_url_raw( (string) $attrs['imageUrl'] );
	}

	// Fall back: theme asset bundled image.
	if ( '' === $image_url ) {
		$asset_path = get_theme_file_path( 'assets/images/landing page UGM.png' );
		if ( file_exists( $asset_path ) ) {
			$image_url = str_replace( ' ', '%20', get_theme_file_uri( 'assets/images/landing page UGM.png' ) );
		}
		// Also try Customizer.
		if ( '' === $image_url ) {
			$cust_val = get_theme_mod( 'ugm_hero_background_image' );
			if ( is_numeric( $cust_val ) && (int) $cust_val > 0 ) {
				$image_url = (string) wp_get_attachment_image_url( (int) $cust_val, 'full' );
			} elseif ( is_string( $cust_val ) ) {
				$image_url = esc_url_raw( $cust_val );
			}
		}
	}

	$default_title = "UNIVERSITAS\nGADJAH MADA";
	$default_desc  = 'Sebagai universitas nasional pertama di Indonesia, UGM telah menjadi pusat pendidikan, penelitian, dan pengabdian masyarakat sejak berdiri tahun 1949, melahirkan ribuan alumni yang berkiprah di berbagai bidang untuk bangsa dan dunia.';

	$title = isset( $attrs['title'] ) && '' !== $attrs['title']
		? $attrs['title']
		: trim( (string) get_theme_mod( 'ugm_hero_headline', $default_title ) );
	if ( '' === $title ) {
		$title = $default_title;
	}

	$desc = isset( $attrs['description'] ) && '' !== $attrs['description']
		? $attrs['description']
		: trim( (string) get_theme_mod( 'ugm_hero_description', $default_desc ) );
	if ( '' === $desc ) {
		$desc = $default_desc;
	}

	ob_start();
	?>
	<section class="hero" <?php echo $image_url ? 'style="background-image: url(' . esc_url( $image_url ) . ');"' : ''; ?> aria-labelledby="hero-title">
		<div class="hero__overlay" aria-hidden="true"></div>
		<div class="hero__content">
			<?php if ( $title ) : ?>
				<h1 id="hero-title" class="hero__title"><?php echo wp_kses_post( nl2br( esc_html( $title ), false ) ); ?></h1>
			<?php endif; ?>
			<?php if ( $desc ) : ?>
				<p class="hero__description"><?php echo esc_html( $desc ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

register_block_type( 'ugm/hero-section', array(
	'title'           => __( 'Hero Section', 'ugm-faculty' ),
	'description'     => __( 'Bagian hero landing page: gambar latar, judul, dan deskripsi.', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_hero_section',
	'supports'        => array( 'html' => false ),
	'attributes'      => array(
		'imageId'     => array( 'type' => 'integer', 'default' => 0 ),
		'imageUrl'    => array( 'type' => 'string',  'default' => '' ),
		'title'       => array( 'type' => 'string',  'default' => '' ),
		'description' => array( 'type' => 'string',  'default' => '' ),
	),
) );

/* ==========================================================================
 * 3. ugm/site-footer — Theme Footer
 * ========================================================================== */

function ugm_render_block_site_footer( $attrs ) {
	ob_start();
	// Render the <footer> element using the widgetized footer output.
	?><footer id="colophon" class="site-footer ugm-footer" aria-labelledby="footer-block-title">
		<h2 id="footer-block-title" class="screen-reader-text"><?php esc_html_e( 'Footer Information', 'ugm-faculty' ); ?></h2>
		<?php
		$areas = array(
			'footer-social-widget', 'footer-brand-widget', 'footer-contact-widget',
			'footer-nav-widget', 'footer-accreditation-widget',
			'footer-quick-links-widget', 'footer-institutional-widget',
		);
		$has_top = false;
		foreach ( $areas as $area ) {
			if ( is_active_sidebar( $area ) ) { $has_top = true; break; }
		}
		if ( $has_top ) {
			echo '<div class="ugm-footer__top"><div class="ugm-footer__container">';
			foreach ( $areas as $area ) {
				if ( is_active_sidebar( $area ) ) {
					echo '<div class="ugm-footer__wrap">';
					dynamic_sidebar( $area );
					echo '</div>';
				}
			}
			echo '</div></div>';
		}
		?>
	</footer><?php
	return ob_get_clean();
}

register_block_type( 'ugm/site-footer', array(
	'title'           => __( 'Footer Situs', 'ugm-faculty' ),
	'description'     => __( 'Menampilkan footer tema UGM Faculty.', 'ugm-faculty' ),
	'category'        => 'theme',
	'render_callback' => 'ugm_render_block_site_footer',
	'supports'        => array( 'html' => false ),
) );

/* ==========================================================================
 * Common attributes shared by content sections
 * ========================================================================== */

$ugm_section_attrs = array(
	'title'        => array(
		'type'    => 'string',
		'default' => '',
	),
	'categorySlug' => array(
		'type'    => 'string',
		'default' => '',
	),
);

/* ==========================================================================
 * 3. ugm/latest-news — Berita Terbaru
 * ========================================================================== */

function ugm_render_block_latest_news( $attrs ) {
	$title    = isset( $attrs['title'] ) && '' !== $attrs['title']
		? $attrs['title']
		: get_theme_mod( 'ugm_latest_section_title', __( 'Berita Terbaru', 'ugm-faculty' ) );
	$cat_slug = isset( $attrs['categorySlug'] ) ? sanitize_key( $attrs['categorySlug'] ) : '';

	$count = max( 1, absint( get_theme_mod( 'ugm_latest_news_count', 4 ) ) );
	$args  = array(
		'post_type'           => 'post',
		'posts_per_page'      => $count,
		'ignore_sticky_posts' => true,
		'post_status'         => 'publish',
		'orderby'             => 'date',
		'order'               => 'DESC',
		'no_found_rows'       => true,
	);

	if ( '' !== $cat_slug ) {
		// Explicit category override on the block itself.
		$cat = get_category_by_slug( $cat_slug );
		if ( $cat ) {
			$args['category__in'] = ugm_get_category_tree_ids( (int) $cat->term_id );
		} else {
			$args['post__in'] = array( 0 );
		}
	} else {
		// ── Whitelist: hanya tampilkan post dari kategori Berita Akademik,
		//    Profile, dan Prestasi (baca dari blok di halaman yang sama).
		$whitelist_ids = ugm_collect_news_whitelist_category_ids();
		if ( ! empty( $whitelist_ids ) ) {
			$args['category__in'] = $whitelist_ids;
		}
		// Jika whitelist kosong (tidak ada blok tsb di halaman), tampilkan semua
		// kecuali agenda & fakultas (backward compat).
		if ( empty( $whitelist_ids ) ) {
			$exclude = array_unique( array_merge(
				ugm_resolve_agenda_exclude_ids( '' ),
				ugm_resolve_faculty_exclude_ids( '' )
			) );
			if ( ! empty( $exclude ) ) {
				$args['category__not_in'] = $exclude;
			}
		}
	}

	$q = new WP_Query( $args );

	$archive_link = add_query_arg( 'ugm_latest_news', '1', home_url( '/' ) );

	ob_start();
	echo '<section class="home-section section-news" aria-labelledby="block-news-title">';
	echo ugm_block_section_header( $title, 'block-news-title', $archive_link, __( 'Lihat semua berita terbaru', 'ugm-faculty' ) ); // phpcs:ignore
	ugm_render_latest_news_grid( $q );
	echo '</section>';

	wp_reset_postdata();
	return ob_get_clean();
}

/**
 * Collect category IDs from ugm/academic-news, ugm/profile-section, and
 * ugm/achievement-section blocks on the current page.
 *
 * These are the only categories that should appear in "Berita Terkini".
 * Falls back to default slugs (pendidikan, profile, prestasi) if the blocks
 * are not found on the current page.
 *
 * @return int[]
 */
function ugm_collect_news_whitelist_category_ids() {
	// Resolve current page.
	$page_id = (int) get_the_ID();
	if ( $page_id <= 0 ) {
		$page_id = (int) get_queried_object_id();
	}

	// Default fallback slugs when page cannot be determined.
	$defaults = array(
		'ugm/academic-news'       => 'pendidikan',
		'ugm/profile-section'     => 'profile',
		'ugm/achievement-section' => 'prestasi',
	);

	if ( $page_id <= 0 ) {
		return ugm_resolve_multiple_slugs_to_ids( array_values( $defaults ) );
	}

	$post = get_post( $page_id );
	if ( ! $post ) {
		return ugm_resolve_multiple_slugs_to_ids( array_values( $defaults ) );
	}

	$blocks      = parse_blocks( $post->post_content );
	$target_names = array_keys( $defaults );
	$slugs_found  = array();
	$found_any    = false;

	foreach ( $blocks as $block ) {
		$name = $block['blockName'] ?? null;
		if ( ! in_array( $name, $target_names, true ) ) {
			continue;
		}
		$found_any  = true;
		$attrs      = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
		$slug       = isset( $attrs['categorySlug'] ) && '' !== $attrs['categorySlug']
			? sanitize_key( $attrs['categorySlug'] )
			: $defaults[ $name ];
		$slugs_found[] = $slug;
	}

	if ( ! $found_any ) {
		// Page has no academic/profile/achievement blocks — use defaults.
		return ugm_resolve_multiple_slugs_to_ids( array_values( $defaults ) );
	}

	return ugm_resolve_multiple_slugs_to_ids( $slugs_found );
}

/**
 * Resolve an array of category slugs (with -2/-3 fallback) to term IDs.
 *
 * @param string[] $slugs
 * @return int[]
 */
function ugm_resolve_multiple_slugs_to_ids( $slugs ) {
	$ids = array();
	foreach ( $slugs as $slug ) {
		$resolved = ugm_resolve_slug_to_category_ids( $slug );
		$ids      = array_merge( $ids, $resolved );
	}
	return array_values( array_unique( array_filter( $ids ) ) );
}

/**
 * Resolve a single category slug (with -2/-3 fallback) to an array of term IDs.
 *
 * @param string $slug Category slug (may be empty).
 * @return int[]
 */
function ugm_resolve_slug_to_category_ids( $slug ) {
	if ( '' === $slug ) {
		return array();
	}
	// Try exact slug, then WordPress-suffixed variants (-2, -3).
	foreach ( array( $slug, $slug . '-2', $slug . '-3' ) as $try ) {
		$cat = get_category_by_slug( sanitize_key( $try ) );
		if ( $cat ) {
			return ugm_get_category_tree_ids( (int) $cat->term_id );
		}
	}
	return array();
}

/**
 * Render the news grid layout (featured + list).
 *
 * Extracted so it can be used from both the block render callback and the
 * classic PHP template.
 *
 * @param WP_Query $q
 */
function ugm_render_latest_news_grid( $q ) {
	if ( ! $q->have_posts() ) {
		echo ugm_render_empty_skeleton( 'latest-news', __( 'Belum ada berita terbaru.', 'ugm-faculty' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return;
	}

	$posts         = $q->posts;
	$featured      = array_shift( $posts );
	$side          = array_shift( $posts );
	$list_posts    = array_slice( $posts, 0, 2 );
	$side_items    = array_merge( $side ? array( $side ) : array(), $list_posts );
	$side_missing  = max( 0, 3 - count( $side_items ) );

	$get_label = function () {
		$cats       = get_the_category();
		$default_id = (int) get_option( 'default_category' );
		$label      = ''; // Kosong jika tidak ada kategori non-default.
		foreach ( $cats as $cat ) {
			if ( $default_id !== (int) $cat->term_id && 'uncategorized' !== $cat->slug ) {
				$label = $cat->name;
				break;
			}
		}
		return $label;
	};

	echo '<div class="news-layout">';

	// Featured post.
	if ( $featured instanceof WP_Post ) {
		$post            = $featured; // phpcs:ignore
		$GLOBALS['post'] = $post;     // Required: setup_postdata alone does not set the global post.
		setup_postdata( $post );
		$excerpt = trim( wp_strip_all_tags( get_the_excerpt() ) );
		if ( '' === $excerpt ) {
			$excerpt = wp_trim_words( wp_strip_all_tags( get_the_content( null, false ) ), 30, '...' );
		}
		echo '<div class="news-area news-area--featured">';
		echo '<article class="' . esc_attr( implode( ' ', get_post_class( 'news-featured' ) ) ) . '">';
		if ( has_post_thumbnail() ) {
			echo '<a class="news-featured__media" href="' . esc_url( get_the_permalink() ) . '" aria-hidden="true" tabindex="-1">' . get_the_post_thumbnail( null, 'large' ) . '</a>';
		} else {
			echo '<div class="news-featured__media news-featured__media--placeholder" aria-hidden="true"></div>';
		}
		echo '<div class="news-featured__body">';
		$_label = $get_label(); if ( '' !== $_label ) { echo '<p class="card-kicker">' . esc_html( $_label ) . '</p>'; }
		echo '<h3 class="card-title"><a href="' . esc_url( get_the_permalink() ) . '">' . get_the_title() . '</a></h3>';
		echo '<p class="card-date">' . esc_html( get_the_date( 'j F Y, H.i' ) ) . '</p>';
		if ( '' !== $excerpt ) {
			echo '<p class="news-featured__excerpt">' . esc_html( $excerpt ) . '</p>';
		}
		echo '</div></article></div>';
	}

	// Side + list posts.
	echo '<div class="news-area news-area--list news-list berita-list">';
	if ( ! empty( $side_items ) ) {
		foreach ( $side_items as $item ) {
			$post            = $item; // phpcs:ignore
			$GLOBALS['post'] = $post; // Required: setup_postdata alone does not set the global post.
			setup_postdata( $post );
			echo '<article class="' . esc_attr( implode( ' ', get_post_class( 'portal-list-card' ) ) ) . '">';
			if ( has_post_thumbnail() ) {
				echo '<a class="portal-list-card__media" href="' . esc_url( get_the_permalink() ) . '" aria-hidden="true" tabindex="-1">' . get_the_post_thumbnail( null, 'thumbnail' ) . '</a>';
			} else {
				echo '<div class="portal-list-card__media portal-list-card__media--placeholder" aria-hidden="true"></div>';
			}
			echo '<div class="portal-list-card__body">';
			$_label = $get_label(); if ( '' !== $_label ) { echo '<p class="card-kicker">' . esc_html( $_label ) . '</p>'; }
			echo '<h3 class="card-title"><a href="' . esc_url( get_the_permalink() ) . '">' . get_the_title() . '</a></h3>';
			echo '<p class="card-date">' . esc_html( get_the_date( 'j F Y, H.i' ) ) . '</p>';
			echo '</div></article>';
		}
	}
	if ( $side_missing > 0 ) {
		echo ugm_render_partial_skeleton_items( 'latest-news-card', $side_missing ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</div>';

	echo '</div>'; // .news-layout
}

register_block_type( 'ugm/latest-news', array(
	'title'           => __( 'Berita Terbaru', 'ugm-faculty' ),
	'description'     => __( 'Menampilkan berita terbaru pada landing page.', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_latest_news',
	'supports'        => array( 'html' => false ),
	'attributes'      => $ugm_section_attrs,
) );

/* ==========================================================================
 * 4. ugm/academic-news — Berita Akademik
 * ========================================================================== */

function ugm_render_block_academic_news( $attrs ) {
	$title    = isset( $attrs['title'] ) && '' !== $attrs['title']
		? $attrs['title']
		: get_theme_mod( 'ugm_academic_section_title', __( 'Berita Akademik', 'ugm-faculty' ) );
	$cat_slug = isset( $attrs['categorySlug'] ) && '' !== $attrs['categorySlug']
		? sanitize_key( $attrs['categorySlug'] ) : 'pendidikan';

	$cat     = get_category_by_slug( $cat_slug );
	$cat_id  = $cat ? (int) $cat->term_id : 0;
	$archive = $cat_id > 0 ? get_category_link( $cat_id ) : home_url( '/category/' . $cat_slug . '/' );
	$count   = max( 1, absint( get_theme_mod( 'ugm_academic_news_count', 3 ) ) );

	$args = $cat_id > 0
		? array(
			'post_type'           => 'post',
			'posts_per_page'      => $count,
			'category__in'        => ugm_get_category_tree_ids( $cat_id ),
			'ignore_sticky_posts' => true,
			'post_status'         => 'publish',
			'orderby'             => 'date',
			'order'               => 'DESC',
			'no_found_rows'       => true,
		)
		: array( 'post__in' => array( 0 ) );

	ob_start();
	$q = new WP_Query( $args );
	echo '<section class="home-section section-academic" aria-labelledby="block-academic-title">';
	echo ugm_block_section_header( $title, 'block-academic-title' ); // phpcs:ignore
	ugm_render_portal_column( $q, __( 'Belum ada berita akademik.', 'ugm-faculty' ) );
	echo '<a class="section-arrow-link" href="' . esc_url( $archive ) . '" aria-label="' . esc_attr__( 'Lihat semua berita akademik', 'ugm-faculty' ) . '">&#8594;</a>';
	echo '</section>';
	wp_reset_postdata();
	return ob_get_clean();
}

register_block_type( 'ugm/academic-news', array(
	'title'           => __( 'Berita Akademik', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_academic_news',
	'supports'        => array( 'html' => false ),
	'attributes'      => $ugm_section_attrs,
) );

/* ==========================================================================
 * 5. ugm/profile-section — Profile
 * ========================================================================== */

function ugm_render_block_profile_section( $attrs ) {
	$title    = isset( $attrs['title'] ) && '' !== $attrs['title']
		? $attrs['title']
		: get_theme_mod( 'ugm_profile_section_title', __( 'Profile', 'ugm-faculty' ) );
	$cat_slug = isset( $attrs['categorySlug'] ) && '' !== $attrs['categorySlug']
		? sanitize_key( $attrs['categorySlug'] ) : 'profile';

	$cat     = get_category_by_slug( $cat_slug );
	$cat_id  = $cat ? (int) $cat->term_id : 0;
	$archive = $cat_id > 0 ? get_category_link( $cat_id ) : home_url( '/category/' . $cat_slug . '/' );
	$count   = max( 1, absint( get_theme_mod( 'ugm_profile_news_count', 3 ) ) );

	$args = $cat_id > 0
		? array(
			'post_type'           => 'post',
			'posts_per_page'      => $count,
			'category__in'        => ugm_get_category_tree_ids( $cat_id ),
			'ignore_sticky_posts' => true,
			'post_status'         => 'publish',
			'orderby'             => 'date',
			'order'               => 'DESC',
			'no_found_rows'       => true,
		)
		: array( 'post__in' => array( 0 ) );

	ob_start();
	$q = new WP_Query( $args );
	echo '<section class="home-section section-profile" aria-labelledby="block-profile-title">';
	echo ugm_block_section_header( $title, 'block-profile-title' ); // phpcs:ignore
	ugm_render_portal_column( $q, __( 'Belum ada konten profile.', 'ugm-faculty' ) );
	echo '<a class="section-arrow-link" href="' . esc_url( $archive ) . '" aria-label="' . esc_attr__( 'Lihat semua profile', 'ugm-faculty' ) . '">&#8594;</a>';
	echo '</section>';
	wp_reset_postdata();
	return ob_get_clean();
}

register_block_type( 'ugm/profile-section', array(
	'title'           => __( 'Profile', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_profile_section',
	'supports'        => array( 'html' => false ),
	'attributes'      => $ugm_section_attrs,
) );

/* ==========================================================================
 * 6. ugm/achievement-section — Prestasi
 * ========================================================================== */

function ugm_render_block_achievement_section( $attrs ) {
	$title    = isset( $attrs['title'] ) && '' !== $attrs['title']
		? $attrs['title']
		: get_theme_mod( 'ugm_achievement_section_title', __( 'Prestasi', 'ugm-faculty' ) );
	$cat_slug = isset( $attrs['categorySlug'] ) && '' !== $attrs['categorySlug']
		? sanitize_key( $attrs['categorySlug'] ) : 'prestasi';

	$cat     = get_category_by_slug( $cat_slug );
	$cat_id  = $cat ? (int) $cat->term_id : 0;
	$archive = $cat_id > 0 ? get_category_link( $cat_id ) : home_url( '/category/' . $cat_slug . '/' );
	$count   = max( 1, absint( get_theme_mod( 'ugm_achievement_news_count', 3 ) ) );

	$args = $cat_id > 0
		? array(
			'post_type'           => 'post',
			'posts_per_page'      => $count,
			'category__in'        => ugm_get_category_tree_ids( $cat_id ),
			'ignore_sticky_posts' => true,
			'post_status'         => 'publish',
			'orderby'             => 'date',
			'order'               => 'DESC',
			'no_found_rows'       => true,
		)
		: array( 'post__in' => array( 0 ) );

	ob_start();
	$q = new WP_Query( $args );
	echo '<section class="home-section section-achievement" aria-labelledby="block-achievement-title">';
	echo ugm_block_section_header( $title, 'block-achievement-title' ); // phpcs:ignore
	ugm_render_portal_column( $q, __( 'Belum ada konten prestasi.', 'ugm-faculty' ) );
	echo '<a class="section-arrow-link" href="' . esc_url( $archive ) . '" aria-label="' . esc_attr__( 'Lihat semua prestasi', 'ugm-faculty' ) . '">&#8594;</a>';
	echo '</section>';
	wp_reset_postdata();
	return ob_get_clean();
}

register_block_type( 'ugm/achievement-section', array(
	'title'           => __( 'Prestasi', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_achievement_section',
	'supports'        => array( 'html' => false ),
	'attributes'      => $ugm_section_attrs,
) );

/* ==========================================================================
 * 7. ugm/featured-categories — Sorotan kategori
 * ========================================================================== */

function ugm_render_block_featured_categories( $attrs ) {
	ob_start();
	echo ugm_render_featured_categories_markup( $attrs ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return ob_get_clean();
}

register_block_type( 'ugm/featured-categories', array(
	'title'           => __( 'Sorotan Kategori (Lama)', 'ugm-faculty' ),
	'description'     => __( 'Blok lama — gunakan "Sorotan Kategori Kolom" agar tiap kolom bisa dipindah secara terpisah.', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_featured_categories',
	'supports'        => array( 'html' => false ),
	'attributes'      => array(
		'campusTitle' => array(
			'type'    => 'string',
			'default' => __( 'Seputar Kampus', 'ugm-faculty' ),
		),
		'campusCategorySlug' => array(
			'type'    => 'string',
			'default' => 'seputar-kampus',
		),
		'facultyTitle' => array(
			'type'    => 'string',
			'default' => __( 'Kabar Fakultas', 'ugm-faculty' ),
		),
		'facultyCategorySlug' => array(
			'type'    => 'string',
			'default' => 'kabar-fakultas',
		),
		'partnershipTitle' => array(
			'type'    => 'string',
			'default' => __( 'Kerjasama', 'ugm-faculty' ),
		),
		'partnershipCategorySlug' => array(
			'type'    => 'string',
			'default' => 'kerjasama',
		),
	),
) );

/* ==========================================================================
 * 7b. ugm/featured-category-column — Satu kolom sorotan kategori (mandiri)
 *
 * Blok ini merender SATU kolom dari grid sorotan kategori sehingga tiap
 * kolom bisa dipindah, disembunyikan, atau diubah urutannya secara terpisah
 * di editor Gutenberg.
 *
 * Di template landing-page.php, blok-blok ugm/featured-category-column yang
 * berurutan dibungkus otomatis dalam .section-featured-categories >
 * .featured-category-grid sehingga tampil dalam layout grid 3-kolom.
 * ========================================================================== */

/**
 * Render a single featured-category column.
 *
 * Used both by the block render_callback (standalone preview in editor) and
 * by the template renderer (grouped into the grid wrapper).
 *
 * @param array $attrs  Block attributes: title, categorySlug, emptyText.
 * @return string HTML for a single .featured-category-column.
 */
function ugm_render_single_featured_column( $attrs ) {
	// Normalise attrs — same logic as ugm_render_featured_categories_markup.
	$title      = isset( $attrs['title'] ) && '' !== trim( (string) $attrs['title'] )
		? trim( (string) $attrs['title'] )
		: __( 'Sorotan Kategori', 'ugm-faculty' );
	$cat_slug   = isset( $attrs['categorySlug'] ) && '' !== trim( (string) $attrs['categorySlug'] )
		? sanitize_key( trim( (string) $attrs['categorySlug'] ) )
		: '';
	$empty_text = isset( $attrs['emptyText'] ) && '' !== trim( (string) $attrs['emptyText'] )
		? trim( (string) $attrs['emptyText'] )
		: sprintf( __( 'Belum ada artikel %s.', 'ugm-faculty' ), $title );

	$slugs = $cat_slug ? array( $cat_slug ) : array();
	$term  = $cat_slug ? ugm_get_category_by_candidate_slugs( $slugs ) : null;
	$link  = $term instanceof WP_Term ? get_category_link( $term->term_id ) : home_url( '/category/' );

	$args = array(
		'post_type'           => 'post',
		'posts_per_page'      => 1,
		'ignore_sticky_posts' => true,
		'post_status'         => 'publish',
		'no_found_rows'       => true,
	);
	if ( $term instanceof WP_Term ) {
		$args['cat'] = (int) $term->term_id;
	} else {
		$args['post__in'] = array( 0 );
	}

	$q = new WP_Query( $args );

	ob_start();
	// Output HTML yang identik dengan yang dihasilkan ugm_render_featured_categories_markup
	// untuk satu kolom — sehingga CSS yang sudah ada langsung berlaku tanpa modifikasi.
	?>
	<section class="featured-category-column" aria-label="<?php echo esc_attr( $title ); ?>">
		<header class="section-header">
			<h2 class="section-title"><?php echo esc_html( $title ); ?></h2>
			<span class="section-line" aria-hidden="true"></span>
		</header>

		<?php if ( $q->have_posts() ) : ?>
			<?php
			$q->the_post();
			$has_thumb = has_post_thumbnail();
			?>
			<article <?php post_class( 'featured-category-card' ); ?>>
				<a class="featured-category-card__link" href="<?php the_permalink(); ?>">
					<div class="featured-category-card__media"<?php echo $has_thumb ? '' : ' aria-hidden="true"'; ?>>
						<?php if ( $has_thumb ) : ?>
							<?php the_post_thumbnail( 'large' ); ?>
						<?php else : ?>
							<div class="featured-category-card__placeholder">
								<span class="card-placeholder__text"><?php echo esc_html( $title ); ?></span>
							</div>
						<?php endif; ?>
					</div>
					<div class="featured-category-card__overlay">
						<h3 class="featured-category-card__title"><?php the_title(); ?></h3>
						<p class="featured-category-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
						<p class="featured-category-card__meta"><?php echo esc_html( get_the_date( 'j F Y, H.i' ) ); ?></p>
						<span class="featured-category-card__cta"><?php esc_html_e( 'Cari tahu lebih lanjut', 'ugm-faculty' ); ?></span>
					</div>
				</a>
			</article>
			<?php wp_reset_postdata(); ?>
		<?php else : ?>
			<?php echo ugm_render_empty_skeleton( 'featured-category', $empty_text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endif; ?>

		<a class="section-arrow-link" href="<?php echo esc_url( $link ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Lihat semua artikel %s', 'ugm-faculty' ), $title ) ); ?>">&#8594;</a>
	</section>
	<?php
	return ob_get_clean();
}


/**
 * Block render_callback for ugm/featured-category-column.
 *
 * Wraps the single column in the full section + grid container so the
 * standalone editor preview looks correct (one column at full width).
 * In the landing page template, the wrapper is added externally so
 * multiple consecutive columns appear side-by-side.
 *
 * @param array $attrs Block attributes.
 * @return string
 */
function ugm_render_block_featured_category_column( $attrs ) {
	// Render sebagai .home-section seperti Berita Akademik / Profile / Prestasi.
	// Di template landing page, blok-blok ini dikumpulkan dalam .home-sections-triple
	// (grid 3-kolom) karena sudah ditambahkan ke $triple_names.
	$col_html = ugm_render_single_featured_column( $attrs );

	return '<section class="home-section section-featured-category-col">' .
		$col_html .
		'</section>';
}

register_block_type( 'ugm/featured-category-column', array(
	'title'           => __( 'Sorotan Kategori Kolom', 'ugm-faculty' ),
	'description'     => __( 'Satu kolom sorotan kategori. Tambahkan beberapa blok ini berdampingan — tiap kolom bisa dipindah secara terpisah.', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_featured_category_column',
	'supports'        => array( 'html' => false ),
	'attributes'      => array(
		'title'        => array( 'type' => 'string', 'default' => '' ),
		'categorySlug' => array( 'type' => 'string', 'default' => '' ),
		'emptyText'    => array( 'type' => 'string', 'default' => '' ),
	),
) );

/* ==========================================================================
 * 8. ugm/category-section — Kategori
 * ========================================================================== */

function ugm_render_block_category_section( $attrs ) {
	ob_start();
	echo ugm_render_category_section_markup( $attrs ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return ob_get_clean();
}

register_block_type( 'ugm/category-section', array(
	'title'           => __( 'Kategori', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_category_section',
	'supports'        => array( 'html' => false ),
	'attributes'      => array(
		'title' => array(
			'type'    => 'string',
			'default' => __( 'Kategori', 'ugm-faculty' ),
		),
	),
) );

/* ==========================================================================
 * 9. ugm/facility-section — Fasilitas
 * ========================================================================== */

function ugm_render_block_facility_section( $attrs ) {
	$title    = isset( $attrs['title'] ) && '' !== $attrs['title']
		? $attrs['title']
		: __( 'Fasilitas', 'ugm-faculty' );
	$cat_slug = isset( $attrs['categorySlug'] ) && '' !== $attrs['categorySlug']
		? sanitize_key( $attrs['categorySlug'] ) : 'fasilitas';

	$cat     = get_category_by_slug( $cat_slug );
	$cat_id  = $cat ? (int) $cat->term_id : 0;
	$archive = $cat_id > 0 ? get_category_link( $cat_id ) : home_url( '/category/' . $cat_slug . '/' );
	$count   = max( 1, absint( get_theme_mod( 'ugm_facility_news_count', 3 ) ) );

	$args = $cat_id > 0
		? array(
			'post_type'           => 'post',
			'posts_per_page'      => $count,
			'category__in'        => ugm_get_category_tree_ids( $cat_id ),
			'ignore_sticky_posts' => true,
			'post_status'         => 'publish',
			'orderby'             => 'date',
			'order'               => 'DESC',
			'no_found_rows'       => true,
		)
		: array( 'post__in' => array( 0 ) );

	ob_start();
	$q = new WP_Query( $args );
	echo '<section class="home-section section-facility" aria-labelledby="block-facility-title">';
	echo ugm_block_section_header( $title, 'block-facility-title' ); // phpcs:ignore
	ugm_render_portal_column( $q, __( 'Belum ada konten fasilitas.', 'ugm-faculty' ) );
	echo '<a class="section-arrow-link" href="' . esc_url( $archive ) . '" aria-label="' . esc_attr__( 'Lihat semua fasilitas', 'ugm-faculty' ) . '">&#8594;</a>';
	echo '</section>';
	wp_reset_postdata();
	return ob_get_clean();
}

register_block_type( 'ugm/facility-section', array(
	'title'           => __( 'Fasilitas', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_facility_section',
	'supports'        => array( 'html' => false ),
	'attributes'      => $ugm_section_attrs,
) );

/* ==========================================================================
 * 10. ugm/faculty-section — Fakultas dan Sekolah
 * ========================================================================== */

function ugm_render_block_faculty_section( $attrs ) {
	$overline = isset( $attrs['overline'] ) && '' !== trim( (string) $attrs['overline'] )
		? trim( (string) $attrs['overline'] )
		: __( 'Seputar UGM', 'ugm-faculty' );
	$title = isset( $attrs['title'] ) && '' !== $attrs['title']
		? $attrs['title']
		: get_theme_mod( 'ugm_faculty_section_title', __( 'Fakultas dan Sekolah', 'ugm-faculty' ) );

	$cat_slug = isset( $attrs['categorySlug'] ) && '' !== trim( (string) $attrs['categorySlug'] )
		? sanitize_key( $attrs['categorySlug'] )
		: '';
	$list     = array();

	if ( '' !== $cat_slug ) {
		$cat = get_category_by_slug( $cat_slug );
		if ( $cat instanceof WP_Term ) {
			$q = new WP_Query(
				array(
					'post_type'           => 'post',
					'posts_per_page'      => 16,
					'category__in'        => ugm_get_category_tree_ids( (int) $cat->term_id ),
					'ignore_sticky_posts' => true,
					'post_status'         => 'publish',
					'orderby'             => 'date',
					'order'               => 'DESC',
					'no_found_rows'       => true,
				)
			);

			if ( $q->have_posts() ) {
				foreach ( $q->posts as $faculty_post ) {
					$thumb_url = get_the_post_thumbnail_url( $faculty_post->ID, 'large' );
					$list[]    = array(
						'name'      => get_the_title( $faculty_post ),
						'image_url' => $thumb_url ? $thumb_url : '',
						'link'      => get_the_permalink( $faculty_post->ID ),
					);
				}
			}
			wp_reset_postdata();
		}
	}

	ob_start();
	echo '<section class="home-section section-faculty" aria-labelledby="block-faculty-title">';
	echo '<p class="section-overline section-overline--faculty">' . esc_html( $overline ) . '</p>';
	echo ugm_block_section_header( $title, 'block-faculty-title' ); // phpcs:ignore

	if ( ! empty( $list ) ) {
		$items_per_page = 8;
		$total_pages    = ceil( count( $list ) / $items_per_page );
		echo '<div class="faculty-slider-wrapper">';
		echo '<button class="faculty-nav faculty-nav--prev" aria-label="' . esc_attr__( 'Previous page', 'ugm-faculty' ) . '">&#8249;</button>';
		echo '<button class="faculty-nav faculty-nav--next" aria-label="' . esc_attr__( 'Next page', 'ugm-faculty' ) . '">&#8250;</button>';
		echo '<div class="faculty-slider" role="list">';
		$page = 0;
		foreach ( $list as $idx => $item ) {
			if ( 0 === $idx % $items_per_page ) {
				if ( $idx > 0 ) echo '</div>';
				echo '<div class="faculty-page" data-page="' . esc_attr( $page ) . '">';
				$page++;
			}
			$card_link = isset( $item['link'] ) && '' !== $item['link'] ? $item['link'] : '';
			$style = $item['image_url'] ? ' style="background-image:url(\'' . esc_url( $item['image_url'] ) . '\')"' : '';
			if ( $card_link ) {
				echo '<article class="faculty-card" role="listitem">';
				echo '<a class="faculty-card__link" href="' . esc_url( $card_link ) . '" target="_blank" rel="noopener noreferrer">';
				echo '<div class="faculty-card__image" aria-hidden="true"' . $style . '></div>';
				echo '<div class="faculty-card__overlay"><h3 class="faculty-card__title">' . esc_html( $item['name'] ) . '</h3></div>';
				echo '</a></article>';
			} else {
				echo '<article class="faculty-card" role="listitem">';
				echo '<div class="faculty-card__image" aria-hidden="true"' . $style . '></div>';
				echo '<div class="faculty-card__overlay"><h3 class="faculty-card__title">' . esc_html( $item['name'] ) . '</h3></div>';
				echo '</article>';
			}
		}
		$faculty_missing = max( 0, $items_per_page - ( count( $list ) % $items_per_page ) );
		if ( $faculty_missing === $items_per_page && count( $list ) > 0 ) {
			$faculty_missing = 0;
		}
		echo ugm_render_partial_skeleton_items( 'faculty-card', $faculty_missing ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div></div>';
		echo '<div class="faculty-pagination" aria-hidden="true">';
		for ( $i = 0; $i < $total_pages; $i++ ) {
			echo '<span class="pagination-dot ' . ( 0 === $i ? 'active' : '' ) . '" data-page="' . esc_attr( $i ) . '"></span>';
		}
		echo '</div></div>';
	} else {
		echo ugm_render_empty_skeleton( 'faculty-grid', __( 'Belum ada konten untuk kategori ini. Tambahkan post lalu isi slug kategori pada block.', 'ugm-faculty' ), array( 'count' => 8 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	echo '</section>';
	return ob_get_clean();
}

register_block_type( 'ugm/faculty-section', array(
	'title'           => __( 'Fakultas dan Sekolah', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_faculty_section',
	'supports'        => array( 'html' => false ),
	'attributes'      => array(
		'overline' => array( 'type' => 'string', 'default' => 'Seputar UGM' ),
		'title' => array( 'type' => 'string', 'default' => '' ),
		'categorySlug' => array( 'type' => 'string', 'default' => '' ),
	),
) );

/* ==========================================================================
 * 10b. ugm/faculty-list — Fakultas dan Sekolah (Data Statis / Manual)
 *
 * Block baru yang menyimpan daftar fakultas langsung di attributes.
 * Setiap item punya: name (string), imageUrl (string URL), link (string URL).
 * Tidak membutuhkan post — cocok untuk input langsung di editor.
 * ========================================================================== */

/**
 * Render ugm/faculty-list block.
 * Items stored as JSON array in the 'items' attribute:
 *   [{"name":"Fakultas Biologi","imageUrl":"https://...","link":"https://..."},...]
 *
 * @param array $attrs Block attributes.
 * @return string HTML.
 */
function ugm_render_block_faculty_list( $attrs ) {
	$overline = isset( $attrs['overline'] ) && '' !== trim( (string) $attrs['overline'] )
		? trim( (string) $attrs['overline'] )
		: __( 'Seputar UGM', 'ugm-faculty' );
	$title = isset( $attrs['title'] ) && '' !== trim( (string) $attrs['title'] )
		? trim( (string) $attrs['title'] )
		: __( 'Fakultas dan Sekolah', 'ugm-faculty' );

	// Decode items from JSON attribute.
	$raw_items = isset( $attrs['items'] ) ? $attrs['items'] : array();
	$list      = array();
	if ( is_string( $raw_items ) ) {
		$raw_items = json_decode( $raw_items, true );
	}
	if ( is_array( $raw_items ) ) {
		foreach ( $raw_items as $item ) {
			if ( ! is_array( $item ) ) continue;
			$list[] = array(
				'name'      => isset( $item['name'] ) ? sanitize_text_field( $item['name'] ) : '',
				'image_url' => isset( $item['imageUrl'] ) ? esc_url_raw( $item['imageUrl'] ) : '',
				'link'      => isset( $item['link'] ) ? esc_url_raw( $item['link'] ) : '',
			);
		}
	}

	ob_start();
	echo '<section class="home-section section-faculty" aria-labelledby="block-faculty-list-title">';
	echo '<p class="section-overline section-overline--faculty">' . esc_html( $overline ) . '</p>';
	echo ugm_block_section_header( $title, 'block-faculty-list-title' ); // phpcs:ignore

	if ( ! empty( $list ) ) {
		$items_per_page = 8;
		$total_pages    = (int) ceil( count( $list ) / $items_per_page );
		echo '<div class="faculty-slider-wrapper">';
		echo '<button class="faculty-nav faculty-nav--prev" aria-label="' . esc_attr__( 'Previous page', 'ugm-faculty' ) . '">&#8249;</button>';
		echo '<button class="faculty-nav faculty-nav--next" aria-label="' . esc_attr__( 'Next page', 'ugm-faculty' ) . '">&#8250;</button>';
		echo '<div class="faculty-slider" role="list">';
		$page = 0;
		foreach ( $list as $idx => $item ) {
			if ( 0 === $idx % $items_per_page ) {
				if ( $idx > 0 ) echo '</div>';
				echo '<div class="faculty-page" data-page="' . esc_attr( $page ) . '">';
				$page++;
			}
			$style = $item['image_url'] ? ' style="background-image:url(\'' . esc_url( $item['image_url'] ) . '\')"' : '';
			if ( $item['link'] ) {
				echo '<article class="faculty-card" role="listitem">';
				echo '<a class="faculty-card__link" href="' . esc_url( $item['link'] ) . '" target="_blank" rel="noopener noreferrer">';
				echo '<div class="faculty-card__image" aria-hidden="true"' . $style . '></div>';
				echo '<div class="faculty-card__overlay"><h3 class="faculty-card__title">' . esc_html( $item['name'] ) . '</h3></div>';
				echo '</a></article>';
			} else {
				echo '<article class="faculty-card" role="listitem">';
				echo '<div class="faculty-card__image" aria-hidden="true"' . $style . '></div>';
				echo '<div class="faculty-card__overlay"><h3 class="faculty-card__title">' . esc_html( $item['name'] ) . '</h3></div>';
				echo '</article>';
			}
		}
		$faculty_missing = max( 0, $items_per_page - ( count( $list ) % $items_per_page ) );
		if ( $faculty_missing === $items_per_page && count( $list ) > 0 ) {
			$faculty_missing = 0;
		}
		echo ugm_render_partial_skeleton_items( 'faculty-card', $faculty_missing ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div></div>';
		echo '<div class="faculty-pagination" aria-hidden="true">';
		for ( $i = 0; $i < $total_pages; $i++ ) {
			echo '<span class="pagination-dot ' . ( 0 === $i ? 'active' : '' ) . '" data-page="' . esc_attr( $i ) . '"></span>';
		}
		echo '</div></div>';
	} else {
		echo ugm_render_empty_skeleton( 'faculty-grid', __( 'Belum ada fakultas. Tambahkan item lewat sidebar editor.', 'ugm-faculty' ), array( 'count' => 8 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	echo '</section>';
	return ob_get_clean();
}

register_block_type( 'ugm/faculty-list', array(
	'title'           => __( 'Fakultas dan Sekolah (Manual)', 'ugm-faculty' ),
	'description'     => __( 'Daftar fakultas dengan gambar, nama, dan link — diinput langsung di editor, tanpa post.', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_faculty_list',
	'supports'        => array( 'html' => false ),
	'attributes'      => array(
		'overline' => array( 'type' => 'string', 'default' => 'Seputar UGM' ),
		'title'    => array( 'type' => 'string', 'default' => '' ),
		'items'    => array(
			'type'    => 'array',
			'default' => array(),
			'items'   => array(
				'type'       => 'object',
				'properties' => array(
					'name'     => array( 'type' => 'string' ),
					'imageUrl' => array( 'type' => 'string' ),
					'link'     => array( 'type' => 'string' ),
				),
			),
		),
	),
) );

/* ==========================================================================
 * 11. ugm/agenda-section — Agenda Kegiatan + Fasilitas (Lama & Mandiri)
 *
 * Helper functions untuk render tiap kolom secara terpisah.
 * Digunakan oleh:
 *   - ugm/agenda-section (lama — gabungan, backward compat)
 *   - ugm/agenda-only   (baru — hanya Agenda Kegiatan)
 *   - ugm/facility-only (baru — hanya Fasilitas Mahasiswa)
 * ========================================================================== */

/**
 * Render the Agenda Kegiatan column HTML (tanpa outer section wrapper).
 *
 * @param string $title    Section title.
 * @param string $cat_slug Category slug.
 * @return string HTML.
 */
function ugm_render_agenda_column_html( $title, $cat_slug ) {
	$agenda_term_ids = ugm_resolve_agenda_exclude_ids( $cat_slug );

	$agenda_archive = home_url( '/' );
	if ( ! empty( $agenda_term_ids ) ) {
		$first = reset( $agenda_term_ids );
		$link  = get_category_link( $first );
		if ( ! is_wp_error( $link ) ) {
			$agenda_archive = $link;
		}
	}

	$agenda_count = max( 1, min( 6, absint( get_theme_mod( 'ugm_events_count', 3 ) ) ) );
	$agenda_args  = array(
		'post_type'           => 'post',
		'posts_per_page'      => $agenda_count,
		'ignore_sticky_posts' => true,
		'post_status'         => 'publish',
		'orderby'             => 'date',
		'order'               => 'DESC',
		'no_found_rows'       => true,
	);
	if ( ! empty( $agenda_term_ids ) ) {
		$agenda_args['category__in'] = $agenda_term_ids;
	} else {
		$agenda_args['post__in'] = array( 0 );
	}
	$agenda_q = new WP_Query( $agenda_args );

	ob_start();
	echo '<section class="section-campus-desktop__agenda" aria-labelledby="block-agenda-title">';
	echo '<header class="section-header section-header--desktop-agenda">';
	echo '<h2 id="block-agenda-title" class="section-title">' . esc_html( $title ) . '</h2>';
	echo '<span class="section-line" aria-hidden="true"></span>';
	echo '</header>';

	if ( $agenda_q->have_posts() ) {
		$agenda_items = 0;
		echo '<div class="desktop-agenda-list">';
		while ( $agenda_q->have_posts() ) {
			$agenda_q->the_post();
			$agenda_items++;
			$ts = (int) get_post_timestamp( get_the_ID() );
			echo '<article class="' . esc_attr( implode( ' ', get_post_class( 'desktop-agenda-card' ) ) ) . '">';
			echo '<a class="desktop-agenda-card__date" href="' . esc_url( get_the_permalink() ) . '" aria-label="' . esc_attr__( 'Buka agenda', 'ugm-faculty' ) . '">';
			echo '<span class="desktop-agenda-card__day">' . esc_html( wp_date( 'd', $ts ) ) . '</span>';
			echo '<span class="desktop-agenda-card__month">' . esc_html( wp_date( 'M', $ts ) ) . '</span>';
			echo '</a>';
			echo '<div class="desktop-agenda-card__body">';
			echo '<h3 class="desktop-agenda-card__title"><a href="' . esc_url( get_the_permalink() ) . '">' . get_the_title() . '</a></h3>';
			echo '<p class="desktop-agenda-card__meta">' . esc_html( get_the_author() ) . '</p>';
			echo '</div></article>';
		}
		echo ugm_render_partial_skeleton_items( 'agenda-card', max( 0, 3 - $agenda_items ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
	} else {
		echo ugm_render_empty_skeleton( 'agenda-list', __( 'Belum ada agenda kegiatan.', 'ugm-faculty' ), array( 'count' => 3 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	wp_reset_postdata();

	echo '<a class="desktop-agenda-arrow" href="' . esc_url( $agenda_archive ) . '" aria-label="' . esc_attr__( 'Lihat semua agenda kegiatan', 'ugm-faculty' ) . '">&rarr;</a>';
	echo '</section>'; // .section-campus-desktop__agenda

	return ob_get_clean();
}

/**
 * Render the Fasilitas Mahasiswa column HTML (tanpa outer section wrapper).
 *
 * @param string $facility_title Section title.
 * @param string $facility_slug  Category slug.
 * @return string HTML.
 */
function ugm_render_facility_column_html( $facility_title, $facility_slug ) {
	$facility_root     = ugm_get_category_root_by_slugs( array_filter( array( $facility_slug, 'fasilitas-mahasiswa', 'fasilitas', 'sarana-prasarana', 'sarana', 'prasarana' ) ) );
	$facility_term_ids = array();
	$facility_archive  = home_url( '/' );

	if ( $facility_root ) {
		$facility_root_id  = (int) $facility_root->term_id;
		$facility_term_ids = ugm_get_category_tree_ids( $facility_root_id );
		$fac_link          = get_category_link( $facility_root_id );
		if ( ! is_wp_error( $fac_link ) ) {
			$facility_archive = $fac_link;
		}
	}

	$facility_args = array(
		'post_type'           => 'post',
		'posts_per_page'      => 4,
		'ignore_sticky_posts' => true,
		'post_status'         => 'publish',
		'orderby'             => 'date',
		'order'               => 'DESC',
		'no_found_rows'       => true,
	);
	if ( ! empty( $facility_term_ids ) ) {
		$facility_args['category__in'] = $facility_term_ids;
	} else {
		$facility_args['post__in'] = array( 0 );
	}
	$facility_q = new WP_Query( $facility_args );

	ob_start();
	echo '<section class="section-campus-desktop__facility" aria-labelledby="block-facility-desktop-title">';
	echo '<header class="section-header section-header--desktop-facility">';
	echo '<h2 id="block-facility-desktop-title" class="section-title">' . esc_html( $facility_title ) . '</h2>';
	echo '<span class="section-line" aria-hidden="true"></span>';
	echo '<a class="section-view-all section-view-all--desktop-facility" href="' . esc_url( $facility_archive ) . '" aria-label="' . esc_attr( sprintf( __( 'Lihat semua %s', 'ugm-faculty' ), $facility_title ) ) . '">';
	echo esc_html__( 'Lihat Semua', 'ugm-faculty' ) . ' <span aria-hidden="true">&rarr;</span></a>';
	echo '</header>';

	if ( $facility_q->have_posts() ) {
		$facility_posts = $facility_q->posts;
		$fac_total      = count( $facility_posts );
		echo '<div class="desktop-facility-grid desktop-facility-grid--count-4">';
		foreach ( $facility_posts as $fac_post ) {
			$post            = $fac_post; // phpcs:ignore
			$GLOBALS['post'] = $post;
			setup_postdata( $post );
			$thumb_url = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'large' ) : '';
			echo '<article class="' . esc_attr( implode( ' ', get_post_class( 'desktop-facility-card' ) ) ) . '">';
			echo '<a class="desktop-facility-card__link" href="' . esc_url( get_the_permalink() ) . '">';
			$style = $thumb_url ? ' style="background-image:url(\'' . esc_url( $thumb_url ) . '\')"' : '';
			echo '<div class="desktop-facility-card__media"' . $style . '>';
			if ( ! $thumb_url ) {
				echo '<span class="card-placeholder__text">' . esc_html__( 'Fasilitas', 'ugm-faculty' ) . '</span>';
			}
			echo '<h3 class="desktop-facility-card__title">' . get_the_title() . '</h3>';
			echo '</div></a></article>';
		}
		echo ugm_render_partial_skeleton_items( 'facility-card', max( 0, 4 - $fac_total ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
	} else {
		echo ugm_render_empty_skeleton( 'facility-grid', __( 'Belum ada konten fasilitas mahasiswa.', 'ugm-faculty' ), array( 'count' => 4 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	wp_reset_postdata();

	echo '</section>'; // .section-campus-desktop__facility

	return ob_get_clean();
}

/* --------------------------------------------------------------------------
 * 11a. ugm/agenda-section (Lama) — Agenda + Fasilitas gabungan
 * -------------------------------------------------------------------------- */

function ugm_render_block_agenda_section( $attrs ) {
	$title          = isset( $attrs['title'] ) && '' !== $attrs['title']
		? $attrs['title']
		: get_theme_mod( 'ugm_events_section_title', __( 'Agenda Kegiatan', 'ugm-faculty' ) );
	$cat_slug       = isset( $attrs['categorySlug'] ) && '' !== $attrs['categorySlug']
		? sanitize_key( $attrs['categorySlug'] ) : '';
	$facility_title = isset( $attrs['facilityTitle'] ) && '' !== trim( (string) $attrs['facilityTitle'] )
		? trim( (string) $attrs['facilityTitle'] )
		: __( 'Fasilitas Mahasiswa', 'ugm-faculty' );
	$facility_slug  = isset( $attrs['facilityCategorySlug'] ) && '' !== trim( (string) $attrs['facilityCategorySlug'] )
		? sanitize_key( $attrs['facilityCategorySlug'] )
		: 'fasilitas-mahasiswa';

	ob_start();
	echo '<section class="home-section section-campus-desktop" aria-label="' . esc_attr__( 'Agenda dan fasilitas kampus', 'ugm-faculty' ) . '">';
	echo ugm_render_agenda_column_html( $title, $cat_slug );    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo ugm_render_facility_column_html( $facility_title, $facility_slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</section>';
	return ob_get_clean();
}

register_block_type( 'ugm/agenda-section', array(
	'title'           => __( 'Agenda + Fasilitas (Lama)', 'ugm-faculty' ),
	'description'     => __( 'Blok lama — gunakan "Agenda Kegiatan" dan "Fasilitas Mahasiswa" terpisah agar bisa dipindah secara mandiri.', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_agenda_section',
	'supports'        => array( 'html' => false ),
	'attributes'      => array(
		'title'                => array( 'type' => 'string', 'default' => '' ),
		'categorySlug'         => array( 'type' => 'string', 'default' => '' ),
		'facilityTitle'        => array( 'type' => 'string', 'default' => 'Fasilitas Mahasiswa' ),
		'facilityCategorySlug' => array( 'type' => 'string', 'default' => 'fasilitas-mahasiswa' ),
	),
) );

/* --------------------------------------------------------------------------
 * 11b. ugm/agenda-only — Agenda Kegiatan mandiri
 * -------------------------------------------------------------------------- */

function ugm_render_block_agenda_only( $attrs ) {
	$title    = isset( $attrs['title'] ) && '' !== trim( (string) $attrs['title'] )
		? trim( (string) $attrs['title'] )
		: get_theme_mod( 'ugm_events_section_title', __( 'Agenda Kegiatan', 'ugm-faculty' ) );
	$cat_slug = isset( $attrs['categorySlug'] ) && '' !== trim( (string) $attrs['categorySlug'] )
		? sanitize_key( trim( (string) $attrs['categorySlug'] ) )
		: '';

	$inner = ugm_render_agenda_column_html( $title, $cat_slug );
	// Wrapped in the same outer section so CSS stays intact even when standalone.
	return '<section class="home-section section-campus-desktop section-campus-desktop--agenda-only" aria-label="' .
		esc_attr__( 'Agenda kegiatan', 'ugm-faculty' ) . '">' . $inner . '</section>';
}

register_block_type( 'ugm/agenda-only', array(
	'title'           => __( 'Agenda Kegiatan', 'ugm-faculty' ),
	'description'     => __( 'Menampilkan daftar agenda kegiatan berdasarkan kategori.', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_agenda_only',
	'supports'        => array( 'html' => false ),
	'attributes'      => array(
		'title'        => array( 'type' => 'string', 'default' => '' ),
		'categorySlug' => array( 'type' => 'string', 'default' => '' ),
	),
) );

/* --------------------------------------------------------------------------
 * 11c. ugm/facility-only — Fasilitas Mahasiswa mandiri
 * -------------------------------------------------------------------------- */

function ugm_render_block_facility_only( $attrs ) {
	$title    = isset( $attrs['title'] ) && '' !== trim( (string) $attrs['title'] )
		? trim( (string) $attrs['title'] )
		: __( 'Fasilitas Mahasiswa', 'ugm-faculty' );
	$cat_slug = isset( $attrs['categorySlug'] ) && '' !== trim( (string) $attrs['categorySlug'] )
		? sanitize_key( trim( (string) $attrs['categorySlug'] ) )
		: 'fasilitas-mahasiswa';

	$inner = ugm_render_facility_column_html( $title, $cat_slug );
	return '<section class="home-section section-campus-desktop section-campus-desktop--facility-only" aria-label="' .
		esc_attr__( 'Fasilitas mahasiswa', 'ugm-faculty' ) . '">' . $inner . '</section>';
}

register_block_type( 'ugm/facility-only', array(
	'title'           => __( 'Fasilitas Mahasiswa', 'ugm-faculty' ),
	'description'     => __( 'Menampilkan grid fasilitas mahasiswa berdasarkan kategori.', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_facility_only',
	'supports'        => array( 'html' => false ),
	'attributes'      => array(
		'title'        => array( 'type' => 'string', 'default' => '' ),
		'categorySlug' => array( 'type' => 'string', 'default' => '' ),
	),
) );



/* ==========================================================================
 * 12. ugm/magazine-section — Majalah Digital
 * ========================================================================== */

function ugm_render_block_magazine_section( $attrs ) {
	ob_start();
	get_template_part( 'template-parts/section-majalah' );
	return ob_get_clean();
}

register_block_type( 'ugm/magazine-section', array(
	'title'           => __( 'Majalah Digital', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_magazine_section',
	'supports'        => array( 'html' => false ),
) );

/* ==========================================================================
 * 13. ugm/video-section — Video
 * ========================================================================== */

/**
 * Render the video section markup.
 *
 * Layout: featured video (left, large thumbnail) + list of 3 smaller videos (right).
 * Skeleton displayed when no posts found.
 *
 * @param array $attrs Block attributes.
 * @return string
 */
function ugm_render_block_video_section( $attrs ) {
	$title    = isset( $attrs['title'] ) && '' !== trim( (string) $attrs['title'] )
		? trim( (string) $attrs['title'] )
		: __( 'Video', 'ugm-faculty' );
	$cat_slug = isset( $attrs['categorySlug'] ) && '' !== trim( (string) $attrs['categorySlug'] )
		? sanitize_key( trim( (string) $attrs['categorySlug'] ) )
		: 'video';

	$cat    = get_category_by_slug( $cat_slug );
	$cat_id = $cat instanceof WP_Term ? (int) $cat->term_id : 0;
	$archive = $cat_id > 0 ? get_category_link( $cat_id ) : home_url( '/category/' . $cat_slug . '/' );

	$args = array(
		'post_type'           => 'post',
		'posts_per_page'      => 4,
		'ignore_sticky_posts' => true,
		'post_status'         => 'publish',
		'orderby'             => 'date',
		'order'               => 'DESC',
		'no_found_rows'       => true,
	);
	if ( $cat_id > 0 ) {
		$args['category__in'] = ugm_get_category_tree_ids( $cat_id );
	} else {
		$args['post__in'] = array( 0 );
	}

	$q = new WP_Query( $args );

	ob_start();
	echo '<section class="home-section section-video" aria-labelledby="block-video-title">';
	echo ugm_block_section_header( $title, 'block-video-title', $archive, __( 'Lihat semua video', 'ugm-faculty' ) ); // phpcs:ignore

	if ( $q->have_posts() ) {
		$posts    = $q->posts;
		$featured = array_shift( $posts );
		$list     = array_slice( $posts, 0, 3 );

		echo '<div class="video-layout">';

		// Featured video (left).
		if ( $featured instanceof WP_Post ) {
			$GLOBALS['post'] = $featured;
			setup_postdata( $featured );
			echo '<div class="video-area video-area--featured">';
			echo '<article class="' . esc_attr( implode( ' ', get_post_class( 'video-featured' ) ) ) . '">';
			echo '<a class="video-featured__media" href="' . esc_url( get_the_permalink() ) . '" tabindex="-1" aria-hidden="true">';
			if ( has_post_thumbnail() ) {
				echo get_the_post_thumbnail( null, 'large' );
			} else {
				echo '<div class="video-featured__placeholder"><span class="card-placeholder__text">' . esc_html( $title ) . '</span></div>';
			}
			echo '<span class="video-featured__play" aria-hidden="true"></span>';
			echo '</a>';
			echo '<div class="video-featured__body">';
			echo '<h3 class="video-featured__title"><a href="' . esc_url( get_the_permalink() ) . '">' . get_the_title() . '</a></h3>';
			echo '<p class="video-featured__date">' . esc_html( get_the_date( 'j F Y, H.i' ) ) . '</p>';
			echo '</div>';
			echo '</article>';
			echo '</div>';
		}

		// List of videos (right).
		if ( ! empty( $list ) ) {
			echo '<div class="video-area video-area--list">';
			foreach ( $list as $item ) {
				$GLOBALS['post'] = $item;
				setup_postdata( $item );
				echo '<article class="' . esc_attr( implode( ' ', get_post_class( 'video-list-card' ) ) ) . '">';
				echo '<a class="video-list-card__media" href="' . esc_url( get_the_permalink() ) . '" tabindex="-1" aria-hidden="true">';
				if ( has_post_thumbnail() ) {
					echo get_the_post_thumbnail( null, 'thumbnail' );
				} else {
					echo '<div class="video-list-card__placeholder"></div>';
				}
				echo '<span class="video-list-card__play" aria-hidden="true"></span>';
				echo '</a>';
				echo '<div class="video-list-card__body">';
				echo '<h3 class="video-list-card__title"><a href="' . esc_url( get_the_permalink() ) . '">' . get_the_title() . '</a></h3>';
				echo '<p class="video-list-card__date">' . esc_html( get_the_date( 'j F Y, H.i' ) ) . '</p>';
				echo '</div>';
				echo '</article>';
			}
			// Skeleton placeholders for missing list items.
			$missing = max( 0, 3 - count( $list ) );
			echo ugm_render_partial_skeleton_items( 'video-list-card', $missing ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '</div>';
		}

		echo '</div>'; // .video-layout
	} else {
		// Full skeleton when no posts.
		echo '<div class="video-layout video-layout--skeleton">';
		echo '<div class="video-area video-area--featured">';
		echo '<div class="video-featured video-featured--skeleton">';
		echo '<div class="ugm-skeleton-block video-featured__media video-featured__media--skeleton"></div>';
		echo '<div class="video-featured__body">';
		echo '<div class="ugm-skeleton-block ugm-skeleton-block--title" style="width:80%;height:18px;margin-bottom:8px;"></div>';
		echo '<div class="ugm-skeleton-block" style="width:45%;height:14px;"></div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '<div class="video-area video-area--list">';
		for ( $i = 0; $i < 3; $i++ ) {
			echo '<div class="video-list-card video-list-card--skeleton">';
			echo '<div class="ugm-skeleton-block video-list-card__media video-list-card__media--skeleton"></div>';
			echo '<div class="video-list-card__body">';
			echo '<div class="ugm-skeleton-block ugm-skeleton-block--title" style="width:90%;height:14px;margin-bottom:6px;"></div>';
			echo '<div class="ugm-skeleton-block ugm-skeleton-block--title" style="width:70%;height:14px;margin-bottom:8px;"></div>';
			echo '<div class="ugm-skeleton-block" style="width:40%;height:12px;"></div>';
			echo '</div>';
			echo '</div>';
		}
		echo '</div>';
		echo '</div>'; // .video-layout--skeleton
	}

	wp_reset_postdata();
	echo '</section>';
	return ob_get_clean();
}

register_block_type( 'ugm/video-section', array(
	'title'           => __( 'Video', 'ugm-faculty' ),
	'description'     => __( 'Menampilkan video unggulan dan daftar video berdasarkan kategori.', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_video_section',
	'supports'        => array( 'html' => false ),
	'attributes'      => array(
		'title'        => array( 'type' => 'string', 'default' => '' ),
		'categorySlug' => array( 'type' => 'string', 'default' => 'video' ),
	),
) );

/* ==========================================================================
 * Shared rendering helper: portal column (featured + list cards)
 * ========================================================================== */

/**
 * Render a portal column layout: 1 featured card + up to 2 list cards.
 *
 * @param WP_Query $q
 * @param string   $empty_msg
 */
function ugm_render_portal_column( WP_Query $q, $empty_msg = '' ) {
	if ( ! $q->have_posts() ) {
		echo ugm_render_empty_skeleton( 'portal-column', $empty_msg ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return;
	}

	$posts    = $q->posts;
	$featured = array_shift( $posts );
	$list     = array_slice( $posts, 0, 2 );
	$list_missing = max( 0, 2 - count( $list ) );

	$get_label = function () {
		$cats       = get_the_category();
		$default_id = (int) get_option( 'default_category' );
		$label      = ''; // Kosong jika tidak ada kategori non-default.
		foreach ( $cats as $cat ) {
			if ( $default_id !== (int) $cat->term_id && 'uncategorized' !== $cat->slug ) {
				$label = $cat->name;
				break;
			}
		}
		return $label;
	};

	echo '<div class="portal-column">';

	if ( $featured instanceof WP_Post ) {
		$post            = $featured; // phpcs:ignore
		$GLOBALS['post'] = $post;     // Required: setup_postdata alone does not set the global post.
		setup_postdata( $post );
		$_has_thumb = has_post_thumbnail();
		echo '<article class="' . esc_attr( implode( ' ', get_post_class( 'portal-card portal-card--featured' ) ) ) . '">';
		if ( $_has_thumb ) {
			echo '<a class="portal-card__media" href="' . esc_url( get_the_permalink() ) . '" aria-hidden="true" tabindex="-1">' . get_the_post_thumbnail( null, 'medium_large' ) . '</a>';
		} else {
			echo '<div class="portal-card__media portal-card__media--placeholder" aria-hidden="true"></div>';
		}
		echo '<div class="portal-card__body">';
		$_label = $get_label(); if ( '' !== $_label ) { echo '<p class="card-kicker">' . esc_html( $_label ) . '</p>'; }
		echo '<h3 class="card-title"><a href="' . esc_url( get_the_permalink() ) . '">' . get_the_title() . '</a></h3>';
		echo '<p class="card-date">' . esc_html( get_the_date( 'j F Y, H.i' ) ) . '</p>';
		echo '</div></article>';
	}

	echo '<div class="portal-column__list">';
	if ( ! empty( $list ) ) {
		foreach ( $list as $item ) {
			$post            = $item; // phpcs:ignore
			$GLOBALS['post'] = $post; // Required: setup_postdata alone does not set the global post.
			setup_postdata( $post );
			$_has_thumb = has_post_thumbnail();
			echo '<article class="' . esc_attr( implode( ' ', get_post_class( 'portal-list-card' ) ) ) . '">';
			if ( $_has_thumb ) {
				echo '<a class="portal-list-card__media" href="' . esc_url( get_the_permalink() ) . '" aria-hidden="true" tabindex="-1">' . get_the_post_thumbnail( null, 'thumbnail' ) . '</a>';
			} else {
				echo '<div class="portal-list-card__media portal-list-card__media--placeholder" aria-hidden="true"></div>';
			}
			echo '<div class="portal-list-card__body">';
			$_label = $get_label(); if ( '' !== $_label ) { echo '<p class="card-kicker">' . esc_html( $_label ) . '</p>'; }
			echo '<h3 class="card-title"><a href="' . esc_url( get_the_permalink() ) . '">' . get_the_title() . '</a></h3>';
			echo '<p class="card-date">' . esc_html( get_the_date( 'j F Y, H.i' ) ) . '</p>';
			echo '</div></article>';
		}
	}
	if ( $list_missing > 0 ) {
		echo ugm_render_partial_skeleton_items( 'portal-list-card', $list_missing ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</div>';

	echo '</div>'; // .portal-column
}

/* ==========================================================================
 * Helper: resolve agenda exclude term IDs
 * ========================================================================== */

/**
 * Resolve the term IDs for the agenda category (used to exclude from latest news).
 *
 * Priority: per-block slug attr → Customizer category ID → auto-detect slug.
 *
 * @param string $slug_attr Category slug from block attribute (may be empty).
 * @return int[]
 */
function ugm_resolve_agenda_exclude_ids( $slug_attr ) {
	// 1. Explicit slug attr (from block attribute).
	if ( '' !== $slug_attr ) {
		// Try exact slug first, then WP-suffixed variants (-2, -3, -4).
		$slug_candidates = array( $slug_attr, $slug_attr . '-2', $slug_attr . '-3', $slug_attr . '-4' );
		foreach ( $slug_candidates as $try_slug ) {
			$cat = get_category_by_slug( sanitize_key( $try_slug ) );
			if ( $cat ) {
				return ugm_get_category_tree_ids( (int) $cat->term_id );
			}
		}
		// Last resort: search by name containing the slug keyword.
		$all_cats = get_categories( array( 'hide_empty' => false, 'number' => 50 ) );
		$keyword  = str_replace( '-', ' ', $slug_attr );
		foreach ( $all_cats as $cat ) {
			if ( false !== stripos( $cat->name, $keyword ) || false !== stripos( $cat->slug, $slug_attr ) ) {
				return ugm_get_category_tree_ids( (int) $cat->term_id );
			}
		}
		return array();
	}

	// 2. Customizer setting.
	$customizer_id = absint( get_theme_mod( 'ugm_events_category_id', 0 ) );
	if ( $customizer_id > 0 ) {
		return ugm_get_category_tree_ids( $customizer_id );
	}

	// 3. Try known slug candidates (WP may suffix with -2, -3, etc. if slug is taken).
	$root = ugm_get_category_root_by_slugs( array( 'agenda', 'agenda-2', 'agenda-3', 'kegiatan', 'events', 'event' ) );
	if ( $root ) {
		return ugm_get_category_tree_ids( (int) $root->term_id );
	}

	// 4. Last resort: find any category whose name contains "agenda" or "kegiatan".
	$name_candidates = get_categories( array(
		'hide_empty' => false,
		'number'     => 20,
	) );
	foreach ( $name_candidates as $cat ) {
		$lower = strtolower( $cat->name );
		if ( false !== strpos( $lower, 'agenda' ) || false !== strpos( $lower, 'kegiatan' ) ) {
			return ugm_get_category_tree_ids( (int) $cat->term_id );
		}
	}

	return array();
}

/**
 * Resolve the term IDs for the faculty section category (used to exclude from latest news).
 *
 * Reads categorySlug from the ugm/faculty-section block on the current page.
 * Falls back to the default slug 'fakultas-dan-sekolah'.
 *
 * @param string $slug_attr Category slug override (usually empty; pass '' to auto-detect).
 * @return int[]
 */
function ugm_resolve_faculty_exclude_ids( $slug_attr ) {
	if ( '' !== $slug_attr ) {
		$cat = get_category_by_slug( sanitize_key( $slug_attr ) );
		if ( $cat ) {
			return ugm_get_category_tree_ids( (int) $cat->term_id );
		}
		return array();
	}

	// Resolve the current page ID.
	// In REST API / block-editor SSR context, WordPress sets up $post via setup_postdata(),
	// so get_the_ID() returns the page being edited. Fall back to get_queried_object_id()
	// for the front-end template context.
	$page_id = (int) get_the_ID();
	if ( $page_id <= 0 ) {
		$page_id = (int) get_queried_object_id();
	}

	if ( $page_id > 0 ) {
		$post = get_post( $page_id );
		if ( $post instanceof WP_Post ) {
			foreach ( parse_blocks( (string) $post->post_content ) as $block ) {
				if ( 'ugm/faculty-section' === $block['blockName'] ) {
					$slug = sanitize_key( trim( (string) ( $block['attrs']['categorySlug'] ?? '' ) ) );
					if ( '' !== $slug ) {
						$cat = get_category_by_slug( $slug );
						if ( $cat ) {
							return ugm_get_category_tree_ids( (int) $cat->term_id );
						}
						// Slug configured but category not found — return empty (nothing to exclude).
						return array();
					}
					break; // block found but no slug configured → use fallback below.
				}
			}
		}
	}

	// Fallback: try common faculty slug variants.
	foreach ( array( 'fakultas-dan-sekolah', 'fakultas', 'faculty' ) as $fallback_slug ) {
		$cat = get_category_by_slug( $fallback_slug );
		if ( $cat ) {
			return ugm_get_category_tree_ids( (int) $cat->term_id );
		}
	}
	return array();
}

/* ==========================================================================
 * Register custom block category
 * ========================================================================== */

add_filter( 'block_categories_all', function ( $categories ) {
	array_unshift( $categories, array(
		'slug'  => 'ugm-sections',
		'title' => __( 'UGM — Landing Page Sections', 'ugm-faculty' ),
		'icon'  => 'layout',
	) );
	return $categories;
} );

/* ==========================================================================
 * Enqueue block editor JavaScript (Inspector Controls for configurable blocks)
 * ========================================================================== */

add_action( 'enqueue_block_editor_assets', function () {
	wp_enqueue_script(
		'ugm-blocks',
		get_template_directory_uri() . '/assets/js/blocks.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render' ),
		ugm_get_asset_version( '/assets/js/blocks.js' ),
		true
	);

	wp_enqueue_style(
		'ugm-editor-style-base',
		get_template_directory_uri() . '/assets/css/base.css',
		array(),
		ugm_get_asset_version( '/assets/css/base.css' )
	);

	wp_enqueue_style(
		'ugm-editor-style-hero',
		get_template_directory_uri() . '/assets/css/hero.css',
		array( 'ugm-editor-style-base' ),
		ugm_get_asset_version( '/assets/css/hero.css' )
	);

	wp_enqueue_style(
		'ugm-editor-style-content',
		get_template_directory_uri() . '/assets/css/content.css',
		array( 'ugm-editor-style-base' ),
		ugm_get_asset_version( '/assets/css/content.css' )
	);

	wp_enqueue_style(
		'ugm-editor-landing-preview',
		get_template_directory_uri() . '/assets/css/landing-page-editor.css',
		array( 'ugm-editor-style-base', 'ugm-editor-style-content' ),
		ugm_get_asset_version( '/assets/css/landing-page-editor.css' )
	);
} );

/* ==========================================================================
 * Block Pattern: Konten Halaman Landing
 *
 * Registers a full-page pattern containing all landing page section blocks.
 * Users can insert it via the block editor's pattern picker (+  → Patterns →
 * UGM Landing Page → Konten Halaman Landing).
 * ========================================================================== */

add_action( 'init', function () {
	if ( ! function_exists( 'register_block_pattern_category' ) ) {
		return;
	}

	register_block_pattern_category( 'ugm-landing', array(
		'label' => __( 'UGM Landing Page', 'ugm-faculty' ),
	) );

	$landing_blocks =
		'<!-- wp:ugm/hero-section {"imageId":0,"imageUrl":"","title":"","description":""} /-->' . "\n" .
		'<!-- wp:ugm/latest-news {"title":"Berita Terbaru","categorySlug":""} /-->' . "\n" .
		'<!-- wp:ugm/academic-news {"title":"Berita Akademik","categorySlug":"pendidikan"} /-->' . "\n" .
		'<!-- wp:ugm/profile-section {"title":"Profile","categorySlug":"profile"} /-->' . "\n" .
		'<!-- wp:ugm/achievement-section {"title":"Prestasi","categorySlug":"prestasi"} /-->' . "\n" .
		'<!-- wp:ugm/featured-categories {"campusTitle":"Seputar Kampus","campusCategorySlug":"seputar-kampus","facultyTitle":"Kabar Fakultas","facultyCategorySlug":"kabar-fakultas","partnershipTitle":"Kerjasama","partnershipCategorySlug":"kerjasama"} /-->' . "\n" .
		'<!-- wp:ugm/category-section {"title":"Kategori"} /-->' . "\n" .
		'<!-- wp:ugm/faculty-section {"title":"Fakultas dan Sekolah","categorySlug":"fakultas-dan-sekolah"} /-->' . "\n" .
		'<!-- wp:ugm/agenda-section {"title":"Agenda Kegiatan","categorySlug":"agenda"} /-->' . "\n" .
		'<!-- wp:ugm/magazine-section /-->' . "\n" .
		'<!-- wp:ugm/video-section {"title":"Video","categorySlug":"video"} /-->';

	register_block_pattern( 'ugm/landing-page-sections', array(
		'title'       => __( 'Konten Halaman Landing — Semua Section', 'ugm-faculty' ),
		'description' => __( 'Hero + semua section halaman landing. Insert ke halaman yang menggunakan template Halaman Landing.', 'ugm-faculty' ),
		'categories'  => array( 'ugm-landing' ),
		'content'     => $landing_blocks,
	) );
} );

/* ==========================================================================
 * One-time migration: populate existing landing pages with section blocks.
 *
 * When switching from the "all-in-template" approach to the "blocks-in-post-
 * content" approach, existing pages that used the landing page template will
 * have empty post_content. This function runs once on admin_init and fills
 * those pages with the section blocks so the page editor shows the design.
 *
 * It is guarded by a WP option flag so it never runs more than once.
 * To re-run, delete the option 'ugm_landing_blocks_migrated_v1' from the DB.
 * ========================================================================== */

add_action( 'admin_init', function () {
	// Block seeding migration disabled; landing pages now use PHP templates.
	return;

	// v2: adds hero-section block at the top. Delete 'ugm_landing_blocks_migrated_v2' to re-run.
	if ( get_option( 'ugm_landing_blocks_migrated_v2' ) ) {
		return;
	}

	$landing_blocks =
		'<!-- wp:ugm/hero-section {"imageId":0,"imageUrl":"","title":"","description":""} /-->' . "\n" .
		'<!-- wp:ugm/latest-news {"title":"Berita Terbaru","categorySlug":""} /-->' . "\n" .
		'<!-- wp:ugm/academic-news {"title":"Berita Akademik","categorySlug":"pendidikan"} /-->' . "\n" .
		'<!-- wp:ugm/profile-section {"title":"Profile","categorySlug":"profile"} /-->' . "\n" .
		'<!-- wp:ugm/achievement-section {"title":"Prestasi","categorySlug":"prestasi"} /-->' . "\n" .
		'<!-- wp:ugm/faculty-section {"title":"Fakultas dan Sekolah","categorySlug":"fakultas-dan-sekolah"} /-->' . "\n" .
		'<!-- wp:ugm/agenda-section {"title":"Agenda Kegiatan","categorySlug":"agenda"} /-->' . "\n" .
		'<!-- wp:ugm/magazine-section /-->';

	// Find pages using either the legacy PHP template OR the new block template.
	$pages = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'posts_per_page' => -1,
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
	) );

	foreach ( $pages as $page ) {
		$content = (string) $page->post_content;

		// Always overwrite v1 content (no hero block) or empty content.
		if ( '' === trim( $content )
			|| false === strpos( $content, 'wp:ugm/' )
			|| false === strpos( $content, 'ugm/hero-section' )
		) {
			wp_update_post( array(
				'ID'           => $page->ID,
				'post_content' => $landing_blocks,
			) );
		}
	}

	update_option( 'ugm_landing_blocks_migrated_v2', true );
} );

/* ==========================================================================
 * Migration v3: switch pages from block template → PHP template.
 *
 * Pages that were set to use the block template ('landing-page') show
 * Header Situs + Footer Situs chrome in the page editor, hiding the section
 * blocks from view. Switching them to the PHP template
 * ('page-templates/template-landing-page.php') makes the block editor display
 * ONLY the post content (hero + sections), which is what the admin needs.
 *
 * The PHP template calls get_header() / get_footer() for the frontend, and its
 * block-based early-exit path calls the_content() to render the blocks.
 *
 * Delete 'ugm_landing_blocks_migrated_v3' option to re-run.
 * ========================================================================== */

add_action( 'admin_init', function () {
	// Block seeding migration disabled; landing pages now use PHP templates.
	return;

	if ( get_option( 'ugm_landing_blocks_migrated_v3' ) ) {
		return;
	}

	$landing_blocks =
		'<!-- wp:ugm/hero-section {"imageId":0,"imageUrl":"","title":"","description":""} /-->' . "\n" .
		'<!-- wp:ugm/latest-news {"title":"Berita Terbaru","categorySlug":""} /-->' . "\n" .
		'<!-- wp:ugm/academic-news {"title":"Berita Akademik","categorySlug":"pendidikan"} /-->' . "\n" .
		'<!-- wp:ugm/profile-section {"title":"Profile","categorySlug":"profile"} /-->' . "\n" .
		'<!-- wp:ugm/achievement-section {"title":"Prestasi","categorySlug":"prestasi"} /-->' . "\n" .
		'<!-- wp:ugm/faculty-section {"title":"Fakultas dan Sekolah","categorySlug":"fakultas-dan-sekolah"} /-->' . "\n" .
		'<!-- wp:ugm/agenda-section {"title":"Agenda Kegiatan","categorySlug":"agenda"} /-->' . "\n" .
		'<!-- wp:ugm/magazine-section /-->';

	// Find ALL pages using the block template slug.
	$pages = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'posts_per_page' => -1,
		'meta_query'     => array(
			array(
				'key'   => '_wp_page_template',
				'value' => 'landing-page', // block template slug
			),
		),
	) );

	foreach ( $pages as $page ) {
		// 1. Populate blocks if not already done.
		$content = (string) $page->post_content;
		$updates = array( 'ID' => $page->ID );

		if ( '' === trim( $content ) || false === strpos( $content, 'ugm/hero-section' ) ) {
			$updates['post_content'] = $landing_blocks;
		}

		// 2. Switch template from block template to PHP template.
		$updates['page_template'] = 'page-templates/template-landing-page.php';

		wp_update_post( $updates );

		// Also update the meta directly to be sure.
		update_post_meta( $page->ID, '_wp_page_template', 'page-templates/template-landing-page.php' );
	}

	update_option( 'ugm_landing_blocks_migrated_v3', true );
} );

/* ==========================================================================
 * Auto-populate new landing pages with section blocks.
 *
 * When an admin creates a brand-new page using the "Halaman Landing" PHP
 * template, WordPress starts with empty post_content. This filter pre-fills
 * it with all landing page blocks so the editor immediately shows the design.
 * ========================================================================== */

add_filter( 'default_content', function ( $content, $post ) {
	// Landing pages are rendered by PHP templates now; do not seed SSR blocks.
	return $content;

	if ( 'page' !== $post->post_type ) {
		return $content;
	}

	$tpl = get_page_template_slug( $post->ID );
	if ( 'page-templates/template-landing-page.php' !== $tpl ) {
		return $content;
	}

	// Only fill in if truly empty.
	if ( '' !== trim( $content ) ) {
		return $content;
	}

	return
		'<!-- wp:ugm/hero-section {"imageId":0,"imageUrl":"","title":"","description":""} /-->' . "\n" .
		'<!-- wp:ugm/latest-news {"title":"Berita Terbaru","categorySlug":""} /-->' . "\n" .
		'<!-- wp:ugm/academic-news {"title":"Berita Akademik","categorySlug":"pendidikan"} /-->' . "\n" .
		'<!-- wp:ugm/profile-section {"title":"Profile","categorySlug":"profile"} /-->' . "\n" .
		'<!-- wp:ugm/achievement-section {"title":"Prestasi","categorySlug":"prestasi"} /-->' . "\n" .
		'<!-- wp:ugm/faculty-section {"title":"Fakultas dan Sekolah","categorySlug":"fakultas-dan-sekolah"} /-->' . "\n" .
		'<!-- wp:ugm/agenda-section {"title":"Agenda Kegiatan","categorySlug":"agenda"} /-->' . "\n" .
		'<!-- wp:ugm/magazine-section /-->';
}, 10, 2 );

add_action( 'admin_init', function () {
	if ( get_option( 'ugm_landing_blocks_migrated_v4_featured_categories' ) ) {
		return;
	}

	$pages = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'posts_per_page' => -1,
		'meta_query'     => array(
			array(
				'key'   => '_wp_page_template',
				'value' => 'page-templates/template-landing-page.php',
			),
		),
	) );

	foreach ( $pages as $page ) {
		$content = (string) $page->post_content;

		if ( '' === trim( $content ) || false !== strpos( $content, 'wp:ugm/featured-categories' ) ) {
			continue;
		}

		$insert = '<!-- wp:ugm/featured-categories {"campusTitle":"Seputar Kampus","campusCategorySlug":"seputar-kampus","facultyTitle":"Kabar Fakultas","facultyCategorySlug":"kabar-fakultas","partnershipTitle":"Kerjasama","partnershipCategorySlug":"kerjasama"} /-->' . "\n";
		$needle = '<!-- wp:ugm/faculty-section';

		if ( false !== strpos( $content, $needle ) ) {
			$content = str_replace( $needle, $insert . $needle, $content );
		} else {
			$content .= "\n" . $insert;
		}

		wp_update_post( array(
			'ID'           => $page->ID,
			'post_content' => $content,
		) );
	}

	update_option( 'ugm_landing_blocks_migrated_v4_featured_categories', true );
} );

add_action( 'admin_init', function () {
	if ( get_option( 'ugm_landing_blocks_migrated_v5_category_section' ) ) {
		return;
	}

	$pages = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'posts_per_page' => -1,
		'meta_query'     => array(
			array(
				'key'   => '_wp_page_template',
				'value' => 'page-templates/template-landing-page.php',
			),
		),
	) );

	foreach ( $pages as $page ) {
		$content = (string) $page->post_content;

		if ( '' === trim( $content ) || false !== strpos( $content, 'wp:ugm/category-section' ) ) {
			continue;
		}

		$insert = '<!-- wp:ugm/category-section {"title":"Kategori"} /-->' . "\n";
		$needle = '<!-- wp:ugm/faculty-section';

		if ( false !== strpos( $content, $needle ) ) {
			$content = str_replace( $needle, $insert . $needle, $content );
		} else {
			$content .= "\n" . $insert;
		}

		wp_update_post( array(
			'ID'           => $page->ID,
			'post_content' => $content,
		) );
	}

	update_option( 'ugm_landing_blocks_migrated_v5_category_section', true );
} );

add_action( 'admin_init', function () {
	if ( get_option( 'ugm_landing_blocks_migrated_v6_remove_facility_section' ) ) {
		return;
	}

	$pages = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'posts_per_page' => -1,
		'meta_query'     => array(
			array(
				'key'   => '_wp_page_template',
				'value' => 'page-templates/template-landing-page.php',
			),
		),
	) );

	foreach ( $pages as $page ) {
		$content = (string) $page->post_content;

		if ( '' === trim( $content ) || false === strpos( $content, 'wp:ugm/facility-section' ) ) {
			continue;
		}

		$content = preg_replace( '/<!-- wp:ugm\\/facility-section\\b.*?\\/-->\\s*/', '', $content );

		wp_update_post( array(
			'ID'           => $page->ID,
			'post_content' => trim( (string) $content ) . "\n",
		) );
	}

	update_option( 'ugm_landing_blocks_migrated_v6_remove_facility_section', true );
} );

/* ==========================================================================
 * Migration v7: add ugm/video-section after magazine-section on existing
 * landing pages so the block appears in the editor canvas.
 * Delete 'ugm_landing_blocks_migrated_v7_video_section' to re-run.
 * ========================================================================== */

add_action( 'admin_init', function () {
	if ( get_option( 'ugm_landing_blocks_migrated_v7_video_section' ) ) {
		return;
	}

	$pages = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'posts_per_page' => -1,
		'meta_query'     => array(
			array(
				'key'   => '_wp_page_template',
				'value' => 'page-templates/template-landing-page.php',
			),
		),
	) );

	foreach ( $pages as $page ) {
		$content = (string) $page->post_content;

		// Skip empty or already-migrated.
		if ( '' === trim( $content ) || false !== strpos( $content, 'wp:ugm/video-section' ) ) {
			continue;
		}

		$insert = '<!-- wp:ugm/video-section {"title":"Video","categorySlug":"video"} /-->' . "\n";
		$needle = '<!-- wp:ugm/magazine-section';

		if ( false !== strpos( $content, $needle ) ) {
			// Insert AFTER the magazine block line.
			$content = preg_replace(
				'/(' . preg_quote( '<!-- wp:ugm/magazine-section', '/' ) . '[^\n]*)(\n|$)/',
				'$1' . "\n" . $insert,
				$content,
				1
			);
		} else {
			// Fallback: append at the end.
			$content .= "\n" . $insert;
		}

		wp_update_post( array(
			'ID'           => $page->ID,
			'post_content' => $content,
		) );
	}

	update_option( 'ugm_landing_blocks_migrated_v7_video_section', true );
} );

/* ==========================================================================
 * Migration v8: split ugm/featured-categories into 3 individual
 * ugm/featured-category-column blocks so each column is independently
 * movable in the Gutenberg editor.
 *
 * Delete 'ugm_landing_blocks_migrated_v8_split_featured_cols' to re-run.
 * ========================================================================== */

add_action( 'admin_init', function () {
	if ( get_option( 'ugm_landing_blocks_migrated_v8_split_featured_cols' ) ) {
		return;
	}

	$pages = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'posts_per_page' => -1,
		'meta_query'     => array(
			array(
				'key'   => '_wp_page_template',
				'value' => 'page-templates/template-landing-page.php',
			),
		),
	) );

	foreach ( $pages as $page ) {
		$content = (string) $page->post_content;

		// Skip pages that don't have the old block, or already have new blocks.
		if ( false === strpos( $content, 'wp:ugm/featured-categories' )
			|| false !== strpos( $content, 'wp:ugm/featured-category-column' )
		) {
			continue;
		}

		// Extract attrs from the old block comment.
		$attrs = array(
			'campusTitle'            => 'Seputar Kampus',
			'campusCategorySlug'     => 'seputar-kampus',
			'facultyTitle'           => 'Kabar Fakultas',
			'facultyCategorySlug'    => 'kabar-fakultas',
			'partnershipTitle'       => 'Kerjasama',
			'partnershipCategorySlug'=> 'kerjasama',
		);

		if ( preg_match( '/<!-- wp:ugm\/featured-categories ({.*?}) \/-->/', $content, $m ) ) {
			$decoded = json_decode( $m[1], true );
			if ( is_array( $decoded ) ) {
				$attrs = array_merge( $attrs, $decoded );
			}
		}

		// Build the three replacement column blocks.
		$col1 = '<!-- wp:ugm/featured-category-column ' . wp_json_encode( array(
			'title'        => $attrs['campusTitle'],
			'categorySlug' => $attrs['campusCategorySlug'],
		) ) . ' /-->';

		$col2 = '<!-- wp:ugm/featured-category-column ' . wp_json_encode( array(
			'title'        => $attrs['facultyTitle'],
			'categorySlug' => $attrs['facultyCategorySlug'],
		) ) . ' /-->';

		$col3 = '<!-- wp:ugm/featured-category-column ' . wp_json_encode( array(
			'title'        => $attrs['partnershipTitle'],
			'categorySlug' => $attrs['partnershipCategorySlug'],
		) ) . ' /-->';

		$replacement = $col1 . "\n" . $col2 . "\n" . $col3;

		// Replace the old block using s-flag to handle any format
		// (with attrs, without attrs, or multi-line variants).
		$new_content = preg_replace(
			'/<!-- wp:ugm\/featured-categories.*?\/-->/s',
			$replacement,
			$content
		);

		if ( null !== $new_content && $new_content !== $content ) {
			$content = $new_content;
		}

		wp_update_post( array(
			'ID'           => $page->ID,
			'post_content' => (string) $content,
		) );
	}

	update_option( 'ugm_landing_blocks_migrated_v8_split_featured_cols', true );
} );
