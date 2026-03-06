<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package ugm-faculty
 */

$faculty_whatsapp = trim( (string) get_theme_mod( 'ugm_faculty_whatsapp', '+628112869988' ) );
$faculty_phone    = trim( (string) get_theme_mod( 'ugm_faculty_phone', '+62(274)588688' ) );

$cs_number_raw = '' !== $faculty_whatsapp ? $faculty_whatsapp : $faculty_phone;
$cs_digits     = preg_replace( '/[^0-9]/', '', $cs_number_raw );

if ( '' !== $cs_digits && '0' === substr( $cs_digits, 0, 1 ) ) {
	$cs_digits = '62' . substr( $cs_digits, 1 );
}

$cs_link   = '' !== $cs_digits ? 'https://wa.me/' . $cs_digits : '';
$cs_target = '_blank';
$cs_rel    = 'noopener noreferrer';
$is_latest_news_route = '1' === get_query_var( 'ugm_latest_news' );
$is_front_landing     = is_front_page() && ! $is_latest_news_route;

if ( '' === $cs_link && '' !== $faculty_phone ) {
	$cs_link   = 'tel:' . preg_replace( '/[^0-9+]/', '', $faculty_phone );
	$cs_target = '';
	$cs_rel    = '';
}

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
	<header id="masthead" class="site-header <?php echo $is_front_landing ? 'is-front-page' : 'is-solid'; ?>" data-front-page="<?php echo $is_front_landing ? '1' : '0'; ?>">
		<div class="menu-backdrop" aria-hidden="true"></div>
		<div class="site-header__inner">
			<button class="menu-toggle" type="button" aria-controls="primary-menu" aria-expanded="false">
				<span class="menu-toggle__icon" aria-hidden="true"></span>
				<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'ugm-faculty' ); ?></span>
			</button>

			<?php get_template_part( 'parts/header/site-branding' ); ?>

			<?php get_template_part( 'parts/header/navigation' ); ?>

			<div class="site-header__actions">
				<?php get_template_part( 'parts/header/language-switcher' ); ?>
				<?php get_template_part( 'parts/header/search' ); ?>
			</div>

			<div id="header-search-form" class="header-search__panel" hidden>
				<button class="header-search__close" type="button" aria-label="<?php esc_attr_e( 'Close search', 'ugm-faculty' ); ?>">&times;</button>
				<?php get_search_form(); ?>
			</div>
		</div>
	</header>

	<?php if ( '' !== $cs_link ) : ?>
		<a
			class="floating-cs-button"
			href="<?php echo esc_url( $cs_link ); ?>"
			aria-label="<?php esc_attr_e( 'Hubungi customer service', 'ugm-faculty' ); ?>"
			<?php echo $cs_target ? 'target="' . esc_attr( $cs_target ) . '"' : ''; ?>
			<?php echo $cs_rel ? 'rel="' . esc_attr( $cs_rel ) . '"' : ''; ?>
		>
			<span class="floating-cs-button__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" role="presentation" focusable="false">
					<path d="M6.6 10.8a15.7 15.7 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.2c1.1.4 2.2.6 3.4.6a1 1 0 0 1 1 1V21a1 1 0 0 1-1 1C10.3 22 2 13.7 2 3.5a1 1 0 0 1 1-1H7a1 1 0 0 1 1 1c0 1.2.2 2.3.6 3.4a1 1 0 0 1-.2 1L6.6 10.8z"/>
				</svg>
			</span>
		</a>
	<?php endif; ?>

	<?php get_template_part( 'parts/content/hero' ); ?>

	<div id="content" class="site-content">
