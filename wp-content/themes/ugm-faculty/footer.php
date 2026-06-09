<?php
/**
 * The template for displaying the footer.
 *
 * Semua konten footer dikelola melalui Appearance → Widgets.
 * Footer hanya tampil jika widget sudah diisi.
 *
 * @package ugm-faculty
 */
?>
	</div><!-- #content -->

	<footer id="colophon" class="site-footer ugm-footer" aria-labelledby="footer-title">
		<h2 id="footer-title" class="screen-reader-text"><?php esc_html_e( 'Footer Information', 'ugm-faculty' ); ?></h2>

		<?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet ?>
		<style id="ugm-footer-centering">
		/* ── CONTAINER: tumpuk semua section secara vertikal ── */
		#colophon .ugm-footer__container {
			display: flex;
			flex-direction: column;
			align-items: center;
		}
		/* ── SETIAP WRAP: lebar penuh, konten di tengah ── */
		#colophon .ugm-footer__wrap--social,
		#colophon .ugm-footer__wrap--brand,
		#colophon .ugm-footer__wrap--contact,
		#colophon .ugm-footer__wrap--nav,
		#colophon .ugm-footer__wrap--accreditation,
		#colophon .ugm-footer__wrap--links,
		#colophon .ugm-footer__wrap--institutional {
			width: 100%;
			display: flex;
			flex-direction: column;
			align-items: center;
		}
		/* ── WIDGET element di dalam wrap: lebar penuh, centered ── */
		#colophon .ugm-footer__wrap--social .widget,
		#colophon .ugm-footer__wrap--brand .widget,
		#colophon .ugm-footer__wrap--contact .widget,
		#colophon .ugm-footer__wrap--brand section,
		#colophon .ugm-footer__wrap--contact section {
			display: flex !important;
			flex-direction: column;
			align-items: center;
			width: 100%;
			text-align: center;
		}
		/* ── SOCIAL: icons di tengah ── */
		#colophon .ugm-footer__social {
			display: flex;
			justify-content: center;
			gap: 10px;
		}
		/* ── BRAND: logo + teks di tengah (horizontal) ── */
		#colophon .ugm-footer__brand,
		#colophon .ugm-footer__brand-link {
			display: flex !important;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			gap: 8px;
		}
		#colophon .ugm-footer__brand-img {
			display: block;
			width: auto;
			height: 54px;
			flex-shrink: 0;
		}
		/* ── CONTACT: alamat dan info di tengah ── */
		#colophon .ugm-footer-contact__address {
			justify-content: center;
		}
		#colophon .ugm-footer-contact__info {
			text-align: center;
		}

		/* ── BANNER:  full-width, proporsional ── */
		#colophon .ugm-footer__wrap--banner {
			overflow: visible;
			padding: 0;
			margin: 0 auto;
			width: min(70%, 1400px);
			line-height: 0;
			border-top: 0;
		}
		#colophon .ugm-footer__wrap--banner .widget,
		#colophon .ugm-footer__wrap--banner section,
		#colophon .ugm-footer__wrap--banner figure,
		#colophon .ugm-footer__wrap--banner div {
			overflow: visible;
			padding: 0 !important;
			margin: 0 !important;
			line-height: 0;
			width: 100%;
		}
		#colophon .ugm-footer__wrap--banner img {
			width: 100% !important;
			height: auto !important;
			object-fit: contain !important;
			object-position: center center !important;
			display: block !important;
			max-width: 100% !important;
		}

		/* ── DESKTOP (768px+): centering melalui padding kiri-kanan ── */
		@media (min-width: 768px) {
			#colophon .ugm-footer__top {
				padding-left: max(32px, calc((100% - 900px) / 2));
				padding-right: max(32px, calc((100% - 900px) / 2));
			}
			#colophon .ugm-footer__container { max-width: 100%; }
			#colophon .ugm-footer__social { gap: 18px; }
			#colophon .ugm-footer__social-link { width: 28px; height: 28px; }
			#colophon .ugm-footer__social-icon { width: 24px; height: 24px; }
			#colophon .ugm-footer__brand-img { height: 64px; }
			#colophon .ugm-footer__wrap--banner { height: auto; width: min(70%, 1400px); max-width: 100%; margin: 0 auto; }
			#colophon .ugm-footer__wrap--banner .widget,
			#colophon .ugm-footer__wrap--banner section { height: auto; }
		}
		@media (min-width: 1280px) {
			#colophon .ugm-footer__top {
				padding-left: max(32px, calc((100% - 1040px) / 2));
				padding-right: max(32px, calc((100% - 1040px) / 2));
			}
			#colophon .ugm-footer__wrap--banner { width: min(70%, 1400px); max-width: 100%; margin: 0 auto; height: auto; }
		}
		@media (max-width: 767px) {
			#colophon .ugm-footer__wrap--banner { width: calc(100% - 24px); }
		}
		</style>


		<?php
		// Cek apakah ada minimal satu widget bagian atas yang aktif.
		$has_footer_social_fallback = function_exists( 'ugm_render_block_footer_social' );
		$has_footer_brand_fallback  = function_exists( 'ugm_render_block_footer_brand' );
		$has_top = $has_footer_social_fallback
			|| $has_footer_brand_fallback
			|| is_active_sidebar( 'footer-social-widget' )
			|| is_active_sidebar( 'footer-brand-widget' )
			|| is_active_sidebar( 'footer-contact-widget' )
			|| is_active_sidebar( 'footer-nav-widget' )
			|| is_active_sidebar( 'footer-accreditation-widget' )
			|| is_active_sidebar( 'footer-quick-links-widget' )
			|| is_active_sidebar( 'footer-institutional-widget' );
		?>

		<?php if ( $has_top ) : ?>
			<div class="ugm-footer__top" style="padding-top:20px;padding-bottom:8px;">
				<div class="ugm-footer__container" style="padding-left:14px;padding-right:14px;">

					<?php if ( is_active_sidebar( 'footer-social-widget' ) || $has_footer_social_fallback ) : ?>
						<div class="ugm-footer__wrap ugm-footer__wrap--social">
							<?php
							if ( is_active_sidebar( 'footer-social-widget' ) ) {
								dynamic_sidebar( 'footer-social-widget' );
							} elseif ( $has_footer_social_fallback ) {
								echo ugm_render_block_footer_social( array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							?>
						</div>
					<?php endif; ?>

					<?php if ( is_active_sidebar( 'footer-brand-widget' ) || $has_footer_brand_fallback ) : ?>
						<div class="ugm-footer__wrap ugm-footer__wrap--brand">
							<?php
							if ( is_active_sidebar( 'footer-brand-widget' ) ) {
								dynamic_sidebar( 'footer-brand-widget' );
							} elseif ( $has_footer_brand_fallback ) {
								echo ugm_render_block_footer_brand( array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							?>
						</div>
					<?php endif; ?>

					<?php if ( is_active_sidebar( 'footer-contact-widget' ) ) : ?>
						<div class="ugm-footer__wrap ugm-footer__wrap--contact">
							<?php dynamic_sidebar( 'footer-contact-widget' ); ?>
						</div>
					<?php endif; ?>

					<?php if ( is_active_sidebar( 'footer-nav-widget' ) ) : ?>
						<div class="ugm-footer__wrap ugm-footer__wrap--nav">
							<div class="ugm-footer__nav-bar">
								<?php dynamic_sidebar( 'footer-nav-widget' ); ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( is_active_sidebar( 'footer-accreditation-widget' ) ) : ?>
						<div class="ugm-footer__wrap ugm-footer__wrap--accreditation">
							<?php dynamic_sidebar( 'footer-accreditation-widget' ); ?>
						</div>
					<?php endif; ?>

					<?php if ( is_active_sidebar( 'footer-quick-links-widget' ) ) : ?>
						<div class="ugm-footer__wrap ugm-footer__wrap--links">
							<?php dynamic_sidebar( 'footer-quick-links-widget' ); ?>
						</div>
					<?php endif; ?>

					<?php if ( is_active_sidebar( 'footer-institutional-widget' ) ) : ?>
						<div class="ugm-footer__wrap ugm-footer__wrap--institutional">
							<?php dynamic_sidebar( 'footer-institutional-widget' ); ?>
						</div>
					<?php endif; ?>

				</div>
			</div>
		<?php endif; ?>

		<?php if ( is_active_sidebar( 'footer-banner-widget' ) ) : ?>
			<div class="ugm-footer__wrap ugm-footer__wrap--banner">
				<?php dynamic_sidebar( 'footer-banner-widget' ); ?>
			</div>
		<?php endif; ?>

		<?php if ( is_active_sidebar( 'footer-bottom-widget' ) ) : ?>
			<div class="ugm-footer__wrap ugm-footer__wrap--bottom">
				<div class="ugm-footer__container">
					<?php dynamic_sidebar( 'footer-bottom-widget' ); ?>
				</div>
			</div>
		<?php endif; ?>

	</footer>
</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
