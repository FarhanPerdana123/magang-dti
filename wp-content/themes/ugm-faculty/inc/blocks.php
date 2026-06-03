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
	if ( '' === trim( (string) $title ) ) {
		return '';
	}

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
 * Resolve a section title while preserving an intentionally empty value.
 *
 * Missing attributes mean the block predates the current default and should
 * receive the default title. An explicit empty string means the editor user
 * intentionally removed the heading.
 *
 * @param array  $attrs         Block attributes.
 * @param string $default_title Default title for legacy blocks.
 * @param string $key           Attribute key.
 * @return string
 */
function ugm_resolve_section_title( $attrs, $default_title = '', $key = 'title' ) {
	if ( is_array( $attrs ) && array_key_exists( $key, $attrs ) ) {
		return trim( (string) $attrs[ $key ] );
	}

	return trim( (string) $default_title );
}

/**
 * Map block attribute visibility to a CSS class.
 *
 * @param array $attrs Block attributes.
 * @return string CSS class name (or empty string).
 */
function ugm_block_visibility_class( $attrs ) {
	$visibility = is_array( $attrs ) && isset( $attrs['visibility'] )
		? (string) $attrs['visibility']
		: 'all';
	$visibility = strtolower( trim( $visibility ) );

	if ( 'desktop' === $visibility ) {
		return 'ugm-only-desktop';
	}
	if ( 'mobile' === $visibility ) {
		return 'ugm-only-mobile';
	}
	return '';
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
 * Parse a category slug attribute into a sanitized unique slug list.
 *
 * Accepts comma-separated input such as "pendidikan, profile".
 *
 * @param mixed            $raw_slug_attr Raw attribute value.
 * @param string|string[] $default_slugs Optional fallback slugs when the attribute is empty.
 * @return string[]
 */
function ugm_parse_category_slug_list( $raw_slug_attr, $default_slugs = array() ) {
	$slugs = array();
	$parts = is_array( $raw_slug_attr )
		? $raw_slug_attr
		: preg_split( '/\s*,\s*/', (string) $raw_slug_attr );

	foreach ( (array) $parts as $part ) {
		$slug = sanitize_key( trim( (string) $part ) );
		if ( '' !== $slug ) {
			$slugs[] = $slug;
		}
	}

	if ( empty( $slugs ) ) {
		foreach ( (array) $default_slugs as $default_slug ) {
			$slug = sanitize_key( trim( (string) $default_slug ) );
			if ( '' !== $slug ) {
				$slugs[] = $slug;
			}
		}
	}

	$slugs = array_values( array_unique( $slugs ) );
	$slugs = ugm_expand_category_slug_aliases( $slugs );
	return array_values( array_unique( $slugs ) );
}

/**
 * Expand alias slugs into additional category slugs.
 *
 * This allows using a single configured slug that represents multiple
 * categories, e.g. entering "pendidikan-profile" can include posts from both
 * "pendidikan" and "profile" categories.
 *
 * The original slug is preserved (union behavior).
 *
 * @param string[] $slugs Sanitized category slugs.
 * @return string[] Expanded slug list.
 */
function ugm_expand_category_slug_aliases( array $slugs ) {
	$aliases = apply_filters(
		'ugm_category_slug_aliases',
		array(
			// Example alias used on some sites.
			'pendidikan-profile' => array( 'pendidikan', 'profile' ),
			'profile-pendidikan' => array( 'profile', 'pendidikan' ),
		)
	);

	if ( empty( $aliases ) || ! is_array( $aliases ) ) {
		return $slugs;
	}

	// Build reverse map so members can include their alias keys.
	$reverse = array();
	foreach ( $aliases as $alias_key => $members ) {
		$alias_key = sanitize_key( trim( (string) $alias_key ) );
		if ( '' === $alias_key || ! is_array( $members ) ) {
			continue;
		}
		foreach ( $members as $member ) {
			$member = sanitize_key( trim( (string) $member ) );
			if ( '' === $member ) {
				continue;
			}
			if ( ! isset( $reverse[ $member ] ) ) {
				$reverse[ $member ] = array();
			}
			$reverse[ $member ][] = $alias_key;
		}
	}

	$out = $slugs;
	foreach ( $slugs as $slug ) {
		// If user enters an alias slug, include its member slugs.
		if ( isset( $aliases[ $slug ] ) && is_array( $aliases[ $slug ] ) ) {
			foreach ( $aliases[ $slug ] as $member_slug ) {
				$member_slug = sanitize_key( trim( (string) $member_slug ) );
				if ( '' !== $member_slug ) {
					$out[] = $member_slug;
				}
			}
		}

		// If user enters a member slug, also include any alias slugs that contain it.
		if ( isset( $reverse[ $slug ] ) && is_array( $reverse[ $slug ] ) ) {
			foreach ( $reverse[ $slug ] as $alias_key ) {
				$alias_key = sanitize_key( trim( (string) $alias_key ) );
				if ( '' !== $alias_key ) {
					$out[] = $alias_key;
				}
			}
		}
	}

	return array_values( array_unique( $out ) );
}

/**
 * Build a category archive URL from one or more slugs.
 *
 * @param string[] $slugs Sanitized category slugs.
 * @return string
 */
function ugm_get_category_archive_url_from_slugs( array $slugs ) {
	$term = ugm_get_category_by_candidate_slugs( $slugs );

	if ( $term instanceof WP_Term ) {
		$link = get_category_link( $term->term_id );
		if ( ! is_wp_error( $link ) ) {
			return $link;
		}
	}

	if ( ! empty( $slugs ) ) {
		return home_url( '/category/' . reset( $slugs ) . '/' );
	}

	return home_url( '/category/' );
}

/**
 * Render the "featured category columns" section shared by template and block preview.
 *
 * @param array $attrs Block attributes.
 * @return string
 */
function ugm_render_featured_categories_markup( $attrs = array() ) {
	$visibility_class = ugm_block_visibility_class( $attrs );
	$defaults = array(
		'campusTitle'       => __( 'Seputar Kampus', 'ugm-faculty' ),
		'campusCategorySlug' => 'seputar-kampus',
		'facultyTitle'      => __( 'Kabar Fakultas', 'ugm-faculty' ),
		'facultyCategorySlug' => 'kabar-fakultas',
		'partnershipTitle'  => __( 'Kerjasama', 'ugm-faculty' ),
		'partnershipCategorySlug' => 'kerjasama',
	);
	$attrs = is_array( $attrs ) ? $attrs : array();

	$sections = array(
		array(
			'title'      => ugm_resolve_section_title( $attrs, $defaults['campusTitle'], 'campusTitle' ),
			'slugs'      => ugm_parse_category_slug_list( $attrs['campusCategorySlug'] ?? '', array( $defaults['campusCategorySlug'] ) ),
			'empty_text' => __( 'Belum ada artikel seputar kampus.', 'ugm-faculty' ),
			'fallback'   => home_url( '/category/' ),
		),
		array(
			'title'      => ugm_resolve_section_title( $attrs, $defaults['facultyTitle'], 'facultyTitle' ),
			'slugs'      => ugm_parse_category_slug_list( $attrs['facultyCategorySlug'] ?? '', array( $defaults['facultyCategorySlug'] ) ),
			'empty_text' => __( 'Belum ada kabar fakultas.', 'ugm-faculty' ),
			'fallback'   => home_url( '/category/' ),
		),
		array(
			'title'      => ugm_resolve_section_title( $attrs, $defaults['partnershipTitle'], 'partnershipTitle' ),
			'slugs'      => ugm_parse_category_slug_list( $attrs['partnershipCategorySlug'] ?? '', array( $defaults['partnershipCategorySlug'] ) ),
			'empty_text' => __( 'Belum ada artikel kerjasama.', 'ugm-faculty' ),
			'fallback'   => home_url( '/category/' ),
		),
	);

	ob_start();
	?>
	<section class="home-section section-featured-categories <?php echo esc_attr( $visibility_class ); ?>" aria-label="<?php esc_attr_e( 'Sorotan kategori', 'ugm-faculty' ); ?>">
		<div class="featured-category-grid">
			<?php foreach ( $sections as $section ) : ?>
				<?php
				$term = ugm_get_category_by_candidate_slugs( $section['slugs'] );
				$link = $term instanceof WP_Term ? get_category_link( $term->term_id ) : $section['fallback'];
				$term_ids = ugm_resolve_multiple_slugs_to_ids( $section['slugs'] );
				$args = array(
					'post_type'           => 'post',
					'posts_per_page'      => 1,
					'ignore_sticky_posts' => true,
					'post_status'         => 'publish',
					'no_found_rows'       => true,
				);

				if ( ! empty( $term_ids ) ) {
					$args['category__in'] = $term_ids;
				} else {
					$args['post__in'] = array( 0 );
				}

				$q = new WP_Query( $args );
				?>
				<section class="featured-category-column" aria-label="<?php echo esc_attr( $section['title'] ); ?>">
					<?php if ( '' !== $section['title'] ) : ?>
						<header class="section-header">
							<h2 class="section-title"><?php echo esc_html( $section['title'] ); ?></h2>
							<span class="section-line" aria-hidden="true"></span>
						</header>
					<?php endif; ?>

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
	$visibility_class = ugm_block_visibility_class( $attrs );
	$title = ugm_resolve_section_title( $attrs, __( 'Kategori', 'ugm-faculty' ) );

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
	<section class="home-section section-category <?php echo esc_attr( $visibility_class ); ?>" aria-labelledby="section-category-title">
		<?php if ( '' !== $title ) : ?>
			<header class="section-header">
				<h2 id="section-category-title" class="section-title"><?php echo esc_html( $title ); ?></h2>
				<span class="section-line" aria-hidden="true"></span>
			</header>
		<?php endif; ?>

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
	$visibility_class = ugm_block_visibility_class( $attrs );
	// Single ob_start/ob_get_clean — everything echoed into one buffer.
	// IMPORTANT: do NOT use nested ob_start() without ob_get_clean(), and do NOT
	// return a manually-built string while a buffer is still open — that leaks
	// extra output into the REST JSON response making it invalid.
	ob_start();

	echo '<header id="masthead" class="site-header is-solid ' . esc_attr( $visibility_class ) . '" style="position:relative;top:auto;">';
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
	'attributes'      => array(
		'visibility' => array( 'type' => 'string', 'default' => 'all' ),
	),
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
	$visibility_class = ugm_block_visibility_class( $attrs );
	$media_type       = isset( $attrs['mediaType'] ) && 'video' === $attrs['mediaType'] ? 'video' : 'image';
	$image_id  = isset( $attrs['imageId'] ) ? absint( $attrs['imageId'] ) : 0;
	$image_url = '';
	$video_id  = isset( $attrs['videoId'] ) ? absint( $attrs['videoId'] ) : 0;
	$video_url = '';
	$slide_images = array();

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

	if ( $video_id > 0 ) {
		$video_url = (string) wp_get_attachment_url( $video_id );
	} elseif ( ! empty( $attrs['videoUrl'] ) ) {
		$video_url = esc_url_raw( (string) $attrs['videoUrl'] );
	}

	$has_video  = 'video' === $media_type && '' !== $video_url;
	$video_type = 'video/mp4';
	if ( $has_video ) {
		$video_filetype = wp_check_filetype( $video_url );
		if ( ! empty( $video_filetype['type'] ) ) {
			$video_type = $video_filetype['type'];
		}
	}

	if ( ! $has_video && ! empty( $attrs['slideImages'] ) && is_array( $attrs['slideImages'] ) ) {
		foreach ( $attrs['slideImages'] as $slide_image ) {
			if ( ! is_array( $slide_image ) ) {
				continue;
			}

			$slide_id  = isset( $slide_image['id'] ) ? absint( $slide_image['id'] ) : 0;
			$slide_url = '';
			if ( $slide_id > 0 ) {
				$slide_url = (string) wp_get_attachment_image_url( $slide_id, 'full' );
			}
			if ( '' === $slide_url && ! empty( $slide_image['url'] ) ) {
				$slide_url = esc_url_raw( (string) $slide_image['url'] );
			}
			if ( '' !== $slide_url ) {
				$slide_images[] = array(
					'id'  => $slide_id,
					'url' => $slide_url,
				);
			}
		}
	}

	if ( empty( $slide_images ) && '' !== $image_url ) {
		$slide_images[] = array(
			'id'  => $image_id,
			'url' => $image_url,
		);
	}

	$has_slider = ! $has_video && count( $slide_images ) > 1;
	$hero_id    = $has_slider ? 'ugm-hero-slider-' . wp_unique_id() : '';
	$title = isset( $attrs['title'] ) ? trim( (string) $attrs['title'] ) : '';
	$desc  = isset( $attrs['description'] ) ? trim( (string) $attrs['description'] ) : '';

	ob_start();
	?>
	<section class="hero <?php echo esc_attr( $visibility_class ); ?><?php echo $has_slider ? ' hero--slider ' . esc_attr( $hero_id ) : ''; ?>" <?php echo ( $image_url && ! $has_video && ! $has_slider ) ? 'style="background-image: url(' . esc_url( $image_url ) . ');"' : ''; ?> aria-labelledby="hero-title">
		<?php if ( $has_slider ) : ?>
			<style>
				<?php
				$slide_count    = count( $slide_images );
				$animation_time = $slide_count * 8;
				foreach ( $slide_images as $slide_index => $slide_image ) :
					$delay = $slide_index * 8;
					?>
					.<?php echo esc_html( $hero_id ); ?> .hero__slide--<?php echo esc_html( (string) $slide_index ); ?> {
						animation-delay: <?php echo esc_html( (string) $delay ); ?>s;
					}
				<?php endforeach; ?>
				.<?php echo esc_html( $hero_id ); ?> .hero__slide {
					animation-duration: <?php echo esc_html( (string) $animation_time ); ?>s;
				}
			</style>
			<div class="hero__slides" aria-hidden="true">
				<?php foreach ( $slide_images as $slide_index => $slide_image ) : ?>
					<span class="hero__slide hero__slide--<?php echo esc_attr( (string) $slide_index ); ?>" style="background-image: url('<?php echo esc_url( $slide_image['url'] ); ?>');"></span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php if ( $has_video ) : ?>
			<video class="hero__video" autoplay muted loop playsinline preload="metadata" <?php echo $image_url ? 'poster="' . esc_url( $image_url ) . '"' : ''; ?> aria-hidden="true">
				<source src="<?php echo esc_url( $video_url ); ?>" type="<?php echo esc_attr( $video_type ); ?>">
			</video>
		<?php endif; ?>
		<div class="hero__overlay" aria-hidden="true"></div>
		<?php if ( '' !== $title || '' !== $desc ) : ?>
			<div class="hero__content">
				<?php if ( '' !== $title ) : ?>
					<h1 id="hero-title" class="hero__title"><?php echo wp_kses_post( nl2br( esc_html( $title ), false ) ); ?></h1>
				<?php endif; ?>
				<?php if ( '' !== $desc ) : ?>
					<p class="hero__description"><?php echo esc_html( $desc ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
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
		'mediaType'   => array( 'type' => 'string',  'default' => 'image' ),
		'slideImages' => array( 'type' => 'array',   'default' => array() ),
		'videoId'     => array( 'type' => 'integer', 'default' => 0 ),
		'videoUrl'    => array( 'type' => 'string',  'default' => '' ),
		'title'       => array( 'type' => 'string',  'default' => '' ),
		'description' => array( 'type' => 'string',  'default' => '' ),
		'visibility'  => array( 'type' => 'string',  'default' => 'all' ),
	),
) );

