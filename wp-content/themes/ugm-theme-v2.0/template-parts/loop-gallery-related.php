<figure class="gallery-item col-md-4 col-sm-6">
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
	<figcaption class="gallery-caption">
		<?php
			if (null == get_the_title()) :
				echo "Lihat Gallery";
			else :
				the_title();
			endif;
		?>
	</figcaption>
</figure>