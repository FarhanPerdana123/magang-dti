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
			<?php
			$ugm_rector_content = (string) get_the_content();
			$ugm_rector_blocks  = parse_blocks( $ugm_rector_content );
			$ugm_rector_layout_blocks = array();
			$ugm_collect_rector_blocks = static function ( $blocks ) use ( &$ugm_collect_rector_blocks, &$ugm_rector_layout_blocks ) {
				foreach ( $blocks as $block ) {
					$block_name = (string) ( $block['blockName'] ?? '' );
					if ( in_array( $block_name, array( 'ugm/rector-greeting-content', 'ugm/about-ugm-sidebar' ), true ) ) {
						$ugm_rector_layout_blocks[] = $block;
						continue;
					}

					if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
						$ugm_collect_rector_blocks( $block['innerBlocks'] );
					}
				}
			};

			$ugm_collect_rector_blocks( $ugm_rector_blocks );

			if ( ! empty( $ugm_rector_layout_blocks ) ) {
				?>
				<div class="ugm-rector-template-layout ugm-rector-greeting-layout">
					<?php echo do_blocks( serialize_blocks( $ugm_rector_layout_blocks ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<?php
			} elseif ( '' !== trim( $ugm_rector_content ) ) {
				the_content();
			} else {
				?>
				<div class="ugm-rector-template-layout ugm-rector-greeting-layout">
					<?php echo do_blocks( ugm_get_default_rector_greeting_blocks() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<?php
			}
			?>
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
