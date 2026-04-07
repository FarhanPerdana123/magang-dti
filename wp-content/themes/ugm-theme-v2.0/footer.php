	<?php if ( get_field( 'help_center_show','option') ) : ?>
		<div class="helpCenter">
			<div class="helpCenter__content">
				<div class="helpCenter__content-title">
					<?php the_field( 'help_center_title','option'); ?>
				</div>
				<ul>
					<?php $list_url_bantuan = get_field('help_center_links','option'); ?>
					<?php if(is_array($list_url_bantuan)) : ?>
						<?php foreach($list_url_bantuan as $list) : ?>
							<li><a href="<?php echo $list['url']; ?>"><span><?php echo $list['title']; ?></span> <?php include UGM_THEME_DIR_ASSETS ."/svgs/arrow-right-active.svg"; ?></a> </li>
						<?php endforeach; ?>
					<?php endif; ?>
				</ul>
			</div>
			<div class="helpCenter__action">
				<div class="helpCenter__action-content bukaPanel">
					<?php include UGM_THEME_DIR_ASSETS ."/svgs/help-center-active.svg"; ?>
				</div>
				<div class="helpCenter__action-content tutupPanel">
					<?php include UGM_THEME_DIR_ASSETS ."/svgs/x-active.svg"; ?>
				</div>
			   
			</div>
		</div>
	<?php endif; ?>

	<?php if ( class_exists( 'Pojo_Accessibility' ) ) : ?>
		<div class="accessibility">
			<div class="accessibility__action">
				<div class="accessibility__action-content bukaPanel">
					<?php include UGM_THEME_DIR_ASSETS ."/svgs/wheelchair.svg"; ?>
				</div>
				<div class="accessibility__action-content tutupPanel">
					<?php include UGM_THEME_DIR_ASSETS ."/svgs/x-white.svg"; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<footer id="footer">
		<div class="footer-body">
			<div class="container">
				<div class="row">
					<div class="col-md-4 col-sm-5 footer-brand-wrapper">

						<?php
							$footer_logo = get_field('ugm_options_footer_logo','option');
							if (!empty($footer_logo)) :
								$footer_logo_img = $footer_logo;
							else:
								$footer_logo_img = UGM_THEME_URI_ASSETS . "/images/ugm_logo.png";
							endif;
						?>

						<a href="<?php echo home_url() ?>" class="footer-brand"><img src="<?php echo $footer_logo_img ?>" alt="Universitas Gadjah Mada"></a>

						<?php the_field_poly('ugm_options_footer_text','option') ?>
					</div>
					<div class="col-md-8 col-sm-7 footer-menu-wrapper">
						<div class="row">
							<?php
								if ( is_active_sidebar( 'footer-widget' ) ) {
									dynamic_sidebar( 'footer-widget' );
								}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="footer-copyright">
			<div class="container">
				<div class="row">
					<div class="col-md-6">
						<p class="copyright">&copy; <?php $copyright = get_field_poly('ugm_options_footer_copyright', 'option'); $replace = str_replace('[tahun]',date('Y'),$copyright); ?>
							<?php echo $replace; ?>						
						</p>
					</div>
					<div class="col-md-6">
						<p class="site-menu text-right">
							<?php
								$theme_locations = get_nav_menu_locations();
								if (!empty($theme_locations['footer'])) {
									$menu = get_term( $theme_locations['footer'], 'nav_menu' );
									$menu_items = wp_get_nav_menu_items($menu->term_id);
									if (!empty($menu_items)) {
										foreach ($menu_items as $m) {
											echo '<a href="'.$m->url.'">'.$m->title.'</a>';
										}
									}
								}
							?>
						</p>
					</div>
				</div>
			</div>
		</div>
	</footer>
	<?php wp_footer(); ?>
	<?php 
		$teks_cookies = is_english() ? get_field( 'accept_cookies_teks_en', 'option' ) : get_field( 'accept_cookies_teks_id', 'option' ) ;
		$custom_js = get_field('ugm_options_custom_js','option');
		if (!empty($custom_js)) :
			echo '<script type="text/javascript">'.$custom_js.'</script>';
		endif;
	?>
	<script type="text/javascript">
		WebFontConfig = {
			google: { families: [ "Lora:400,400i,700","Montserrat:400,700","Open+Sans:400,400i,700,700i" ] }
		};
		(function() {
			var wf = document.createElement('script');
			wf.src = 'https://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
			wf.type = 'text/javascript';
			wf.async = 'true';
			var s = document.getElementsByTagName('script')[0];
			s.parentNode.insertBefore(wf, s);
		})(); 

		cookiesBar({     
			mainText: "<?php echo $teks_cookies; ?>", 
			agreeLinkLabel: "<?php echo __('Saya Setuju','ugm_theme'); ?>",
			declineLinkLabel: "Decline",
			detailsLinkLabel: "Cookies Policy »", 
			declineLink: false, //If set to true, decline link will appear.
			detailsLink: false, //If set to true, link to yout private-policy page will appear.
			expireDays: 365,
			cookieEnabled: true, //Set it to true on production server.
			// Milliseconds between each frame. The lower value, the faster animation is.
			showSpeed: 25,
			hideSpeed: 5,
			position: "bottom", //Possible values - "top" or "bottom".,
			positionMode: "fixed", //Possible values - "absolute" or "fixed".
			delay: 0, //Delay before bar animation starts.
			zIndex: 10 //CSS z-index value for cookies-bar.
		});
	</script>
</body>
</html>