/* ==========================================================================
 * 3. ugm/site-footer — Theme Footer
 * ========================================================================== */

function ugm_render_block_site_footer( $attrs ) {
	$visibility_class = ugm_block_visibility_class( $attrs );
	ob_start();
	// Render the <footer> element using the widgetized footer output.
	?><footer id="colophon" class="site-footer ugm-footer <?php echo esc_attr( $visibility_class ); ?>" aria-labelledby="footer-block-title">
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
	'attributes'      => array(
		'visibility' => array( 'type' => 'string', 'default' => 'all' ),
	),
) );

/* ==========================================================================
 * Footer widget blocks
 * ========================================================================== */

/**
 * Get the footer social-media field definitions.
 *
 * @return array[]
 */
function ugm_get_footer_social_definitions() {
	return array(
		array( 'label' => __( 'Instagram', 'ugm-faculty' ), 'attr' => 'instagramUrl', 'icon' => 'Component Instagram.png', 'default' => 'https://www.instagram.com/' ),
		array( 'label' => __( 'YouTube', 'ugm-faculty' ), 'attr' => 'youtubeUrl', 'icon' => 'Component YouTube.png', 'default' => 'https://www.youtube.com/' ),
		array( 'label' => __( 'Facebook', 'ugm-faculty' ), 'attr' => 'facebookUrl', 'icon' => 'Component Facebook.png', 'default' => 'https://www.facebook.com/' ),
		array( 'label' => __( 'X', 'ugm-faculty' ), 'attr' => 'xUrl', 'icon' => 'Component Twitter.png', 'default' => 'https://x.com/' ),
		array( 'label' => __( 'LinkedIn', 'ugm-faculty' ), 'attr' => 'linkedinUrl', 'icon' => 'Component LinkedIn.png', 'default' => 'https://www.linkedin.com/' ),
		array( 'label' => __( 'TikTok', 'ugm-faculty' ), 'attr' => 'tiktokUrl', 'icon' => 'Component TikTok.png', 'default' => 'https://www.tiktok.com/' ),
	);
}

/**
 * Recursively find the first footer-social block in a parsed block tree.
 *
 * @param array[] $blocks Parsed blocks.
 * @return array|null
 */
function ugm_find_footer_social_block_attrs( $blocks ) {
	foreach ( $blocks as $block ) {
		if ( 'ugm/footer-social' === ( $block['blockName'] ?? '' ) ) {
			return is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array();
		}

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			$attrs = ugm_find_footer_social_block_attrs( $block['innerBlocks'] );
			if ( null !== $attrs ) {
				return $attrs;
			}
		}
	}

	return null;
}

/**
 * Read the footer-social block attributes from the footer social widget area.
 *
 * @return array|null
 */
function ugm_get_footer_social_widget_attrs() {
	$sidebars_widgets = get_option( 'sidebars_widgets', array() );
	$widget_ids       = $sidebars_widgets['footer-social-widget'] ?? array();
	$block_widgets    = get_option( 'widget_block', array() );

	if ( ! is_array( $widget_ids ) || ! is_array( $block_widgets ) ) {
		return null;
	}

	foreach ( $widget_ids as $widget_id ) {
		if ( ! preg_match( '/^block-(\d+)$/', (string) $widget_id, $matches ) ) {
			continue;
		}

		$content = (string) ( $block_widgets[ (int) $matches[1] ]['content'] ?? '' );
		if ( '' === $content || false === strpos( $content, 'ugm/footer-social' ) ) {
			continue;
		}

		$attrs = ugm_find_footer_social_block_attrs( parse_blocks( $content ) );
		if ( null !== $attrs ) {
			return $attrs;
		}
	}

	return null;
}

/**
 * Resolve social media items using footer-social block settings as source.
 *
 * @param array|null $attrs Optional block attributes. If null, read widget settings.
 * @return array[]
 */
