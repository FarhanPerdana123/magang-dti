<?php
/**
 * Latest news listing template.
 *
 * @package ugm-faculty
 */

get_header();

$latest_title = get_theme_mod( 'ugm_latest_section_title', __( 'Berita Terbaru', 'ugm-faculty' ) );
$paged        = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$query_args   = array(
	'post_type'           => 'post',
	'posts_per_page'      => 10,
	'paged'               => $paged,
	'ignore_sticky_posts' => true,
	'post_status'         => 'publish',
	'orderby'             => 'date',
	'order'               => 'DESC',
);

$whitelist_ids = function_exists( 'ugm_collect_news_whitelist_category_ids' )
	? ugm_collect_news_whitelist_category_ids()
	: array();

if ( ! empty( $whitelist_ids ) ) {
	$query_args['category__in'] = $whitelist_ids;
} else {
	$exclude_ids = array_unique(
		array_merge(
			function_exists( 'ugm_resolve_agenda_exclude_ids' ) ? ugm_resolve_agenda_exclude_ids( '' ) : array(),
			function_exists( 'ugm_resolve_faculty_exclude_ids_multi' ) ? ugm_resolve_faculty_exclude_ids_multi( '' ) : array()
		)
	);

	if ( ! empty( $exclude_ids ) ) {
		$query_args['category__not_in'] = $exclude_ids;
	}
}

$latest_news_query = new WP_Query( $query_args );
?>
<main id="primary" class="site-main ugm-archive">
	<div class="ugm-archive__container">
		<nav class="ugm-archive__breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'ugm-faculty' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Berita', 'ugm-faculty' ); ?></a>
			<span aria-hidden="true">&#8250;</span>
			<span aria-current="page"><?php echo esc_html( $latest_title ); ?></span>
		</nav>

		<header class="ugm-archive__header">
			<h1 class="ugm-archive__title"><?php echo esc_html( $latest_title ); ?></h1>
			<span class="ugm-archive__line" aria-hidden="true"></span>
		</header>

		<?php if ( $latest_news_query->have_posts() ) : ?>
			<div class="ugm-archive__list">
				<?php while ( $latest_news_query->have_posts() ) : $latest_news_query->the_post(); ?>
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
							$kicker          = ! empty( $post_categories ) ? $post_categories[0]->name : __( 'Kepakaran', 'ugm-faculty' );
							?>
							<p class="ugm-archive-card__kicker"><?php echo esc_html( $kicker ); ?></p>
							<h2 class="ugm-archive-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
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
							'base'      => esc_url_raw( add_query_arg( array( 'ugm_latest_news' => 1, 'paged' => '%#%' ), home_url( '/' ) ) ),
							'format'    => '',
							'current'   => $paged,
							'total'     => max( 1, (int) $latest_news_query->max_num_pages ),
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
			<p class="section-empty"><?php esc_html_e( 'Belum ada berita terbaru.', 'ugm-faculty' ); ?></p>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
	</div>
</main>
<?php
get_footer();
