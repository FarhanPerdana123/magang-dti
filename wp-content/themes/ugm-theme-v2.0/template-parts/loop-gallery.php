<figure class="gallery-item col-md-3 col-sm-6">
	<div class="gallery-img">
		<a href="<?php the_permalink(); ?>">
			<?php
			if (has_post_thumbnail()) {
				the_post_thumbnail('ugm-archive-thumbnail-small');
			} else {
				$gallery = get_field('ugm_gallery', get_the_ID());
				if (!empty($gallery[0]) && !empty($gallery[0]['image'])) {
					echo wp_get_attachment_image($gallery[0]['image'], 'ugm-archive-thumbnail-small');
				}
			}
			?>
		</a>
	</div>
	<div class="post-title">
		<h3>
			<a href="<?php the_permalink() ?>">
				<?php
					if (null == get_the_title()) :
						echo "Lihat Artikel";
					else :
						the_title();
					endif;
				?>
			</a>
		</h3>
		<p class="post-meta">
			<span class="post-date"><?php the_time('l, j F Y') ?></span>
		</p>
	</div>
</figure>