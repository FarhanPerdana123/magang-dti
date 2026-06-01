<?php
/**
 * Template Name: Halaman Galeri
 * Template Post Type: page
 *
 * Reusable full-width gallery page shell. If the editor content is empty, the
 * default gallery block is rendered automatically.
 *
 * @package ugm-faculty
 */

get_header();
?>

<main id="primary" class="site-main ugm-gallery-template ugm-gallery-page">
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();

			$page_content  = (string) get_the_content();
			$template_slug = get_page_template_slug( get_the_ID() );
			$render_source = ugm_get_gallery_render_source( $page_content, $template_slug );

			if ( $render_source === $page_content && '' !== trim( $page_content ) ) {
				the_content();
			} else {
				echo do_blocks( $render_source ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		endwhile;
	else :
		echo do_blocks( ugm_get_default_gallery_page_blocks() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	endif;
	?>
</main>
<?php
get_footer();
