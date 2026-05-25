<?php
/**
 * Majalah Kabar Digital section for landing page.
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$visibility_class = '';
if ( isset( $args ) && is_array( $args ) && isset( $args['visibility_class'] ) ) {
	$visibility_class = sanitize_html_class( (string) $args['visibility_class'] );
}

$majalah_title        = get_theme_mod( 'ugm_magazine_section_title', __( 'Majalah Kabar UGM', 'ugm-faculty' ) );
$majalah_description  = get_theme_mod( 'ugm_magazine_section_description', __( 'Kabar dari UGM dalam bentuk majalah digital', 'ugm-faculty' ) );
$majalah_archive_link = add_query_arg( 'ugm_magazine_news', '1', home_url( '/' ) );
$majalah_count        = max( 1, min( 12, absint( get_theme_mod( 'ugm_magazine_item_count', 4 ) ) ) );

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

<<<<<<< HEAD
<section class="home-section section-majalah-digital <?php echo esc_attr( $visibility_class ); ?>" aria-labelledby="section-majalah-digital-title">
	<div class="section-majalah-digital__top">
		<div class="section-majalah-digital__intro">
			<header class="section-header section-header--accent section-header--majalah">
				<h2 id="section-majalah-digital-title" class="section-title"><?php echo esc_html( $majalah_title ); ?></h2>
				<span class="section-line" aria-hidden="true"></span>
			</header>
			<?php if ( '' !== trim( $majalah_description ) ) : ?>
				<p class="section-majalah-digital__description"><?php echo esc_html( $majalah_description ); ?></p>
			<?php endif; ?>
		</div>

		<a class="section-view-all section-view-all--majalah" href="<?php echo esc_url( $majalah_archive_link ); ?>" aria-label="<?php esc_attr_e( 'Lihat semua majalah', 'ugm-faculty' ); ?>">
			<?php esc_html_e( 'Lihat Semua', 'ugm-faculty' ); ?>
			<span aria-hidden="true">&#8594;</span>
		</a>
	</div>
=======
<section class="home-section section-majalah-digital" aria-labelledby="section-majalah-digital-title">
	<header class="section-header section-header--accent scroll-reveal">
		<h2 id="section-majalah-digital-title" class="section-title"><?php echo esc_html( $majalah_title ); ?></h2>
		<span class="section-line" aria-hidden="true"></span>
	</header>
>>>>>>> origin/dev-fe

	<?php if ( $majalah_query->have_posts() ) : ?>
		<div class="majalah-grid">
			<?php while ( $majalah_query->have_posts() ) : $majalah_query->the_post(); ?>
				<?php
				$magazine_pdf_url = ugm_get_magazine_pdf_url( get_the_ID() );
				$magazine_link    = '' !== $magazine_pdf_url ? $magazine_pdf_url : get_permalink();
				$open_in_new_tab  = '' !== $magazine_pdf_url;
				?>
				<article <?php post_class( 'majalah-card scroll-reveal' ); ?>>
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
			<?php
			$majalah_loaded_posts = (int) $majalah_query->post_count;
			$majalah_missing      = max( 0, $majalah_count - $majalah_loaded_posts );
			echo ugm_render_partial_skeleton_items( 'magazine-card', $majalah_missing ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</div>
		<a class="section-arrow-link section-arrow-link--majalah" href="<?php echo esc_url( $majalah_archive_link ); ?>" aria-label="<?php esc_attr_e( 'Lihat semua majalah', 'ugm-faculty' ); ?>">&#8594;</a>
		<?php wp_reset_postdata(); ?>
	<?php else : ?>
<<<<<<< HEAD
		<?php echo ugm_render_empty_skeleton( 'magazine-grid', __( 'Belum ada majalah.', 'ugm-faculty' ), array( 'count' => 4 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php endif; ?>
=======
		<p class="section-empty scroll-reveal"><?php esc_html_e( 'Belum ada majalah.', 'ugm-faculty' ); ?></p>
	<?php endif; ?>

	<a class="section-arrow-link scroll-reveal" href="<?php echo esc_url( $majalah_archive_link ); ?>" aria-label="<?php esc_attr_e( 'Lihat semua majalah', 'ugm-faculty' ); ?>">&#8594;</a>
>>>>>>> origin/dev-fe
</section>
