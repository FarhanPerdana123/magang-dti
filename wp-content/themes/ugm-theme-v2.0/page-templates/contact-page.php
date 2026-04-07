<?php
/**
 * Template Name: Halaman Kontak
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
						<div class="col-md-6">
							<?php 
							$location = get_field('ugm_contact_coordinates', get_the_ID());

							if( !empty($location) ):
							?>
							<div class="panel-map">
								<div class="acf-map" style="text-decoration:none; overflow:hidden; height:300px; width:555px; max-width:100%;">
									<div class="marker" data-lat="<?php echo $location['lat']; ?>" data-lng="<?php echo $location['lng']; ?>"></div>
								</div>
							</div>
							<?php endif; ?>
							<address class="address-box">
								<h3 class="address-title"><?php bloginfo('name') ?></h3>
								<?php $address = get_field('ugm_contact_address', get_the_ID()); ?>
								<?php if (!empty($address)) : ?>
									<p><?php the_field('ugm_contact_address', get_the_ID()) ?></p>
								<?php endif; ?>
								<?php
								$contact = get_field('ugm_contact_contact', get_the_ID());
								foreach ($contact as $c) {
									if ($c['type'] == 'email') {
										echo '<p><i class="ion ion-email"></i><a href="mailto:'.$c['number'].'" class="email">'.$c['number'].'</a></p>';
									} else if ($c['type'] == 'phone') {
										echo '<p><i class="ion ion-ios-telephone"></i>'.$c['number'].'</p>';			
									} else if ($c['type'] == 'fax') {
										echo '<p><i class="ion ion-ios-printer"></i>'.$c['number'].'</p>';			
									}
								}
								?>
							</address>
						</div>
						<div class="col-md-6">
							<div class="widget contact-form">
								<div class="widget-header">
									<h2 class="widget-title"><?php esc_html_e( 'Leave Us a Message', 'ugm-theme' ); ?></h2>
								</div>
								<?php the_field('ugm_contact_form', get_the_ID()); ?>
							</div>
						</div>
					</div>
				</div>

			</div>

		</div>

	</div>
</div>

<?php
get_footer();
?>