<article class="single-post">
	<div class="post-content">
		<?php if (has_post_thumbnail()) : ?>
			<div class="wp-caption">
				<?php the_post_thumbnail('ugm-post-content') ?>
				<p class="wp-caption-text"><?php echo get_post(get_post_thumbnail_id())->post_excerpt; ?></p>
			</div>
		<?php endif; ?>
		<?php the_content(); ?>
	</div>
</article>