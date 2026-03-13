<?php
/**
 * The template for displaying the footer.
 *
 * @package ugm-faculty
 */

$faculty_name       = trim( (string) get_theme_mod( 'ugm_faculty_name', 'Universitas Gadjah Mada' ) );
$faculty_address    = trim( (string) get_theme_mod( 'ugm_faculty_address', "Bulaksumur, Caturtunggal, Kec. Depok,\nKabupaten Sleman, Daerah Istimewa\nYogyakarta 55281" ) );
$faculty_phone      = trim( (string) get_theme_mod( 'ugm_faculty_phone', '+62(274)588688' ) );
$faculty_email      = trim( (string) get_theme_mod( 'ugm_faculty_email', 'info@ugm.ac.id' ) );
$faculty_fax        = trim( (string) get_theme_mod( 'ugm_faculty_fax', '+62(274)565223' ) );
$faculty_whatsapp   = trim( (string) get_theme_mod( 'ugm_faculty_whatsapp', '+628112869988' ) );
$faculty_hours      = trim( (string) get_theme_mod( 'ugm_faculty_hours', '' ) );
$institutional_info = trim( (string) get_theme_mod( 'ugm_institutional_info', 'Universitas Gadjah Mada telah mendapatkan Akreditasi Institusi Unggul dari Badan Akreditasi Nasional Perguruan Tinggi (BAN-PT) untuk periode 2022-2027.' ) );
$accreditation_info = trim( (string) get_theme_mod( 'ugm_accreditation_info', '' ) );
$footer_banner_id   = absint( get_theme_mod( 'ugm_footer_banner_image', 0 ) );
$footer_banner_url  = $footer_banner_id ? wp_get_attachment_image_url( $footer_banner_id, 'full' ) : '';
$footer_brand_path  = get_theme_file_path( 'assets/images/Footer.png' );
$footer_brand_url   = file_exists( $footer_brand_path ) ? get_theme_file_uri( 'assets/images/Footer.png' ) : '';
$default_banner_path = get_theme_file_path( 'assets/images/Image Footer.png' );
$default_banner_url  = file_exists( $default_banner_path ) ? get_theme_file_uri( 'assets/images/Image Footer.png' ) : '';

if ( '' === $footer_banner_url && '' !== $default_banner_url ) {
	$footer_banner_url = $default_banner_url;
}

$footer_social_icons = array(
	array(
		'label' => __( 'Instagram', 'ugm-faculty' ),
		'file'  => 'Component Instagram.png',
		'url'   => 'https://www.instagram.com/',
	),
	array(
		'label' => __( 'YouTube', 'ugm-faculty' ),
		'file'  => 'Component YouTube.png',
		'url'   => 'https://www.youtube.com/',
	),
	array(
		'label' => __( 'Facebook', 'ugm-faculty' ),
		'file'  => 'Component Facebook.png',
		'url'   => 'https://www.facebook.com/',
	),
	array(
		'label' => __( 'X', 'ugm-faculty' ),
		'file'  => 'Component Twitter.png',
		'url'   => 'https://x.com/',
	),
	array(
		'label' => __( 'LinkedIn', 'ugm-faculty' ),
		'file'  => 'Component LinkedIn.png',
		'url'   => 'https://www.linkedin.com/',
	),
	array(
		'label' => __( 'TikTok', 'ugm-faculty' ),
		'file'  => 'Component TikTok.png',
		'url'   => 'https://www.tiktok.com/',
	),
);

if ( '' === $faculty_name ) {
	$faculty_name = get_bloginfo( 'name' );
}

