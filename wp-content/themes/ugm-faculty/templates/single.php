<?php
/**
 * The template for displaying all single posts.
 *
 * @package ugm-faculty
 */

get_header();
?>
<main id="primary" class="site-main ugm-single">
	<?php while ( have_posts() ) : the_post(); ?>
		<?php
		$post_id        = get_the_ID();
		$category_ids   = wp_get_post_categories( $post_id );
		$primary_cat_id = ! empty( $category_ids ) ? (int) $category_ids[0] : 0;
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'ugm-article' ); ?>>
			<div class="ugm-article__container">
				<nav class="ugm-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'ugm-faculty' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'ugm-faculty' ); ?></a>
					<span aria-hidden="true">/</span>
					<?php if ( $primary_cat_id ) : ?>
						<a href="<?php echo esc_url( get_category_link( $primary_cat_id ) ); ?>">
							<?php echo esc_html( get_cat_name( $primary_cat_id ) ); ?>
						</a>
						<span aria-hidden="true">/</span>
					<?php endif; ?>
					<span aria-current="page"><?php the_title(); ?></span>
				</nav>

				<header class="ugm-article__header">
					<?php the_title( '<h1 class="ugm-article__title">', '</h1>' ); ?>
					<div class="ugm-article__meta">
						<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
						<?php $categories_list = get_the_category_list( ', ' ); ?>
						<?php if ( $categories_list ) : ?>
							<span class="ugm-article__meta-sep" aria-hidden="true">|</span>
							<span class="ugm-article__categories"><?php echo wp_kses_post( $categories_list ); ?></span>
						<?php endif; ?>
					</div>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="ugm-article__featured-image">
						<?php the_post_thumbnail( 'large', array( 'class' => 'ugm-article__image' ) ); ?>
					</figure>
				<?php endif; ?>

				<div class="ugm-article__content">
					<?php
					the_content();
					wp_link_pages(
						array(
							'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Page navigation', 'ugm-faculty' ) . '"><span class="page-links__label">' . esc_html__( 'Pages:', 'ugm-faculty' ) . '</span>',
							'after'  => '</nav>',
						)
					);
					?>
				</div>
			</div>
		</article>

		<?php
		$related_args = array(
			'post_type'           => 'post',
			'posts_per_page'      => 3,
			'post__not_in'        => array( $post_id ),
			'ignore_sticky_posts' => true,
		);
		if ( ! empty( $category_ids ) ) {
			$related_args['category__in'] = $category_ids;
		}
		$related_query = new WP_Query( $related_args );
		?>
		<?php if ( $related_query->have_posts() ) : ?>
			<section class="ugm-related" aria-labelledby="related-posts-title">
				<div class="ugm-article__container">
					<h2 id="related-posts-title" class="ugm-related__title"><?php esc_html_e( 'Related Posts', 'ugm-faculty' ); ?></h2>
					<div class="ugm-related__grid">
						<?php while ( $related_query->have_posts() ) : $related_query->the_post(); ?>
							<article class="ugm-related__card">
								<h3 class="ugm-related__card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<p class="ugm-related__card-meta"><?php echo esc_html( get_the_date() ); ?></p>
							</article>
						<?php endwhile; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>

		<?php if ( comments_open() || get_comments_number() ) : ?>
			<section class="ugm-comments">
				<div class="ugm-article__container">
					<?php comments_template(); ?>
				</div>
			</section>
		<?php endif; ?>
	<?php endwhile; ?>
</main>
<?php
get_footer();