function ugm_get_footer_social_items( $attrs = null ) {
	if ( null === $attrs ) {
		$attrs = ugm_get_footer_social_widget_attrs();
	}

	$attrs = is_array( $attrs ) ? $attrs : array();
	$items = array();

	foreach ( ugm_get_footer_social_definitions() as $definition ) {
		$url = array_key_exists( $definition['attr'], $attrs )
			? (string) $attrs[ $definition['attr'] ]
			: (string) $definition['default'];

		$items[] = array(
			'label' => $definition['label'],
			'file'  => $definition['icon'],
			'icon'  => $definition['icon'],
			'url'   => $url,
		);
	}

	return $items;
}

/**
 * Render the footer social-media widget block.
 *
 * @param array $attrs Block attributes.
 * @return string
 */
function ugm_render_block_footer_social( $attrs ) {
	$social_items = ugm_get_footer_social_items( $attrs );

	ob_start();
	?>
	<ul class="ugm-footer__social" aria-label="<?php esc_attr_e( 'Social media', 'ugm-faculty' ); ?>">
		<?php foreach ( $social_items as $social_item ) : ?>
			<?php $social_url = esc_url( (string) ( $social_item['url'] ?? '' ) ); ?>
			<?php if ( '' !== $social_url ) : ?>
				<li class="ugm-footer__social-item">
					<a class="ugm-footer__social-link" href="<?php echo esc_url( $social_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $social_item['label'] ); ?>">
						<img class="ugm-footer__social-icon" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/' . $social_item['file'] ) ); ?>" alt="" loading="lazy">
					</a>
				</li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>
	<?php
	return ob_get_clean();
}

register_block_type( 'ugm/footer-social', array(
	'title'           => __( 'Footer - Social Media', 'ugm-faculty' ),
	'description'     => __( 'Ikon dan tautan media sosial untuk area widget footer.', 'ugm-faculty' ),
	'category'        => 'widgets',
	'render_callback' => 'ugm_render_block_footer_social',
	'supports'        => array( 'html' => false, 'multiple' => false ),
	'attributes'      => array(
		'instagramUrl' => array( 'type' => 'string', 'default' => 'https://www.instagram.com/' ),
		'youtubeUrl'   => array( 'type' => 'string', 'default' => 'https://www.youtube.com/' ),
		'facebookUrl'  => array( 'type' => 'string', 'default' => 'https://www.facebook.com/' ),
		'xUrl'         => array( 'type' => 'string', 'default' => 'https://x.com/' ),
		'linkedinUrl'  => array( 'type' => 'string', 'default' => 'https://www.linkedin.com/' ),
		'tiktokUrl'    => array( 'type' => 'string', 'default' => 'https://www.tiktok.com/' ),
	),
) );

/**
 * Resolve a selected media image with a bundled theme-asset fallback.
 *
 * @param array  $attrs         Block attributes.
 * @param string $id_key        Attachment ID attribute.
 * @param string $url_key       Image URL attribute.
 * @param string $fallback_path Relative theme asset path.
 * @param string $size          Image size.
 * @return string
 */
function ugm_resolve_footer_widget_image( $attrs, $id_key, $url_key, $fallback_path, $size = 'full' ) {
	$image_id  = absint( $attrs[ $id_key ] ?? 0 );
	$image_url = $image_id > 0 ? (string) wp_get_attachment_image_url( $image_id, $size ) : '';

	if ( '' === $image_url && ! empty( $attrs[ $url_key ] ) ) {
		$image_url = esc_url_raw( (string) $attrs[ $url_key ] );
	}

	if ( '' === $image_url && file_exists( get_theme_file_path( $fallback_path ) ) ) {
		$image_url = get_theme_file_uri( $fallback_path );
	}

	return $image_url;
}

/**
 * Render the footer brand/logo widget block.
 *
 * @param array $attrs Block attributes.
 * @return string
 */
function ugm_render_block_footer_brand( $attrs ) {
	$light_logo_id  = absint( get_theme_mod( 'ugm_logo_light' ) );
	$dark_logo_id   = absint( get_theme_mod( 'ugm_logo_dark' ) );
	$custom_logo_id = absint( get_theme_mod( 'custom_logo' ) );
	$home_url       = home_url( '/' );

	$image_url = $light_logo_id ? (string) wp_get_attachment_image_url( $light_logo_id, 'full' ) : '';
	if ( '' === $image_url && $dark_logo_id ) {
		$image_url = (string) wp_get_attachment_image_url( $dark_logo_id, 'full' );
	}
	if ( '' === $image_url && $custom_logo_id ) {
		$image_url = (string) wp_get_attachment_image_url( $custom_logo_id, 'full' );
	}
	if ( '' === $image_url ) {
		$image_url = ugm_resolve_footer_widget_image( array(), 'imageId', 'imageUrl', 'assets/images/Footer.png', 'medium' );
	}

	$branding_lines = array_values(
		array_filter(
			array(
				trim( (string) get_theme_mod( 'ugm_branding_line_1', 'UNIVERSITAS' ) ),
				trim( (string) get_theme_mod( 'ugm_branding_line_2', 'GADJAH MADA' ) ),
				trim( (string) get_theme_mod( 'ugm_branding_line_3', '' ) ),
			),
			static function ( $line ) {
				return '' !== $line;
			}
		)
	);

	if ( '' === $image_url && empty( $branding_lines ) ) {
		return '';
	}

	$alt = trim( (string) ( $attrs['alt'] ?? '' ) );
	if ( '' === $alt ) {
		$alt = get_bloginfo( 'name' );
	}

	ob_start();
	?>
	<a class="ugm-footer__brand-link" href="<?php echo esc_url( $home_url ); ?>" rel="home" aria-label="<?php esc_attr_e( 'Home', 'ugm-faculty' ); ?>">
		<?php if ( '' !== $image_url ) : ?>
			<img class="ugm-footer__brand-img" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $alt ); ?>" loading="lazy">
		<?php endif; ?>
		<?php if ( ! empty( $branding_lines ) ) : ?>
			<span class="ugm-footer__brand-text-stack">
				<?php foreach ( $branding_lines as $branding_line ) : ?>
					<span class="ugm-footer__brand-text-line"><?php echo esc_html( $branding_line ); ?></span>
				<?php endforeach; ?>
			</span>
		<?php endif; ?>
	</a>
	<?php
	return (string) ob_get_clean();
}

register_block_type( 'ugm/footer-brand', array(
	'title'           => __( 'Footer - Brand / Logo', 'ugm-faculty' ),
	'description'     => __( 'Logo institusi untuk area widget footer.', 'ugm-faculty' ),
	'category'        => 'widgets',
	'render_callback' => 'ugm_render_block_footer_brand',
	'supports'        => array( 'html' => false, 'multiple' => false ),
	'attributes'      => array(
		'imageId'  => array( 'type' => 'integer', 'default' => 0 ),
		'imageUrl' => array( 'type' => 'string', 'default' => '' ),
		'alt'      => array( 'type' => 'string', 'default' => 'Universitas Gadjah Mada' ),
	),
) );

/**
 * Render the footer contact widget block.
 *
 * @param array $attrs Block attributes.
 * @return string
 */