$has_contact_info = ( '' !== $faculty_email || '' !== $faculty_phone || '' !== $faculty_fax || '' !== $faculty_whatsapp || '' !== $faculty_hours );
?>
	</div><!-- #content -->

	<footer id="colophon" class="site-footer ugm-footer" aria-labelledby="footer-title">
		<div class="ugm-footer__container">
			<h2 id="footer-title" class="screen-reader-text"><?php esc_html_e( 'Footer Information', 'ugm-faculty' ); ?></h2>
			<ul class="ugm-footer__social" aria-label="<?php esc_attr_e( 'Social media', 'ugm-faculty' ); ?>">
				<?php foreach ( $footer_social_icons as $social_icon ) : ?>
					<?php
					$icon_path = get_theme_file_path( 'assets/images/' . $social_icon['file'] );
					if ( ! file_exists( $icon_path ) ) {
						continue;
					}
					?>
					<li class="ugm-footer__social-item">
						<a
							class="ugm-footer__social-link"
							href="<?php echo esc_url( $social_icon['url'] ); ?>"
							target="_blank"
							rel="noopener noreferrer"
							aria-label="<?php echo esc_attr( $social_icon['label'] ); ?>"
						>
							<img
								class="ugm-footer__social-icon"
								src="<?php echo esc_url( get_theme_file_uri( 'assets/images/' . $social_icon['file'] ) ); ?>"
								alt=""
								loading="lazy"
								decoding="async"
							>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>

			<div class="ugm-footer__brand">
				<?php if ( '' !== $footer_brand_url ) : ?>
					<img class="ugm-footer__brand-image" src="<?php echo esc_url( $footer_brand_url ); ?>" alt="<?php echo esc_attr( $faculty_name ); ?>" loading="lazy" decoding="async">
				<?php else : ?>
					<div class="ugm-footer__brand-mark" aria-hidden="true">
						<?php if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) : ?>
							<?php echo wp_kses_post( get_custom_logo() ); ?>
						<?php else : ?>
							<span class="ugm-footer__mark-placeholder">UGM</span>
						<?php endif; ?>
					</div>
					<h3 class="ugm-footer__heading"><?php echo esc_html( $faculty_name ); ?></h3>
				<?php endif; ?>
			</div>

			<?php if ( $faculty_address ) : ?>
				<p class="ugm-footer__text ugm-footer__text--address"><?php echo nl2br( esc_html( $faculty_address ) ); ?></p>
			<?php endif; ?>

			<?php if ( $has_contact_info ) : ?>
				<ul class="ugm-footer__contact-list">
					<?php if ( $faculty_email ) : ?>
						<li><span class="ugm-footer__contact-key">E:</span> <a href="<?php echo esc_url( 'mailto:' . sanitize_email( $faculty_email ) ); ?>"><?php echo esc_html( $faculty_email ); ?></a></li>
					<?php endif; ?>
					<?php if ( $faculty_phone ) : ?>
						<li><span class="ugm-footer__contact-key">P:</span> <a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $faculty_phone ) ); ?>"><?php echo esc_html( $faculty_phone ); ?></a></li>
					<?php endif; ?>
					<?php if ( $faculty_fax ) : ?>
						<li><span class="ugm-footer__contact-key">F:</span> <?php echo esc_html( $faculty_fax ); ?></li>
					<?php endif; ?>
					<?php if ( $faculty_whatsapp ) : ?>
						<li><span class="ugm-footer__contact-key">WA:</span> <?php echo esc_html( $faculty_whatsapp ); ?></li>
					<?php endif; ?>
					<?php if ( $faculty_hours ) : ?>
						<li><span class="ugm-footer__contact-key">Info:</span> <?php echo esc_html( $faculty_hours ); ?></li>
					<?php endif; ?>
				</ul>
			<?php endif; ?>

			<?php if ( $has_contact_info || $institutional_info || $accreditation_info ) : ?>
				<div class="ugm-footer__highlight" aria-hidden="true"></div>
			<?php endif; ?>

			<?php if ( '' !== $institutional_info || '' !== $accreditation_info ) : ?>
				<p class="ugm-footer__text ugm-footer__text--accreditation">
					<?php
					echo nl2br(
						esc_html(
							trim(
								$institutional_info . "\n" . $accreditation_info
							)
						)
					);
					?>
				</p>
			<?php endif; ?>

			<?php if ( $footer_banner_url ) : ?>
				<div class="ugm-footer__banner">
					<img src="<?php echo esc_url( $footer_banner_url ); ?>" alt="<?php esc_attr_e( 'Footer banner', 'ugm-faculty' ); ?>">
				</div>
			<?php else : ?>
				<div class="ugm-footer__banner ugm-footer__banner--placeholder" aria-hidden="true"></div>
			<?php endif; ?>
		</div>
	</footer>
</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
