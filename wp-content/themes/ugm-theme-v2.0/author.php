<?php
get_header();
?>

<div id="body">
	<div class="container">

		<div class="row">

			<div id="content" class="<?php ugm_layout_class('content') ?>">

				<?php ugm_breadcrumbs(); ?>

				<div class="section-header author-header">
					<span class="section-subtitle"><?php esc_html_e( 'Post by :', 'ugm-theme' ) ?></span>
					<div class="row">
						<div class="col-md-3 col-sm-3">
							<?php $author_id = get_the_author_meta( 'ID' ); ?>
							<div class="author-img">
								<?php echo get_avatar( $author_id, 200 ); ?> 
							</div>
						</div>
						<div class="col-md-9 col-sm-9">
							<h2 class="author-title"><?php the_author(); ?></h2>
							<div class="author-social">
								<?php
									$profile_key = array(
										'facebook', 'twitter', 'instagram', 'email'
									);
									foreach ( $profile_key as $key => $value ) :
										$data = get_user_meta( $author_id, 'ugm_user_' . $value, true );
										if ( ! empty( $data ) ) :
											if ($value == 'email') :
									?>
											<a href="mailto:<?php echo $data; ?>">
												<i class="fa fa-envelope-o"></i>
											</a>
									<?php
											else:
									?>
											<a href="<?php echo $data; ?>" target="_blank">
												<i class="fa fa-<?php echo $value; ?>"></i>
											</a>
									<?php
											endif;
										endif; 
									endforeach; 
								?>
							</div>
							<p class="author-description"><?php the_author_meta( 'description' ) ?></p>
						</div>
					</div>
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