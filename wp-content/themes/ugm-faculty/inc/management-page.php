<?php
/**
 * Management page template helpers and block registration.
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ugm_get_default_management_page_blocks() {
	return '<!-- wp:ugm/management-hero {"title":"Manajemen Organisasi","background":"#dceef6"} /-->' . "\n" .
		'<!-- wp:ugm/management-section {"title":"Manajemen Fakultas"} /-->' . "\n" .
		'<!-- wp:ugm/study-program-section {"title":"Program Studi"} /-->' . "\n" .
		'<!-- wp:ugm/management-share-section {"title":"Share This Page"} /-->';
}

function ugm_get_management_block_template_blocks() {
	return '<!-- wp:ugm/management-template-preview /-->' . "\n" .
		'<!-- wp:post-content /-->';
}

function ugm_has_management_page_blocks( $page_content ) {
	$page_content = (string) $page_content;

	return false !== strpos( $page_content, '<!-- wp:ugm/management-hero' ) ||
		false !== strpos( $page_content, '<!-- wp:ugm/management-section' ) ||
		false !== strpos( $page_content, '<!-- wp:ugm/study-program-section' ) ||
		false !== strpos( $page_content, '<!-- wp:ugm/management-share-section' );
}

function ugm_management_content_has_visible_content( $page_content ) {
	$page_content = (string) $page_content;

	if ( ugm_has_management_page_blocks( $page_content ) ) {
		return true;
	}

	$page_content = preg_replace( '/<!--[\s\S]*?-->/', '', $page_content );

	return '' !== trim( wp_strip_all_tags( strip_shortcodes( $page_content ) ) );
}

function ugm_upgrade_management_page_blocks( $page_content ) {
	$page_content = (string) $page_content;

	if (
		false === strpos( $page_content, '<!-- wp:ugm/management-section' ) ||
		false !== strpos( $page_content, '<!-- wp:ugm/management-hero' )
	) {
		return $page_content;
	}

	$blocks = parse_blocks( $page_content );
	foreach ( $blocks as $index => &$block ) {
		if ( 'ugm/management-section' !== ( $block['blockName'] ?? '' ) ) {
			continue;
		}

		$legacy_attrs = is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array();
		$hero_attrs   = array(
			'title'      => trim( wp_strip_all_tags( (string) ( $legacy_attrs['heroTitle'] ?? 'Manajemen Organisasi' ) ) ),
			'background' => sanitize_hex_color( (string) ( $legacy_attrs['heroBackground'] ?? '#dceef6' ) ),
		);

		if ( '' === $hero_attrs['title'] ) {
			$hero_attrs['title'] = 'Manajemen Organisasi';
		}

		if ( ! $hero_attrs['background'] ) {
			$hero_attrs['background'] = '#dceef6';
		}

		unset( $block['attrs']['heroTitle'], $block['attrs']['heroBackground'] );
		array_splice(
			$blocks,
			$index,
			0,
			array(
				array(
					'blockName'    => 'ugm/management-hero',
					'attrs'        => $hero_attrs,
					'innerBlocks'  => array(),
					'innerHTML'    => '',
					'innerContent' => array(),
				),
				array(
					'blockName'    => null,
					'attrs'        => array(),
					'innerBlocks'  => array(),
					'innerHTML'    => "\n",
					'innerContent' => array( "\n" ),
				),
			)
		);
		break;
	}
	unset( $block );

	return serialize_blocks( $blocks );
}

function ugm_filter_management_page_blocks( $blocks ) {
	$filtered = array();

	foreach ( is_array( $blocks ) ? $blocks : array() as $block ) {
		$block_name = $block['blockName'] ?? null;

		if ( in_array( $block_name, array( 'ugm/management-hero', 'ugm/management-section', 'ugm/study-program-section', 'ugm/management-share-section' ), true ) ) {
			continue;
		}

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			$block['innerBlocks'] = ugm_filter_management_page_blocks( $block['innerBlocks'] );
		}

		$filtered[] = $block;
	}

	return $filtered;
}

function ugm_remove_management_page_blocks_from_content( $content ) {
	$content = (string) $content;

	if (
		false === strpos( $content, '<!-- wp:ugm/management-hero' ) &&
		false === strpos( $content, '<!-- wp:ugm/management-section' ) &&
		false === strpos( $content, '<!-- wp:ugm/study-program-section' ) &&
		false === strpos( $content, '<!-- wp:ugm/management-share-section' )
	) {
		return $content;
	}

	return trim( serialize_blocks( ugm_filter_management_page_blocks( parse_blocks( $content ) ) ) );
}

function ugm_is_management_page_template_slug( $template ) {
	return in_array( (string) $template, array( 'page-templates/template-management.php', 'management-page' ), true );
}

function ugm_get_management_block_template_content() {
	if ( ! function_exists( 'get_block_template' ) ) {
		return '';
	}

	$template = get_block_template( get_stylesheet() . '//management-page', 'wp_template' );
	if ( ! $template || empty( $template->content ) || ! is_string( $template->content ) ) {
		return '';
	}

	return trim( $template->content );
}

function ugm_get_management_render_source( $page_content = '', $template_slug = '' ) {
	$page_content = ugm_upgrade_management_page_blocks( $page_content );
	$template_slug = (string) $template_slug;

	if ( ugm_has_management_page_blocks( $page_content ) ) {
		return $page_content;
	}

	if ( 'management-page' === $template_slug ) {
		$template_content = ugm_get_management_block_template_content();
		if ( '' !== $template_content && ugm_has_management_page_blocks( $template_content ) ) {
			return ugm_upgrade_management_page_blocks( $template_content );
		}
	}

	if ( false !== strpos( $page_content, '<!-- wp:' ) || '' !== trim( wp_strip_all_tags( strip_shortcodes( $page_content ) ) ) ) {
		return $page_content;
	}

	return ugm_get_default_management_page_blocks();
}

function ugm_populate_empty_management_page( $post_id ) {
	$post_id = absint( $post_id );
	if ( $post_id <= 0 || 'page' !== get_post_type( $post_id ) ) {
		return false;
	}

	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post || ugm_management_content_has_visible_content( $post->post_content ) ) {
		return false;
	}

	if ( ! ugm_is_management_page_template_slug( get_page_template_slug( $post_id ) ) ) {
		return false;
	}

	remove_action( 'save_post_page', 'ugm_seed_management_page_on_save', 20 );
	wp_update_post(
		array(
			'ID'           => $post_id,
			'post_content' => ugm_get_default_management_page_blocks(),
		)
	);
	add_action( 'save_post_page', 'ugm_seed_management_page_on_save', 20, 3 );

	return true;
}

function ugm_seed_management_page_on_save( $post_id, $post, $update ) {
	unset( $post, $update );

	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	ugm_populate_empty_management_page( $post_id );
}
add_action( 'save_post_page', 'ugm_seed_management_page_on_save', 20, 3 );

function ugm_clear_management_page_on_default_template( $post_id, $post, $update ) {
	unset( $update );

	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) || 'page' !== get_post_type( $post_id ) ) {
		return;
	}

	if ( ugm_is_management_page_template_slug( get_page_template_slug( $post_id ) ) ) {
		return;
	}

	$content = $post instanceof WP_Post ? (string) $post->post_content : (string) get_post_field( 'post_content', $post_id );
	$cleaned = ugm_remove_management_page_blocks_from_content( $content );
	if ( $cleaned === $content ) {
		return;
	}

	remove_action( 'save_post_page', 'ugm_clear_management_page_on_default_template', 25 );
	wp_update_post(
		array(
			'ID'           => $post_id,
			'post_content' => $cleaned,
		)
	);
	add_action( 'save_post_page', 'ugm_clear_management_page_on_default_template', 25, 3 );
}
add_action( 'save_post_page', 'ugm_clear_management_page_on_default_template', 25, 3 );

function ugm_seed_management_page_after_rest_save( $post, $request, $creating ) {
	unset( $request, $creating );

	if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
		return;
	}

	if ( ! ugm_is_management_page_template_slug( get_page_template_slug( $post->ID ) ) ) {
		ugm_clear_management_page_on_default_template( $post->ID, get_post( $post->ID ), true );
		return;
	}

	ugm_populate_empty_management_page( $post->ID );
}
add_action( 'rest_after_insert_page', 'ugm_seed_management_page_after_rest_save', 20, 3 );

function ugm_upgrade_management_page_on_save( $post_id, $post, $update ) {
	unset( $update );

	if (
		wp_is_post_autosave( $post_id ) ||
		wp_is_post_revision( $post_id ) ||
		! $post instanceof WP_Post ||
		! ugm_is_management_page_template_slug( get_page_template_slug( $post_id ) )
	) {
		return;
	}

	$content = (string) $post->post_content;
	$updated = ugm_upgrade_management_page_blocks( $content );
	if ( $updated === $content ) {
		return;
	}

	remove_action( 'save_post_page', 'ugm_upgrade_management_page_on_save', 30 );
	wp_update_post(
		array(
			'ID'           => $post_id,
			'post_content' => $updated,
		)
	);
	add_action( 'save_post_page', 'ugm_upgrade_management_page_on_save', 30, 3 );
}
add_action( 'save_post_page', 'ugm_upgrade_management_page_on_save', 30, 3 );

function ugm_seed_existing_empty_management_pages() {
	if ( ! is_admin() ) {
		return;
	}

	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'private', 'pending' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'   => '_wp_page_template',
					'value' => 'page-templates/template-management.php',
				),
				array(
					'key'   => '_wp_page_template',
					'value' => 'management-page',
				),
			),
		)
	);

	foreach ( $pages as $page_id ) {
		ugm_populate_empty_management_page( $page_id );

		$post = get_post( $page_id );
		if ( ! $post instanceof WP_Post ) {
			continue;
		}

		$content = (string) $post->post_content;
		$updated = ugm_upgrade_management_page_blocks( $content );
		if ( $updated === $content ) {
			continue;
		}

		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => $updated,
			)
		);
	}
}
add_action( 'admin_init', 'ugm_seed_existing_empty_management_pages' );

function ugm_migrate_management_pages_to_php_template() {
	if ( ! is_admin() ) {
		return;
	}

	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'private', 'pending' ),
			'posts_per_page' => -1,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => 'management-page',
		)
	);

	foreach ( $pages as $page ) {
		if ( ! $page instanceof WP_Post ) {
			continue;
		}

		$content = ugm_upgrade_management_page_blocks( (string) $page->post_content );
		if ( ! ugm_has_management_page_blocks( $content ) ) {
			$content = ugm_get_default_management_page_blocks();
		}

		wp_update_post(
			array(
				'ID'           => $page->ID,
				'post_content' => $content,
				'page_template' => 'page-templates/template-management.php',
			)
		);
		update_post_meta( $page->ID, '_wp_page_template', 'page-templates/template-management.php' );
	}
}
add_action( 'admin_init', 'ugm_migrate_management_pages_to_php_template', 30 );

function ugm_repair_management_block_template() {
	if ( ! is_admin() ) {
		return;
	}

	$template_posts = get_posts(
		array(
			'post_type'      => 'wp_template',
			'post_status'    => array( 'publish', 'draft' ),
			'name'           => 'management-page',
			'posts_per_page' => 1,
		)
	);

	foreach ( $template_posts as $template_post ) {
		if ( ! $template_post instanceof WP_Post ) {
			continue;
		}

		if ( ugm_get_management_block_template_blocks() === trim( (string) $template_post->post_content ) ) {
			continue;
		}

		wp_update_post(
			array(
				'ID'           => $template_post->ID,
				'post_content' => ugm_get_management_block_template_blocks(),
			)
		);
	}
}
add_action( 'admin_init', 'ugm_repair_management_block_template', 25 );


function ugm_use_php_management_template_on_frontend( $template ) {
	if ( is_admin() || ! is_page() ) {
		return $template;
	}

	$page_id = (int) get_queried_object_id();
	if ( $page_id <= 0 || ! ugm_is_management_page_template_slug( get_page_template_slug( $page_id ) ) ) {
		return $template;
	}

	$php_template = get_theme_file_path( 'page-templates/template-management.php' );
	return file_exists( $php_template ) ? $php_template : $template;
}
add_filter( 'template_include', 'ugm_use_php_management_template_on_frontend', 20 );

function ugm_management_page_body_class( $classes ) {
	if ( is_page() && ugm_is_management_page_template_slug( get_page_template_slug( get_queried_object_id() ) ) ) {
		$classes[] = 'ugm-is-management-page';
	}

	return $classes;
}
add_filter( 'body_class', 'ugm_management_page_body_class' );

function ugm_normalize_management_people( $people ) {
	$normalized = array();

	foreach ( is_array( $people ) ? $people : array() as $person ) {
		if ( ! is_array( $person ) ) {
			continue;
		}

		$name = trim( (string) ( $person['name'] ?? '' ) );
		$role = trim( (string) ( $person['role'] ?? '' ) );
		$url  = esc_url_raw( (string) ( $person['imageUrl'] ?? '' ) );

		if ( '' === $name && '' === $role && '' === $url ) {
			continue;
		}

		$normalized[] = array(
			'name'     => $name,
			'role'     => $role,
			'imageUrl' => $url,
		);
	}

	return $normalized;
}

function ugm_render_management_person_card( $person, $modifier = '' ) {
	$class_name = 'ugm-management-person';
	if ( '' !== $modifier ) {
		$class_name .= ' ugm-management-person--' . sanitize_html_class( $modifier );
	}

	ob_start();
	?>
	<article class="<?php echo esc_attr( $class_name ); ?>">
		<div class="ugm-management-person__photo">
			<?php if ( '' !== $person['imageUrl'] ) : ?>
				<img src="<?php echo esc_url( $person['imageUrl'] ); ?>" alt="<?php echo esc_attr( $person['name'] ); ?>" loading="lazy" decoding="async">
			<?php else : ?>
				<span aria-hidden="true"></span>
			<?php endif; ?>
		</div>
		<div class="ugm-management-person__info">
			<?php if ( '' !== $person['name'] ) : ?><h3><?php echo esc_html( $person['name'] ); ?></h3><?php endif; ?>
			<?php if ( '' !== $person['role'] ) : ?><p><?php echo esc_html( $person['role'] ); ?></p><?php endif; ?>
		</div>
	</article>
	<?php

	return ob_get_clean();
}

function ugm_should_render_management_page_block( $attrs = array(), $block = null ) {
	$template_slug = '';

	if ( is_array( $attrs ) && isset( $attrs['_templateSlug'] ) ) {
		$template_slug = (string) $attrs['_templateSlug'];
	}

	if ( '' !== $template_slug ) {
		return ugm_is_management_page_template_slug( $template_slug );
	}

	$post_id = 0;
	if ( $block instanceof WP_Block && ! empty( $block->context['postId'] ) ) {
		$post_id = absint( $block->context['postId'] );
	}

	if ( $post_id <= 0 ) {
		$post_id = absint( get_the_ID() );
	}

	if ( $post_id <= 0 ) {
		$post_id = absint( get_queried_object_id() );
	}

	return $post_id > 0 && ugm_is_management_page_template_slug( get_page_template_slug( $post_id ) );
}

function ugm_render_block_management_hero( $attrs, $content = '', $block = null ) {
	unset( $content );

	if ( ! ugm_should_render_management_page_block( $attrs, $block ) ) {
		return '';
	}

	$title                = trim( wp_strip_all_tags( (string) ( $attrs['title'] ?? __( 'Manajemen Organisasi', 'ugm-faculty' ) ) ) );
	$background           = sanitize_hex_color( (string) ( $attrs['background'] ?? '#dceef6' ) );
	$background_image_url = esc_url_raw( (string) ( $attrs['backgroundImageUrl'] ?? '' ) );

	if ( '' === $title ) {
		$title = __( 'Manajemen Organisasi', 'ugm-faculty' );
	}

	if ( ! $background ) {
		$background = '#dceef6';
	}

	$hero_style = 'background-color: ' . $background . ';';
	if ( '' !== $background_image_url ) {
		$hero_style .= ' background-image: url(' . esc_url( $background_image_url ) . ');';
	}

	ob_start();
	?>
	<header class="ugm-management-page__hero" style="<?php echo esc_attr( $hero_style ); ?>">
		<h1><?php echo esc_html( $title ); ?></h1>
	</header>
	<?php

	return ob_get_clean();
}

function ugm_render_block_management_section( $attrs, $content = '', $block = null ) {
	unset( $content );

	if ( ! ugm_should_render_management_page_block( $attrs, $block ) ) {
		return '';
	}

	$title  = trim( (string) ( $attrs['title'] ?? __( 'Manajemen Fakultas', 'ugm-faculty' ) ) );
	$people = ugm_normalize_management_people( $attrs['people'] ?? array() );

	ob_start();
	?>
	<section class="ugm-management-section" aria-labelledby="ugm-management-section-title">
		<header class="ugm-management-section__heading">
			<h2 id="ugm-management-section-title"><?php echo esc_html( '' !== $title ? $title : __( 'Manajemen Fakultas', 'ugm-faculty' ) ); ?></h2>
		</header>
		<?php if ( ! empty( $people ) ) : ?>
			<div class="ugm-management-section__leader">
				<?php echo ugm_render_management_person_card( $people[0], 'leader' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<?php if ( count( $people ) > 1 ) : ?>
				<div class="ugm-management-section__team">
					<?php foreach ( array_slice( $people, 1 ) as $person ) : ?>
						<?php echo ugm_render_management_person_card( $person ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		<?php else : ?>
			<p class="ugm-management-empty"><?php esc_html_e( 'Tambahkan kartu pimpinan fakultas melalui sidebar editor.', 'ugm-faculty' ); ?></p>
		<?php endif; ?>
	</section>
	<?php

	return ob_get_clean();
}

function ugm_normalize_study_programs( $programs ) {
	$normalized = array();

	foreach ( is_array( $programs ) ? $programs : array() as $program ) {
		if ( ! is_array( $program ) ) {
			continue;
		}

		$title  = trim( (string) ( $program['title'] ?? '' ) );
		$people = ugm_normalize_management_people( $program['people'] ?? array() );
		if ( '' === $title && empty( $people ) ) {
			continue;
		}

		$normalized[] = array(
			'title'  => $title,
			'people' => $people,
		);
	}

	return $normalized;
}

function ugm_render_block_study_program_section( $attrs, $content = '', $block = null ) {
	unset( $content );

	if ( ! ugm_should_render_management_page_block( $attrs, $block ) ) {
		return '';
	}

	$title    = trim( (string) ( $attrs['title'] ?? __( 'Program Studi', 'ugm-faculty' ) ) );
	$programs = ugm_normalize_study_programs( $attrs['programs'] ?? array() );

	ob_start();
	?>
	<section class="ugm-study-program-section" aria-labelledby="ugm-study-program-section-title">
		<header class="ugm-management-section__heading">
			<h2 id="ugm-study-program-section-title"><?php echo esc_html( '' !== $title ? $title : __( 'Program Studi', 'ugm-faculty' ) ); ?></h2>
		</header>
		<?php if ( ! empty( $programs ) ) : ?>
			<div class="ugm-study-program-list">
				<?php foreach ( $programs as $program ) : ?>
					<section class="ugm-study-program">
						<h3><?php echo esc_html( '' !== $program['title'] ? $program['title'] : __( 'Program Studi', 'ugm-faculty' ) ); ?></h3>
						<div class="ugm-study-program__people">
							<?php foreach ( $program['people'] as $person ) : ?>
								<?php echo ugm_render_management_person_card( $person, 'program' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="ugm-management-empty"><?php esc_html_e( 'Tambahkan program studi dan pengelolanya melalui sidebar editor.', 'ugm-faculty' ); ?></p>
		<?php endif; ?>
	</section>
	<?php

	return ob_get_clean();
}

function ugm_render_block_management_share_section( $attrs, $content = '', $block = null ) {
	unset( $content );

	if ( ! ugm_should_render_management_page_block( $attrs, $block ) ) {
		return '';
	}

	$title = trim( wp_strip_all_tags( (string) ( $attrs['title'] ?? __( 'Share This Page', 'ugm-faculty' ) ) ) );
	if ( '' === $title ) {
		$title = __( 'Share This Page', 'ugm-faculty' );
	}

	$page_url   = get_permalink();
	$page_title = get_the_title();
	if ( ! $page_url ) {
		$page_url = home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) );
	}

	$encoded_url   = rawurlencode( $page_url );
	$encoded_title = rawurlencode( wp_strip_all_tags( $page_title ) );
	$default_links = array(
		array(
			'class' => 'facebook',
			'label' => __( 'Facebook', 'ugm-faculty' ),
			'icon'  => 'f',
			'url'   => 'https://www.facebook.com/sharer/sharer.php?u=' . $encoded_url,
		),
		array(
			'class' => 'twitter',
			'label' => __( 'Twitter', 'ugm-faculty' ),
			'icon'  => 't',
			'url'   => 'https://twitter.com/intent/tweet?url=' . $encoded_url . '&text=' . $encoded_title,
		),
		array(
			'class' => 'linkedin',
			'label' => __( 'LinkedIn', 'ugm-faculty' ),
			'icon'  => 'in',
			'url'   => 'https://www.linkedin.com/shareArticle?mini=true&url=' . $encoded_url . '&title=' . $encoded_title,
		),
		array(
			'class' => 'whatsapp',
			'label' => __( 'WhatsApp', 'ugm-faculty' ),
			'icon'  => 'wa',
			'url'   => 'https://api.whatsapp.com/send?text=' . $encoded_title . '%20' . $encoded_url,
		),
		array(
			'class' => 'email',
			'label' => __( 'Email', 'ugm-faculty' ),
			'icon'  => '@',
			'url'   => 'mailto:?subject=' . $encoded_title . '&body=' . $encoded_url,
		),
	);
	$custom_links  = is_array( $attrs['links'] ?? null ) ? $attrs['links'] : array();
	$links         = array();

	foreach ( $default_links as $index => $default_link ) {
		$custom = isset( $custom_links[ $index ] ) && is_array( $custom_links[ $index ] ) ? $custom_links[ $index ] : array();
		$label  = trim( wp_strip_all_tags( (string) ( $custom['label'] ?? $default_link['label'] ) ) );
		$icon   = trim( wp_strip_all_tags( (string) ( $custom['icon'] ?? $default_link['icon'] ) ) );
		$icon_url = esc_url_raw( (string) ( $custom['iconUrl'] ?? '' ) );
		$url    = trim( (string) ( $custom['url'] ?? '' ) );

		$links[] = array(
			'class'   => $default_link['class'],
			'label'   => '' !== $label ? $label : $default_link['label'],
			'icon'    => '' !== $icon ? $icon : $default_link['icon'],
			'iconUrl' => $icon_url,
			'url'     => '' !== $url ? $url : $default_link['url'],
		);
	}

	ob_start();
	?>
	<section class="ugm-management-share" aria-label="<?php echo esc_attr( $title ); ?>">
		<span class="ugm-management-share__label"><?php echo esc_html( $title ); ?></span>
		<div class="ugm-management-share__links">
			<?php foreach ( $links as $link ) : ?>
				<a class="ugm-management-share__button ugm-management-share__button--<?php echo esc_attr( $link['class'] ); ?>" href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $link['label'] ); ?>">
					<?php if ( '' !== $link['iconUrl'] ) : ?>
						<img src="<?php echo esc_url( $link['iconUrl'] ); ?>" alt="" loading="lazy" decoding="async">
					<?php else : ?>
						<?php echo esc_html( $link['icon'] ); ?>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
	<?php

	return ob_get_clean();
}

function ugm_render_block_management_template_preview() {
	$template_attr = array( '_templateSlug' => 'management-page' );

	return ugm_render_block_management_hero(
		array_merge(
			$template_attr,
			array(
				'title'      => 'Manajemen Organisasi',
				'background' => '#dceef6',
			)
		)
	) .
	ugm_render_block_management_section(
		array_merge(
			$template_attr,
			array(
				'title'  => 'Manajemen Fakultas',
				'people' => array(),
			)
		)
	) .
	ugm_render_block_study_program_section(
		array_merge(
			$template_attr,
			array(
				'title'    => 'Program Studi',
				'programs' => array(),
			)
		)
	) .
	ugm_render_block_management_share_section(
		array_merge(
			$template_attr,
			array(
				'title' => 'Share This Page',
			)
		)
	);
}

function ugm_register_management_page_blocks() {
	register_block_type(
		'ugm/management-hero',
		array(
			'api_version'     => 2,
			'category'        => 'ugm-management-page-sections',
			'render_callback' => 'ugm_render_block_management_hero',
			'uses_context'    => array( 'postId' ),
			'supports'        => array( 'html' => false, 'multiple' => false ),
			'attributes'      => array(
				'title'         => array( 'type' => 'string', 'default' => 'Manajemen Organisasi' ),
				'background'    => array( 'type' => 'string', 'default' => '#dceef6' ),
				'backgroundImageId' => array( 'type' => 'number', 'default' => 0 ),
				'backgroundImageUrl' => array( 'type' => 'string', 'default' => '' ),
				'_templateSlug' => array( 'type' => 'string', 'default' => '' ),
			),
		)
	);

	register_block_type(
		'ugm/management-section',
		array(
			'api_version'     => 2,
			'category'        => 'ugm-management-page-sections',
			'render_callback' => 'ugm_render_block_management_section',
			'uses_context'    => array( 'postId' ),
			'supports'        => array( 'html' => false, 'multiple' => false ),
			'attributes'      => array(
				'title'         => array( 'type' => 'string', 'default' => 'Manajemen Fakultas' ),
				'people'        => array( 'type' => 'array',  'default' => array() ),
				'_templateSlug' => array( 'type' => 'string', 'default' => '' ),
			),
		)
	);

	register_block_type(
		'ugm/study-program-section',
		array(
			'api_version'     => 2,
			'category'        => 'ugm-management-page-sections',
			'render_callback' => 'ugm_render_block_study_program_section',
			'uses_context'    => array( 'postId' ),
			'supports'        => array( 'html' => false, 'multiple' => false ),
			'attributes'      => array(
				'title'         => array( 'type' => 'string', 'default' => 'Program Studi' ),
				'programs'      => array( 'type' => 'array',  'default' => array() ),
				'_templateSlug' => array( 'type' => 'string', 'default' => '' ),
			),
		)
	);

	register_block_type(
		'ugm/management-share-section',
		array(
			'api_version'     => 2,
			'category'        => 'ugm-management-page-sections',
			'render_callback' => 'ugm_render_block_management_share_section',
			'uses_context'    => array( 'postId' ),
			'supports'        => array( 'html' => false, 'multiple' => false ),
			'attributes'      => array(
				'title'         => array( 'type' => 'string', 'default' => 'Share This Page' ),
				'links'         => array( 'type' => 'array', 'default' => array() ),
				'_templateSlug' => array( 'type' => 'string', 'default' => '' ),
			),
		)
	);

	register_block_type(
		'ugm/management-template-preview',
		array(
			'api_version'     => 2,
			'render_callback' => 'ugm_render_block_management_template_preview',
			'uses_context'    => array( 'postId' ),
			'supports'        => array(
				'html'     => false,
				'inserter' => false,
			),
		)
	);
}

add_action( 'init', 'ugm_register_management_page_blocks' );

function ugm_register_management_page_block_category( $categories ) {
	array_unshift(
		$categories,
		array(
			'slug'  => 'ugm-management-page-sections',
			'title' => __( 'UGM - Manajemen Page Sections', 'ugm-faculty' ),
			'icon'  => 'groups',
		)
	);

	return $categories;
}
add_filter( 'block_categories_all', 'ugm_register_management_page_block_category' );

function ugm_enqueue_management_page_editor_assets() {
	wp_enqueue_script(
		'ugm-management-blocks',
		get_template_directory_uri() . '/assets/js/management-blocks.js',
		array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-block-editor', 'wp-components', 'wp-compose', 'wp-data', 'wp-hooks', 'wp-plugins', 'wp-server-side-render' ),
		ugm_get_asset_version( '/assets/js/management-blocks.js' ),
		true
	);

	wp_localize_script(
		'ugm-management-blocks',
		'ugmManagementPageEditor',
		array(
			'defaultBlocks' => ugm_get_default_management_page_blocks(),
		)
	);

	wp_enqueue_style(
		'ugm-editor-style-management-page',
		get_template_directory_uri() . '/assets/css/management-page.css',
		array( 'ugm-editor-style-base', 'ugm-editor-style-content' ),
		ugm_get_asset_version( '/assets/css/management-page.css' )
	);
}
add_action( 'enqueue_block_editor_assets', 'ugm_enqueue_management_page_editor_assets' );

function ugm_register_management_page_block_pattern() {
	if ( ! function_exists( 'register_block_pattern' ) ) {
		return;
	}

	register_block_pattern(
		'ugm/management-page-sections',
		array(
			'title'       => __( 'Konten Halaman Manajemen', 'ugm-faculty' ),
			'description' => __( 'Section manajemen fakultas dan program studi. Insert ke halaman yang menggunakan template Manajemen Page.', 'ugm-faculty' ),
			'categories'  => array( 'ugm-landing' ),
			'content'     => ugm_get_default_management_page_blocks(),
		)
	);
}
add_action( 'init', 'ugm_register_management_page_block_pattern' );