function ugm_render_block_footer_contact( $attrs ) {
	$address  = trim( (string) ( $attrs['address'] ?? '' ) );
	$email    = sanitize_email( (string) ( $attrs['email'] ?? '' ) );
	$whatsapp = trim( (string) ( $attrs['whatsapp'] ?? '' ) );
	$wa_url   = preg_replace( '/[^0-9]/', '', $whatsapp );

	$contact_icons = array(
		'address'  => array(
			'id'       => absint( $attrs['addressIconId'] ?? 0 ),
			'url'      => esc_url_raw( (string) ( $attrs['addressIconUrl'] ?? '' ) ),
			'class'    => 'ugm-footer-contact__icon--address',
			'fallback' => '<svg viewBox="0 0 24 24" role="presentation" focusable="false"><path d="M12 2C8.2 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.2-7-7-7zm0 9.4A2.4 2.4 0 1 1 12 6a2.4 2.4 0 0 1 0 4.8z"/></svg>',
		),
		'email'    => array(
			'id'       => absint( $attrs['emailIconId'] ?? 0 ),
			'url'      => esc_url_raw( (string) ( $attrs['emailIconUrl'] ?? '' ) ),
			'class'    => 'ugm-footer-contact__icon--email',
			'fallback' => '<svg viewBox="0 0 24 24" role="presentation" focusable="false"><path d="M2 5h20v14H2V5zm2.1 2 7.9 6.2L19.9 7H4.1zm-.1 10h16V8.9l-8 6.3-8-6.3V17z"/></svg>',
		),
		'whatsapp' => array(
			'id'       => absint( $attrs['whatsappIconId'] ?? 0 ),
			'url'      => esc_url_raw( (string) ( $attrs['whatsappIconUrl'] ?? '' ) ),
			'class'    => 'ugm-footer-contact__icon--whatsapp',
			'fallback' => '<svg viewBox="0 0 24 24" role="presentation" focusable="false"><path d="M12 2a9.8 9.8 0 0 0-8.5 14.8L2.4 22l5.3-1.3A9.9 9.9 0 1 0 12 2zm0 2a7.9 7.9 0 0 1 0 15.8 8 8 0 0 1-4-.9l-.4-.2-2.4.6.5-2.3-.3-.4A7.9 7.9 0 0 1 12 4zm-3.1 3.8c-.2 0-.5.1-.7.4-.2.3-.8.8-.8 2s.8 2.3.9 2.5c.1.2 1.6 2.6 4 3.5 2 .8 2.4.6 2.8.6.4 0 1.4-.6 1.6-1.1.2-.6.2-1 .1-1.1l-.6-.3-1.6-.8c-.2-.1-.4-.1-.6.2l-.7.9c-.1.2-.3.2-.5.1-.3-.1-1.1-.4-2-1.2-.7-.7-1.2-1.4-1.4-1.7-.1-.2 0-.4.1-.5l.4-.5.2-.4c.1-.2.1-.3 0-.5l-.8-1.8c-.2-.2-.3-.2-.5-.2z"/></svg>',
		),
	);

	$render_icon = static function ( $key ) use ( $contact_icons ) {
		$icon = $contact_icons[ $key ];
		$url  = $icon['id'] ? (string) wp_get_attachment_image_url( $icon['id'], 'thumbnail' ) : '';
		if ( '' === $url && '' !== $icon['url'] ) {
			$url = $icon['url'];
		}

		if ( '' !== $url ) {
			return '<img class="ugm-footer-contact__icon ' . esc_attr( $icon['class'] ) . '" src="' . esc_url( $url ) . '" alt="" loading="lazy" decoding="async">';
		}

		return '<span class="ugm-footer-contact__icon ' . esc_attr( $icon['class'] ) . '" aria-hidden="true">' . $icon['fallback'] . '</span>';
	};

	ob_start();
	?>
	<div class="ugm-footer-contact">
		<?php if ( '' !== $address ) : ?>
			<p class="ugm-footer-contact__address ugm-footer-contact__item">
				<?php echo $render_icon( 'address' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="ugm-footer-contact__address-text"><?php echo wp_kses_post( nl2br( esc_html( $address ), false ) ); ?></span>
			</p>
		<?php endif; ?>
		<div class="ugm-footer-contact__links">
			<?php if ( '' !== $email ) : ?>
				<a class="ugm-footer-contact__link ugm-footer-contact__item" href="mailto:<?php echo esc_attr( $email ); ?>">
					<?php echo $render_icon( 'email' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php echo esc_html( $email ); ?></span>
				</a>
			<?php endif; ?>
			<?php if ( '' !== $whatsapp ) : ?>
				<a class="ugm-footer-contact__link ugm-footer-contact__item" href="<?php echo esc_url( '' !== $wa_url ? 'https://wa.me/' . $wa_url : '#' ); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo $render_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php echo esc_html( $whatsapp ); ?></span>
				</a>
			<?php endif; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

register_block_type( 'ugm/footer-contact', array(
	'title'           => __( 'Footer - Kontak & Alamat', 'ugm-faculty' ),
	'description'     => __( 'Alamat, email, telepon, faks, dan WhatsApp institusi.', 'ugm-faculty' ),
	'category'        => 'widgets',
	'render_callback' => 'ugm_render_block_footer_contact',
	'supports'        => array( 'html' => false, 'multiple' => false ),
	'attributes'      => array(
		'address'  => array( 'type' => 'string', 'default' => "Bulaksumur, Caturtunggal, Kec. Depok,\nKabupaten Sleman, Daerah Istimewa\nYogyakarta 55281" ),
		'email'    => array( 'type' => 'string', 'default' => 'info@ugm.ac.id' ),
		'phone'    => array( 'type' => 'string', 'default' => '+62(274)588688' ),
		'fax'      => array( 'type' => 'string', 'default' => '+62(274)565223' ),
		'whatsapp' => array( 'type' => 'string', 'default' => '+628112869988' ),
		'addressIconId'  => array( 'type' => 'integer', 'default' => 0 ),
		'addressIconUrl' => array( 'type' => 'string', 'default' => '' ),
		'emailIconId'    => array( 'type' => 'integer', 'default' => 0 ),
		'emailIconUrl'   => array( 'type' => 'string', 'default' => '' ),
		'whatsappIconId'  => array( 'type' => 'integer', 'default' => 0 ),
		'whatsappIconUrl' => array( 'type' => 'string', 'default' => '' ),
	),
) );

/**
 * Render the footer bottom-banner widget block.
 *
 * @param array $attrs Block attributes.
 * @return string
 */
function ugm_render_block_footer_banner( $attrs ) {
	$image_url = ugm_resolve_footer_widget_image( $attrs, 'imageId', 'imageUrl', 'assets/images/Image Footer.png' );
	if ( '' === $image_url ) {
		return '';
	}

	return '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( (string) ( $attrs['alt'] ?? '' ) ) . '" loading="lazy">';
}

register_block_type( 'ugm/footer-banner', array(
	'title'           => __( 'Footer - Banner Bawah', 'ugm-faculty' ),
	'description'     => __( 'Gambar panorama di bagian paling bawah footer.', 'ugm-faculty' ),
	'category'        => 'widgets',
	'render_callback' => 'ugm_render_block_footer_banner',
	'supports'        => array( 'html' => false, 'multiple' => false ),
	'attributes'      => array(
		'imageId'  => array( 'type' => 'integer', 'default' => 0 ),
		'imageUrl' => array( 'type' => 'string', 'default' => '' ),
		'alt'      => array( 'type' => 'string', 'default' => 'Kampus Universitas Gadjah Mada' ),
	),
) );

/* ==========================================================================
 * Common attributes shared by content sections
 * ========================================================================== */

function ugm_get_section_attrs( $default_title = '' ) {
	return array(
		'title'        => array(
			'type'    => 'string',
			'default' => $default_title,
		),
		'categorySlug' => array(
			'type'    => 'string',
			'default' => '',
		),
		'visibility'  => array(
			'type'    => 'string',
			'default' => 'all',
		),
	);
}

/* ==========================================================================
 * 3. ugm/latest-news — Berita Terbaru
 * ========================================================================== */

function ugm_render_block_latest_news( $attrs ) {
	$visibility_class = ugm_block_visibility_class( $attrs );
	$title    = ugm_resolve_section_title( $attrs, __( 'Highlight Informasi', 'ugm-faculty' ) );
	$cat_slugs = ugm_parse_category_slug_list( $attrs['categorySlug'] ?? '' );

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

	if ( ! empty( $cat_slugs ) ) {
		// Explicit category override on the block itself.
		$term_ids = ugm_resolve_multiple_slugs_to_ids( $cat_slugs );
		if ( ! empty( $term_ids ) ) {
			$args['category__in'] = $term_ids;
		} else {
			$args['post__in'] = array( 0 );
		}
	} else {
		// ── Whitelist: hanya tampilkan post dari kategori Berita Akademik,
		//    Profile, dan Prestasi (baca dari blok di halaman yang sama).
		$whitelist_ids = ugm_collect_news_whitelist_category_ids();
		if ( ! empty( $whitelist_ids ) ) {
			$args['category__in'] = $whitelist_ids;
		} else {
			$args['post__in'] = array( 0 );
		}
	}

	$q = new WP_Query( $args );

	$archive_link = add_query_arg( 'ugm_latest_news', '1', home_url( '/' ) );

	ob_start();
	echo '<section class="home-section section-news ' . esc_attr( $visibility_class ) . '" aria-labelledby="block-news-title">';
	echo ugm_block_section_header( $title, 'block-news-title', $archive_link, __( 'Lihat semua berita terbaru', 'ugm-faculty' ) ); // phpcs:ignore
	ugm_render_latest_news_grid( $q );
	echo '<a class="section-arrow-link section-arrow-link--latest-news" href="' . esc_url( $archive_link ) . '" aria-label="' . esc_attr__( 'Lihat semua berita terbaru', 'ugm-faculty' ) . '">&#8594;</a>';
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
		$attrs       = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
		$slugs_found = array_merge(
			$slugs_found,
			ugm_parse_category_slug_list( $attrs['categorySlug'] ?? '', array( $defaults[ $name ] ) )
		);
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
	'attributes'      => ugm_get_section_attrs( __( 'Highlight Informasi', 'ugm-faculty' ) ),
) );

/* ==========================================================================
 * 4. ugm/academic-news — Berita Akademik
 * ========================================================================== */

function ugm_render_block_academic_news( $attrs ) {
	$visibility_class = ugm_block_visibility_class( $attrs );
	$title    = ugm_resolve_section_title( $attrs, __( 'Informasi Akademik', 'ugm-faculty' ) );
	$cat_slugs = ugm_parse_category_slug_list( $attrs['categorySlug'] ?? '', array( 'pendidikan' ) );
	$cat_ids   = ugm_resolve_multiple_slugs_to_ids( $cat_slugs );
	$archive   = ugm_get_category_archive_url_from_slugs( $cat_slugs );
	$count   = max( 1, absint( get_theme_mod( 'ugm_academic_news_count', 3 ) ) );

	$args = ! empty( $cat_ids )
		? array(
			'post_type'           => 'post',
			'posts_per_page'      => $count,
			'category__in'        => $cat_ids,
			'ignore_sticky_posts' => true,
			'post_status'         => 'publish',
			'orderby'             => 'date',
			'order'               => 'DESC',
			'no_found_rows'       => true,
		)
		: array( 'post__in' => array( 0 ) );

	ob_start();
	$q = new WP_Query( $args );
	echo '<section class="home-section section-academic col-12 col-lg-4 ' . esc_attr( $visibility_class ) . '" aria-labelledby="block-academic-title">';
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
	'attributes'      => ugm_get_section_attrs( __( 'Informasi Akademik', 'ugm-faculty' ) ),
) );

/* ==========================================================================
 * 5. ugm/profile-section — Profile
 * ========================================================================== */

function ugm_render_block_profile_section( $attrs ) {
	$visibility_class = ugm_block_visibility_class( $attrs );
	$title    = ugm_resolve_section_title( $attrs, __( 'Informasi Umum', 'ugm-faculty' ) );
	$cat_slugs = ugm_parse_category_slug_list( $attrs['categorySlug'] ?? '', array( 'profile' ) );
	$cat_ids   = ugm_resolve_multiple_slugs_to_ids( $cat_slugs );
	$archive   = ugm_get_category_archive_url_from_slugs( $cat_slugs );
	$count   = max( 1, absint( get_theme_mod( 'ugm_profile_news_count', 3 ) ) );

	$args = ! empty( $cat_ids )
		? array(
			'post_type'           => 'post',
			'posts_per_page'      => $count,
			'category__in'        => $cat_ids,
			'ignore_sticky_posts' => true,
			'post_status'         => 'publish',
			'orderby'             => 'date',
			'order'               => 'DESC',
			'no_found_rows'       => true,
		)
		: array( 'post__in' => array( 0 ) );

	ob_start();
	$q = new WP_Query( $args );
	echo '<section class="home-section section-profile col-12 col-lg-4 ' . esc_attr( $visibility_class ) . '" aria-labelledby="block-profile-title">';
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
	'attributes'      => ugm_get_section_attrs( __( 'Informasi Umum', 'ugm-faculty' ) ),
) );

