<?php
get_header();
?>

<div id="body">
	<div class="container">

		<div class="row">

			<div id="content" class="<?php ugm_layout_class('content') ?>">

				<?php ugm_breadcrumbs(); ?>
				
				<?php if(!is_home() && !is_front_page() && !is_singular()) : ?>
					<div class="section-header">
						<span>Archive:</span>
						<h2 class="section-title"><?php echo get_the_archive_title(); ?></h2>
					</div>
				<?php endif; ?>

				<?php
				while ( have_posts() ) : the_post();

					get_template_part( 'template-parts/loop', 'post' );

					// If comments are open or we have at least one comment, load up the comment template.
					if ( comments_open() || get_comments_number() ) :
						comments_template();
					endif;

				endwhile; // End of the loop.
				?>

				<?php
					if (!is_singular()) :
						ugm_theme_paginate_links();
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