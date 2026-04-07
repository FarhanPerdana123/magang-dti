<?php
/**
 * Template Name: Direktori 1 Kolom
 */

get_header();
?>

<div id="body">
	<div class="container">
	
		<div id="page-header">
			<?php ugm_breadcrumbs(); ?>
		</div>
				
		<?php
		while ( have_posts() ) : the_post();

			//content
			get_template_part( 'template-parts/content', 'directory-1' );

		endwhile; // End of the loop.
		?>

	</div>
</div>

<?php
get_footer();
?>