/* ==========================================================================
 * 6. ugm/achievement-section — Prestasi
 * ========================================================================== */

function ugm_render_block_achievement_section( $attrs ) {
	$visibility_class = ugm_block_visibility_class( $attrs );
	$title    = ugm_resolve_section_title( $attrs, __( 'Pencapaian', 'ugm-faculty' ) );
	$cat_slugs = ugm_parse_category_slug_list( $attrs['categorySlug'] ?? '', array( 'prestasi' ) );
	$cat_ids   = ugm_resolve_multiple_slugs_to_ids( $cat_slugs );
	$archive   = ugm_get_category_archive_url_from_slugs( $cat_slugs );
	$count   = max( 1, absint( get_theme_mod( 'ugm_achievement_news_count', 3 ) ) );

	$args = ! empty( $cat_ids )
		? array(
			'post_type'           => 'post',
			'posts_per_page'      => $count,
			'category__in'        => $cat_ids,
			'ignore_sticky_posts' => true,
			'post_status'         => 'publish',
			'orderby'             => 'date',
			'order'               => 'DESC',
			'no_found_rows'       => true,
		)
		: array( 'post__in' => array( 0 ) );

	ob_start();
	$q = new WP_Query( $args );
	echo '<section class="home-section section-achievement col-12 col-lg-4 ' . esc_attr( $visibility_class ) . '" aria-labelledby="block-achievement-title">';
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
	'attributes'      => ugm_get_section_attrs( __( 'Pencapaian', 'ugm-faculty' ) ),
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
	'supports'        => array(
		'html'     => false,
		'inserter' => false,
	),
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
		'visibility' => array(
			'type'    => 'string',
			'default' => 'all',
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
	$title      = ugm_resolve_section_title( $attrs, __( 'Highlight Konten', 'ugm-faculty' ) );
	$empty_text = isset( $attrs['emptyText'] ) && '' !== trim( (string) $attrs['emptyText'] )
		? trim( (string) $attrs['emptyText'] )
		: sprintf( __( 'Belum ada artikel %s.', 'ugm-faculty' ), $title );

	$slugs    = ugm_parse_category_slug_list( $attrs['categorySlug'] ?? '' );
	$term_ids = ugm_resolve_multiple_slugs_to_ids( $slugs );
	$link     = ugm_get_category_archive_url_from_slugs( $slugs );

	$args = array(
		'post_type'           => 'post',
		'posts_per_page'      => 1,
		'ignore_sticky_posts' => true,
		'post_status'         => 'publish',
		'no_found_rows'       => true,
	);
	if ( ! empty( $term_ids ) ) {
		$args['category__in'] = $term_ids;
	} else {
		$args['post__in'] = array( 0 );
	}

	$q = new WP_Query( $args );

	ob_start();
	// Output HTML yang identik dengan yang dihasilkan ugm_render_featured_categories_markup
	// untuk satu kolom — sehingga CSS yang sudah ada langsung berlaku tanpa modifikasi.
	?>
	<section class="featured-category-column" aria-label="<?php echo esc_attr( $title ); ?>">
		<?php if ( '' !== $title ) : ?>
			<header class="section-header">
				<h2 class="section-title"><?php echo esc_html( $title ); ?></h2>
				<span class="section-line" aria-hidden="true"></span>
			</header>
		<?php endif; ?>

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
	$visibility_class = ugm_block_visibility_class( $attrs );
	// Render sebagai .home-section seperti Berita Akademik / Profile / Prestasi.
	// Di template landing page, blok-blok ini dikumpulkan dalam .home-sections-triple
	// (grid 3-kolom) karena sudah ditambahkan ke $triple_names.
	$col_html = ugm_render_single_featured_column( $attrs );

	return '<section class="home-section section-featured-category-col ' . esc_attr( $visibility_class ) . '">' .
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
		'title'        => array( 'type' => 'string', 'default' => 'Highlight Konten' ),
		'categorySlug' => array( 'type' => 'string', 'default' => '' ),
		'emptyText'    => array( 'type' => 'string', 'default' => '' ),
		'visibility'   => array( 'type' => 'string', 'default' => 'all' ),
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
			'default' => 'Kategori',
		),
		'visibility' => array(
			'type'    => 'string',
			'default' => 'all',
		),
	),
) );

/* ==========================================================================
 * 9. ugm/facility-section — Fasilitas
 * ========================================================================== */

function ugm_render_block_facility_section( $attrs ) {
	$visibility_class = ugm_block_visibility_class( $attrs );
	$title    = ugm_resolve_section_title( $attrs, __( 'Fasilitas', 'ugm-faculty' ) );
	$cat_slugs = ugm_parse_category_slug_list( $attrs['categorySlug'] ?? '', array( 'fasilitas' ) );
	$cat_ids   = ugm_resolve_multiple_slugs_to_ids( $cat_slugs );
	$archive   = ugm_get_category_archive_url_from_slugs( $cat_slugs );
	$count   = max( 1, absint( get_theme_mod( 'ugm_facility_news_count', 3 ) ) );

	$args = ! empty( $cat_ids )
		? array(
			'post_type'           => 'post',
			'posts_per_page'      => $count,
			'category__in'        => $cat_ids,
			'ignore_sticky_posts' => true,
			'post_status'         => 'publish',
			'orderby'             => 'date',
			'order'               => 'DESC',
			'no_found_rows'       => true,
		)
		: array( 'post__in' => array( 0 ) );

	ob_start();
	$q = new WP_Query( $args );
	echo '<section class="home-section section-facility ' . esc_attr( $visibility_class ) . '" aria-labelledby="block-facility-title">';
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
	'supports'        => array(
		'html'     => false,
		'inserter' => false,
	),
	'attributes'      => ugm_get_section_attrs( __( 'Fasilitas', 'ugm-faculty' ) ),
) );

/* ==========================================================================
 * 10. ugm/faculty-section — Fakultas dan Sekolah
 * ========================================================================== */

