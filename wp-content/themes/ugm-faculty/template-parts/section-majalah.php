<?php
/**
 * Majalah Kabar Digital section for landing page.
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$majalah_title        = get_theme_mod( 'ugm_magazine_section_title', __( 'Majalah Kabar Digital', 'ugm-faculty' ) );
$majalah_archive_link = get_post_type_archive_link( 'majalah' );
$majalah_count        = max( 1, min( 12, absint( get_theme_mod( 'ugm_magazine_item_count', 4 ) ) ) );

if ( ! $majalah_archive_link ) {
	$majalah_archive_link = home_url( '/majalah/' );
}

$majalah_query = new WP_Query(
	array(
		'post_type'           => 'majalah',
		'posts_per_page'      => $majalah_count,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);
?>

<section class="home-section section-majalah-digital" aria-labelledby="section-majalah-digital-title">
	<header class="section-header section-header--accent">
		<h2 id="section-majalah-digital-title" class="section-title"><?php echo esc_html( $majalah_title ); ?></h2>
		<span class="section-line" aria-hidden="true"></span>
	</header>

	<?php if ( $majalah_query->have_posts() ) : ?>
		<div class="majalah-grid">
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
						<h3 class="majalah-card__title"><?php the_title(); ?></h3>
					</a>
				</article>
			<?php endwhile; ?>
		</div>
		<?php wp_reset_postdata(); ?>
	<?php else : ?>
		<p class="section-empty"><?php esc_html_e( 'Belum ada majalah.', 'ugm-faculty' ); ?></p>
	<?php endif; ?>

	<a class="section-arrow-link" href="<?php echo esc_url( $majalah_archive_link ); ?>" aria-label="<?php esc_attr_e( 'Lihat semua majalah', 'ugm-faculty' ); ?>">&#8594;</a>
</section>
