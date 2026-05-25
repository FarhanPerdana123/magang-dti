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

				$render_source = ugm_get_announcement_render_source( get_the_content() );

				echo do_blocks( $render_source ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			endwhile;
		else :
			echo do_blocks( ugm_get_announcement_render_source() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		endif;
		?>
	</div>
</main>
<?php
get_footer();
