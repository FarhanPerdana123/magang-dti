<?php
/**
 * Digital magazine listing template.
 *
 * @package ugm-faculty
 */

get_header();

$magazine_title    = get_theme_mod( 'ugm_magazine_section_title', __( 'Majalah Kabar Digital', 'ugm-faculty' ) );
$paged             = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$magazine_root     = ugm_get_category_root_by_slugs( array( 'majalah-kabar-digital', 'majalah-kabar', 'kabar-ugm' ) );
$magazine_term_ids = array();

if ( $magazine_root ) {
	$magazine_term_ids = ugm_get_category_tree_ids( (int) $magazine_root->term_id );
}

$magazine_query_args = array(
	'post_type'           => 'post',
	'posts_per_page'      => 12,
	'paged'               => $paged,
	'ignore_sticky_posts' => true,
	'post_status'         => 'publish',
	'orderby'             => 'date',
	'order'               => 'DESC',
);

if ( ! empty( $magazine_term_ids ) ) {
	$magazine_query_args['category__in'] = $magazine_term_ids;
} else {
	$magazine_query_args['category_name'] = 'majalah-kabar-digital,majalah-kabar,kabar-ugm';
}

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
			<div class="ugm-archive__list">
				<?php while ( $magazine_query->have_posts() ) : $magazine_query->the_post(); ?>
					<?php
					$magazine_pdf_url = ugm_get_magazine_pdf_url( get_the_ID() );
					$magazine_link    = '' !== $magazine_pdf_url ? $magazine_pdf_url : get_permalink();
					$open_in_new_tab  = '' !== $magazine_pdf_url;
					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'ugm-archive-card' ); ?>>
						<a class="ugm-archive-card__media-link" href="<?php echo esc_url( $magazine_link ); ?>" aria-label="<?php the_title_attribute(); ?>" <?php echo $open_in_new_tab ? 'target="_blank" rel="noopener"' : ''; ?>>
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="ugm-archive-card__media">
									<?php the_post_thumbnail( 'large' ); ?>
								</div>
							<?php else : ?>
								<div class="ugm-archive-card__media ugm-archive-card__media--placeholder" aria-hidden="true"></div>
							<?php endif; ?>
						</a>

						<div class="ugm-archive-card__body">
							<p class="ugm-archive-card__kicker"><?php esc_html_e( 'Majalah Digital', 'ugm-faculty' ); ?></p>
							<h2 class="ugm-archive-card__title"><a href="<?php echo esc_url( $magazine_link ); ?>" <?php echo $open_in_new_tab ? 'target="_blank" rel="noopener"' : ''; ?>><?php the_title(); ?></a></h2>
							<p class="ugm-archive-card__date"><?php echo esc_html( get_the_date( 'l, j F Y' ) ); ?></p>
							<div class="ugm-archive-card__excerpt"><?php the_excerpt(); ?></div>
						</div>
					</article>
				<?php endwhile; ?>
			</div>

			<nav class="ugm-archive__pagination" aria-label="<?php esc_attr_e( 'Posts navigation', 'ugm-faculty' ); ?>">
				<?php
				echo wp_kses_post(
					paginate_links(
						array(
							'base'      => esc_url_raw( add_query_arg( array( 'ugm_magazine_news' => 1, 'paged' => '%#%' ), home_url( '/' ) ) ),
							'format'    => '',
							'current'   => $paged,
							'total'     => max( 1, (int) $magazine_query->max_num_pages ),
							'mid_size'  => 1,
							'prev_text' => __( 'Sebelumnya', 'ugm-faculty' ),
							'next_text' => __( 'Selanjutnya', 'ugm-faculty' ),
							'type'      => 'list',
						)
					)
				);
				?>
			</nav>
		<?php else : ?>
			<p class="section-empty"><?php esc_html_e( 'Belum ada majalah kabar.', 'ugm-faculty' ); ?></p>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
	</div>
</main>
<?php
get_footer();
