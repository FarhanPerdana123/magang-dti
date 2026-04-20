<?php
/**
 * Widget areas registration
 *
 * Semua widget area di footer dikelola di sini.
 * Masuk ke: Appearance → Widgets untuk mengisi setiap area.
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register widget areas.
 */
function ugm_footer_widgets_init() {

	$shared_args = array(
		'before_widget' => '<section class="widget %2$s" id="%1$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="ugm-footer-widget__title">',
		'after_title'   => '</h4>',
	);

	// ── 1. Footer — Social Media Icons ────────────────────────────────────
	// Baris ikon sosial di paling atas footer.
	// Gunakan widget "Custom HTML" dengan markup <ul class="ugm-footer__social">...
	register_sidebar(
		array_merge(
			$shared_args,
			array(
				'name'        => esc_html__( 'Footer — Social Media', 'ugm-faculty' ),
				'id'          => 'footer-social-widget',
				'description' => esc_html__( 'Baris ikon sosial di atas footer. Gunakan widget Custom HTML dengan class ugm-footer__social.', 'ugm-faculty' ),
			)
		)
	);

	// ── 2. Footer — Brand / Logo ──────────────────────────────────────────
	// Logo dan nama institusi di bawah ikon sosial.
	// Gunakan widget "Custom HTML" dengan markup <div class="ugm-footer__brand">...
	register_sidebar(
		array_merge(
			$shared_args,
			array(
				'name'        => esc_html__( 'Footer — Brand / Logo', 'ugm-faculty' ),
				'id'          => 'footer-brand-widget',
				'description' => esc_html__( 'Logo dan nama institusi. Gunakan widget Image atau Custom HTML dengan class ugm-footer__brand.', 'ugm-faculty' ),
			)
		)
	);

	// ── 3. Footer — Kontak & Alamat ───────────────────────────────────────
	// Alamat dan daftar info kontak (E, P, F, WA).
	// Gunakan widget "Custom HTML" dengan markup <p class="ugm-footer__text--address"> dan <ul class="ugm-footer__contact-list">...
	register_sidebar(
		array_merge(
			$shared_args,
			array(
				'name'        => esc_html__( 'Footer — Kontak & Alamat', 'ugm-faculty' ),
				'id'          => 'footer-contact-widget',
				'description' => esc_html__( 'Alamat dan info kontak institusi. Gunakan widget Custom HTML dengan class ugm-footer__text--address dan ugm-footer__contact-list.', 'ugm-faculty' ),
			)
		)
	);

	// ── 4. Footer — Navigation Bar ────────────────────────────────────────
	// Kotak highlight biru di tengah footer (selalu tampil sebagai elemen visual).
	// Gunakan widget "Navigation Menu" atau "Custom HTML" untuk isi link navigasi.
	register_sidebar(
		array_merge(
			$shared_args,
			array(
				'name'        => esc_html__( 'Footer — Navigation Bar', 'ugm-faculty' ),
				'id'          => 'footer-nav-widget',
				'description' => esc_html__( 'Isi kotak highlight biru di tengah footer. Gunakan widget Navigation Menu atau Custom HTML.', 'ugm-faculty' ),
			)
		)
	);

	// ── 5. Footer — Akreditasi / Info Kelembagaan ─────────────────────────
	// Teks akreditasi dan informasi kelembagaan, di bawah nav bar.
	// Gunakan widget "Text" atau "Custom HTML".
	register_sidebar(
		array_merge(
			$shared_args,
			array(
				'name'        => esc_html__( 'Footer — Akreditasi', 'ugm-faculty' ),
				'id'          => 'footer-accreditation-widget',
				'description' => esc_html__( 'Teks akreditasi atau info kelembagaan. Gunakan widget Text atau Custom HTML dengan class ugm-footer__text--accreditation.', 'ugm-faculty' ),
			)
		)
	);

	// ── 6. Footer — Quick Links ───────────────────────────────────────────
	// Daftar tautan cepat opsional di bawah akreditasi.
	register_sidebar(
		array_merge(
			$shared_args,
			array(
				'name'        => esc_html__( 'Footer — Quick Links', 'ugm-faculty' ),
				'id'          => 'footer-quick-links-widget',
				'description' => esc_html__( 'Tautan cepat opsional di bawah teks akreditasi.', 'ugm-faculty' ),
			)
		)
	);

	// ── 7. Footer — Institutional ─────────────────────────────────────────
	// Widget opsional untuk badge akreditasi atau catatan tambahan.
	register_sidebar(
		array_merge(
			$shared_args,
			array(
				'name'        => esc_html__( 'Footer — Institutional', 'ugm-faculty' ),
				'id'          => 'footer-institutional-widget',
				'description' => esc_html__( 'Badge akreditasi atau catatan kelembagaan tambahan.', 'ugm-faculty' ),
			)
		)
	);

	// ── 8. Footer — Banner Bawah ──────────────────────────────────────────
	// Banner gambar di bagian paling bawah footer (sebelum copyright).
	// Gunakan widget "Image" atau "Custom HTML" dengan markup <img>.
	register_sidebar(
		array_merge(
			$shared_args,
			array(
				'name'        => esc_html__( 'Footer — Banner Bawah', 'ugm-faculty' ),
				'id'          => 'footer-banner-widget',
				'description' => esc_html__( 'Gambar panorama/banner di bawah footer. Gunakan widget Image atau Custom HTML dengan tag <img>.', 'ugm-faculty' ),
			)
		)
	);

	// ── 9. Footer — Bottom Bar (Copyright) ───────────────────────────────
	// Baris terbawah footer, cocok untuk teks copyright.
	// Gunakan widget "Text" atau "Custom HTML".
	register_sidebar(
		array_merge(
			$shared_args,
			array(
				'name'        => esc_html__( 'Footer — Bottom Bar', 'ugm-faculty' ),
				'id'          => 'footer-bottom-widget',
				'description' => esc_html__( 'Baris terbawah footer. Gunakan widget Text untuk teks copyright.', 'ugm-faculty' ),
			)
		)
	);
}
add_action( 'widgets_init', 'ugm_footer_widgets_init' );
