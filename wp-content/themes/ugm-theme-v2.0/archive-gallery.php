<?php
get_header();
?>

<div id="body">
	<div class="container">

		<div class="row">

			<div id="content" class="col-md-12">

				<?php ugm_breadcrumbs(); ?>

				<div class="section-header">
					<h2 class="section-title">Gallery</h2>
				</div>

				<div class="single-page">
					<div class="row row-flex">

						<?php
						while ( have_posts() ) : the_post();
							
							get_template_part( 'template-parts/loop', 'gallery' );

						endwhile; // End of the loop.
						?>

					</div>
					<?php ugm_theme_paginate_links(); ?>
				</div>

			</div>

		</div>

	</div>
</div>

<?php
get_footer();
?>