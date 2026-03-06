<?php
/**
 * Template part for displaying site branding
 *
 * @package ugm-faculty
 */

$dark_logo_id   = (int) get_theme_mod( 'ugm_logo_dark' );
$light_logo_id  = (int) get_theme_mod( 'ugm_logo_light' );
$custom_logo_id = (int) get_theme_mod( 'custom_logo' );

$dark_logo_url  = $dark_logo_id ? wp_get_attachment_image_url( $dark_logo_id, 'full' ) : '';
$light_logo_url = $light_logo_id ? wp_get_attachment_image_url( $light_logo_id, 'full' ) : '';
$header_asset_logo_path = 'assets/images/header UGM.png';

if ( ! $dark_logo_url && $custom_logo_id ) {
	$dark_logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
}
if ( ! $light_logo_url && $dark_logo_url ) {
	$light_logo_url = $dark_logo_url;
}

// Temporary preview: force header logo from local theme asset.
if ( file_exists( get_theme_file_path( $header_asset_logo_path ) ) ) {
	$header_asset_logo_url = str_replace( ' ', '%20', get_theme_file_uri( $header_asset_logo_path ) );
	$dark_logo_url         = $header_asset_logo_url;
	$light_logo_url        = $header_asset_logo_url;
}
?>

<div class="site-branding">
	<a class="brand-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php esc_attr_e( 'Home', 'ugm-faculty' ); ?>">
		<?php if ( $light_logo_url || $dark_logo_url ) : ?>
			<img class="brand-logo brand-logo--light" src="<?php echo esc_url( $light_logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			<img class="brand-logo brand-logo--dark" src="<?php echo esc_url( $dark_logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
		<?php else : ?>
			<span class="brand-text"><?php bloginfo( 'name' ); ?></span>
		<?php endif; ?>
	</a>
</div>
