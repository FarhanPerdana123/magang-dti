<?php
get_header();
?>

<div id="body">
	<div class="container">

		<div class="row">

			<div id="content" class="<?php ugm_layout_class('content') ?>">

				<?php ugm_breadcrumbs(); ?>
				
				<?php
				while ( have_posts() ) : the_post();

					//content
					get_template_part( 'template-parts/content', 'gallery' );

					//sharer
					ugm_social_sharer();

					// If comments are open or we have at least one comment, load up the comment template.
					if ( comments_open() || get_comments_number() ) :
						comments_template();
					endif;

					get_template_part( 'template-parts/related', 'gallery' );

				endwhile; // End of the loop.
				?>
			</div>
			
			<div class="<?php ugm_layout_class('sharer_wrapper') ?> share-box-wrapper">
				<?php ugm_social_sharer(); ?>
			</div>
			<?php get_sidebar(); ?>

		</div>

	</div>
</div>

<?php
get_footer();
?>