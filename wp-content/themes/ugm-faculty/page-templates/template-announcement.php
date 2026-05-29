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

				$page_content = (string) get_the_content();
				if ( '' !== trim( $page_content ) ) {
					the_content();
				} else {
					echo do_blocks( ugm_get_announcement_render_source() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			endwhile;
		else :
			echo do_blocks( ugm_get_announcement_render_source() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		endif;
		?>
	</div>
</main>
<?php
get_footer();
