<article class="single-post <?php echo post_password_required() ? "protected" : "" ?>">
	<div class="post-heading">
		<?php
			the_title( '<h1 class="post-title">', '</h1>' );
		?>
		<ul class="entry-meta">
			<?php if ( has_category() ) : ?>
				<li class="post-category">
					<?php the_category( ', ' ); ?>
				</li>
			<?php endif; ?>
			<li class="post-date"><?php echo the_time('j F Y, H.i') ?></li>
			<li class="post-author"><?php esc_html_e( 'By', 'ugm-theme' ); ?> : <?php the_author_posts_link(); ?></li>
			<!-- <li class="post-reads">245</li> -->
			<?php if(get_comments_number()>0) : ?>
				<li class="post-comments"><?php echo get_comments_number() ?></li>
			<?php endif; ?>
		</ul>
	</div>
	<div class="post-content">
		<?php if (has_post_thumbnail()) : ?>
			<?php the_post_thumbnail('ugm-post-content') ?>
		<?php endif; ?>
		<?php the_content(); ?>
		<?php ugm_theme_paginate_links(); ?>
	</div>
	<?php $tags = get_the_tags(); ?>
	<?php if ( ! empty($tags) ) : ?>
		<div class="tagcloud">
			<span class="tag-title">Tags:</span>
			<?php foreach ( $tags as $t ) : ?>
				<a href="<?php echo get_tag_link( $t ) ?>"><?php echo $t->name ?></a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</article>