<?php
/**
 * Sambutan Rektor block module.
 *
 * Registers Rector Greeting blocks, template seeding, preview, and frontend routing.
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ==========================================================================
 * 15. Sambutan Rektor page blocks
 * ========================================================================== */

function ugm_get_default_rector_greeting_paragraphs() {
	return implode(
		"\n\n",
		array(
			__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer vitae lectus at massa dictum fermentum. Donec sed augue non erat porta tempor.', 'ugm-faculty' ),
			__( 'Praesent euismod, lorem at facilisis consequat, sem lorem tincidunt nibh, vitae luctus neque erat vitae urna. Sed non mauris vel nibh bibendum posuere.', 'ugm-faculty' ),
			__( 'Aliquam erat volutpat. Curabitur vitae libero in ipsum porta vulputate. Suspendisse potenti. Nam vitae risus eget augue feugiat faucibus.', 'ugm-faculty' ),
			__( 'Morbi consequat, sapien sed dignissim malesuada, justo arcu volutpat mi, sed finibus neque lorem vitae erat. Pellentesque habitant morbi tristique senectus et netus.', 'ugm-faculty' ),
		)
	);
}

function ugm_get_legacy_rector_greeting_paragraphs() {
	return implode(
		"\n\n",
		array(
			__( 'Selamat datang di Universitas Gadjah Mada (UGM), tempat Anda dapat mulai membuat perubahan nyata.', 'ugm-faculty' ),
			__( 'Sebagai salah satu universitas terkemuka di Indonesia, Universitas Gadjah Mada berupaya untuk memfasilitasi generasi muda dari seluruh penjuru negeri dan dunia untuk mengembangkan diri dan memaksimalkan potensi yang dimiliki. Kami bertekad membekali komunitas yang dinamis dan penuh semangat ini dengan pendidikan berkualitas demi hari esok yang lebih baik.', 'ugm-faculty' ),
			__( 'Keunggulan UGM mencakup spektrum bidang yang luas. Ada lebih dari 270 program studi dan 23 pusat penelitian yang akan membantu para mahasiswa memperluas wawasan dan memperkaya pengalaman dalam penelitian, kolaborasi interdisipliner, dan kehidupan secara umum.', 'ugm-faculty' ),
			__( 'UGM memiliki jaringan kemitraan yang luas dengan institusi pendidikan nasional dan global, lembaga penelitian, lembaga pemerintah, LSM, dan industri. Kami bersinergi dalam pendidikan, pertukaran pengetahuan, transfer teknologi, dan banyak lagi. Saat ini, UGM memiliki lebih dari 120 program dual-degree dengan berbagai universitas terkenal di dunia.', 'ugm-faculty' ),
			__( 'Kampus kami terletak di jantung kota Yogyakarta, sebuah kota yang terkenal akan sejarah dan warisan budayanya. Oleh karenanya, tak hanya pengalaman akademis, di sini, siapa pun Anda, dari mana pun Anda berasal, dapat merasakan secara langsung pengalaman antarbudaya yang kaya. Kami mengundang Anda belajar di kampus kami yang beragam dan inklusif, tempat kita dapat bahu-membahu menciptakan dampak nyata bagi bangsa dan dunia.', 'ugm-faculty' ),
			__( 'Terima kasih telah mengunjungi halaman kami. Semoga kampus UGM memberikan kesan yang manis bagi Anda.', 'ugm-faculty' ),
		)
	);
}

function ugm_get_default_rector_greeting_blocks() {
	$content_attrs = array(
		'breadcrumbHome'  => __( 'Lorem Ipsum', 'ugm-faculty' ),
		'breadcrumbParent' => __( 'Lorem Ipsum', 'ugm-faculty' ),
		'title'           => __( 'Lorem Ipsum', 'ugm-faculty' ),
		'body'            => ugm_get_default_rector_greeting_paragraphs(),
		'rectorName'      => __( 'Nama Rektor', 'ugm-faculty' ),
		'rectorRole'      => __( 'Jabatan Rektor', 'ugm-faculty' ),
		'photoPosition'   => 'right',
		'showPhotoFrame'  => true,
	);

	$sidebar_attrs = array(
		'title'                   => __( 'Tentang UGM', 'ugm-faculty' ),
		'selectedParentMenuTitle' => __( 'Tentang', 'ugm-faculty' ),
	);

	return '<!-- wp:ugm/rector-greeting-content ' . wp_json_encode( $content_attrs ) . ' /-->' . "\n" .
		'<!-- wp:ugm/about-ugm-sidebar ' . wp_json_encode( $sidebar_attrs ) . ' /-->';
}

