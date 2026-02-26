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

if ( '' === $faculty_name ) {
	$faculty_name = get_bloginfo( 'name' );
}

$has_contact_info = ( '' !== $faculty_email || '' !== $faculty_phone || '' !== $faculty_fax || '' !== $faculty_whatsapp || '' !== $faculty_hours );
?>
	</div><!-- #content -->

	<footer id="colophon" class="site-footer ugm-footer" aria-labelledby="footer-title">
		<div class="ugm-footer__container">
			<h2 id="footer-title" class="screen-reader-text"><?php esc_html_e( 'Footer Information', 'ugm-faculty' ); ?></h2>
			<div class="ugm-footer__brand">
				<div class="ugm-footer__brand-mark" aria-hidden="true">
					<?php if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) : ?>
						<?php echo wp_kses_post( get_custom_logo() ); ?>
					<?php else : ?>
						<span class="ugm-footer__mark-placeholder">UGM</span>
					<?php endif; ?>
				</div>
				<h3 class="ugm-footer__heading"><?php echo esc_html( $faculty_name ); ?></h3>
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
