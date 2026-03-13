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
		$build_fallback_url = static function ( $label ) {
			return home_url( '/' . sanitize_title( (string) $label ) . '/' );
		};
		$render_fallback_children = static function ( $children ) use ( &$render_fallback_children, $build_fallback_url ) {
			foreach ( $children as $child_item ) {
				if ( is_array( $child_item ) ) {
					$child_label = isset( $child_item['label'] ) ? (string) $child_item['label'] : '';
					$grandchildren = ( isset( $child_item['children'] ) && is_array( $child_item['children'] ) ) ? $child_item['children'] : array();
				} else {
					$child_label = (string) $child_item;
					$grandchildren = array();
				}

				if ( '' === trim( $child_label ) ) {
					continue;
				}

				$has_children = ! empty( $grandchildren );
				?>
				<li class="<?php echo $has_children ? 'menu-item-has-children' : ''; ?>">
					<a href="<?php echo esc_url( $build_fallback_url( $child_label ) ); ?>"><?php echo esc_html( $child_label ); ?></a>
					<?php if ( $has_children ) : ?>
						<button class="menu-dropdown-toggle" type="button" aria-expanded="false" aria-label="<?php echo esc_attr( sprintf( __( 'Tampilkan submenu %s', 'ugm-faculty' ), $child_label ) ); ?>">
							<span aria-hidden="true"></span>
						</button>
						<ul class="sub-menu" hidden>
							<?php $render_fallback_children( $grandchildren ); ?>
						</ul>
					<?php endif; ?>
				</li>
				<?php
			}
		};

		$fallback_items = array(
			array(
				'label' => __( 'Pendaftaran', 'ugm-faculty' ),
				'url'   => home_url( '/pendaftaran/' ),
				'children' => array(
					__( 'Sarjana dan Sarjana Terapan', 'ugm-faculty' ),
					__( 'International Undergraduate Program (IUP)', 'ugm-faculty' ),
					__( 'Pascasarjana', 'ugm-faculty' ),
					__( 'Bantuan Keuangan & Beasiswa', 'ugm-faculty' ),
					__( 'Tinggal di Kampus', 'ugm-faculty' ),
					__( 'Biaya Pendidikan', 'ugm-faculty' ),
					__( 'Pelajar Internasional', 'ugm-faculty' ),
				),
			),
			array(
				'label' => __( 'Pendidikan', 'ugm-faculty' ),
				'url'   => home_url( '/pendidikan/' ),
				'children' => array(
					__( 'Fakultas dan Sekolah', 'ugm-faculty' ),
					__( 'Kalender Akademik', 'ugm-faculty' ),
					__( 'Diktisaintek Berdampak', 'ugm-faculty' ),
				),
			),
			array(
				'label' => __( 'Penelitian', 'ugm-faculty' ),
				'url'   => home_url( '/penelitian/' ),
				'children' => array(
					__( 'Penelitian', 'ugm-faculty' ),
					__( 'Sorotan dan Dampak Tinggi', 'ugm-faculty' ),
					__( 'Publikasi', 'ugm-faculty' ),
					__( 'Buku', 'ugm-faculty' ),
					__( 'Produk', 'ugm-faculty' ),
					__( 'Pusat Penelitian', 'ugm-faculty' ),
					__( 'Keahlian', 'ugm-faculty' ),
				),
			),
			array(
				'label' => __( 'Pengabdian', 'ugm-faculty' ),
				'url'   => home_url( '/pengabdian/' ),
				'children' => array(
					__( 'Pengabdian', 'ugm-faculty' ),
					__( 'Highlight dan High Impact', 'ugm-faculty' ),
					__( 'KKN PPM', 'ugm-faculty' ),
					__( 'KKN ECL', 'ugm-faculty' ),
					__( 'Desa Binaan', 'ugm-faculty' ),
					__( 'Teknologi Tepat Guna', 'ugm-faculty' ),
					__( 'Pendidikan Untuk Pembangunan Berkelanjutan', 'ugm-faculty' ),
					__( 'Unit Tanggap Bencana', 'ugm-faculty' ),
					__( 'Pusat Regional Keahlian', 'ugm-faculty' ),
					__( 'UMKM', 'ugm-faculty' ),
				),
			),
			array(
				'label' => __( 'Layanan', 'ugm-faculty' ),
				'url'   => home_url( '/layanan/' ),
				'children' => array(
					__( 'Suara Kita', 'ugm-faculty' ),
					__( 'Aspirasi UGM', 'ugm-faculty' ),
					__( 'Whistleblowing System', 'ugm-faculty' ),
					__( 'lapor.go.id', 'ugm-faculty' ),
					array(
						'label'    => __( 'Layanan Darurat dan Pusat Krisis', 'ugm-faculty' ),
						'children' => array(
							__( 'Layanan Kesehatan Terpadu', 'ugm-faculty' ),
							__( 'Kontak Darurat', 'ugm-faculty' ),
							__( 'Pusat Krisis', 'ugm-faculty' ),
							__( 'Satgas PPKS', 'ugm-faculty' ),
						),
					),
					array(
						'label'    => __( 'Layanan Terpadu', 'ugm-faculty' ),
						'children' => array(
							__( 'University Services', 'ugm-faculty' ),
							__( 'Layanan Laboratorium Terpadu', 'ugm-faculty' ),
							__( 'Layanan Homestay UGM', 'ugm-faculty' ),
							__( 'Asrama Mahasiswa', 'ugm-faculty' ),
							__( 'Layanan Pengadaan', 'ugm-faculty' ),
							__( 'Layanan Terpadu', 'ugm-faculty' ),
							__( 'Layanan Alumni', 'ugm-faculty' ),
							__( 'Campus Visit', 'ugm-faculty' ),
						),
					),
					array(
						'label'    => __( 'Layanan Data dan Informasi', 'ugm-faculty' ),
						'children' => array(
							__( 'Search UGM', 'ugm-faculty' ),
							__( 'Peta Kampus', 'ugm-faculty' ),
							__( 'UGM dalam Angka', 'ugm-faculty' ),
							__( 'Layanan Informasi Publik', 'ugm-faculty' ),
							__( 'Laporan Keuangan', 'ugm-faculty' ),
							__( 'Agenda', 'ugm-faculty' ),
							__( 'Layanan Elektronik/E-Mail', 'ugm-faculty' ),
							__( 'UGM Online', 'ugm-faculty' ),
							__( 'SIMASTER VNext Parents - Android', 'ugm-faculty' ),
							__( 'SIMASTER Vnext Parents - IOS', 'ugm-faculty' ),
							__( 'Virtual Campus Tour', 'ugm-faculty' ),
						),
					),
				),
			),
			array(
				'label' => __( 'Tentang', 'ugm-faculty' ),
				'url'   => home_url( '/tentang/' ),
				'children' => array(
					__( 'Tentang UGM', 'ugm-faculty' ),
					__( 'Sambutan Rektor', 'ugm-faculty' ),
					__( 'Visi dan Misi', 'ugm-faculty' ),
					__( 'Tugas dan Fungsi', 'ugm-faculty' ),
					__( 'Organisasi', 'ugm-faculty' ),
					__( 'Direktorat dan Unit Kerja', 'ugm-faculty' ),
					__( 'Sejarah', 'ugm-faculty' ),
					__( 'Makna Lambang', 'ugm-faculty' ),
					__( 'Himne Gadjah Mada', 'ugm-faculty' ),
					__( 'Panduan Identitas', 'ugm-faculty' ),
				),
			),
			array(
				'label' => __( 'SDGs', 'ugm-faculty' ),
				'url'   => home_url( '/sdgs/' ),
				'children' => array(
					__( 'SDGs Portal', 'ugm-faculty' ),
					__( 'SDGs Dashboard', 'ugm-faculty' ),
					__( 'SDGs dengan AI', 'ugm-faculty' ),
				),
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
				<?php $has_children = ! empty( $fallback_item['children'] ) && is_array( $fallback_item['children'] ); ?>
				<li class="<?php echo $has_children ? 'menu-item-has-children' : ''; ?>">
					<a href="<?php echo esc_url( $fallback_item['url'] ); ?>"><?php echo esc_html( $fallback_item['label'] ); ?></a>
					<?php if ( $has_children ) : ?>
						<button class="menu-dropdown-toggle" type="button" aria-expanded="false" aria-label="<?php echo esc_attr( sprintf( __( 'Tampilkan submenu %s', 'ugm-faculty' ), $fallback_item['label'] ) ); ?>">
							<span aria-hidden="true"></span>
						</button>
						<ul class="sub-menu" hidden>
							<?php $render_fallback_children( $fallback_item['children'] ); ?>
						</ul>
					<?php endif; ?>
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
