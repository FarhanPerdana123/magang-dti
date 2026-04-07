<?php
	$sidebar = get_field('ugm_directory_sidebar');
	$image = get_field('ugm_directory_show_image');
	$directory = get_field('ugm_directory_list');
	$dir = 1;
?>
<div id="directory" class="row">
	<div id="content" class="<?php echo $sidebar == 'none' ? 'col-md-12' : 'col-md-9'; ?>">
		<div class="section-header">
			<h2 class="section-title"><?php the_title() ?></h2>
			<div class="section-intro"><?php the_content(); ?></div>
		</div>
		<?php if (!empty($directory)) : ?>
			<?php foreach ($directory as $d) : ?>
				<section class="directory-item" id="directory-<?php echo $dir ?>">
					<div class="content-header">
						<h2 class="content-title"><?php echo $d['title'] ?></h2>
					</div>
					<?php $is_image = $image && !empty($d['image']); ?>
					<div class="content-body <?php echo $is_image ? 'row' : '' ?>">
						<?php if ($is_image) : ?>
							<div class="content-img col-md-4 col-sm-4">
								<?php echo wp_get_attachment_image($d['image'], 'ugm-archive-thumbnail-medium', false, array('class'=>'img-responsive')); ?>
							</div>
						<?php endif; ?>
						<div class="content-text <?php echo $is_image ? 'col-md-8 col-sm-8' : '' ?>">
							<p><?php echo $d['description'] ?></p>
							<?php if (!empty($d['button_link'])) : ?>
								<a href="<?php echo $d['button_link'] ?>" class="btn btn-primary"><?php echo $d['button_text'] ?></a>
							<?php endif; ?>
							<?php if (!empty($d['sub_directory']) && $is_image) : ?>
								<ul class="directory-link <?php echo ($sidebar != 'none' && $is_image) ? '' : 'two-columns'?>">
									<?php foreach ($d['sub_directory'] as $s) : ?>
										<li><a href="<?php echo $s['hyperlink'] ?>"><?php echo $s['title'] ?></a></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					</div>
					<?php if (!empty($d['sub_directory']) && !$is_image) : ?>
						<ul class="directory-link two-columns">
							<?php foreach ($d['sub_directory'] as $s) : ?>
								<li><a href="<?php echo $s['hyperlink'] ?>"><?php echo $s['title'] ?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</section>
				<?php $dir++; ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
	<?php if ($sidebar != 'none') : ?>
		<div id="sidebar" class="col-md-3">
			<?php if ($sidebar == 'menu') : ?>
				<?php $dirMenu = 1; ?>
				<aside class="sidebar-menu">
					<ul id="sidebar-nav" class="list-menu">
						<?php foreach ($directory as $d) : ?>
							<li><a href="#directory-<?php echo $dirMenu; ?>"><?php echo $d['title'] ?></a></li>
							<?php $dirMenu++; ?>
						<?php endforeach; ?>
					</ul>
				</aside>
			<?php elseif ($sidebar == 'html') : ?>
				<aside class="sidebar-text">
					<?php the_field('ugm_directory_html_content'); ?>
				</aside>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>