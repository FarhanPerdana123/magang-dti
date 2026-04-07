<?php
get_header();
?>

<div id="body">
	<div class="container">

		<div class="row">

			<div id="content" class="col-md-9">

				<?php ugm_breadcrumbs(); ?>

				<div class="section-header">
					<span><?php esc_html_e( 'Search Result For:', 'ugm-theme' ); ?></span>
					<h2 class="section-title"><?php the_search_query(); ?></h2>
				</div>

				<?php
				if ( have_posts() ) :

					while ( have_posts() ) : the_post();
						
						get_template_part( 'template-parts/loop', 'post-search' );

					endwhile; // End of the loop.

					ugm_theme_paginate_links();

				else:

					get_template_part( 'template-parts/content', 'none' );

				endif;

				?>

			</div>

			<?php get_sidebar(); ?>

		</div>

	</div>
</div>

<?php
get_footer();
?>