function ugm_render_block_faculty_section( $attrs ) {
	$visibility_class = ugm_block_visibility_class( $attrs );
	$overline = isset( $attrs['overline'] ) && '' !== trim( (string) $attrs['overline'] )
		? trim( (string) $attrs['overline'] )
		: __( 'Seputar UGM', 'ugm-faculty' );
	$title = ugm_resolve_section_title( $attrs, __( 'Fakultas dan Sekolah', 'ugm-faculty' ) );

	$cat_slugs = ugm_parse_category_slug_list( $attrs['categorySlug'] ?? '' );
	$list     = array();

	if ( ! empty( $cat_slugs ) ) {
		$cat_ids = ugm_resolve_multiple_slugs_to_ids( $cat_slugs );
		if ( ! empty( $cat_ids ) ) {
			$q = new WP_Query(
				array(
					'post_type'           => 'post',
					'posts_per_page'      => 16,
					'category__in'        => $cat_ids,
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
	echo '<section class="home-section section-faculty ' . esc_attr( $visibility_class ) . '" aria-labelledby="block-faculty-title">';
	echo '<p class="section-overline section-overline--faculty">' . esc_html( $overline ) . '</p>';
	echo ugm_block_section_header( $title, 'block-faculty-title' ); // phpcs:ignore

	if ( ! empty( $list ) ) {
		$items_per_page = wp_is_mobile() ? 3 : 8;
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
	'supports'        => array(
		'html'     => false,
		'inserter' => false,
	),
	'attributes'      => array(
		'overline' => array( 'type' => 'string', 'default' => 'Seputar UGM' ),
		'title' => array( 'type' => 'string', 'default' => 'Fakultas dan Sekolah' ),
		'categorySlug' => array( 'type' => 'string', 'default' => '' ),
		'visibility' => array( 'type' => 'string', 'default' => 'all' ),
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
	$visibility_class = ugm_block_visibility_class( $attrs );
	$overline = isset( $attrs['overline'] ) && '' !== trim( (string) $attrs['overline'] )
		? trim( (string) $attrs['overline'] )
		: __( 'Seputar UGM', 'ugm-faculty' );
	$title = ugm_resolve_section_title( $attrs, __( 'Struktur Akademik', 'ugm-faculty' ) );

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
	echo '<section class="home-section section-faculty ' . esc_attr( $visibility_class ) . '" aria-labelledby="block-faculty-list-title">';
	echo '<p class="section-overline section-overline--faculty">' . esc_html( $overline ) . '</p>';
	echo ugm_block_section_header( $title, 'block-faculty-list-title' ); // phpcs:ignore

	if ( ! empty( $list ) ) {
		$items_per_page = wp_is_mobile() ? 3 : 8;
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
		'title'    => array( 'type' => 'string', 'default' => 'Struktur Akademik' ),
		'visibility' => array( 'type' => 'string', 'default' => 'all' ),
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

	$agenda_archive = ugm_get_agenda_page_url();
	if ( ! empty( $agenda_term_ids ) ) {
		$first = reset( $agenda_term_ids );
		$link  = get_category_link( $first );
		if ( home_url( '/' ) === $agenda_archive && ! is_wp_error( $link ) ) {
			$agenda_archive = $link;
		}
	}

	$agenda_count = max( 1, min( 6, absint( get_theme_mod( 'ugm_events_count', 3 ) ) ) );
	$agenda_args  = array(
		'post_type'           => 'post',
		'posts_per_page'      => $agenda_count,
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
		$agenda_args['category__in'] = $agenda_term_ids;
	} else {
		$agenda_args['post__in'] = array( 0 );
	}
	$agenda_q = new WP_Query( $agenda_args );

	ob_start();
	echo '<section class="section-campus-desktop__agenda" aria-labelledby="block-agenda-title">';
	if ( '' !== trim( (string) $title ) ) {
		echo '<header class="section-header section-header--desktop-agenda">';
		echo '<h2 id="block-agenda-title" class="section-title">' . esc_html( $title ) . '</h2>';
		echo '<span class="section-line" aria-hidden="true"></span>';
		echo '</header>';
	}

	if ( $agenda_q->have_posts() ) {
		$agenda_items = 0;
		echo '<div class="desktop-agenda-list">';
		while ( $agenda_q->have_posts() ) {
			$agenda_q->the_post();
			$agenda_items++;
			$ts = function_exists( 'ugm_get_agenda_event_timestamp' )
				? ugm_get_agenda_event_timestamp( get_the_ID() )
				: (int) get_post_timestamp( get_the_ID() );
			echo '<article class="' . esc_attr( implode( ' ', get_post_class( 'desktop-agenda-card' ) ) ) . '">';
			echo '<a class="desktop-agenda-card__date" href="' . esc_url( get_the_permalink() ) . '" aria-label="' . esc_attr__( 'Buka agenda', 'ugm-faculty' ) . '">';
			echo '<span class="desktop-agenda-card__day">' . esc_html( wp_date( 'd', $ts ) ) . '</span>';
			echo '<span class="desktop-agenda-card__month">' . esc_html( wp_date( 'M', $ts ) ) . '</span>';
			echo '</a>';
			echo '<div class="desktop-agenda-card__body">';
			echo '<h3 class="desktop-agenda-card__title"><a href="' . esc_url( get_the_permalink() ) . '">' . get_the_title() . '</a></h3>';
			$agenda_location = function_exists( 'ugm_get_agenda_event_location' )
				? ugm_get_agenda_event_location( get_the_ID() )
				: trim( (string) get_post_meta( get_the_ID(), 'agenda_location', true ) );
			echo '<p class="desktop-agenda-card__meta">' . esc_html( $agenda_location ) . '</p>';
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
	$facility_slugs    = ugm_parse_category_slug_list( $facility_slug, array( 'fasilitas-mahasiswa' ) );
	$facility_term_ids = ugm_resolve_multiple_slugs_to_ids( $facility_slugs );
	if ( empty( $facility_term_ids ) ) {
		$facility_term_ids = ugm_resolve_multiple_slugs_to_ids( array( 'fasilitas-mahasiswa', 'fasilitas', 'sarana-prasarana', 'sarana', 'prasarana' ) );
	}
	$facility_archive  = ugm_get_category_archive_url_from_slugs( $facility_slugs );

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
	if ( '' !== trim( (string) $facility_title ) ) {
		echo '<header class="section-header section-header--desktop-facility">';
		echo '<h2 id="block-facility-desktop-title" class="section-title">' . esc_html( $facility_title ) . '</h2>';
		echo '<span class="section-line" aria-hidden="true"></span>';
		echo '<a class="section-view-all section-view-all--desktop-facility" href="' . esc_url( $facility_archive ) . '" aria-label="' . esc_attr( sprintf( __( 'Lihat semua %s', 'ugm-faculty' ), $facility_title ) ) . '">';
		echo esc_html__( 'Lihat Semua', 'ugm-faculty' ) . ' <span aria-hidden="true">&rarr;</span></a>';
		echo '</header>';
	}

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
	$visibility_class = ugm_block_visibility_class( $attrs );
	$title          = ugm_resolve_section_title( $attrs, __( 'Event & Agenda', 'ugm-faculty' ) );
	$cat_slug       = isset( $attrs['categorySlug'] ) ? trim( (string) $attrs['categorySlug'] ) : '';
	$facility_title = ugm_resolve_section_title( $attrs, __( 'Fasilitas Kampus', 'ugm-faculty' ), 'facilityTitle' );
	$facility_slug  = isset( $attrs['facilityCategorySlug'] ) && '' !== trim( (string) $attrs['facilityCategorySlug'] )
		? trim( (string) $attrs['facilityCategorySlug'] )
		: 'fasilitas-mahasiswa';

	ob_start();
	echo '<section class="home-section section-campus-desktop ' . esc_attr( $visibility_class ) . '" aria-label="' . esc_attr__( 'Agenda dan fasilitas kampus', 'ugm-faculty' ) . '">';
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
	'supports'        => array(
		'html'     => false,
		'inserter' => false,
	),
	'attributes'      => array(
		'title'                => array( 'type' => 'string', 'default' => 'Event & Agenda' ),
		'categorySlug'         => array( 'type' => 'string', 'default' => '' ),
		'facilityTitle'        => array( 'type' => 'string', 'default' => 'Fasilitas Kampus' ),
		'facilityCategorySlug' => array( 'type' => 'string', 'default' => 'fasilitas-mahasiswa' ),
		'visibility'           => array( 'type' => 'string', 'default' => 'all' ),
	),
) );

/* --------------------------------------------------------------------------
 * 11b. ugm/agenda-only — Agenda Kegiatan mandiri
 * -------------------------------------------------------------------------- */

function ugm_render_block_agenda_only( $attrs ) {
	$visibility_class = ugm_block_visibility_class( $attrs );
	$title    = ugm_resolve_section_title( $attrs, __( 'Event & Agenda', 'ugm-faculty' ) );
	$cat_slug = isset( $attrs['categorySlug'] ) ? trim( (string) $attrs['categorySlug'] ) : '';

	$inner = ugm_render_agenda_column_html( $title, $cat_slug );
	// Wrapped in the same outer section so CSS stays intact even when standalone.
	return '<section class="home-section section-campus-desktop section-campus-desktop--agenda-only ' . esc_attr( $visibility_class ) . '" aria-label="' .
		esc_attr__( 'Agenda kegiatan', 'ugm-faculty' ) . '">' . $inner . '</section>';
}

register_block_type( 'ugm/agenda-only', array(
	'title'           => __( 'Agenda Kegiatan', 'ugm-faculty' ),
	'description'     => __( 'Menampilkan daftar agenda kegiatan berdasarkan kategori.', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_agenda_only',
	'supports'        => array( 'html' => false ),
	'attributes'      => array(
		'title'        => array( 'type' => 'string', 'default' => 'Event & Agenda' ),
		'categorySlug' => array( 'type' => 'string', 'default' => '' ),
		'visibility'   => array( 'type' => 'string', 'default' => 'all' ),
	),
) );

/* --------------------------------------------------------------------------
 * 11c. ugm/facility-only — Fasilitas Mahasiswa mandiri
 * -------------------------------------------------------------------------- */

function ugm_render_block_facility_only( $attrs ) {
	$visibility_class = ugm_block_visibility_class( $attrs );
	$title    = ugm_resolve_section_title( $attrs, __( 'Fasilitas Kampus', 'ugm-faculty' ) );
	$cat_slug = isset( $attrs['categorySlug'] ) && '' !== trim( (string) $attrs['categorySlug'] )
		? trim( (string) $attrs['categorySlug'] )
		: 'fasilitas-mahasiswa';

	$inner = ugm_render_facility_column_html( $title, $cat_slug );
	return '<section class="home-section section-campus-desktop section-campus-desktop--facility-only ' . esc_attr( $visibility_class ) . '" aria-label="' .
		esc_attr__( 'Fasilitas mahasiswa', 'ugm-faculty' ) . '">' . $inner . '</section>';
}

register_block_type( 'ugm/facility-only', array(
	'title'           => __( 'Fasilitas Mahasiswa', 'ugm-faculty' ),
	'description'     => __( 'Menampilkan grid fasilitas mahasiswa berdasarkan kategori.', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_facility_only',
	'supports'        => array( 'html' => false ),
	'attributes'      => array(
		'title'        => array( 'type' => 'string', 'default' => 'Fasilitas Kampus' ),
		'categorySlug' => array( 'type' => 'string', 'default' => '' ),
		'visibility'   => array( 'type' => 'string', 'default' => 'all' ),
	),
) );



/* ==========================================================================
 * 12. ugm/magazine-section — Majalah Digital
 * ========================================================================== */

function ugm_render_block_magazine_section( $attrs ) {
	$visibility_class = ugm_block_visibility_class( $attrs );
	ob_start();
	get_template_part( 'template-parts/section-majalah', null, array( 'visibility_class' => $visibility_class ) );
	return ob_get_clean();
}

register_block_type( 'ugm/magazine-section', array(
	'title'           => __( 'Majalah Digital', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_magazine_section',
	'supports'        => array( 'html' => false ),
	'attributes'      => array(
		'visibility' => array( 'type' => 'string', 'default' => 'all' ),
	),
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
	$visibility_class = ugm_block_visibility_class( $attrs );
	$title    = ugm_resolve_section_title( $attrs, __( 'Media Video', 'ugm-faculty' ) );
	$cat_slugs = ugm_parse_category_slug_list( $attrs['categorySlug'] ?? '', array( 'video' ) );
	$cat_ids   = ugm_resolve_multiple_slugs_to_ids( $cat_slugs );
	$archive   = ugm_get_category_archive_url_from_slugs( $cat_slugs );

	$args = array(
		'post_type'           => 'post',
		'posts_per_page'      => 4,
		'ignore_sticky_posts' => true,
		'post_status'         => 'publish',
		'orderby'             => 'date',
		'order'               => 'DESC',
		'no_found_rows'       => true,
	);
	if ( ! empty( $cat_ids ) ) {
		$args['category__in'] = $cat_ids;
	} else {
		$args['post__in'] = array( 0 );
	}

	$q = new WP_Query( $args );

	ob_start();
	echo '<section class="home-section section-video ' . esc_attr( $visibility_class ) . '" aria-labelledby="block-video-title">';
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
		if ( ! ( $featured instanceof WP_Post ) ) {
			// Safety: render featured skeleton if no featured post.
			echo '<div class="video-area video-area--featured">';
			echo '<div class="video-featured video-featured--skeleton">';
			echo '<div class="video-featured__media video-featured__media--skeleton">';
			echo '<span class="ugm-skeleton-box ugm-skeleton-box--media"></span>';
			echo '</div>';
			echo '<div class="video-featured__body">';
			echo '<span class="ugm-skeleton-line ugm-skeleton-line--title"></span>';
			echo '<span class="ugm-skeleton-line ugm-skeleton-line--meta"></span>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}

		// List of videos (right) + skeleton placeholders for remaining slots.
		echo '<div class="video-area video-area--list">';
		if ( ! empty( $list ) ) {
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
		}
		$missing = max( 0, 3 - count( $list ) );
		echo ugm_render_partial_skeleton_items( 'video-list-card', $missing ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';

		echo '</div>'; // .video-layout
	} else {
		// Full skeleton when no posts.
		echo '<div class="video-layout video-layout--skeleton">';
		echo '<div class="video-area video-area--featured">';
		echo '<div class="video-featured video-featured--skeleton">';
		echo '<div class="video-featured__media video-featured__media--skeleton">';
		echo '<span class="ugm-skeleton-box ugm-skeleton-box--media"></span>';
		echo '</div>';
		echo '<div class="video-featured__body">';
		echo '<span class="ugm-skeleton-line ugm-skeleton-line--title"></span>';
		echo '<span class="ugm-skeleton-line ugm-skeleton-line--meta"></span>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '<div class="video-area video-area--list">';
		echo ugm_render_partial_skeleton_items( 'video-list-card', 3 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
		'title'        => array( 'type' => 'string', 'default' => 'Media Video' ),
		'categorySlug' => array( 'type' => 'string', 'default' => 'video' ),
		'visibility'   => array( 'type' => 'string', 'default' => 'all' ),
	),
) );

/* ==========================================================================
 * 14. ugm/template-links — Tautan Layanan (Grid Kartu Manual)
 *
 * Displays a manually-configured grid of link cards — icon image, bold title,
 * subtitle / URL, and an external link. Designed to match the UGM portal
 * navigation style (dark-navy cards, gold accent, white typography).
 * ========================================================================== */

/**
 * Render the template-links section.
 *
 * @param array $attrs Block attributes.
 * @return string
 */
function ugm_render_block_template_links( $attrs ) {
	$visibility_class = ugm_block_visibility_class( $attrs );
	$title = ugm_resolve_section_title( $attrs, __( 'Layanan Pilihan', 'ugm-faculty' ) );

	$items = isset( $attrs['items'] ) && is_array( $attrs['items'] ) ? $attrs['items'] : array();

	ob_start();
	echo '<section class="home-section section-template-links ' . esc_attr( $visibility_class ) . '" aria-labelledby="block-template-links-title">';

	if ( '' !== $title ) {
		echo ugm_block_section_header( $title, 'block-template-links-title' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	if ( ! empty( $items ) ) {
		echo '<div class="template-links-grid">';

		foreach ( $items as $item ) {
			$label    = isset( $item['label'] )    ? trim( (string) $item['label'] )    : '';
			$sublabel = isset( $item['sublabel'] ) ? trim( (string) $item['sublabel'] ) : '';
			$link     = isset( $item['link'] )     ? esc_url( (string) $item['link'] )  : '';
			$icon_url = isset( $item['iconUrl'] )  ? esc_url( (string) $item['iconUrl'] ) : '';
			$bg_url   = isset( $item['bgUrl'] )    ? esc_url( (string) $item['bgUrl'] )   : '';

			if ( '' === $label && '' === $link ) {
				continue;
			}

			$tag       = '' !== $link ? 'a' : 'div';
			$link_attr = '' !== $link
				? ' href="' . $link . '" target="_blank" rel="noopener noreferrer"'
				: '';
			$bg_style  = '' !== $bg_url
				? ' style="--tlc-bg: url(' . $bg_url . ');"'
				: '';

			echo '<' . $tag . ' class="template-link-card"' . $link_attr . $bg_style . '>'; // phpcs:ignore
			echo '<div class="template-link-card__bg" aria-hidden="true"></div>';
			echo '<div class="template-link-card__inner">';

			if ( '' !== $icon_url ) {
				echo '<div class="template-link-card__icon">';
				echo '<img src="' . $icon_url . '" alt="' . esc_attr( $label ) . '" loading="lazy">';
				echo '</div>';
			} else {
				echo '<div class="template-link-card__icon template-link-card__icon--empty" aria-hidden="true"></div>';
			}

			echo '<div class="template-link-card__text">';
			if ( '' !== $label ) {
				echo '<span class="template-link-card__label">' . esc_html( $label ) . '</span>';
			}
			if ( '' !== $sublabel ) {
				echo '<span class="template-link-card__sublabel">' . esc_html( $sublabel ) . '</span>';
			}
			echo '</div>'; // .template-link-card__text

			echo '</div>'; // .template-link-card__inner
			echo '</' . $tag . '>';
		}

		echo '</div>'; // .template-links-grid
	} else {
		// Skeleton placeholder when no items configured.
		echo '<div class="template-links-grid template-links-grid--skeleton">';
		for ( $i = 0; $i < 7; $i++ ) {
			echo '<div class="template-link-card template-link-card--skeleton">';
			echo '<div class="template-link-card__bg" aria-hidden="true"></div>';
			echo '<div class="template-link-card__inner">';
			echo '<div class="template-link-card__icon">';
			echo '<span class="ugm-skeleton-box" style="width:52px;height:52px;border-radius:8px;opacity:.35;"></span>';
			echo '</div>';
			echo '<div class="template-link-card__text">';
			echo '<span class="ugm-skeleton-line ugm-skeleton-line--title" style="width:65%;margin-bottom:7px;opacity:.35;"></span>';
			echo '<span class="ugm-skeleton-line ugm-skeleton-line--meta" style="width:48%;opacity:.25;"></span>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}
		echo '</div>';
	}

	echo '</section>';
	return ob_get_clean();
}

register_block_type( 'ugm/template-links', array(
	'title'           => __( 'Tautan Layanan', 'ugm-faculty' ),
	'description'     => __( 'Grid kartu tautan layanan dengan ikon, label, dan URL. Data diinput manual langsung di editor.', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_template_links',
	'supports'        => array( 'html' => false ),
	'attributes'      => array(
		'title' => array( 'type' => 'string', 'default' => 'Layanan Pilihan' ),
		'items' => array( 'type' => 'array',  'default' => array() ),
		'visibility' => array( 'type' => 'string', 'default' => 'all' ),
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

	$get_excerpt = function () {
		$excerpt = trim( wp_strip_all_tags( get_the_excerpt() ) );
		if ( '' === $excerpt ) {
			$excerpt = wp_trim_words( wp_strip_all_tags( get_the_content( null, false ) ), 30, '...' );
		}
		return $excerpt;
	};

	echo '<div class="portal-column">';

	if ( $featured instanceof WP_Post ) {
		$post            = $featured; // phpcs:ignore
		$GLOBALS['post'] = $post;     // Required: setup_postdata alone does not set the global post.
		setup_postdata( $post );
		$_has_thumb = has_post_thumbnail();
		$_excerpt   = $get_excerpt();
		echo '<article class="' . esc_attr( implode( ' ', get_post_class( 'portal-card portal-card--featured' ) ) ) . '">';
		if ( $_has_thumb ) {
			echo '<a class="portal-card__media" href="' . esc_url( get_the_permalink() ) . '" aria-hidden="true" tabindex="-1">' . get_the_post_thumbnail( null, 'medium_large' ) . '</a>';
		} else {
			echo '<div class="portal-card__media portal-card__media--placeholder" aria-hidden="true"></div>';
		}
		echo '<div class="portal-card__body">';
		$_label = $get_label(); if ( '' !== $_label ) { echo '<p class="card-kicker">' . esc_html( $_label ) . '</p>'; }
		echo '<h3 class="card-title"><a href="' . esc_url( get_the_permalink() ) . '">' . get_the_title() . '</a></h3>';
		if ( '' !== $_excerpt ) { echo '<p class="card-excerpt">' . esc_html( $_excerpt ) . '</p>'; }
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
			$_excerpt   = $get_excerpt();
			echo '<article class="' . esc_attr( implode( ' ', get_post_class( 'portal-list-card' ) ) ) . '">';
			if ( $_has_thumb ) {
				echo '<a class="portal-list-card__media" href="' . esc_url( get_the_permalink() ) . '" aria-hidden="true" tabindex="-1">' . get_the_post_thumbnail( null, 'thumbnail' ) . '</a>';
			} else {
				echo '<div class="portal-list-card__media portal-list-card__media--placeholder" aria-hidden="true"></div>';
			}
			echo '<div class="portal-list-card__body">';
			$_label = $get_label(); if ( '' !== $_label ) { echo '<p class="card-kicker">' . esc_html( $_label ) . '</p>'; }
			echo '<h3 class="card-title"><a href="' . esc_url( get_the_permalink() ) . '">' . get_the_title() . '</a></h3>';
			if ( '' !== $_excerpt ) { echo '<p class="card-excerpt">' . esc_html( $_excerpt ) . '</p>'; }
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
	$slug_list = ugm_parse_category_slug_list( $slug_attr );

	// 1. Explicit slug attr (from block attribute).
	if ( ! empty( $slug_list ) ) {
		$resolved_ids = ugm_resolve_multiple_slugs_to_ids( $slug_list );
		if ( ! empty( $resolved_ids ) ) {
			return $resolved_ids;
		}

		// Last resort: search by name containing the slug keyword.
		$all_cats = get_categories( array( 'hide_empty' => false, 'number' => 50 ) );
		foreach ( $slug_list as $slug ) {
			$keyword = str_replace( '-', ' ', $slug );
			foreach ( $all_cats as $cat ) {
				if ( false !== stripos( $cat->name, $keyword ) || false !== stripos( $cat->slug, $slug ) ) {
					return ugm_get_category_tree_ids( (int) $cat->term_id );
				}
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
 * Resolve the term IDs for the faculty section category with multi-slug support.
 *
 * @param string $slug_attr Category slug override (may contain commas).
 * @return int[]
 */
function ugm_resolve_faculty_exclude_ids_multi( $slug_attr ) {
	$slug_list = ugm_parse_category_slug_list( $slug_attr );

	if ( ! empty( $slug_list ) ) {
		return ugm_resolve_multiple_slugs_to_ids( $slug_list );
	}

	$page_id = (int) get_the_ID();
	if ( $page_id <= 0 ) {
		$page_id = (int) get_queried_object_id();
	}

	if ( $page_id > 0 ) {
		$post = get_post( $page_id );
		if ( $post instanceof WP_Post ) {
			foreach ( parse_blocks( (string) $post->post_content ) as $block ) {
				if ( 'ugm/faculty-section' !== ( $block['blockName'] ?? '' ) ) {
					continue;
				}

				$block_slugs = ugm_parse_category_slug_list( $block['attrs']['categorySlug'] ?? '' );
				if ( ! empty( $block_slugs ) ) {
					return ugm_resolve_multiple_slugs_to_ids( $block_slugs );
				}

				break;
			}
		}
	}

	return ugm_resolve_multiple_slugs_to_ids( array( 'fakultas-dan-sekolah', 'fakultas', 'faculty' ) );
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
	$slug_list = ugm_parse_category_slug_list( $slug_attr );

	if ( ! empty( $slug_list ) ) {
		return ugm_resolve_multiple_slugs_to_ids( $slug_list );
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
	), array(
		'slug'  => 'ugm-agenda-page-sections',
		'title' => __( 'UGM - Agenda Page Sections', 'ugm-faculty' ),
		'icon'  => 'calendar',
	), array(
		'slug'  => 'ugm-announcement-page-sections',
		'title' => __( 'UGM - Pengumuman Page Sections', 'ugm-faculty' ),
		'icon'  => 'megaphone',
	), array(
		'slug'  => 'ugm-gallery-page-sections',
		'title' => __( 'UGM - Galeri Page Sections', 'ugm-faculty' ),
		'icon'  => 'format-gallery',
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
		array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-block-editor', 'wp-components', 'wp-data', 'wp-core-data', 'wp-server-side-render' ),
		ugm_get_asset_version( '/assets/js/blocks.js' ),
		true
	);

	wp_enqueue_script(
		'ugm-agenda-blocks',
		get_template_directory_uri() . '/assets/js/agenda-blocks.js',
		array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-block-editor', 'wp-components', 'wp-data', 'wp-core-data', 'wp-server-side-render', 'wp-plugins', 'wp-edit-post' ),
		ugm_get_asset_version( '/assets/js/agenda-blocks.js' ),
		true
	);

	wp_enqueue_script(
		'ugm-announcement-blocks',
		get_template_directory_uri() . '/assets/js/announcement-blocks.js',
		array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-block-editor', 'wp-components', 'wp-data', 'wp-core-data', 'wp-server-side-render', 'wp-plugins', 'wp-media-utils' ),
		ugm_get_asset_version( '/assets/js/announcement-blocks.js' ),
		true
	);

	wp_enqueue_script(
		'ugm-gallery-blocks',
		get_template_directory_uri() . '/assets/js/gallery-blocks.js',
		array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-block-editor', 'wp-components', 'wp-compose', 'wp-data', 'wp-hooks', 'wp-plugins', 'wp-server-side-render' ),
		ugm_get_asset_version( '/assets/js/gallery-blocks.js' ),
		true
	);

	wp_localize_script(
		'ugm-gallery-blocks',
		'ugmGalleryPageEditor',
		array(
			'defaultBlocks' => function_exists( 'ugm_get_default_gallery_page_blocks' )
				? ugm_get_default_gallery_page_blocks()
				: '',
		)
	);

	wp_localize_script(
		'ugm-agenda-blocks',
		'ugmAgendaPageEditor',
		array(
			'defaultBlocks' => function_exists( 'ugm_get_default_agenda_page_blocks' )
				? ugm_get_default_agenda_page_blocks()
				: '',
		)
	);

	wp_localize_script(
		'ugm-announcement-blocks',
		'ugmAnnouncementPageEditor',
		array(
			'defaultBlocks' => function_exists( 'ugm_get_default_announcement_page_blocks' )
				? ugm_get_default_announcement_page_blocks()
				: '',
		)
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
		'ugm-editor-style-agenda-page',
		get_template_directory_uri() . '/assets/css/agenda-page.css',
		array( 'ugm-editor-style-base', 'ugm-editor-style-content' ),
		ugm_get_asset_version( '/assets/css/agenda-page.css' )
	);

	wp_enqueue_style(
		'ugm-editor-style-announcement-page',
		get_template_directory_uri() . '/assets/css/announcement-page.css',
		array( 'ugm-editor-style-base', 'ugm-editor-style-content' ),
		ugm_get_asset_version( '/assets/css/announcement-page.css' )
	);

	wp_enqueue_style(
		'ugm-editor-style-gallery-page',
		get_template_directory_uri() . '/assets/css/gallery-page.css',
		array( 'ugm-editor-style-base', 'ugm-editor-style-content' ),
		ugm_get_asset_version( '/assets/css/gallery-page.css' )
	);

	wp_enqueue_style(
		'ugm-editor-landing-preview',
		get_template_directory_uri() . '/assets/css/landing-page-editor.css',
		array( 'ugm-editor-style-base', 'ugm-editor-style-content', 'ugm-editor-style-agenda-page', 'ugm-editor-style-announcement-page', 'ugm-editor-style-gallery-page' ),
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

	$landing_blocks = function_exists( 'ugm_get_default_landing_page_blocks' )
		? ugm_get_default_landing_page_blocks()
		: '';

	register_block_pattern( 'ugm/landing-page-sections', array(
		'title'       => __( 'Konten Halaman Landing — Semua Section', 'ugm-faculty' ),
		'description' => __( 'Hero + semua section halaman landing. Insert ke halaman yang menggunakan template Halaman Landing.', 'ugm-faculty' ),
		'categories'  => array( 'ugm-landing' ),
		'content'     => $landing_blocks,
	) );

	register_block_pattern( 'ugm/agenda-page-sections', array(
		'title'       => __( 'Konten Halaman Agenda', 'ugm-faculty' ),
		'description' => __( 'Daftar agenda lengkap dengan filter dan pagination. Insert ke halaman yang menggunakan template Agenda Page.', 'ugm-faculty' ),
		'categories'  => array( 'ugm-landing' ),
		'content'     => ugm_get_default_agenda_page_blocks(),
	) );

	register_block_pattern( 'ugm/announcement-page-sections', array(
		'title'       => __( 'Konten Halaman Pengumuman', 'ugm-faculty' ),
		'description' => __( 'Daftar pengumuman dengan sidebar berita dan agenda terbaru. Insert ke halaman yang menggunakan template Pengumuman Page.', 'ugm-faculty' ),
		'categories'  => array( 'ugm-landing' ),
		'content'     => ugm_get_default_announcement_page_blocks(),
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
	// Superseded by ugm_normalize_existing_landing_page_content().
	return;

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
	// Superseded by ugm_normalize_existing_landing_page_content().
	return;

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
	// Superseded by ugm_normalize_existing_landing_page_content().
	return;

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
	// Superseded by ugm_normalize_existing_landing_page_content().
	return;

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
	// Superseded by ugm_normalize_existing_landing_page_content().
	return;

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

/* ==========================================================================
 * Migration v9: add ugm/template-links after ugm/video-section on existing
 * landing pages so the new block appears in the editor canvas.
 * Delete 'ugm_landing_blocks_migrated_v9_template_links' to re-run.
 * ========================================================================== */

add_action( 'admin_init', function () {
	// Superseded by ugm_normalize_existing_landing_page_content().
	return;

	if ( get_option( 'ugm_landing_blocks_migrated_v9_template_links' ) ) {
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
		if ( '' === trim( $content ) || false !== strpos( $content, 'wp:ugm/template-links' ) ) {
			continue;
		}

		$insert = '<!-- wp:ugm/template-links {"title":"Tautan Layanan","items":[]} /-->' . "\n";
		$needle = '<!-- wp:ugm/video-section';

		if ( false !== strpos( $content, $needle ) ) {
			// Insert AFTER the video-section block line.
			$content = preg_replace(
				'/(' . preg_quote( '<!-- wp:ugm/video-section', '/' ) . '[^\n]*)(\n|$)/',
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

	update_option( 'ugm_landing_blocks_migrated_v9_template_links', true );
} );
