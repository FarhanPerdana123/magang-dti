<article class="single-post <?php echo post_password_required() ? "protected" : "" ?>">
	<div class="post-heading">
		<?php
			the_title( '<h1 class="post-title">', '</h1>' );
		?>
		<ul class="entry-meta">
			<?php $cats = get_the_category(); ?>
			<?php if (!empty($cats)) : ?>
				<li class="post-category">
					<?php
					$x = 1;
					foreach ($cats as $c) :
					?>
						<a href="<?php echo home_url() ?>/category/<?php echo $c->slug ?>"><?php echo $c->name ?></a><?php echo $x < sizeof($cats) ? ',' : '' ?>
					<?php 
					$x++; 
					endforeach; 
					?>
				</li>
			<?php endif; ?>
			<li class="post-date"><?php echo the_time('j F Y, H.i') ?></li>
			<li class="post-author"><?php esc_html_e( 'By', 'ugm-theme' ); ?>: <?php the_author_posts_link(); ?></li>
			<!-- <li class="post-reads">245</li> -->
			<?php if(get_comments_number()>0) : ?>
			<li class="post-comments"><?php echo get_comments_number() ?></li>
			<?php endif; ?>
		</ul>
	</div>
	<div class="post-content">
		<?php 
			if(post_password_required()){
				the_content();
			} else {
				$files = get_field('ugm_post_file');
				$preview = get_field('ugm_post_file_preview');
				if (!empty($files) && is_array($files)) :
					if (empty($files[1]) || empty($files[1]['file'])) :
						if ($files[0]['file']['mime_type'] == 'application/pdf') :
							if ($preview) :
								?>
								<!-- <div class="row">
									<div class="col-md-12 text-center">
										<a download href="<?php echo $files[0]['file']['url'] ?>" class="btn btn-outline">
											<?php esc_html_e( 'Download', 'ugm-theme' ); ?>
											<i class="fa fa-download"></i> 
										</a>
									</div>
								</div> -->

								<div class="data">
									<h5><?php esc_html_e( 'Click to download :', 'ugm-theme' ); ?></h5>
									<ul class="data-list">
										<?php foreach ($files as $f) : ?>
											<li>
												<a class="data-file" download href="<?php echo $f['file']['url'] ?>"><?php echo $f['file']['filename'] ?></a>
												<a class="btn btn-outline" download href="<?php echo $f['file']['url'] ?>">
													Download
													<i class="fa fa-download"></i>
												</a>
											</li>
										<?php endforeach ?>
									</ul>
								</div>
								<br>
								<iframe style="width: 100%; height: 700px;" src="<?php echo $files[0]['file']['url'] ?>" frameborder="0"></iframe>
								<?php 
							elseif ($files[0]['file']['type'] == 'image') :
								?>
								<div class="text-center">
									<?php echo wp_get_attachment_image($files[0]['file']['id'], 'ugm-post-content') ?>
								</div>
								<br>
								<div class="row">
									<div class="col-md-12 text-center">
										<a download href="<?php echo $files[0]['file']['url'] ?>" class="btn btn-outline"><i class="fa fa-download"></i> <?php esc_html_e( 'Download', 'ugm-theme' ); ?></a>
									</div>
								</div>
								<?php
							else:
								?>
								<div class="data">
									<h5><?php esc_html_e( 'Click to download :', 'ugm-theme' ); ?></h5>
									<ul class="data-list">
										<?php foreach ($files as $f) : ?>
											<li>
												<a class="data-file" download href="<?php echo $f['file']['url'] ?>"><?php echo $f['file']['filename'] ?></a>
												<a class="btn btn-outline" download href="<?php echo $f['file']['url'] ?>">
													Download
													<i class="fa fa-download"></i>
												</a>
											</li>
										<?php endforeach ?>
									</ul>
								</div>
								<?php
							endif;
						endif;
					else:
						if ($preview) :
							?>
							<div class="osc-res-tab tabbable osc-tabs-left">
								<div style="clear:both;width: 100%;">
									<ul class="nav osc-res-nav nav-tabs osc-tabs-left-ul">
										<li class="dropdown pull-right tabdrop hide"><a class="dropdown-toggle" data-toggle="dropdown" href="#">More <b class="caret"></b></a>
											<ul class="dropdown-menu"></ul>
										</li>
										<?php foreach ($files as $i => $f) : ?>
											<li <?php echo $i == 0 ? 'class="active"' : '' ?>><a href="#ert_pane1-<?php echo $i ?>" data-toggle="tab"><?php echo $f['file']['title'] ?></a></li>
										<?php endforeach ?>
									</ul>
								</div>
								<div style="clear:both;width: 100%;">
									<ul class="tab-content">
										<?php foreach ($files as $i => $f) : ?>
											<li class="tab-pane <?php echo $i == 0 ? 'active' : '' ?>" id="ert_pane1-<?php echo $i ?>">
												<?php if ($f['file']['mime_type'] == 'application/pdf') : ?>
													<!-- <div class="row">
														<div class="col-md-12 text-center">
															<a download href="<?php echo $f['file']['url'] ?>" class="btn btn-outline"><?php esc_html_e( 'Download', 'ugm-theme' ); ?> <i class="fa fa-download"></i></a>
														</div>
													</div> -->

													<div class="data">
														<h5><?php esc_html_e( 'Click to download :', 'ugm-theme' ); ?></h5>
														<ul class="data-list">
															<?php //foreach ($files as $f) : ?>
																<li>
																	<a class="data-file" download href="<?php echo $f[$i]['file']['url'] ?>"><?php echo $f['file']['filename'] ?></a>
																	<a class="btn btn-outline" download href="<?php echo $f[$i]['file']['url'] ?>">
																		Download
																		<i class="fa fa-download"></i>
																	</a>
																</li>
															<?php //endforeach ?>
														</ul>
													</div>
													<br>
													<iframe style="width: 100%; height: 700px;" src="<?php echo $f['file']['url'] ?>" frameborder="0"></iframe>
												<?php elseif ($f['file']['type'] == 'image') : ?>
													<!-- <div class="row">
														<div class="col-md-12 text-center">
															<a download href="<?php echo $f['file']['url'] ?>" class="btn btn-outline"><?php esc_html_e( 'Download', 'ugm-theme' ); ?> <i class="fa fa-download"></i></a>
														</div>
													</div> -->

													<div class="data">
														<h5><?php esc_html_e( 'Click to download :', 'ugm-theme' ); ?></h5>
														<ul class="data-list">
															<?php //foreach ($files as $f) : ?>
																<li>
																	<a class="data-file" download href="<?php echo $f[$i]['file']['url'] ?>"><?php echo $f['file']['filename'] ?></a>
																	<a class="btn btn-outline" download href="<?php echo $f[$i]['file']['url'] ?>">
																		Download
																		<i class="fa fa-download"></i>
																	</a>
																</li>
															<?php //endforeach ?>
														</ul>
													</div>
													<br>
													<div class="text-center">
														<?php echo wp_get_attachment_image($f['file']['id'], 'ugm-post-content') ?>
													</div>
													
												<?php else: ?>
													<div class="row">
														<div class="col-md-12 text-center">
															<h5><?php echo $f['file']['filename'] ?></h5>
															<a download href="<?php echo $f['file']['url'] ?>" class="btn btn-outline btn-sm"><i class="fa fa-download"></i> <?php esc_html_e( 'Download', 'ugm-theme' ); ?></a>
														</div>
													</div>
												<?php endif; ?>
											</li>	
										<?php endforeach; ?>
									</ul>
								</div>
							</div>
							<?php
						else:
							?>
							<div class="data">
								<h5><?php esc_html_e( 'Click to download :', 'ugm-theme' ); ?></h5>
								<ul class="data-list">
									<?php foreach ($files as $f) : ?>
										<li>
											<a class="data-file" download href="<?php echo $f['file']['url'] ?>"><?php echo $f['file']['filename'] ?></a>
											<a class="btn btn-outline" download href="<?php echo $f['file']['url'] ?>">
												Download
												<i class="fa fa-download"></i>
											</a>
										</li>
									<?php endforeach ?>
								</ul>
							</div>
							<?php
						endif;
					endif;
				endif;
			}
		?>
	</div>
	<?php $tags = get_the_tags(); ?>
	<?php if (!empty($tags)) : ?>
		<div class="tagcloud">
			<span class="tag-title">Tags:</span>
			<?php foreach ($tags as $t) : ?>
				<a href="<?php echo home_url(); ?>/tag/<?php echo $t->slug ?>/"><?php echo $t->name ?></a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</article>