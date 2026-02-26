<?php
/**
 * Template part for displaying hero section
 *
 * @package ugm-faculty
 */

if ( ! is_front_page() ) {
	return;
}

$hero_bg_id  = (int) get_theme_mod( 'ugm_hero_background_image' );
$hero_bg_url = $hero_bg_id ? wp_get_attachment_image_url( $hero_bg_id, 'full' ) : '';
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
