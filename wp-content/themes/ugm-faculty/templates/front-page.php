<?php
/**
 * Front page template.
 *
 * @package ugm-faculty
 */

get_header();
?>

<main id="primary" class="site-main ugm-home">
	<div class="home-content">
		<section class="home-section section-news" aria-labelledby="section-news-title">
			<?php $latest_news_archive_link = add_query_arg( 'ugm_latest_news', '1', home_url( '/' ) ); ?>
			<header class="section-header section-header--news">
				<h2 id="section-news-title" class="section-title">
					<?php echo esc_html( get_theme_mod( 'ugm_latest_section_title', __( 'Berita Terbaru', 'ugm-faculty' ) ) ); ?>
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

			}

			$latest_news_query = new WP_Query( $latest_news_args );
			?>

			<?php if ( $latest_news_query->have_posts() ) : ?>
				<?php
				$latest_news_posts = $latest_news_query->posts;
				$featured_news     = array_shift( $latest_news_posts );
				$side_news         = array_shift( $latest_news_posts );
				$list_news_posts   = array_slice( $latest_news_posts, 0, 2 );
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

					<?php if ( $side_news instanceof WP_Post || ! empty( $list_news_posts ) ) : ?>
						<div class="news-area news-area--list news-list berita-list">
							<?php if ( $side_news instanceof WP_Post ) : ?>
								<?php
								$post = $side_news; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
								setup_postdata( $post );
								$side_news_category = $get_news_category_label();
								?>
								<article <?php post_class( 'news-grid-card news-grid-card--side' ); ?>>
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
										<p class="card-kicker"><?php echo esc_html( $side_news_category ); ?></p>
										<h3 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
										<p class="card-date"><?php echo esc_html( get_the_date( 'j F Y, H.i' ) ); ?></p>
									</div>
								</article>
							<?php endif; ?>

							<?php foreach ( $list_news_posts as $news_post ) : ?>
								<?php
								$post = $news_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
								setup_postdata( $post );
								$news_category = $get_news_category_label();
								?>
								<article <?php post_class( 'news-grid-card news-grid-card--list' ); ?>>
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
						</div>
					<?php endif; ?>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<p class="section-empty"><?php esc_html_e( 'Belum ada berita terbaru.', 'ugm-faculty' ); ?></p>
			<?php endif; ?>
		</section>

		<div class="home-sections-triple">
		<section class="home-section section-academic" aria-labelledby="section-academic-title">
			<header class="section-header">
				<h2 id="section-academic-title" class="section-title">
					<?php echo esc_html( get_theme_mod( 'ugm_academic_section_title', __( 'Berita Akademik', 'ugm-faculty' ) ) ); ?>
				</h2>
				<span class="section-line" aria-hidden="true"></span>
			</header>

			<?php
			// Berita akademik landing page (kategori induk "pendidikan" + subkategori).
			$academic_news_count = max( 1, absint( get_theme_mod( 'ugm_academic_news_count', 3 ) ) );
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
				<?php
				$academic_posts      = $academic_query->posts;
				$academic_featured   = array_shift( $academic_posts );
				$academic_list_posts = array_slice( $academic_posts, 0, 2 );
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
								<p class="card-date"><?php echo esc_html( get_the_date( 'j F Y, H.i' ) ); ?></p>
							</div>
						</article>
					<?php endif; ?>

					<?php if ( ! empty( $academic_list_posts ) ) : ?>
						<div class="portal-column__list">
							<?php foreach ( $academic_list_posts as $academic_post ) : ?>
								<?php
								$post = $academic_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
								setup_postdata( $post );
								$academic_label = $get_academic_category_label();
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
										<p class="card-date"><?php echo esc_html( get_the_date( 'j F Y, H.i' ) ); ?></p>
									</div>
								</article>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<p class="section-empty"><?php esc_html_e( 'Belum ada berita akademik.', 'ugm-faculty' ); ?></p>
			<?php endif; ?>

			<a class="section-arrow-link" href="<?php echo esc_url( $academic_archive_link ); ?>" aria-label="<?php esc_attr_e( 'Lihat semua berita akademik', 'ugm-faculty' ); ?>">&#8594;</a>
		</section>

		<section class="home-section section-profile" aria-labelledby="section-profile-title">
			<header class="section-header">
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
				<?php
				$profile_posts      = $profile_query->posts;
				$profile_featured   = array_shift( $profile_posts );
				$profile_list_posts = array_slice( $profile_posts, 0, 2 );
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
								<p class="card-date"><?php echo esc_html( get_the_date( 'j F Y, H.i' ) ); ?></p>
							</div>
						</article>
					<?php endif; ?>

					<?php if ( ! empty( $profile_list_posts ) ) : ?>
						<div class="portal-column__list">
							<?php foreach ( $profile_list_posts as $profile_post ) : ?>
								<?php
								$post = $profile_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
								setup_postdata( $post );
								$profile_label = $get_profile_category_label();
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
										<p class="card-date"><?php echo esc_html( get_the_date( 'j F Y, H.i' ) ); ?></p>
									</div>
								</article>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<p class="section-empty"><?php esc_html_e( 'Belum ada konten profile.', 'ugm-faculty' ); ?></p>
			<?php endif; ?>

			<a class="section-arrow-link" href="<?php echo esc_url( $profile_archive_link ); ?>" aria-label="<?php esc_attr_e( 'Lihat semua profile', 'ugm-faculty' ); ?>">&#8594;</a>
		</section>

		<section class="home-section section-achievement" aria-labelledby="section-achievement-title">
			<header class="section-header">
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
				<?php
				$achievement_posts      = $achievement_query->posts;
				$achievement_featured   = array_shift( $achievement_posts );
				$achievement_list_posts = array_slice( $achievement_posts, 0, 2 );
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
								<p class="card-date"><?php echo esc_html( get_the_date( 'j F Y, H.i' ) ); ?></p>
							</div>
						</article>
					<?php endif; ?>

					<?php if ( ! empty( $achievement_list_posts ) ) : ?>
						<div class="portal-column__list">
							<?php foreach ( $achievement_list_posts as $achievement_post ) : ?>
								<?php
								$post = $achievement_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
								setup_postdata( $post );
								$achievement_label = $get_achievement_category_label();
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
										<p class="card-date"><?php echo esc_html( get_the_date( 'j F Y, H.i' ) ); ?></p>
									</div>
								</article>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<p class="section-empty"><?php esc_html_e( 'Belum ada konten prestasi.', 'ugm-faculty' ); ?></p>
			<?php endif; ?>

			<a class="section-arrow-link" href="<?php echo esc_url( $achievement_archive_link ); ?>" aria-label="<?php esc_attr_e( 'Lihat semua prestasi', 'ugm-faculty' ); ?>">&#8594;</a>
		</section>
		</div>

		<section class="home-section section-category" aria-labelledby="section-category-title">
			<header class="section-header">
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
			$category_archive_link = home_url( '/category/' );
			?>

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
						<a class="category-card" href="<?php echo esc_url( get_category_link( $category_term->term_id ) ); ?>">
							<span class="category-card__label"><?php echo esc_html( $category_term->name ); ?></span>
							<span class="category-card__count">
								<?php
								printf(
									/* translators: %s: category post count */
									esc_html__( '%s Artikel Total', 'ugm-faculty' ),
									esc_html( number_format_i18n( (int) $category_term->count ) )
								);
								?>
							</span>
						</a>
					<?php endforeach; ?>
				</div>

				<a class="section-view-all section-view-all--category" href="<?php echo esc_url( $category_archive_link ); ?>" aria-label="<?php esc_attr_e( 'Lihat semua kategori', 'ugm-faculty' ); ?>">
					<?php esc_html_e( 'Lihat Semua', 'ugm-faculty' ); ?>
					<span aria-hidden="true">&rarr;</span>
				</a>
			<?php else : ?>
				<p class="section-empty"><?php esc_html_e( 'Belum ada kategori.', 'ugm-faculty' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="home-section section-faculty" aria-labelledby="section-faculty-title">
			<p class="section-overline section-overline--faculty"><?php esc_html_e( 'Seputar UGM', 'ugm-faculty' ); ?></p>
			<header class="section-header section-header--faculty">
				<h2 id="section-faculty-title" class="section-title">
					<?php echo esc_html( get_theme_mod( 'ugm_faculty_section_title', __( 'Fakultas dan Sekolah', 'ugm-faculty' ) ) ); ?>
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

		<section class="home-section section-campus-desktop" aria-label="<?php esc_attr_e( 'Agenda dan fasilitas kampus', 'ugm-faculty' ); ?>">
			<?php
			$agenda_section_title = get_theme_mod( 'ugm_events_section_title', __( 'Agenda Kegiatan', 'ugm-faculty' ) );
			$agenda_category_id   = absint( get_theme_mod( 'ugm_events_category_id', 0 ) );
			$agenda_posts_count   = max( 1, min( 6, absint( get_theme_mod( 'ugm_events_count', 3 ) ) ) );
			$agenda_archive_link  = home_url( '/' );
			$agenda_term_ids      = array();

			if ( $agenda_category_id > 0 ) {
				$agenda_term_ids     = ugm_get_category_tree_ids( $agenda_category_id );
				$agenda_link         = get_category_link( $agenda_category_id );
				if ( ! is_wp_error( $agenda_link ) ) {
					$agenda_archive_link = $agenda_link;
				}
			} else {
				$agenda_root = ugm_get_category_root_by_slugs( array( 'agenda', 'kegiatan', 'events', 'event' ) );
				if ( $agenda_root ) {
					$agenda_root_id      = (int) $agenda_root->term_id;
					$agenda_term_ids     = ugm_get_category_tree_ids( $agenda_root_id );
					$agenda_link         = get_category_link( $agenda_root_id );
					if ( ! is_wp_error( $agenda_link ) ) {
						$agenda_archive_link = $agenda_link;
					}
				}
			}

			$agenda_query_args = array(
				'post_type'           => 'post',
				'posts_per_page'      => $agenda_posts_count,
				'ignore_sticky_posts' => true,
				'post_status'         => 'publish',
				'orderby'             => 'date',
				'order'               => 'DESC',
				'no_found_rows'       => true,
			);

			if ( ! empty( $agenda_term_ids ) ) {
				$agenda_query_args['category__in'] = $agenda_term_ids;
			}

			$agenda_query = new WP_Query( $agenda_query_args );
			?>
			<section class="section-campus-desktop__agenda" aria-labelledby="section-agenda-title">
				<header class="section-header section-header--desktop-agenda">
					<h2 id="section-agenda-title" class="section-title"><?php echo esc_html( $agenda_section_title ); ?></h2>
					<span class="section-line" aria-hidden="true"></span>
				</header>

				<?php if ( $agenda_query->have_posts() ) : ?>
					<div class="desktop-agenda-list">
						<?php while ( $agenda_query->have_posts() ) : ?>
							<?php
							$agenda_query->the_post();
							$agenda_timestamp = (int) get_post_timestamp( get_the_ID() );
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

			$facility_query_args = array(
				'post_type'           => 'post',
				'posts_per_page'      => 4,
				'ignore_sticky_posts' => true,
				'post_status'         => 'publish',
				'orderby'             => 'date',
				'order'               => 'DESC',
				'no_found_rows'       => true,
			);

			if ( ! empty( $facility_term_ids ) ) {
				$facility_query_args['category__in'] = $facility_term_ids;
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


		<?php get_template_part( 'template-parts/section-majalah' ); ?>
	</div>
</main>
<?php
get_footer();

