<?php
/**
 * Template Name: Manajemen Page
 * Template Post Type: page
 *
 * @package ugm-faculty
 */

get_header();
?>
<main id="primary" class="site-main ugm-management-page">
	<div class="ugm-management-page__container">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				$page_content  = (string) get_the_content();
				$template_slug = get_page_template_slug( get_the_ID() );
				$render_source = ugm_get_management_render_source( $page_content, $template_slug );

				if ( $render_source === $page_content && '' !== trim( $page_content ) ) {
					the_content();
				} else {
					echo do_blocks( $render_source ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			endwhile;
		else :
			echo do_blocks( ugm_get_default_management_page_blocks() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		endif;
		?>
	</div>
</main>
<?php
get_footer();
