<?php
get_header();
?>

<div id="body">
	<div class="container">

		<div class="row">

			<div id="content" class="<?php ugm_layout_class('content') ?>">

				<?php ugm_breadcrumbs(); ?>

				<div class="section-header">
					<?php if (is_category()) : ?>
						<span><?php _e('Arsip', 'ugm-theme') ?>:</span>
						<h2 class="section-title"><?php single_cat_title(); ?></h2>
					<?php elseif(is_tag()) : ?>
						<span><?php _e('Arsip', 'ugm-theme') ?>:</span>
						<h2 class="section-title"><?php single_cat_title(); ?></h2>
					<?php elseif(is_year()) : ?>
						<span><?php _e('Arsip', 'ugm-theme') ?>:</span>
						<h2 class="section-title"><?php echo get_the_date('Y'); ?></h2>
					<?php elseif(is_month()) : ?>
						<span><?php _e('Arsip', 'ugm-theme') ?> <?php echo get_the_date('Y'); ?>:</span>
						<h2 class="section-title"><?php echo get_the_date('F'); ?></h2>
					<?php elseif(is_day()) : ?>
						<span><?php _e('Arsip', 'ugm-theme') ?> <?php echo get_the_date('Y'); ?>:</span>
						<h2 class="section-title"><?php echo get_the_date('j F'); ?></h2>
					<?php else: ?>
						<span><?php _e('Arsip', 'ugm-theme') ?>:</span>
						<h2 class="section-title"><?php echo get_the_archive_title(); ?></h2>
					<?php endif; ?>
				</div>

				<?php
				if ( have_posts() ) :

					while ( have_posts() ) : the_post();
						
						get_template_part( 'template-parts/loop', 'post' );

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