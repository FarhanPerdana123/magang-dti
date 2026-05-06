<?php
/**
 * Template Name: Halaman Konten
 * Template Post Type: page
 *
 * Blank content page: renders only the page title and block editor content
 * within the theme's styled container. Use for pages like About, Contact, etc.
 *
 * @package ugm-faculty
 */

get_header();
?>

<main id="primary" class="site-main ugm-page ugm-page--content-template">
	<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'ugm-page__article' ); ?>>
			<div class="ugm-page__container">
				<header class="ugm-page__header">
					<?php the_title( '<h1 class="ugm-page__title">', '</h1>' ); ?>
				</header>

				<div class="ugm-page__content">
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

		<?php if ( comments_open() || get_comments_number() ) : ?>
			<div class="ugm-page__container ugm-page__comments">
				<?php comments_template(); ?>
			</div>
		<?php endif; ?>
	<?php endwhile; ?>
</main>

<?php
get_footer();
