<?php
	$date = get_field('ugm_event_date', get_the_ID());
?>
<article class="post">
	<div class="row">
	
		<div class="col-md-8 col-sm-8 post-content">
			<div class="post-title">
				<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
				<span class="post-date"><?php the_time('j F Y') ?></span>
			</div>
		</div>
	</div>
</article>