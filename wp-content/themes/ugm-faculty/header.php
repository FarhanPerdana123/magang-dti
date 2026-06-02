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
$header_email     = sanitize_email( (string) get_theme_mod( 'ugm_header_contact_email', 'info@ugm.ac.id' ) );
$header_phone     = trim( (string) get_theme_mod( 'ugm_header_contact_phone', $faculty_phone ) );
$header_whatsapp  = trim( (string) get_theme_mod( 'ugm_header_contact_whatsapp', $faculty_whatsapp ) );

$cs_number_raw = '' !== $header_whatsapp ? $header_whatsapp : $header_phone;
$cs_digits     = preg_replace( '/[^0-9]/', '', $cs_number_raw );

if ( '' !== $cs_digits && '0' === substr( $cs_digits, 0, 1 ) ) {
	$cs_digits = '62' . substr( $cs_digits, 1 );
}

$cs_link   = '' !== $cs_digits ? 'https://wa.me/' . $cs_digits : '';
$cs_target = '_blank';
$cs_rel    = 'noopener noreferrer';
$is_latest_news_route = '1' === get_query_var( 'ugm_latest_news' );
$is_magazine_news_route = '1' === get_query_var( 'ugm_magazine_news' );
$is_landing_template    = is_page_template( array( 'page-templates/template-landing-page.php', 'landing-page' ) );
$is_front_landing       = $is_landing_template && ! $is_latest_news_route && ! $is_magazine_news_route;
$front_route_no_hero    = is_front_page() && ! $is_front_landing;
$header_social_icons    = array(
	array(
		'label' => __( 'Instagram', 'ugm-faculty' ),
		'file'  => 'Component Instagram.png',
		'url'   => get_theme_mod( 'ugm_social_instagram_url', 'https://www.instagram.com/' ),
	),
	array(
		'label' => __( 'YouTube', 'ugm-faculty' ),
		'file'  => 'Component YouTube.png',
		'url'   => get_theme_mod( 'ugm_social_youtube_url', 'https://www.youtube.com/' ),
	),
	array(
		'label' => __( 'Facebook', 'ugm-faculty' ),
		'file'  => 'Component Facebook.png',
		'url'   => get_theme_mod( 'ugm_social_facebook_url', 'https://www.facebook.com/' ),
	),
	array(
		'label' => __( 'X', 'ugm-faculty' ),
		'file'  => 'Component Twitter.png',
		'url'   => get_theme_mod( 'ugm_social_x_url', 'https://x.com/' ),
	),
	array(
		'label' => __( 'LinkedIn', 'ugm-faculty' ),
		'file'  => 'Component LinkedIn.png',
		'url'   => get_theme_mod( 'ugm_social_linkedin_url', 'https://www.linkedin.com/' ),
	),
	array(
		'label' => __( 'TikTok', 'ugm-faculty' ),
		'file'  => 'Component TikTok.png',
		'url'   => get_theme_mod( 'ugm_social_tiktok_url', 'https://www.tiktok.com/' ),
	),
);
$header_quick_links     = array(
	array(
		'label' => __( 'Email', 'ugm-faculty' ),
		'url'   => '' !== $header_email ? 'mailto:' . $header_email : 'mailto:info@ugm.ac.id',
	),
	array(
		'label' => __( 'Perpustakaan', 'ugm-faculty' ),
		'url'   => home_url( '/perpustakaan/' ),
	),
	array(
		'label' => __( 'Mahasiswa', 'ugm-faculty' ),
		'url'   => home_url( '/mahasiswa/' ),
	),
	array(
		'label' => __( 'Staff', 'ugm-faculty' ),
		'url'   => home_url( '/staff/' ),
	),
	array(
		'label' => __( 'Alumni', 'ugm-faculty' ),
		'url'   => home_url( '/alumni/' ),
	),
);

if ( '' === $cs_link && '' !== $header_phone ) {
	$cs_link   = 'tel:' . preg_replace( '/[^0-9+]/', '', $header_phone );
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

<body <?php body_class( $front_route_no_hero ? 'ugm-front-route-no-hero' : '' ); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
	<header id="masthead" class="site-header <?php echo $is_front_landing ? 'is-front-page' : 'is-solid'; ?>" data-front-page="<?php echo $is_front_landing ? '1' : '0'; ?>">
		<div class="menu-backdrop" aria-hidden="true"></div>
		<div class="site-header__inner">
			<button class="menu-toggle" type="button" aria-controls="primary-menu" aria-expanded="false">
				<span class="menu-toggle__icon" aria-hidden="true"></span>
				<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'ugm-faculty' ); ?></span>
			</button>

			<?php get_template_part( 'template-parts/header/site-branding' ); ?>

			<div class="site-header__meta">
				<div class="site-header__meta-top">
					<ul class="site-header__social" aria-label="<?php esc_attr_e( 'Social media', 'ugm-faculty' ); ?>">
						<?php foreach ( $header_social_icons as $social_icon ) : ?>
							<?php
							$icon_path = get_theme_file_path( 'assets/images/' . $social_icon['file'] );
							$social_url = esc_url( (string) $social_icon['url'] );

							if ( ! file_exists( $icon_path ) || '' === $social_url ) {
								continue;
							}
							?>
							<li class="site-header__social-item">
								<a
									class="site-header__social-link"
									href="<?php echo esc_url( $social_url ); ?>"
									target="_blank"
									rel="noopener noreferrer"
									aria-label="<?php echo esc_attr( $social_icon['label'] ); ?>"
								>
									<img
										class="site-header__social-icon"
										src="<?php echo esc_url( get_theme_file_uri( 'assets/images/' . $social_icon['file'] ) ); ?>"
										alt=""
										loading="lazy"
										decoding="async"
									>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>

					<div class="site-header__actions">
						<?php get_template_part( 'template-parts/header/language-switcher' ); ?>
						<?php get_template_part( 'template-parts/header/search' ); ?>
					</div>
				</div>

				<nav class="site-header__quick-links" aria-label="<?php esc_attr_e( 'Quick links', 'ugm-faculty' ); ?>">
					<?php
					$desktop_quick_links_location = '';

					// Desktop quick-links follow "Menu Atas" (Mobile Quick Links) by default.
					if ( has_nav_menu( 'mobile-quick-links' ) ) {
						$desktop_quick_links_location = 'mobile-quick-links';
					} elseif ( has_nav_menu( 'header-quick-links' ) ) {
						$desktop_quick_links_location = 'header-quick-links';
					}

					if ( '' !== $desktop_quick_links_location ) {
						wp_nav_menu(
							array(
								'theme_location' => $desktop_quick_links_location,
								'menu_id'        => 'header-quick-links',
								'menu_class'     => 'site-header__quick-links-list',
								'container'      => false,
								'fallback_cb'    => false,
								'depth'          => 1,
							)
						);
					} else {
						?>
						<ul class="site-header__quick-links-list">
							<?php foreach ( $header_quick_links as $quick_link ) : ?>
								<li><a href="<?php echo esc_url( $quick_link['url'] ); ?>"><?php echo esc_html( $quick_link['label'] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
						<?php
					}
					?>
				</nav>
			</div>

			<?php get_template_part( 'template-parts/header/navigation' ); ?>

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

	<?php if ( $is_front_landing ) : ?>
		<?php get_template_part( 'template-parts/content/hero' ); ?>
	<?php endif; ?>

	<div id="content" class="site-content">
