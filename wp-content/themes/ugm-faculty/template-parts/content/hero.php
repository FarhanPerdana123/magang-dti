<?php
/**
 * Template part for displaying hero section
 *
 * @package ugm-faculty
 */

if ( ! is_page_template( array( 'page-templates/template-landing-page.php', 'landing-page' ) ) || '1' === get_query_var( 'ugm_latest_news' ) ) {
	return;
}

// Skip PHP hero if the page's post content already has a ugm/hero-section block.
// In that case, the block's own render_callback handles the output.
global $post;
if ( $post && has_block( 'ugm/hero-section', $post ) ) {
	return;
}

if ( ! (bool) get_theme_mod( 'ugm_show_hero', 1 ) ) {
	return;
}

$hero_bg_value = get_theme_mod( 'ugm_hero_background_image' );
$hero_bg_url   = '';
$hero_asset_bg_path = 'assets/images/landing page UGM.png';

if ( is_numeric( $hero_bg_value ) && (int) $hero_bg_value > 0 ) {
	$hero_bg_url = wp_get_attachment_image_url( (int) $hero_bg_value, 'full' );
} elseif ( is_string( $hero_bg_value ) ) {
	$hero_bg_url = esc_url_raw( $hero_bg_value );
}

// Force landing page hero image from local theme asset for preview/consistency.
if ( file_exists( get_theme_file_path( $hero_asset_bg_path ) ) ) {
	$hero_bg_url = str_replace( ' ', '%20', get_theme_file_uri( $hero_asset_bg_path ) );
}
$default_hero_title = "UNIVERSITAS\nGADJAH MADA";
$default_hero_desc  = 'Sebagai universitas nasional pertama di Indonesia, UGM telah menjadi pusat pendidikan, penelitian, dan pengabdian masyarakat sejak berdiri tahun 1949, melahirkan ribuan alumni yang berkiprah di berbagai bidang untuk bangsa dan dunia.';
$hero_title         = trim( (string) get_theme_mod( 'ugm_hero_headline', $default_hero_title ) );
$hero_desc          = trim( (string) get_theme_mod( 'ugm_hero_description', $default_hero_desc ) );

if ( '' === $hero_title ) {
	$hero_title = $default_hero_title;
}

if ( '' === $hero_desc ) {
	$hero_desc = $default_hero_desc;
}
?>

<section class="hero" <?php echo $hero_bg_url ? 'style="background-image: url(' . esc_url( $hero_bg_url ) . ');"' : ''; ?> aria-labelledby="hero-title">
	<div class="hero__overlay" aria-hidden="true"></div>
	<div class="hero__content">
		<?php if ( $hero_title ) : ?>
			<h1 id="hero-title" class="hero__title"><?php echo wp_kses_post( nl2br( esc_html( $hero_title ), false ) ); ?></h1>
		<?php endif; ?>
		<?php if ( $hero_desc ) : ?>
			<p class="hero__description"><?php echo esc_html( $hero_desc ); ?></p>
		<?php endif; ?>
	</div>
</section>
