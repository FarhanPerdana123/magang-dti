<?php
/**
 * Template part for displaying primary navigation
 *
 * @package ugm-faculty
 */
?>

<nav id="site-navigation" class="primary-navigation" aria-label="<?php esc_attr_e( 'Primary menu', 'ugm-faculty' ); ?>">
	<?php
	if ( has_nav_menu( 'menu-1' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'menu-1',
				'menu_id'        => 'primary-menu',
				'menu_class'     => 'primary-menu__list',
				'container'      => false,
				'fallback_cb'    => false,
			)
		);
	} else {
		$fallback_items = array(
			array(
				'label' => __( 'Pendaftaran', 'ugm-faculty' ),
				'url'   => home_url( '/pendaftaran/' ),
			),
			array(
				'label' => __( 'Pendidikan', 'ugm-faculty' ),
				'url'   => home_url( '/pendidikan/' ),
			),
			array(
				'label' => __( 'Penelitian', 'ugm-faculty' ),
				'url'   => home_url( '/penelitian/' ),
			),
			array(
				'label' => __( 'Pengabdian', 'ugm-faculty' ),
				'url'   => home_url( '/pengabdian/' ),
			),
			array(
				'label' => __( 'Layanan', 'ugm-faculty' ),
				'url'   => home_url( '/layanan/' ),
			),
			array(
				'label' => __( 'Tentang', 'ugm-faculty' ),
				'url'   => home_url( '/tentang/' ),
			),
			array(
				'label' => __( 'SDGs', 'ugm-faculty' ),
				'url'   => home_url( '/sdgs/' ),
			),
			array(
				'label' => __( 'Berita', 'ugm-faculty' ),
				'url'   => home_url( '/berita/' ),
			),
			array(
				'label' => __( 'Peduli Bencana', 'ugm-faculty' ),
				'url'   => home_url( '/peduli-bencana/' ),
			),
		);
		?>
		<ul id="primary-menu" class="primary-menu__list">
			<?php foreach ( $fallback_items as $fallback_item ) : ?>
				<li>
					<a href="<?php echo esc_url( $fallback_item['url'] ); ?>"><?php echo esc_html( $fallback_item['label'] ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}
	?>
	<?php if ( has_nav_menu( 'mobile-quick-links' ) ) : ?>
		<div class="mobile-drawer__footer">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'mobile-quick-links',
					'menu_id'        => 'mobile-quick-links',
					'menu_class'     => 'mobile-drawer__links',
					'container'      => false,
					'fallback_cb'    => false,
					'depth'          => 1,
				)
			);
			?>
		</div>
	<?php endif; ?>
</nav>
