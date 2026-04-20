<?php
/**
 * The template for displaying search results.
 *
 * @package ugm-faculty
 */

get_header();
?>
<main id="primary" class="site-main ugm-page">
	<div class="ugm-page__container">
		<header class="ugm-page__header">
			<h1 class="ugm-page__title">
				<?php
				printf(
					/* translators: %s: search query. */
					esc_html__( 'Hasil pencarian untuk: %s', 'ugm-faculty' ),
					esc_html( get_search_query() )
				);
				?>
			</h1>
		</header>

		<?php if ( have_posts() ) : ?>
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
			<p><?php esc_html_e( 'Tidak ada hasil pencarian.', 'ugm-faculty' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();