function ugm_get_rector_greeting_block_template_blocks() {
	return '<!-- wp:ugm/rector-greeting-template-preview /-->' . "\n" .
		'<!-- wp:post-content /-->';
}

function ugm_has_rector_greeting_blocks( $content ) {
	$content = (string) $content;

	return false !== strpos( $content, '<!-- wp:ugm/rector-greeting-layout' ) ||
		false !== strpos( $content, '<!-- wp:ugm/rector-greeting-content' ) ||
		false !== strpos( $content, '<!-- wp:ugm/about-ugm-sidebar' );
}

function ugm_is_rector_greeting_template_slug( $template ) {
	return in_array( (string) $template, array( 'page-templates/template-rector-greeting.php', 'rector-greeting-page' ), true );
}

function ugm_rector_greeting_content_has_visible_content( $content ) {
	$content = (string) $content;

	if ( ugm_has_rector_greeting_blocks( $content ) ) {
		return true;
	}

	$content = preg_replace( '/<!--[\s\S]*?-->/', '', $content );

	return '' !== trim( wp_strip_all_tags( strip_shortcodes( $content ) ) );
}

function ugm_populate_empty_rector_greeting_page( $post_id ) {
	$post_id = absint( $post_id );
	if ( $post_id <= 0 || 'page' !== get_post_type( $post_id ) ) {
		return false;
	}

	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post || ugm_rector_greeting_content_has_visible_content( $post->post_content ) ) {
		return false;
	}

	if ( ! ugm_is_rector_greeting_template_slug( get_page_template_slug( $post_id ) ) ) {
		return false;
	}

	remove_action( 'save_post_page', 'ugm_seed_rector_greeting_page_on_save', 20 );
	wp_update_post(
		array(
			'ID'           => $post_id,
			'post_content' => ugm_get_default_rector_greeting_blocks(),
		)
	);
	add_action( 'save_post_page', 'ugm_seed_rector_greeting_page_on_save', 20, 3 );

	return true;
}

function ugm_seed_rector_greeting_page_on_save( $post_id, $post, $update ) {
	unset( $post, $update );

	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	ugm_populate_empty_rector_greeting_page( $post_id );
}
add_action( 'save_post_page', 'ugm_seed_rector_greeting_page_on_save', 20, 3 );

function ugm_seed_rector_greeting_page_after_rest_save( $post, $request, $creating ) {
	unset( $request, $creating );

	if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
		return;
	}

	ugm_populate_empty_rector_greeting_page( $post->ID );
}
add_action( 'rest_after_insert_page', 'ugm_seed_rector_greeting_page_after_rest_save', 20, 3 );

function ugm_repair_rector_greeting_block_template() {
	if ( ! is_admin() ) {
		return;
	}

	$template_posts = get_posts(
		array(
			'post_type'      => 'wp_template',
			'post_status'    => array( 'publish', 'draft' ),
			'name'           => 'rector-greeting-page',
			'posts_per_page' => 1,
		)
	);

	foreach ( $template_posts as $template_post ) {
		if ( ! $template_post instanceof WP_Post ) {
			continue;
		}

		if ( ugm_get_rector_greeting_block_template_blocks() === trim( (string) $template_post->post_content ) ) {
			continue;
		}

		wp_update_post(
			array(
				'ID'           => $template_post->ID,
				'post_content' => ugm_get_rector_greeting_block_template_blocks(),
			)
		);
	}
}
add_action( 'admin_init', 'ugm_repair_rector_greeting_block_template', 25 );

function ugm_use_php_rector_greeting_template_on_frontend( $template ) {
	if ( is_admin() || ! is_page() ) {
		return $template;
	}

	$page_id = (int) get_queried_object_id();
	if ( $page_id <= 0 || ! ugm_is_rector_greeting_template_slug( get_page_template_slug( $page_id ) ) ) {
		return $template;
	}

	$php_template = get_theme_file_path( 'page-templates/template-rector-greeting.php' );
	return file_exists( $php_template ) ? $php_template : $template;
}
add_filter( 'template_include', 'ugm_use_php_rector_greeting_template_on_frontend', 20 );

