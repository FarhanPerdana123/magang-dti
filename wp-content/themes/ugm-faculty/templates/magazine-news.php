<?php
/**
 * Digital magazine listing template.
 *
 * @package ugm-faculty
 */

get_header();

$magazine_title    = get_theme_mod( 'ugm_magazine_section_title', __( 'Majalah Kabar Digital', 'ugm-faculty' ) );

$magazine_query_args = array(
	'post_type'           => 'majalah',
	'posts_per_page'      => -1,
	'ignore_sticky_posts' => true,
	'post_status'         => 'publish',
	'orderby'             => 'date',
	'order'               => 'DESC',
	'no_found_rows'       => true,
);

$magazine_query = new WP_Query( $magazine_query_args );
?>
<main id="primary" class="site-main ugm-archive">
	<div class="ugm-archive__container">
		<nav class="ugm-archive__breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'ugm-faculty' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Beranda', 'ugm-faculty' ); ?></a>
			<span aria-hidden="true">&#8250;</span>
			<span aria-current="page"><?php echo esc_html( $magazine_title ); ?></span>
		</nav>

		<header class="ugm-archive__header">
			<h1 class="ugm-archive__title"><?php echo esc_html( $magazine_title ); ?></h1>
			<span class="ugm-archive__line" aria-hidden="true"></span>
		</header>

		<?php if ( $magazine_query->have_posts() ) : ?>
			<div class="majalah-archive-grid">
				<?php while ( $magazine_query->have_posts() ) : $magazine_query->the_post(); ?>
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
		<?php else : ?>
			<p class="section-empty"><?php esc_html_e( 'Belum ada majalah.', 'ugm-faculty' ); ?></p>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
	</div>
</main>
<?php
get_footer();
