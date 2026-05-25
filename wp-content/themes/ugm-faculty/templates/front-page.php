<?php
/**
 * Front page template.
 *
 * Uses the full PHP landing page template so the homepage and the Landing Page
 * preview share one hardcoded layout system.
 *
 * @package ugm-faculty
 */

<<<<<<< HEAD
require get_theme_file_path( 'page-templates/template-landing-page.php' );
=======
get_header();
?>

<main id="primary" class="site-main ugm-home">
	<div class="home-content">
		<section class="home-section section-news" aria-labelledby="section-news-title">
			<header class="section-header scroll-reveal">
				<h2 id="section-news-title" class="section-title">
					<?php echo esc_html( get_theme_mod( 'ugm_latest_section_title', __( 'Berita Terbaru', 'ugm-faculty' ) ) ); ?>
				</h2>
				<span class="section-line" aria-hidden="true"></span>
			</header>

			<?php
			// Post terbaru untuk section berita landing page.
			$latest_news_count = max( 1, absint( get_theme_mod( 'ugm_latest_news_count', 3 ) ) );
			$latest_news_mode  = get_theme_mod( 'ugm_latest_news_mode', 'auto' );
			$latest_news_args  = array();
			$latest_news_archive_link = add_query_arg( 'ugm_latest_news', '1', home_url( '/' ) );

			if ( 'manual' === $latest_news_mode ) {
				$manual_news_ids = array_values(
					array_filter(
						array(
							absint( get_theme_mod( 'ugm_latest_news_post_1', 0 ) ),
							absint( get_theme_mod( 'ugm_latest_news_post_2', 0 ) ),
							absint( get_theme_mod( 'ugm_latest_news_post_3', 0 ) ),
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

			}

			$latest_news_query = new WP_Query( $latest_news_args );
			?>

			<?php if ( $latest_news_query->have_posts() ) : ?>
				<div class="news-stack">
					<?php $news_index = 0; ?>
					<?php while ( $latest_news_query->have_posts() ) : $latest_news_query->the_post(); ?>
						<?php
						$news_index++;
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
						?>
						<article <?php post_class( 1 === $news_index ? 'news-card news-card--featured scroll-reveal' : 'news-card news-card--compact scroll-reveal' ); ?>>
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="card-image">
									<?php the_post_thumbnail( 1 === $news_index ? 'large' : 'thumbnail' ); ?>
								</div>
							<?php else : ?>
								<div class="card-placeholder" aria-hidden="true">
									<span class="card-placeholder__text">
										<?php echo esc_html( 1 === $news_index ? 'Tulisan Bebas Area Utama' : 'Tulisan Bebas' ); ?>
									</span>
								</div>
							<?php endif; ?>
							<div class="news-card__content">
								<p class="card-kicker"><?php echo esc_html( $category_label ); ?></p>
								<h3 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<p class="card-date"><?php echo esc_html( get_the_date( 'd F Y, H.i' ) ); ?></p>
							</div>
						</article>
					<?php endwhile; ?>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<p class="section-empty scroll-reveal"><?php esc_html_e( 'Belum ada berita terbaru.', 'ugm-faculty' ); ?></p>
			<?php endif; ?>

			<a class="section-arrow-link scroll-reveal" href="<?php echo esc_url( $latest_news_archive_link ); ?>" aria-label="<?php esc_attr_e( 'Lihat semua berita terbaru', 'ugm-faculty' ); ?>">&#8594;</a>
		</section>

		<section class="home-section section-academic" aria-labelledby="section-academic-title">
			<header class="section-header scroll-reveal">
				<h2 id="section-academic-title" class="section-title">
					<?php echo esc_html( get_theme_mod( 'ugm_academic_section_title', __( 'Berita Akademik', 'ugm-faculty' ) ) ); ?>
				</h2>
				<span class="section-line" aria-hidden="true"></span>
			</header>

			<?php
			// Berita akademik landing page (kategori induk "pendidikan" + subkategori).
			$academic_news_count = max( 1, absint( get_theme_mod( 'ugm_academic_news_count', 2 ) ) );
			$academic_news_mode  = get_theme_mod( 'ugm_academic_news_mode', 'auto' );
			$academic_news_args  = array();
			$education_category  = get_category_by_slug( 'pendidikan' );
			$education_cat_id    = $education_category ? (int) $education_category->term_id : 0;
			$academic_archive_link = $education_cat_id > 0 ? get_category_link( $education_cat_id ) : home_url( '/category/pendidikan/' );

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

					if ( $education_cat_id > 0 ) {
						$academic_news_args['cat'] = $education_cat_id;
					} else {
						$academic_news_args['category_name'] = 'pendidikan';
					}
				}
			}

			if ( empty( $academic_news_args ) ) {
				$academic_news_args = array(
					'post_type'           => 'post',
					'posts_per_page'      => $academic_news_count,
					'ignore_sticky_posts' => true,
					'post_status'         => 'publish',
					'orderby'             => 'date',
					'order'               => 'DESC',
					'no_found_rows'       => true,
				);

				if ( $education_cat_id > 0 ) {
					$academic_news_args['cat'] = $education_cat_id;
				} else {
					$academic_news_args['category_name'] = 'pendidikan';
				}
			}

			$academic_query = new WP_Query( $academic_news_args );
			?>

			<?php if ( $academic_query->have_posts() ) : ?>
				<div class="academic-stack">
					<?php while ( $academic_query->have_posts() ) : $academic_query->the_post(); ?>
						<article <?php post_class( 'academic-card scroll-reveal' ); ?>>
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="card-image">
									<?php the_post_thumbnail( 'medium' ); ?>
								</div>
							<?php else : ?>
								<div class="card-placeholder" aria-hidden="true">
									<span class="card-placeholder__text">Tulisan Bebas</span>
								</div>
							<?php endif; ?>
							<div class="academic-card__content">
								<h3 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<div class="card-excerpt"><?php the_excerpt(); ?></div>
								<p class="card-date"><?php echo esc_html( get_the_date( 'd F Y, H.i' ) ); ?></p>
							</div>
						</article>
					<?php endwhile; ?>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<p class="section-empty scroll-reveal"><?php esc_html_e( 'Belum ada berita akademik.', 'ugm-faculty' ); ?></p>
			<?php endif; ?>

			<a class="section-arrow-link scroll-reveal" href="<?php echo esc_url( $academic_archive_link ); ?>" aria-label="<?php esc_attr_e( 'Lihat semua berita akademik', 'ugm-faculty' ); ?>">&#8594;</a>
		</section>

		<section class="home-section section-profile" aria-labelledby="section-profile-title">
			<header class="section-header scroll-reveal">
				<h2 id="section-profile-title" class="section-title">
					<?php echo esc_html( get_theme_mod( 'ugm_profile_section_title', __( 'Profile', 'ugm-faculty' ) ) ); ?>
				</h2>
				<span class="section-line" aria-hidden="true"></span>
			</header>

			<?php
			// Konten profile landing page (kategori profile).
			$profile_news_count   = max( 1, absint( get_theme_mod( 'ugm_profile_news_count', 3 ) ) );
			$profile_news_mode    = get_theme_mod( 'ugm_profile_news_mode', 'auto' );
			$profile_news_args    = array();
			$profile_root         = ugm_get_category_root_by_slugs( array( 'profile', 'profil' ) );

			$profile_term_ids     = array();
			$profile_archive_link = home_url( '/category/profile/' );

			if ( $profile_root ) {
				$profile_root_id = (int) $profile_root->term_id;
				$profile_term_ids = ugm_get_category_tree_ids( $profile_root_id );
				$profile_archive_link = get_category_link( $profile_root_id );
			}

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
				$profile_news_args = array(
					'post_type'           => 'post',
					'posts_per_page'      => $profile_news_count,
					'ignore_sticky_posts' => true,
					'post_status'         => 'publish',
					'orderby'             => 'date',
					'order'               => 'DESC',
					'no_found_rows'       => true,
				);

				if ( ! empty( $profile_term_ids ) ) {
					$profile_news_args['category__in'] = $profile_term_ids;
				}
			}

			$profile_query = new WP_Query( $profile_news_args );
			?>

			<?php if ( $profile_query->have_posts() ) : ?>
				<div class="list-cards">
					<?php while ( $profile_query->have_posts() ) : $profile_query->the_post(); ?>
						<?php
						$categories     = get_the_category();
						$category_label = ! empty( $categories ) ? $categories[0]->name : __( 'Kepakaran', 'ugm-faculty' );
						?>
						<article <?php post_class( 'list-card scroll-reveal' ); ?>>
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="list-card__thumb">
									<?php the_post_thumbnail( 'thumbnail' ); ?>
								</div>
							<?php else : ?>
								<div class="list-card__thumb" aria-hidden="true"></div>
							<?php endif; ?>
							<div class="list-card__content">
								<p class="card-kicker"><?php echo esc_html( $category_label ); ?></p>
								<h3 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<div class="card-excerpt"><?php the_excerpt(); ?></div>
								<p class="card-date"><?php echo esc_html( get_the_date( 'd F Y, H.i' ) ); ?></p>
							</div>
						</article>
					<?php endwhile; ?>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<p class="section-empty scroll-reveal"><?php esc_html_e( 'Belum ada konten profile.', 'ugm-faculty' ); ?></p>
			<?php endif; ?>

			<a class="section-arrow-link scroll-reveal" href="<?php echo esc_url( $profile_archive_link ); ?>" aria-label="<?php esc_attr_e( 'Lihat semua profile', 'ugm-faculty' ); ?>">&#8594;</a>
		</section>

		<section class="home-section section-achievement" aria-labelledby="section-achievement-title">
			<header class="section-header scroll-reveal">
				<h2 id="section-achievement-title" class="section-title">
					<?php echo esc_html( get_theme_mod( 'ugm_achievement_section_title', __( 'Prestasi', 'ugm-faculty' ) ) ); ?>
				</h2>
				<span class="section-line" aria-hidden="true"></span>
			</header>

			<?php
			// Konten prestasi landing page (kategori prestasi + subkategori).
			$achievement_news_count  = max( 1, absint( get_theme_mod( 'ugm_achievement_news_count', 3 ) ) );
			$achievement_news_mode   = get_theme_mod( 'ugm_achievement_news_mode', 'auto' );
			$achievement_news_args   = array();
			$achievement_root        = ugm_get_category_root_by_slugs( array( 'prestasi' ) );
			$achievement_term_ids    = array();
			$achievement_archive_link = home_url( '/category/prestasi/' );

			if ( $achievement_root ) {
				$achievement_root_id = (int) $achievement_root->term_id;
				$achievement_term_ids = ugm_get_category_tree_ids( $achievement_root_id );
				$achievement_archive_link = get_category_link( $achievement_root_id );
			}

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
						$achievement_news_args['category_name'] = 'prestasi';
					}
				}
			}

			if ( empty( $achievement_news_args ) ) {
				$achievement_news_args = array(
					'post_type'           => 'post',
					'posts_per_page'      => $achievement_news_count,
					'ignore_sticky_posts' => true,
					'post_status'         => 'publish',
					'orderby'             => 'date',
					'order'               => 'DESC',
					'no_found_rows'       => true,
				);

				if ( ! empty( $achievement_term_ids ) ) {
					$achievement_news_args['category__in'] = $achievement_term_ids;
				} else {
					$achievement_news_args['category_name'] = 'prestasi';
				}
			}

			$achievement_query = new WP_Query( $achievement_news_args );
			?>

			<?php if ( $achievement_query->have_posts() ) : ?>
				<div class="list-cards">
					<?php while ( $achievement_query->have_posts() ) : $achievement_query->the_post(); ?>
						<?php
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
						?>
						<article <?php post_class( 'list-card list-card--reverse scroll-reveal' ); ?>>
							<div class="list-card__content">
								<p class="card-kicker"><?php echo esc_html( $category_label ); ?></p>
								<h3 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<div class="card-excerpt"><?php the_excerpt(); ?></div>
								<p class="card-date"><?php echo esc_html( get_the_date( 'd F Y, H.i' ) ); ?></p>
							</div>
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="list-card__thumb">
									<?php the_post_thumbnail( 'thumbnail' ); ?>
								</div>
							<?php else : ?>
								<div class="list-card__thumb" aria-hidden="true"></div>
							<?php endif; ?>
						</article>
					<?php endwhile; ?>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<p class="section-empty scroll-reveal"><?php esc_html_e( 'Belum ada konten prestasi.', 'ugm-faculty' ); ?></p>
			<?php endif; ?>

			<a class="section-arrow-link scroll-reveal" href="<?php echo esc_url( $achievement_archive_link ); ?>" aria-label="<?php esc_attr_e( 'Lihat semua prestasi', 'ugm-faculty' ); ?>">&#8594;</a>
		</section>

		<section class="home-section section-faculty" aria-labelledby="section-faculty-title">
			<header class="section-header scroll-reveal">
				<h2 id="section-faculty-title" class="section-title">
					<?php echo esc_html( get_theme_mod( 'ugm_faculty_section_title', __( 'Fakultas', 'ugm-faculty' ) ) ); ?>
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

			$items_per_page = 3;
			$total_pages    = ceil( count( $faculty_list ) / $items_per_page );
			?>
			<?php if ( ! empty( $faculty_list ) ) : ?>
				<div class="faculty-slider-wrapper">
					<button class="faculty-nav faculty-nav--prev scroll-reveal" aria-label="<?php esc_attr_e( 'Previous page', 'ugm-faculty' ); ?>">&#8249;</button>
					<button class="faculty-nav faculty-nav--next scroll-reveal" aria-label="<?php esc_attr_e( 'Next page', 'ugm-faculty' ); ?>">&#8250;</button>
					
					<div class="faculty-slider" role="list">
						<?php
						$page_index = 0;
						foreach ( $faculty_list as $index => $faculty_item ) :
							// Start new page every 3 items.
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
							<article class="faculty-card scroll-reveal" role="listitem">
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
					
					<div class="faculty-pagination scroll-reveal" aria-hidden="true">
						<?php for ( $i = 0; $i < $total_pages; $i++ ) : ?>
							<span class="pagination-dot <?php echo 0 === $i ? 'active' : ''; ?>" data-page="<?php echo esc_attr( $i ); ?>"></span>
						<?php endfor; ?>
					</div>
				</div>
			<?php else : ?>
				<p class="section-empty scroll-reveal"><?php esc_html_e( 'Belum ada data fakultas.', 'ugm-faculty' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="home-section section-category" aria-labelledby="section-category-title">
			<header class="section-header scroll-reveal">
				<h2 id="section-category-title" class="section-title"><?php esc_html_e( 'Kategori', 'ugm-faculty' ); ?></h2>
				<span class="section-line" aria-hidden="true"></span>
			</header>

			<?php
			$top_level_category_terms = get_categories(
				array(
					'hide_empty' => true,
					'parent'     => 0,
					'orderby'    => 'name',
					'order'      => 'ASC',
				)
			);
			$category_terms = array_slice( $top_level_category_terms, 0, 6 );
			?>

			<?php if ( ! empty( $top_level_category_terms ) ) : ?>
				<details class="category-mobile-list scroll-reveal">
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
														/* translators: %s: category post count */
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
																/* translators: %s: category post count */
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
													/* translators: %s: category post count */
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
						<a class="category-card scroll-reveal" href="<?php echo esc_url( get_category_link( $category_term->term_id ) ); ?>">
							<span class="category-card__label"><?php echo esc_html( $category_term->name ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="section-empty scroll-reveal"><?php esc_html_e( 'Belum ada kategori.', 'ugm-faculty' ); ?></p>
			<?php endif; ?>
		</section>

		<?php get_template_part( 'template-parts/section-majalah' ); ?>
	</div>
</main>
<?php
get_footer();
>>>>>>> origin/dev-fe
