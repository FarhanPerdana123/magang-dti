<?php
/**
 * Template Name: Pengumuman Page
 * Template Post Type: page
 *
 * @package ugm-faculty
 */

get_header();
?>
<main id="primary" class="site-main ugm-announcement-page">
	<div class="ugm-announcement-page__container container">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();

				$template_slug = (string) get_page_template_slug( get_the_ID() );
				$page_content  = (string) get_the_content();
				$render_source = function_exists( 'ugm_get_announcement_render_source' )
					? ugm_get_announcement_render_source( $page_content, $template_slug )
					: $page_content;

				if ( '' !== trim( $render_source ) ) {
					if ( function_exists( 'ugm_render_announcement_page_source' ) ) {
						echo ugm_render_announcement_page_source( $render_source ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					} else {
						echo do_blocks( $render_source ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
				} else {
					echo function_exists( 'ugm_render_announcement_page_source' )
						? ugm_render_announcement_page_source( ugm_get_default_announcement_page_blocks() ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						: do_blocks( ugm_get_default_announcement_page_blocks() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			endwhile;
		else :
			$render_source = ugm_get_announcement_render_source();
			echo function_exists( 'ugm_render_announcement_page_source' )
				? ugm_render_announcement_page_source( $render_source ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				: do_blocks( $render_source ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		endif;
		?>
	</div>
</main>
<?php
get_footer();
