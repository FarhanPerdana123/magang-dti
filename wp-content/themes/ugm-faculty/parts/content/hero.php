<?php
/**
 * Template part for displaying hero section
 *
 * @package ugm-faculty
 */

if ( ! is_front_page() || '1' === get_query_var( 'ugm_latest_news' ) ) {
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
$hero_title  = get_theme_mod( 'ugm_hero_headline', get_bloginfo( 'name' ) );
$hero_desc   = get_theme_mod( 'ugm_hero_description', get_bloginfo( 'description' ) );
?>

<section class="hero" <?php echo $hero_bg_url ? 'style="background-image: url(' . esc_url( $hero_bg_url ) . ');"' : ''; ?> aria-labelledby="hero-title">
	<div class="hero__overlay" aria-hidden="true"></div>
	<div class="hero__content">
		<?php if ( $hero_title ) : ?>
			<h1 id="hero-title" class="hero__title"><?php echo esc_html( $hero_title ); ?></h1>
		<?php endif; ?>
		<?php if ( $hero_desc ) : ?>
			<p class="hero__description"><?php echo esc_html( $hero_desc ); ?></p>
		<?php endif; ?>
	</div>
</section>
