<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @package ugm-faculty
 */

get_header();
?>
<main id="primary" class="site-main ugm-page">
	<div class="ugm-page__container">
		<header class="ugm-page__header">
			<h1 class="ugm-page__title"><?php esc_html_e( 'Halaman tidak ditemukan', 'ugm-faculty' ); ?></h1>
		</header>
		<div class="ugm-page__content">
			<p><?php esc_html_e( 'Maaf, halaman yang Anda cari tidak tersedia.', 'ugm-faculty' ); ?></p>
			<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Kembali ke beranda', 'ugm-faculty' ); ?></a></p>
		</div>
	</div>
</main>
<?php
get_footer();