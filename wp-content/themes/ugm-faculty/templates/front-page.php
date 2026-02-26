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
			<header class="section-header">
				<h2 id="section-news-title" class="section-title"><?php esc_html_e( 'Berita Terbaru', 'ugm-faculty' ); ?></h2>
				<span class="section-line" aria-hidden="true"></span>
			</header>

			<?php
			// 3 post terbaru: 1 card besar + 2 card kecil.
			$latest_news_query = new WP_Query(
				array(
					'post_type'           => 'post',
					'posts_per_page'      => 3,
					'ignore_sticky_posts' => true,
				)
			);
			?>

			<?php if ( $latest_news_query->have_posts() ) : ?>
				<div class="news-stack">
					<?php $news_index = 0; ?>
					<?php while ( $latest_news_query->have_posts() ) : $latest_news_query->the_post(); ?>
						<?php
						$news_index++;
						$categories     = get_the_category();
						$category_label = ! empty( $categories ) ? $categories[0]->name : __( 'Kepakaran', 'ugm-faculty' );
						?>
						<article <?php post_class( 1 === $news_index ? 'news-card news-card--featured' : 'news-card news-card--compact' ); ?>>
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
				<p class="section-empty"><?php esc_html_e( 'Belum ada berita terbaru.', 'ugm-faculty' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="home-section section-academic" aria-labelledby="section-academic-title">
			<header class="section-header">
				<h2 id="section-academic-title" class="section-title"><?php esc_html_e( 'Berita Akademik', 'ugm-faculty' ); ?></h2>
				<span class="section-line" aria-hidden="true"></span>
			</header>

			<?php
			// 2 post kategori "akademik".
			$academic_query = new WP_Query(
				array(
					'post_type'           => 'post',
					'posts_per_page'      => 2,
					'category_name'       => 'akademik',
					'ignore_sticky_posts' => true,
					'post_status'         => 'publish',
					'orderby'             => 'date',
					'order'               => 'DESC',
					'no_found_rows'       => true,
				)
			);
			?>

			<?php if ( $academic_query->have_posts() ) : ?>
				<div class="academic-stack">
					<?php while ( $academic_query->have_posts() ) : $academic_query->the_post(); ?>
						<?php
						$categories     = get_the_category();
						$category_label = ! empty( $categories ) ? $categories[0]->name : __( 'Kepakaran', 'ugm-faculty' );
						?>
						<article <?php post_class( 'academic-card' ); ?>>
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
				<p class="section-empty"><?php esc_html_e( 'Belum ada berita akademik.', 'ugm-faculty' ); ?></p>
			<?php endif; ?>

			<a class="section-arrow-link" href="<?php echo esc_url( home_url( '/category/akademik/' ) ); ?>" aria-label="<?php esc_attr_e( 'Lihat semua berita akademik', 'ugm-faculty' ); ?>">&#8594;</a>
		</section>

		<section class="home-section section-profile" aria-labelledby="section-profile-title">
			<header class="section-header">
				<h2 id="section-profile-title" class="section-title"><?php esc_html_e( 'Profile', 'ugm-faculty' ); ?></h2>
				<span class="section-line" aria-hidden="true"></span>
			</header>

			<?php
			// 3 post kategori "profile".
			$profile_query = new WP_Query(
				array(
					'post_type'           => 'post',
					'posts_per_page'      => 3,
					'category_name'       => 'profile',
					'ignore_sticky_posts' => true,
					'post_status'         => 'publish',
					'orderby'             => 'date',
					'order'               => 'DESC',
					'no_found_rows'       => true,
				)
			);
			?>

			<?php if ( $profile_query->have_posts() ) : ?>
				<div class="list-cards">
					<?php while ( $profile_query->have_posts() ) : $profile_query->the_post(); ?>
						<?php
						$categories     = get_the_category();
						$category_label = ! empty( $categories ) ? $categories[0]->name : __( 'Kepakaran', 'ugm-faculty' );
						?>
						<article <?php post_class( 'list-card' ); ?>>
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
				<p class="section-empty"><?php esc_html_e( 'Belum ada konten profile.', 'ugm-faculty' ); ?></p>
			<?php endif; ?>

			<a class="section-arrow-link" href="<?php echo esc_url( home_url( '/category/profile/' ) ); ?>" aria-label="<?php esc_attr_e( 'Lihat semua profile', 'ugm-faculty' ); ?>">&#8594;</a>
		</section>

		<section class="home-section section-achievement" aria-labelledby="section-achievement-title">
			<header class="section-header">
				<h2 id="section-achievement-title" class="section-title"><?php esc_html_e( 'Prestasi', 'ugm-faculty' ); ?></h2>
				<span class="section-line" aria-hidden="true"></span>
			</header>

			<?php
			// 3 post kategori "prestasi".
			$achievement_query = new WP_Query(
				array(
					'post_type'           => 'post',
					'posts_per_page'      => 3,
					'category_name'       => 'prestasi',
					'ignore_sticky_posts' => true,
					'post_status'         => 'publish',
					'orderby'             => 'date',
					'order'               => 'DESC',
					'no_found_rows'       => true,
				)
			);
			?>

			<?php if ( $achievement_query->have_posts() ) : ?>
				<div class="list-cards">
					<?php while ( $achievement_query->have_posts() ) : $achievement_query->the_post(); ?>
						<?php
						$categories     = get_the_category();
						$category_label = ! empty( $categories ) ? $categories[0]->name : __( 'Kepakaran', 'ugm-faculty' );
						?>
						<article <?php post_class( 'list-card list-card--reverse' ); ?>>
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
				<p class="section-empty"><?php esc_html_e( 'Belum ada konten prestasi.', 'ugm-faculty' ); ?></p>
			<?php endif; ?>

			<a class="section-arrow-link" href="<?php echo esc_url( home_url( '/category/prestasi/' ) ); ?>" aria-label="<?php esc_attr_e( 'Lihat semua prestasi', 'ugm-faculty' ); ?>">&#8594;</a>
		</section>

		<section class="home-section section-faculty" aria-labelledby="section-faculty-title">
			<header class="section-header section-header--center">
				<h2 id="section-faculty-title" class="section-title"><?php esc_html_e( 'Fakultas', 'ugm-faculty' ); ?></h2>
			</header>

			<?php
			$faculty_list = array(
				'Fakultas Biologi',
				'Fakultas Ekonomika & Bisnis',
				'Fakultas Farmasi',
				'Fakultas Kedokteran',
				'Fakultas Teknik',
				'Fakultas Hukum',
				'Fakultas Ilmu Sosial dan Politik',
			);
			$items_per_page = 3;
			$total_pages    = ceil( count( $faculty_list ) / $items_per_page );
			?>
			<div class="faculty-slider-wrapper">
				<button class="faculty-nav faculty-nav--prev" aria-label="Previous page">&#8249;</button>
				<button class="faculty-nav faculty-nav--next" aria-label="Next page">&#8250;</button>
				
				<div class="faculty-slider" role="list">
					<?php
					$page_index = 0;
					foreach ( $faculty_list as $index => $faculty_name ) :
						// Start new page every 3 items
						if ( 0 === $index % $items_per_page ) :
							if ( $index > 0 ) :
								echo '</div>'; // Close previous page
							endif;
							?>
							<div class="faculty-page" data-page="<?php echo esc_attr( $page_index ); ?>">
							<?php
							$page_index++;
						endif;
						?>
						<article class="faculty-card" role="listitem">
							<div class="faculty-card__image" aria-hidden="true"></div>
							<div class="faculty-card__overlay">
								<h3 class="faculty-card__title"><?php echo esc_html( $faculty_name ); ?></h3>
							</div>
						</article>
						<?php
					endforeach;
					echo '</div>'; // Close last page
					?>
				</div>
				
				<div class="faculty-pagination" aria-hidden="true">
					<?php for ( $i = 0; $i < $total_pages; $i++ ) : ?>
						<span class="pagination-dot <?php echo 0 === $i ? 'active' : ''; ?>" data-page="<?php echo esc_attr( $i ); ?>"></span>
					<?php endfor; ?>
				</div>
			</div>
		</section>

		<section class="home-section section-category" aria-labelledby="section-category-title">
			<header class="section-header">
				<h2 id="section-category-title" class="section-title"><?php esc_html_e( 'Kategori', 'ugm-faculty' ); ?></h2>
				<span class="section-line" aria-hidden="true"></span>
			</header>

			<?php
			$category_terms = get_categories(
				array(
					'hide_empty' => true,
					'orderby'    => 'name',
					'order'      => 'ASC',
					'number'     => 6,
				)
			);
			?>

			<?php if ( ! empty( $category_terms ) ) : ?>
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
						<?php foreach ( $category_terms as $category_term ) : ?>
							<li>
								<a href="<?php echo esc_url( get_category_link( $category_term->term_id ) ); ?>">
									<?php echo esc_html( $category_term->name ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</details>

				<div class="category-grid">
					<?php foreach ( $category_terms as $category_term ) : ?>
						<a class="category-card" href="<?php echo esc_url( get_category_link( $category_term->term_id ) ); ?>">
							<span class="category-card__label"><?php echo esc_html( $category_term->name ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="section-empty"><?php esc_html_e( 'Belum ada kategori.', 'ugm-faculty' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="home-section section-magazine" aria-labelledby="section-magazine-title">
			<header class="section-header section-header--accent">
				<h2 id="section-magazine-title" class="section-title"><?php esc_html_e( 'Majalah Kabar Digital', 'ugm-faculty' ); ?></h2>
				<span class="section-line" aria-hidden="true"></span>
			</header>

			<?php
			// 4 konten majalah terbaru berdasarkan tanggal publish/upload terbaru.
			$magazine_query = new WP_Query(
				array(
					'post_type'           => 'post',
					'posts_per_page'      => 4,
					'category_name'       => 'majalah-kabar-digital,majalah-kabar,kabar-ugm',
					'ignore_sticky_posts' => true,
					'post_status'         => 'publish',
					'orderby'             => 'date',
					'order'               => 'DESC',
					'no_found_rows'       => true,
				)
			);
			?>

			<?php if ( $magazine_query->have_posts() ) : ?>
				<div class="magazine-grid">
					<?php while ( $magazine_query->have_posts() ) : $magazine_query->the_post(); ?>
						<article <?php post_class( 'magazine-card' ); ?>>
							<a class="magazine-card__link" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
								<?php if ( has_post_thumbnail() ) : ?>
									<div class="magazine-card__thumb">
										<?php the_post_thumbnail( 'medium_large' ); ?>
									</div>
								<?php else : ?>
									<div class="magazine-card__thumb magazine-card__thumb--placeholder">
										<span><?php esc_html_e( 'Majalah Terbaru', 'ugm-faculty' ); ?></span>
									</div>
								<?php endif; ?>
							</a>
						</article>
					<?php endwhile; ?>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<p class="section-empty"><?php esc_html_e( 'Belum ada majalah kabar.', 'ugm-faculty' ); ?></p>
			<?php endif; ?>
		</section>
	</div>
</main>
<?php
get_footer();
