<?php
/**
 * Main template file.
 *
 * @package ugm-faculty
 */

get_header();
?>
<main id="primary" class="site-main ugm-page">
	<div class="ugm-page__container">
		<?php if ( have_posts() ) : ?>
			<?php if ( is_home() && ! is_front_page() ) : ?>
				<header class="ugm-page__header">
					<h1 class="ugm-page__title">
						<?php
						$latest_title = get_theme_mod( 'ugm_latest_section_title', __( 'Berita Terbaru', 'ugm-faculty' ) );
						echo esc_html( $latest_title );
						?>
					</h1>
				</header>
			<?php endif; ?>

			<?php while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'ugm-card' ); ?> style="margin-bottom:1rem;">
					<div class="ugm-card__body">
						<p class="ugm-card__meta"><?php echo esc_html( get_the_date() ); ?></p>
						<h2 class="ugm-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<div class="ugm-card__excerpt"><?php the_excerpt(); ?></div>
					</div>
				</article>
			<?php endwhile; ?>

			<?php the_posts_navigation(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'No content found.', 'ugm-faculty' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
