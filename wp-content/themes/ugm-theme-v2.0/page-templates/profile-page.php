<?php
/**
 * Template Name: Halaman Profil
 */

get_header();
?>

<div id="body">
	<div class="container">

		<div class="row">

			<div id="content" class="col-md-12">

				<?php ugm_breadcrumbs(); ?>
				
				<div class="section-header">
					<h2 class="section-title"><?php the_title() ?></h2>
				</div>
				
				<div class="single-page">
					<div class="row">
						<?php
						$peoples = get_field('ugm_profile_list', get_the_ID());
						if (!empty($peoples)) : foreach ($peoples as $p) :
						?>
						<div class="col-md-3 col-sm-6 profile-item">
							<?php
							$img = wp_get_attachment_image_src($p['image'],'medium');
							if (!empty($img)) :
								echo '<img src="'.$img[0].'" class="img-responsive" alt="'.$p['name'].'">';
							endif;
							?>
							<div class="profile-content">
								<h3 class="name-title"><?php echo $p['name'] ?></h3>
								<span class="name-label"><?php echo $p['position'] ?></span>
							</div>
						</div>
						<?php endforeach; endif; ?>
					</div>
				</div>

			</div>

		</div>

	</div>
</div>

<?php
get_footer();
?>