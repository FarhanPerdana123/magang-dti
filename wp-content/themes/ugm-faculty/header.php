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
	<header id="masthead" class="site-header <?php echo is_front_page() ? 'is-front-page' : 'is-solid'; ?>" data-front-page="<?php echo is_front_page() ? '1' : '0'; ?>">
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

	<?php get_template_part( 'parts/content/hero' ); ?>

	<div id="content" class="site-content">
