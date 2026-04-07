<?php
/**
 * Template Name: Halaman Kosong
 */

get_header();
?>

<div id="body">
	<div class="container">

		<div class="row">

			<div id="content" class="col-md-12">
				
				<?php
				while ( have_posts() ) : the_post();

					//content
					get_template_part( 'template-parts/content', 'blank' );

					// If comments are open or we have at least one comment, load up the comment template.
					if ( comments_open() || get_comments_number() ) :
						comments_template();
					endif;

				endwhile; // End of the loop.
				?>
			</div>

		</div>

	</div>
</div>

<?php
get_footer();
?>