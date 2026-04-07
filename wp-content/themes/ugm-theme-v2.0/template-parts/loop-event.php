<?php
	$date = get_field('ugm_event_date', get_the_ID());
?>
<article class="post post-event">
	<div class="row">
		<div class="col-md-3 col-sm-3 col-xs-3 event-date">
			<span><?php echo date_i18n('d', strtotime($date)); ?> <strong><?php echo date_i18n('M', strtotime($date)); ?></strong></span>
		</div>
		<div class="col-md-9 col-sm-9 col-xs-9 post-content">
			<div class="post-title">
				<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
			</div>
			<div class="entry-content">
				<?php $short_desc = get_field('ugm_event_short_description', get_the_ID()); ?>
				<?php if(!empty($short_desc)) : ?>
					<p><?php the_field('ugm_event_short_description', get_the_ID()); ?></p>
				<?php else: ?>
					<?php echo ugm_berita_excerpt( get_the_ID(), 25 ); ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</article>