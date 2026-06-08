<?php
/**
 * Template Name: Sambutan Rektor
 * Template Post Type: post, page
 *
 * Renders Gutenberg content for the Sambutan Rektor layout.
 *
 * @package ugm-faculty
 */

get_header();
?>

<main id="primary" class="site-main ugm-rector-template">
	<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'ugm-rector-template__article' ); ?>>
			<div class="ugm-rector-template-layout ugm-rector-greeting-layout">
				<?php
				$ugm_rector_content = (string) get_the_content();
				if ( '' !== trim( $ugm_rector_content ) ) {
					the_content();
				} else {
					echo do_blocks( ugm_get_default_rector_greeting_blocks() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</div>
			<?php
			wp_link_pages(
				array(
					'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Page navigation', 'ugm-faculty' ) . '"><span class="page-links__label">' . esc_html__( 'Pages:', 'ugm-faculty' ) . '</span>',
					'after'  => '</nav>',
				)
			);
			?>
		</article>

		<?php if ( comments_open() || get_comments_number() ) : ?>
			<div class="ugm-rector-template-layout ugm-rector-template__comments">
				<?php comments_template(); ?>
			</div>
		<?php endif; ?>
	<?php endwhile; ?>
</main>

<?php
get_footer();
