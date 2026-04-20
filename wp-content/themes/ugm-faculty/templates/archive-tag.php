<?php
/**
 * Tag archive template.
 *
 * @package ugm-faculty
 */

get_header();

$current_tag = get_queried_object();
$tag_name    = $current_tag instanceof WP_Term ? $current_tag->name : __( 'Tag', 'ugm-faculty' );
$tag_desc    = $current_tag instanceof WP_Term ? $current_tag->description : '';
?>
<main id="primary" class="site-main ugm-archive">
	<div class="ugm-archive__container">

		<nav class="ugm-archive__breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'ugm-faculty' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Beranda', 'ugm-faculty' ); ?></a>
			<span aria-hidden="true">&#8250;</span>
			<span aria-current="page"><?php echo esc_html( $tag_name ); ?></span>
		</nav>

		<header class="ugm-archive__header">
			<h1 class="ugm-archive__title">
				<?php
				printf(
					/* translators: %s: tag name */
					esc_html__( 'Tag: %s', 'ugm-faculty' ),
					'<span>' . esc_html( $tag_name ) . '</span>'
				);
				?>
			</h1>
			<?php if ( $tag_desc ) : ?>
				<p class="ugm-archive__description"><?php echo wp_kses_post( $tag_desc ); ?></p>
			<?php endif; ?>
			<span class="ugm-archive__line" aria-hidden="true"></span>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="ugm-archive__list">
				<?php while ( have_posts() ) : the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'ugm-archive-card' ); ?>>
						<a class="ugm-archive-card__media-link" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="ugm-archive-card__media">
									<?php the_post_thumbnail( 'large' ); ?>
								</div>
							<?php else : ?>
								<div class="ugm-archive-card__media ugm-archive-card__media--placeholder" aria-hidden="true"></div>
							<?php endif; ?>
						</a>

						<div class="ugm-archive-card__body">
							<?php
							$post_categories = get_the_category();
							$kicker          = ! empty( $post_categories ) ? $post_categories[0]->name : '';
							?>
							<?php if ( $kicker ) : ?>
								<p class="ugm-archive-card__kicker"><?php echo esc_html( $kicker ); ?></p>
							<?php endif; ?>
							<h2 class="ugm-archive-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p class="ugm-archive-card__date"><?php echo esc_html( get_the_date( 'l, j F Y' ) ); ?></p>
							<div class="ugm-archive-card__excerpt"><?php the_excerpt(); ?></div>
						</div>
					</article>
				<?php endwhile; ?>
			</div>

			<nav class="ugm-archive__pagination" aria-label="<?php esc_attr_e( 'Posts navigation', 'ugm-faculty' ); ?>">
				<?php
				the_posts_pagination(
					array(
						'mid_size'  => 1,
						'prev_text' => __( 'Sebelumnya', 'ugm-faculty' ),
						'next_text' => __( 'Selanjutnya', 'ugm-faculty' ),
					)
				);
				?>
			</nav>
		<?php else : ?>
			<p class="section-empty"><?php esc_html_e( 'Belum ada artikel dengan tag ini.', 'ugm-faculty' ); ?></p>
		<?php endif; ?>

	</div>
</main>
<?php
get_footer();
