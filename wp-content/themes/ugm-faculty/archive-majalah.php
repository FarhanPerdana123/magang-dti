<?php
/**
 * Archive template for Majalah.
 *
 * @package ugm-faculty
 */

get_header();

$majalah_query = new WP_Query(
	array(
		'post_type'           => 'majalah',
		'posts_per_page'      => -1,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);
?>

<main id="primary" class="site-main ugm-archive majalah-archive">
	<div class="ugm-archive__container">
		<nav class="ugm-archive__breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'ugm-faculty' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Beranda', 'ugm-faculty' ); ?></a>
			<span aria-hidden="true">&#8250;</span>
			<span aria-current="page"><?php post_type_archive_title(); ?></span>
		</nav>

		<header class="ugm-archive__header">
			<h1 class="ugm-archive__title"><?php post_type_archive_title(); ?></h1>
			<span class="ugm-archive__line" aria-hidden="true"></span>
		</header>

		<?php if ( $majalah_query->have_posts() ) : ?>
			<div class="majalah-archive-grid">
				<?php while ( $majalah_query->have_posts() ) : $majalah_query->the_post(); ?>
					<?php
					$magazine_pdf_url = ugm_get_magazine_pdf_url( get_the_ID() );
					$magazine_link    = '' !== $magazine_pdf_url ? $magazine_pdf_url : get_permalink();
					$open_in_new_tab  = '' !== $magazine_pdf_url;
					?>
					<article <?php post_class( 'majalah-card' ); ?>>
						<a class="majalah-card__link" href="<?php echo esc_url( $magazine_link ); ?>" aria-label="<?php the_title_attribute(); ?>" <?php echo $open_in_new_tab ? 'target="_blank" rel="noopener"' : ''; ?>>
							<div class="majalah-card__media">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'large' ); ?>
								<?php else : ?>
									<div class="majalah-card__placeholder" aria-hidden="true"></div>
								<?php endif; ?>
							</div>
							<h2 class="majalah-card__title"><?php the_title(); ?></h2>
						</a>
					</article>
				<?php endwhile; ?>
			</div>

			<?php wp_reset_postdata(); ?>
		<?php else : ?>
			<p class="section-empty"><?php esc_html_e( 'Belum ada majalah.', 'ugm-faculty' ); ?></p>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
