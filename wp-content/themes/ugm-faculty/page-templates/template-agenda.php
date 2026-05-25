<?php
/**
 * Template Name: Agenda Page
 * Template Post Type: page
 *
 * Reusable page template for the full agenda listing.
 *
 * @package ugm-faculty
 */

get_header();
?>
<main id="primary" class="site-main ugm-agenda-page">
	<div class="ugm-agenda-page__container container">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();

				$page_content  = (string) get_the_content();
				$render_source = false !== strpos( $page_content, '<!-- wp:' )
					? $page_content
					: ugm_get_default_agenda_page_blocks();

				echo do_blocks( $render_source ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			endwhile;
		else :
			echo do_blocks( ugm_get_default_agenda_page_blocks() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		endif;
		?>
	</div>
</main>
<?php
get_footer();
