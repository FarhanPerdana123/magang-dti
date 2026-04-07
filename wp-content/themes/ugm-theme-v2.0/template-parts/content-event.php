<article class="single-post" <?php echo post_password_required() ? "protected" : "" ?>>
	<div class="post-heading">
		<?php
			the_title( '<h1 class="post-title">', '</h1>' );
		?>
		<ul class="entry-meta">
			<?php if ( has_term( '', 'event-category' ) ) : ?>
				<li class="post-category">
					<?php the_event_category( ', ' ); ?>
				</li>
			<?php endif; ?>
			<li class="post-author"><?php esc_html_e( 'By', 'ugm-theme' ) ?>: <?php the_author() ?></li>
			<!-- <li class="post-reads">245</li> -->
			<?php if(get_comments_number()>0) : ?>
			<li class="post-comments"><?php echo get_comments_number() ?></li>
			<?php endif; ?>
		</ul>
	</div>
	<div class="post-content">
		<?php if (post_password_required()) :
			the_content();
		else: ?>
			<?php if (has_post_thumbnail()) : ?>
				<?php the_post_thumbnail('ugm-post-content') ?>
			<?php endif; ?>
			<div class="event-table">
				<table>
					<?php $comitee = get_field('ugm_event_comitee', get_the_ID()); ?>
					<?php if(!empty($comitee)) : ?>
						<tr>
							<th><?php esc_html_e( 'Comitee', 'ugm-theme' ); ?></th>
							<td class="colon">:</td>
							<td><?php the_field('ugm_event_comitee', get_the_ID()) ?></td>
						</tr>
					<?php endif; ?>
					<?php $location = get_field('ugm_event_location', get_the_ID()); ?>
					<?php if(!empty($location)) : ?>
						<tr>
							<th><?php esc_html_e( 'Location', 'ugm-theme' ); ?></th>
							<td class="colon">:</td>
							<td><?php the_field('ugm_event_location', get_the_ID()) ?></td>
						</tr>
					<?php endif; ?>
					<?php $contact = get_field('ugm_event_contact', get_the_ID()); ?>
					<?php if(!empty($contact)) : ?>
						<tr>
							<th><?php esc_html_e( 'Contact', 'ugm-theme' ); ?></th>
							<td class="colon">:</td>
							<td><?php the_field('ugm_event_contact', get_the_ID()) ?></td>
						</tr>
					<?php endif; ?>
					<?php $website = get_field('ugm_event_website', get_the_ID()); ?>
					<?php if(!empty($website)) : ?>
						<tr>
							<th>Website</th>
							<td class="colon">:</td>
							<td><a href="<?php the_field('ugm_event_website', get_the_ID()) ?>" target="_blank"><?php the_field('ugm_event_website', get_the_ID()) ?></a></td>
						</tr>
					<?php endif; ?>
					<tr>
						<th><?php esc_html_e( 'Date', 'ugm-theme' ); ?></th>
						<td class="colon">:</td>
						<td>
							<?php
								$date_start = get_field('ugm_event_date', get_the_ID());
								$date_end = get_field('ugm_event_date_end', get_the_ID());
							?>
							<?php echo date_i18n('l, d F Y', strtotime($date_start)); ?>
							<?php
								if (!empty($date_end) && $date_start != $date_end) {
									echo '- '.date_i18n('l, d F Y', strtotime($date_end));
								}
							?>
						</td>
					</tr>
				</table>
			</div>
			<?php the_content(); ?>
		<?php endif; ?>
	</div>
	<?php $tags = get_the_terms( get_the_ID(), 'event-tag' ); ?>
	<?php if ( ! empty( $tags ) ) : ?>
		<div class="tagcloud">
			<span class="tag-title">Tags:</span>
			<?php foreach ($tags as $t) : ?>
				<a href="<?php echo get_term_link( $t, 'event-tag' ); ?>"><?php echo esc_html( $t->name ); ?></a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</article>