function ugm_render_block_rector_greeting_layout( $attrs, $content = '' ) {
	return '<div class="ugm-rector-template-layout ugm-rector-greeting-layout">' . $content . '</div>';
}

register_block_type( 'ugm/rector-greeting-layout', array(
	'title'           => __( 'Layout Sambutan Rektor', 'ugm-faculty' ),
	'description'     => __( 'Wrapper grid untuk konten Sambutan Rektor dan sidebar Tentang UGM.', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_rector_greeting_layout',
	'supports'        => array( 'html' => false ),
	'attributes'      => array(),
) );

function ugm_render_block_rector_greeting_content( $attrs ) {
	$title             = trim( (string) ( $attrs['title'] ?? __( 'Lorem Ipsum', 'ugm-faculty' ) ) );
	$breadcrumb_home   = trim( (string) ( $attrs['breadcrumbHome'] ?? __( 'Lorem Ipsum', 'ugm-faculty' ) ) );
	$breadcrumb_parent = trim( (string) ( $attrs['breadcrumbParent'] ?? __( 'Lorem Ipsum', 'ugm-faculty' ) ) );
	$body              = array_key_exists( 'body', $attrs ) ? trim( (string) $attrs['body'] ) : ugm_get_default_rector_greeting_paragraphs();
	$rector_name       = trim( (string) ( $attrs['rectorName'] ?? '' ) );
	$rector_role       = trim( (string) ( $attrs['rectorRole'] ?? '' ) );
	$photo_position    = 'left' === ( $attrs['photoPosition'] ?? 'right' ) ? 'left' : 'right';
	$photo_id          = absint( $attrs['photoId'] ?? 0 );
	$show_photo_frame  = array_key_exists( 'showPhotoFrame', $attrs ) ? (bool) $attrs['showPhotoFrame'] : true;
	$photo_url         = '';

	if ( $photo_id > 0 ) {
		$photo_url = (string) wp_get_attachment_image_url( $photo_id, 'large' );
	} elseif ( ! empty( $attrs['photoUrl'] ) ) {
		$photo_url = esc_url_raw( (string) $attrs['photoUrl'] );
	}

	if ( __( 'Sambutan Rektor', 'ugm-faculty' ) === $title ) {
		$title = __( 'Lorem Ipsum', 'ugm-faculty' );
	}
	if ( __( 'Beranda', 'ugm-faculty' ) === $breadcrumb_home ) {
		$breadcrumb_home = __( 'Lorem Ipsum', 'ugm-faculty' );
	}
	if ( __( 'Tentang UGM', 'ugm-faculty' ) === $breadcrumb_parent ) {
		$breadcrumb_parent = __( 'Lorem Ipsum', 'ugm-faculty' );
	}
	if ( trim( ugm_get_legacy_rector_greeting_paragraphs() ) === $body ) {
		$body = ugm_get_default_rector_greeting_paragraphs();
	}
	if ( __( 'Prof. dr. Ova Emilia, M.MedEd, SpOG (K), PhD', 'ugm-faculty' ) === $rector_name ) {
		$rector_name = __( 'Nama Rektor', 'ugm-faculty' );
	}
	if ( __( 'Rektor UGM', 'ugm-faculty' ) === $rector_role ) {
		$rector_role = __( 'Jabatan Rektor', 'ugm-faculty' );
	}

	$paragraphs = preg_split( "/\r\n\r\n|\n\n|\r\r/", $body );
	$paragraphs = array_values(
		array_filter(
			array_map( 'trim', is_array( $paragraphs ) ? $paragraphs : array() ),
			static function ( $paragraph ) {
				return '' !== $paragraph;
			}
		)
	);

	ob_start();
	?>
	<section class="ugm-rector-greeting ugm-rector-greeting--photo-<?php echo esc_attr( $photo_position ); ?> <?php echo $show_photo_frame ? '' : 'ugm-rector-greeting--no-photo'; ?>" aria-labelledby="ugm-rector-greeting-title">
		<nav class="ugm-rector-greeting__breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'ugm-faculty' ); ?>">
			<?php if ( '' !== $breadcrumb_home ) : ?>
				<span><?php echo esc_html( $breadcrumb_home ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $breadcrumb_parent ) : ?>
				<span aria-hidden="true">&#8250;</span>
				<span><?php echo esc_html( $breadcrumb_parent ); ?></span>
			<?php endif; ?>
		</nav>

		<div class="ugm-rector-greeting__grid">
			<div class="ugm-rector-greeting__content">
				<?php if ( '' !== $title ) : ?>
					<h1 id="ugm-rector-greeting-title" class="ugm-rector-greeting__title"><?php echo esc_html( $title ); ?></h1>
				<?php endif; ?>

				<div class="ugm-rector-greeting__body">
					<?php foreach ( $paragraphs as $paragraph ) : ?>
						<p><?php echo nl2br( esc_html( $paragraph ) ); ?></p>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if ( $show_photo_frame ) : ?>
				<figure class="ugm-rector-card">
					<div class="ugm-rector-card__photo">
						<?php if ( '' !== $photo_url ) : ?>
							<img src="<?php echo esc_url( $photo_url ); ?>" alt="<?php echo esc_attr( $rector_name ); ?>" loading="lazy">
						<?php else : ?>
							<span aria-hidden="true"></span>
						<?php endif; ?>
					</div>
					<?php if ( '' !== $rector_name || '' !== $rector_role ) : ?>
						<figcaption class="ugm-rector-card__caption">
							<?php if ( '' !== $rector_name ) : ?>
								<strong><?php echo esc_html( $rector_name ); ?></strong>
							<?php endif; ?>
							<?php if ( '' !== $rector_role ) : ?>
								<span><?php echo esc_html( $rector_role ); ?></span>
							<?php endif; ?>
						</figcaption>
					<?php endif; ?>
				</figure>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

register_block_type( 'ugm/rector-greeting-content', array(
	'title'           => __( 'Isi Sambutan Rektor', 'ugm-faculty' ),
	'description'     => __( 'Konten utama, foto, nama, dan jabatan rektor.', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_rector_greeting_content',
	'supports'        => array( 'html' => false ),
	'attributes'      => array(
		'breadcrumbHome'   => array( 'type' => 'string', 'default' => 'Lorem Ipsum' ),
		'breadcrumbParent' => array( 'type' => 'string', 'default' => 'Lorem Ipsum' ),
		'title'            => array( 'type' => 'string', 'default' => 'Lorem Ipsum' ),
		'body'             => array( 'type' => 'string', 'default' => ugm_get_default_rector_greeting_paragraphs() ),
		'rectorName'       => array( 'type' => 'string', 'default' => 'Nama Rektor' ),
		'rectorRole'       => array( 'type' => 'string', 'default' => 'Jabatan Rektor' ),
		'photoId'          => array( 'type' => 'integer', 'default' => 0 ),
		'photoUrl'         => array( 'type' => 'string', 'default' => '' ),
		'photoPosition'    => array( 'type' => 'string', 'default' => 'right' ),
		'showPhotoFrame'   => array( 'type' => 'boolean', 'default' => true ),
	),
) );

function ugm_get_primary_header_menu_object() {
	$locations = get_nav_menu_locations();

	if ( isset( $locations['menu-1'] ) ) {
		$menu = wp_get_nav_menu_object( $locations['menu-1'] );
		if ( $menu instanceof WP_Term ) {
			return $menu;
		}
	}

	return null;
}

function ugm_get_primary_header_menu_items() {
	$menu = ugm_get_primary_header_menu_object();
	if ( ! $menu instanceof WP_Term ) {
		return array();
	}

	$items = wp_get_nav_menu_items(
		$menu->term_id,
		array(
			'update_post_term_cache' => false,
		)
	);

	if ( empty( $items ) || ! is_array( $items ) ) {
		return array();
	}

	usort(
		$items,
		static function ( $left, $right ) {
			return (int) $left->menu_order <=> (int) $right->menu_order;
		}
	);

	return $items;
}

function ugm_get_primary_header_parent_menu_options() {
	$items = ugm_get_primary_header_menu_items();
	if ( empty( $items ) ) {
		return array();
	}

	$child_counts = array();
	foreach ( $items as $item ) {
		$parent_id = (int) $item->menu_item_parent;
		if ( $parent_id > 0 ) {
			if ( ! isset( $child_counts[ $parent_id ] ) ) {
				$child_counts[ $parent_id ] = 0;
			}
			++$child_counts[ $parent_id ];
		}
	}

	$options = array();
	foreach ( $items as $item ) {
		if ( 0 !== (int) $item->menu_item_parent ) {
			continue;
		}

		$title = trim( (string) $item->title );
		if ( '' === $title ) {
			continue;
		}

		$options[] = array(
			'id'         => (int) $item->ID,
			'title'      => $title,
			'childCount' => isset( $child_counts[ (int) $item->ID ] ) ? (int) $child_counts[ (int) $item->ID ] : 0,
		);
	}

	return $options;
}

function ugm_rector_normalize_url_for_compare( $url ) {
	$url = trim( (string) $url );
	if ( '' === $url ) {
		return '';
	}

	return untrailingslashit( strtok( $url, '#' ) );
}

function ugm_about_sidebar_menu_item_is_current( WP_Post $item ) {
	$queried_id  = (int) get_queried_object_id();
	$current_url = ugm_rector_normalize_url_for_compare( get_permalink( $queried_id ) );
	$item_url    = ugm_rector_normalize_url_for_compare( (string) $item->url );

	if ( $queried_id > 0 && (int) $item->object_id === $queried_id ) {
		return true;
	}

	return '' !== $current_url && '' !== $item_url && $current_url === $item_url;
}

function ugm_build_about_sidebar_menu_tree( $items, $parent_id = 0, $level = 0, &$active_ids = array() ) {
	$tree = array();

	foreach ( $items as $item ) {
		if ( (int) $item->menu_item_parent !== (int) $parent_id ) {
			continue;
		}

		$children = ugm_build_about_sidebar_menu_tree( $items, (int) $item->ID, $level + 1, $active_ids );
		$current  = ugm_about_sidebar_menu_item_is_current( $item );
		$active   = $current;

		foreach ( $children as $child ) {
			if ( ! empty( $child['active'] ) ) {
				$active = true;
				break;
			}
		}

		if ( $active ) {
			$active_ids[] = (int) $item->ID;
		}

		$tree[] = array(
			'id'       => (int) $item->ID,
			'label'    => (string) $item->title,
			'url'      => (string) $item->url,
			'active'   => $active,
			'current'  => $current,
			'level'    => max( 0, (int) $level ),
			'children' => $children,
		);
	}

	return $tree;
}

function ugm_find_about_sidebar_parent_menu_item( $items, $selected_parent_menu_id = 0, $selected_parent_menu_title = '' ) {
	$selected_parent_menu_id    = absint( $selected_parent_menu_id );
	$selected_parent_menu_title = trim( (string) $selected_parent_menu_title );

	foreach ( $items as $item ) {
		if ( 0 !== (int) $item->menu_item_parent ) {
			continue;
		}

		if ( $selected_parent_menu_id > 0 && (int) $item->ID === $selected_parent_menu_id ) {
			return $item;
		}
	}

	if ( '' === $selected_parent_menu_title ) {
		return null;
	}

	$selected_slug = sanitize_title( $selected_parent_menu_title );
	foreach ( $items as $item ) {
		if ( 0 !== (int) $item->menu_item_parent ) {
			continue;
		}

		if ( sanitize_title( (string) $item->title ) === $selected_slug ) {
			return $item;
		}
	}

	return null;
}

function ugm_get_about_sidebar_menu_state( $selected_parent_menu_id = 0, $selected_parent_menu_title = '' ) {
	$items = ugm_get_primary_header_menu_items();
	if ( empty( $items ) ) {
		return array(
			'status' => 'missing_menu',
			'items'  => array(),
		);
	}

	$parent = ugm_find_about_sidebar_parent_menu_item( $items, $selected_parent_menu_id, $selected_parent_menu_title );
	if ( ! $parent instanceof WP_Post ) {
		return array(
			'status' => 'missing_parent',
			'items'  => array(),
		);
	}

	$active_ids = array();
	$sidebar_items = ugm_build_about_sidebar_menu_tree( $items, (int) $parent->ID, 0, $active_ids );

	return array(
		'status' => empty( $sidebar_items ) ? 'missing_children' : 'ready',
		'items'  => $sidebar_items,
	);
}

function ugm_render_about_sidebar_menu_items( $items, $level = 0 ) {
	if ( empty( $items ) || ! is_array( $items ) ) {
		return;
	}

	$list_class = 0 === (int) $level ? 'ugm-about-sidebar__list' : 'ugm-about-sidebar__submenu';
	?>
	<ul class="<?php echo esc_attr( $list_class ); ?>">
		<?php foreach ( $items as $item ) : ?>
			<?php
			$label       = trim( (string) ( $item['label'] ?? '' ) );
			$url         = trim( (string) ( $item['url'] ?? '' ) );
			$children    = isset( $item['children'] ) && is_array( $item['children'] ) ? $item['children'] : array();
			$has_children = ! empty( $children );
			$active      = ! empty( $item['active'] );
			$current     = ! empty( $item['current'] );
			$item_level  = min( 3, max( 0, absint( $item['level'] ?? $level ) ) );

			if ( '' === $label ) {
				continue;
			}

			$item_classes = array(
				'ugm-about-sidebar__item',
				'ugm-about-sidebar__item--level-' . $item_level,
			);

			if ( $active ) {
				$item_classes[] = 'is-active';
			}

			if ( $has_children ) {
				$item_classes[] = 'has-children';
			}
			?>
			<li class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>">
				<?php if ( $has_children ) : ?>
					<details class="ugm-about-sidebar__details">
						<summary class="ugm-about-sidebar__link ugm-about-sidebar__summary">
							<span><?php echo esc_html( $label ); ?></span>
						</summary>
						<?php ugm_render_about_sidebar_menu_items( $children, $level + 1 ); ?>
					</details>
				<?php else : ?>
					<?php $tag = '' !== $url ? 'a' : 'span'; ?>
					<<?php echo tag_escape( $tag ); ?> class="ugm-about-sidebar__link"<?php echo '' !== $url ? ' href="' . esc_url( $url ) . '"' : ''; ?><?php echo $current ? ' aria-current="page"' : ''; ?>>
						<span><?php echo esc_html( $label ); ?></span>
					</<?php echo tag_escape( $tag ); ?>>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

function ugm_render_block_about_ugm_sidebar( $attrs ) {
	$title                      = trim( (string) ( $attrs['title'] ?? __( 'Tentang UGM', 'ugm-faculty' ) ) );
	$selected_parent_menu_id    = absint( $attrs['selectedParentMenuId'] ?? 0 );
	$selected_parent_menu_title = trim( (string) ( $attrs['selectedParentMenuTitle'] ?? __( 'Tentang', 'ugm-faculty' ) ) );
	$menu_state                 = ugm_get_about_sidebar_menu_state( $selected_parent_menu_id, $selected_parent_menu_title );
	$items                      = $menu_state['items'];

	ob_start();
	?>
	<aside class="ugm-about-sidebar" aria-labelledby="ugm-about-sidebar-title">
		<?php if ( '' !== $title ) : ?>
			<h2 id="ugm-about-sidebar-title" class="ugm-about-sidebar__title"><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>
		<?php if ( ! empty( $items ) ) : ?>
			<nav class="ugm-about-sidebar__nav" aria-label="<?php echo esc_attr( $title ); ?>">
				<?php ugm_render_about_sidebar_menu_items( $items ); ?>
			</nav>
		<?php else : ?>
			<?php
			$empty_message = __( 'Pilih parent menu dari menu utama/header.', 'ugm-faculty' );
			if ( 'missing_menu' === $menu_state['status'] ) {
				$empty_message = __( 'Menu utama/header belum tersedia.', 'ugm-faculty' );
			} elseif ( 'missing_children' === $menu_state['status'] ) {
				$empty_message = __( 'Menu ini belum memiliki submenu.', 'ugm-faculty' );
			}
			?>
			<p class="ugm-about-sidebar__empty"><?php echo esc_html( $empty_message ); ?></p>
		<?php endif; ?>
	</aside>
	<?php
	return ob_get_clean();
}

register_block_type( 'ugm/about-ugm-sidebar', array(
	'title'           => __( 'Sidebar Tentang UGM', 'ugm-faculty' ),
	'description'     => __( 'Menu samping untuk halaman Tentang UGM.', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_about_ugm_sidebar',
	'supports'        => array( 'html' => false ),
	'attributes'      => array(
		'title'                   => array( 'type' => 'string', 'default' => 'Tentang UGM' ),
		'selectedParentMenuId'    => array( 'type' => 'integer', 'default' => 0 ),
		'selectedParentMenuTitle' => array( 'type' => 'string', 'default' => 'Tentang' ),
	),
) );

function ugm_render_block_rector_greeting_template_preview() {
	return '<div class="ugm-rector-template-layout ugm-rector-greeting-layout">' .
		do_blocks( ugm_get_default_rector_greeting_blocks() ) .
		'</div>';
}

register_block_type( 'ugm/rector-greeting-template-preview', array(
	'title'           => __( 'Sambutan Rektor Page Template Preview', 'ugm-faculty' ),
	'category'        => 'ugm-sections',
	'render_callback' => 'ugm_render_block_rector_greeting_template_preview',
	'supports'        => array(
		'html'     => false,
		'inserter' => false,
	),
) );

/**
 * Enqueue Rector Greeting editor assets.
 *
 * The script is separated from the shared landing blocks entry so this template's
 * seeding and preview visibility logic stays isolated from other templates.
 */
function ugm_enqueue_rector_greeting_editor_assets() {
	wp_enqueue_script(
		'ugm-rector-greeting-blocks',
		get_template_directory_uri() . '/assets/js/blocks/rector-greeting-blocks.js',
		array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-block-editor', 'wp-components', 'wp-compose', 'wp-data', 'wp-hooks', 'wp-plugins', 'wp-server-side-render' ),
		ugm_get_asset_version( '/assets/js/blocks/rector-greeting-blocks.js' ),
		true
	);

	wp_localize_script(
		'ugm-rector-greeting-blocks',
		'ugmRectorGreetingEditor',
		array(
			'defaultBlocks'               => ugm_get_default_rector_greeting_blocks(),
			'primaryHeaderMenuAvailable'  => ugm_get_primary_header_menu_object() instanceof WP_Term,
			'primaryHeaderParentMenus'    => ugm_get_primary_header_parent_menu_options(),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'ugm_enqueue_rector_greeting_editor_assets' );

/**
 * Enqueue Rector Greeting frontend styles.
 */
function ugm_enqueue_rector_greeting_frontend_styles() {
	wp_enqueue_style(
		'ugm-style-rector-greeting',
		get_template_directory_uri() . '/assets/css/blocks/rector-greeting.css',
		array( 'ugm-style-content' ),
		ugm_get_asset_version( '/assets/css/blocks/rector-greeting.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'ugm_enqueue_rector_greeting_frontend_styles', 20 );

/**
 * Enqueue Rector Greeting editor styles after the shared editor preview CSS.
 */
function ugm_enqueue_rector_greeting_editor_styles() {
	wp_enqueue_style(
		'ugm-editor-style-rector-greeting',
		get_template_directory_uri() . '/assets/css/blocks/rector-greeting.css',
		array( 'ugm-editor-landing-preview' ),
		ugm_get_asset_version( '/assets/css/blocks/rector-greeting.css' )
	);
}
add_action( 'enqueue_block_editor_assets', 'ugm_enqueue_rector_greeting_editor_styles', 20 );

/**
 * Register Rector Greeting insertion pattern.
 */
function ugm_register_rector_greeting_patterns() {
	register_block_pattern( 'ugm/rector-greeting-page', array(
		'title'       => __( 'Template Sambutan Rektor', 'ugm-faculty' ),
		'description' => __( 'Layout konten Sambutan Rektor dengan sidebar Tentang UGM. Block tetap bisa dipindahkan, dihapus, atau diduplikasi.', 'ugm-faculty' ),
		'categories'  => array( 'ugm-landing' ),
		'content'     => ugm_get_default_rector_greeting_blocks(),
	) );
}
add_action( 'init', 'ugm_register_rector_greeting_patterns', 20 );

