<?php
/**
 * The default template for displaying pages (no template selected).
 *
 * When no page template is chosen, this renders a clean shell:
 * header + footer + editor content only (empty if no blocks added).
 * To use a rich layout, choose a template from the Page sidebar in the editor.
 *
 * @package ugm-faculty
 */

get_header();

$has_content = have_posts();
?>
<main id="primary" class="site-main ugm-page ugm-page--default">
	<?php if ( $has_content ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<?php
			// Only render the article wrapper when there is actual content or a title.
			$page_content       = get_the_content();
			$page_title         = get_the_title();
			$has_visible_blocks = ! empty( trim( $page_content ) );
			$has_title          = ! empty( trim( $page_title ) );
			?>
			<?php if ( $has_visible_blocks || $has_title ) : ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'ugm-page__article' ); ?>>
					<div class="ugm-page__container">
						<?php if ( $has_title ) : ?>
							<header class="ugm-page__header">
								<?php the_title( '<h1 class="ugm-page__title">', '</h1>' ); ?>
							</header>
						<?php endif; ?>

						<?php if ( $has_visible_blocks ) : ?>
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
						<?php endif; ?>
					</div>
				</article>
			<?php endif; ?>

			<?php if ( comments_open() || get_comments_number() ) : ?>
				<div class="ugm-page__container ugm-page__comments">
					<?php comments_template(); ?>
				</div>
			<?php endif; ?>
		<?php endwhile; ?>
	<?php endif; ?>
</main>
<?php
get_footer();
