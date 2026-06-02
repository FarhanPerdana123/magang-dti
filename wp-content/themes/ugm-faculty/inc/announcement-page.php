<?php
/**
 * Announcement page template helpers and block registration.
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ugm_get_default_announcement_page_blocks() {
	return '<!-- wp:group {"className":"ugm-announcement-template-layout","layout":{"type":"constrained"}} -->' . "\n" .
		'<div class="wp-block-group ugm-announcement-template-layout">' . "\n" .
		'<!-- wp:columns {"className":"ugm-announcement-template-columns"} -->' . "\n" .
		'<div class="wp-block-columns ugm-announcement-template-columns">' . "\n" .
		'<!-- wp:column {"width":"1016px","className":"ugm-announcement-template-main"} -->' . "\n" .
		'<div class="wp-block-column ugm-announcement-template-main" style="flex-basis:1016px">' . "\n" .
		'<!-- wp:ugm/announcement-page {"title":"Pengumuman","categorySlug":"pengumuman","postsPerPage":7,"showFacebook":true,"facebookUrl":"","showTwitter":true,"twitterUrl":"","showWhatsapp":true,"whatsappUrl":""} /-->' . "\n" .
		'</div>' . "\n" .
		'<!-- /wp:column -->' . "\n" .
		'<!-- wp:column {"width":"270px","className":"ugm-announcement-template-sidebar"} -->' . "\n" .
		'<div class="wp-block-column ugm-announcement-template-sidebar" style="flex-basis:270px">' . "\n" .
		'<!-- wp:ugm/announcement-latest-news {"title":"Berita Terbaru","postsPerPage":5} /-->' . "\n" .
		'<!-- wp:ugm/announcement-latest-agenda {"title":"Agenda Terbaru","categorySlug":"agenda","postsPerPage":3,"buttonLabel":"Semua Agenda","buttonUrl":"/agenda/"} /-->' . "\n" .
		'</div>' . "\n" .
		'<!-- /wp:column -->' . "\n" .
		'</div>' . "\n" .
		'<!-- /wp:columns -->' . "\n" .
		'</div>' . "\n" .
		'<!-- /wp:group -->';
}

function ugm_is_announcement_page_template_slug( $template ) {
	return in_array(
		(string) $template,
		array(
			'page-templates/template-announcement.php',
			'announcement-page',
		),
		true
	);
}

function ugm_get_announcement_block_template_content() {
	if ( ! function_exists( 'get_block_template' ) ) {
		return '';
	}

	$template = get_block_template( get_stylesheet() . '//announcement-page', 'wp_template' );
	if ( ! $template || empty( $template->content ) || ! is_string( $template->content ) ) {
		return '';
	}

	return trim( $template->content );
}

function ugm_get_announcement_render_source( $page_content = '', $template_slug = '' ) {
	$page_content = (string) $page_content;
	$template_slug = (string) $template_slug;

	if ( 'announcement-page' === $template_slug ) {
		$template_content = ugm_get_announcement_block_template_content();
		if ( '' !== $template_content && false !== strpos( $template_content, 'ugm/announcement-page' ) ) {
			return $template_content;
		}
	}

	if ( false !== strpos( $page_content, '<!-- wp:' ) ) {
		return $page_content;
	}

	if ( '' === trim( wp_strip_all_tags( strip_shortcodes( $page_content ) ) ) ) {
		return '';
	}

	$template_content = ugm_get_announcement_block_template_content();
	if ( '' !== $template_content && false !== strpos( $template_content, '<!-- wp:' ) ) {
		return $template_content;
	}

	return ugm_get_default_announcement_page_blocks();
}

function ugm_repair_announcement_block_template() {
	if ( ! is_admin() ) {
		return;
	}

	$template_posts = get_posts(
		array(
			'post_type'      => 'wp_template',
			'post_status'    => array( 'publish', 'draft' ),
			'name'           => 'announcement-page',
			'posts_per_page' => 1,
		)
	);

	foreach ( $template_posts as $template_post ) {
		if ( ! $template_post instanceof WP_Post ) {
			continue;
		}

		$content = (string) $template_post->post_content;
		if ( false !== strpos( $content, 'ugm/announcement-page' ) ) {
			continue;
		}

		wp_update_post(
			array(
				'ID'           => $template_post->ID,
				'post_content' => ugm_get_default_announcement_page_blocks(),
			)
		);
	}
}
add_action( 'admin_init', 'ugm_repair_announcement_block_template', 25 );

function ugm_use_php_announcement_template_on_frontend( $template ) {
	if ( is_admin() || ! is_page() ) {
		return $template;
	}

	$page_id = (int) get_queried_object_id();
	if ( $page_id <= 0 || ! ugm_is_announcement_page_template_slug( get_page_template_slug( $page_id ) ) ) {
		return $template;
	}

	$php_template = get_theme_file_path( 'page-templates/template-announcement.php' );
	return file_exists( $php_template ) ? $php_template : $template;
}
add_filter( 'template_include', 'ugm_use_php_announcement_template_on_frontend', 20 );

function ugm_announcement_page_body_class( $classes ) {
	if ( is_page() && ugm_is_announcement_page_template_slug( get_page_template_slug( get_queried_object_id() ) ) ) {
		$classes[] = 'ugm-is-announcement-page';
	}

	return $classes;
}
add_filter( 'body_class', 'ugm_announcement_page_body_class' );

function ugm_announcement_term_ids_from_slugs( $slug_attr ) {
	$slugs = array_filter(
		array_map(
			'trim',
			explode( ',', (string) $slug_attr )
		)
	);
	$ids   = array();

	foreach ( $slugs as $slug ) {
		$term = get_category_by_slug( sanitize_title( $slug ) );
		if ( $term instanceof WP_Term ) {
			$ids[] = (int) $term->term_id;
			$children = get_term_children( (int) $term->term_id, 'category' );
			if ( ! is_wp_error( $children ) ) {
				$ids = array_merge( $ids, array_map( 'absint', $children ) );
			}
		}
	}

	return array_values( array_unique( array_filter( $ids ) ) );
}

function ugm_announcement_excerpt( $post_id, $words = 22 ) {
	$excerpt = trim( wp_strip_all_tags( get_the_excerpt( $post_id ) ) );
	if ( '' === $excerpt ) {
		$excerpt = wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ), $words, '...' );
	}

	return $excerpt;
}

function ugm_announcement_image_url( $post_id, $size = 'large' ) {
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail_url( $post_id, $size );
	}

	return get_theme_file_uri( 'assets/images/landing page UGM.png' );
}

function ugm_render_announcement_featured_card( WP_Post $post ) {
	$post_id = (int) $post->ID;
	?>
	<article class="ugm-announcement-featured">
		<a class="ugm-announcement-featured__media" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
			<img src="<?php echo esc_url( ugm_announcement_image_url( $post_id, 'large' ) ); ?>" alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" loading="lazy" decoding="async">
		</a>
		<div class="ugm-announcement-featured__body">
			<p class="ugm-announcement-card__kicker"><?php esc_html_e( 'Pengumuman Utama', 'ugm-faculty' ); ?></p>
			<h2 class="ugm-announcement-featured__title"><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h2>
			<p class="ugm-announcement-featured__excerpt"><?php echo esc_html( ugm_announcement_excerpt( $post_id, 24 ) ); ?></p>
			<div class="ugm-announcement-featured__footer">
				<span><?php echo esc_html( sprintf( __( 'Dipublikasikan: %s', 'ugm-faculty' ), get_the_date( 'j F Y', $post_id ) ) ); ?></span>
				<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php esc_html_e( 'Baca Selengkapnya', 'ugm-faculty' ); ?></a>
			</div>
		</div>
	</article>
	<?php
}

function ugm_render_announcement_compact_card( WP_Post $post ) {
	$post_id = (int) $post->ID;
	?>
	<article class="ugm-announcement-compact">
		<a class="ugm-announcement-compact__media" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
			<img src="<?php echo esc_url( ugm_announcement_image_url( $post_id, 'thumbnail' ) ); ?>" alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" loading="lazy" decoding="async">
		</a>
		<div class="ugm-announcement-compact__body">
			<h3><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h3>
			<p><?php echo esc_html( ugm_announcement_excerpt( $post_id, 15 ) ); ?></p>
		</div>
	</article>
	<?php
}

function ugm_render_announcement_list_item( WP_Post $post ) {
	$post_id = (int) $post->ID;
	?>
	<article class="ugm-announcement-list-item">
		<a class="ugm-announcement-list-item__media" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
			<img src="<?php echo esc_url( ugm_announcement_image_url( $post_id, 'thumbnail' ) ); ?>" alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" loading="lazy" decoding="async">
		</a>
		<div class="ugm-announcement-list-item__body">
			<h3><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h3>
			<p><?php echo esc_html( ugm_announcement_excerpt( $post_id, 20 ) ); ?></p>
			<span><?php echo esc_html( sprintf( __( 'Dipublikasikan: %s', 'ugm-faculty' ), get_the_date( 'j F Y', $post_id ) ) ); ?></span>
		</div>
	</article>
	<?php
}

function ugm_render_announcement_mobile_card( WP_Post $post ) {
	$post_id   = (int) $post->ID;
	$timestamp = (int) get_post_timestamp( $post_id );
	?>
	<article class="ugm-announcement-mobile-card">
		<a class="ugm-announcement-mobile-card__media" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
			<img src="<?php echo esc_url( ugm_announcement_image_url( $post_id, 'medium_large' ) ); ?>" alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" loading="lazy" decoding="async">
			<span class="ugm-announcement-mobile-card__date" aria-hidden="true">
				<strong><?php echo esc_html( wp_date( 'd', $timestamp ) ); ?></strong>
				<span><?php echo esc_html( strtoupper( wp_date( 'M', $timestamp ) ) ); ?></span>
			</span>
		</a>
		<div class="ugm-announcement-mobile-card__body">
			<p class="ugm-announcement-mobile-card__kicker"><?php esc_html_e( 'Pengumuman Utama', 'ugm-faculty' ); ?></p>
			<h2><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h2>
			<p class="ugm-announcement-mobile-card__excerpt"><?php echo esc_html( ugm_announcement_excerpt( $post_id, 21 ) ); ?></p>
			<p class="ugm-announcement-mobile-card__meta"><?php echo esc_html( get_the_date( 'j F Y', $post_id ) ); ?></p>
			<span><?php esc_html_e( 'Pengumuman', 'ugm-faculty' ); ?></span>
		</div>
	</article>
	<?php
}

function ugm_render_announcement_mobile_row( WP_Post $post, $variant = 'latest' ) {
	$post_id = (int) $post->ID;
	?>
	<article class="ugm-announcement-mobile-row ugm-announcement-mobile-row--<?php echo esc_attr( $variant ); ?>">
		<a class="ugm-announcement-mobile-row__media" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
			<img src="<?php echo esc_url( ugm_announcement_image_url( $post_id, 'thumbnail' ) ); ?>" alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" loading="lazy" decoding="async">
		</a>
		<div class="ugm-announcement-mobile-row__body">
			<h3><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h3>
			<?php if ( 'latest' === $variant ) : ?>
				<p><?php echo esc_html( ugm_announcement_excerpt( $post_id, 15 ) ); ?></p>
			<?php else : ?>
				<p><?php echo esc_html( sprintf( '%1$s - %2$s', get_the_date( 'j F Y', $post_id ), get_the_author_meta( 'display_name', (int) $post->post_author ) ) ); ?></p>
			<?php endif; ?>
		</div>
		<a class="ugm-announcement-mobile-row__arrow" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Baca %s', 'ugm-faculty' ), get_the_title( $post_id ) ) ); ?>">&#8250;</a>
	</article>
	<?php
}

function ugm_resolve_announcement_social_url( $value, $fallback, $type = 'url' ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return $fallback;
	}

	if ( 'whatsapp' === $type && ! preg_match( '#^https?://#i', $value ) ) {
		$phone = preg_replace( '/[^0-9]/', '', $value );
		if ( '' !== $phone ) {
			return 'https://wa.me/' . $phone;
		}
	}

	if ( preg_match( '#^https?://#i', $value ) ) {
		return $value;
	}

	return 'https://' . ltrim( $value, '/' );
}

function ugm_get_announcement_social_items( $attrs ) {
	$attrs = is_array( $attrs ) ? $attrs : array();
	$current_url   = rawurlencode( get_permalink() );
	$current_title = rawurlencode( get_the_title() );
	$fallback_urls = array(
		'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . $current_url,
		'twitter'  => 'https://twitter.com/intent/tweet?url=' . $current_url . '&text=' . $current_title,
		'whatsapp' => 'https://api.whatsapp.com/send?text=' . $current_title . '%20' . $current_url,
	);

	if ( ! empty( $attrs['socialItems'] ) && is_array( $attrs['socialItems'] ) ) {
		$items = array();
		foreach ( $attrs['socialItems'] as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$label   = trim( (string) ( $item['label'] ?? '' ) );
			$icon    = trim( (string) ( $item['icon'] ?? '' ) );
			$icon_id = absint( $item['iconImageId'] ?? 0 );
			$icon_url = '';
			$url     = trim( (string) ( $item['url'] ?? '' ) );
			$color   = trim( (string) ( $item['color'] ?? '' ) );
			$type    = sanitize_key( (string) ( $item['type'] ?? '' ) );
			$enabled = (bool) ( $item['enabled'] ?? true );

			if ( $icon_id > 0 ) {
				$icon_url = (string) wp_get_attachment_image_url( $icon_id, 'thumbnail' );
			}
			if ( '' === $icon_url && ! empty( $item['iconImageUrl'] ) ) {
				$icon_url = esc_url_raw( (string) $item['iconImageUrl'] );
			}

			if ( '' === $url && isset( $fallback_urls[ $type ] ) ) {
				$url = $fallback_urls[ $type ];
			}

			if ( ! $enabled || '' === $url ) {
				continue;
			}

			$items[] = array(
				'label' => '' !== $label ? $label : __( 'Media sosial', 'ugm-faculty' ),
				'icon'  => '' !== $icon ? ( function_exists( 'mb_substr' ) ? mb_substr( $icon, 0, 4 ) : substr( $icon, 0, 4 ) ) : 'S',
				'icon_url' => $icon_url,
				'url'   => ugm_resolve_announcement_social_url( $url, '' ),
				'color' => preg_match( '/^#[0-9a-f]{6}$/i', $color ) ? $color : '#083b60',
			);
		}

		return $items;
	}

	return array();
}

function ugm_render_announcement_mobile_share_links( $attrs = array() ) {
	$attrs = is_array( $attrs ) ? $attrs : array();
	$url   = rawurlencode( get_permalink() );
	$title = rawurlencode( get_the_title() );
	$custom_items = ugm_get_announcement_social_items( $attrs );

	if ( array_key_exists( 'socialItems', $attrs ) && is_array( $attrs['socialItems'] ) ) {
		if ( empty( $custom_items ) ) {
			return;
		}
		?>
		<div class="ugm-announcement-mobile-share" aria-label="<?php esc_attr_e( 'Kontak dan media sosial', 'ugm-faculty' ); ?>">
			<?php foreach ( $custom_items as $item ) : ?>
				<a class="ugm-announcement-mobile-share__custom" href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $item['label'] ); ?>" style="background-color: <?php echo esc_attr( $item['color'] ); ?>">
					<?php if ( '' !== $item['icon_url'] ) : ?>
						<img src="<?php echo esc_url( $item['icon_url'] ); ?>" alt="" loading="lazy" decoding="async">
					<?php else : ?>
						<?php echo esc_html( $item['icon'] ); ?>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
		<?php
		return;
	}

	$links = array(
		'facebook' => array(
			'enabled' => (bool) ( $attrs['showFacebook'] ?? true ),
			'url'     => ugm_resolve_announcement_social_url( $attrs['facebookUrl'] ?? '', 'https://www.facebook.com/sharer/sharer.php?u=' . $url ),
			'label'   => __( 'Facebook', 'ugm-faculty' ),
			'text'    => 'F',
		),
		'twitter' => array(
			'enabled' => (bool) ( $attrs['showTwitter'] ?? true ),
			'url'     => ugm_resolve_announcement_social_url( $attrs['twitterUrl'] ?? '', 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title ),
			'label'   => __( 'Twitter', 'ugm-faculty' ),
			'text'    => 'T',
		),
		'whatsapp' => array(
			'enabled' => (bool) ( $attrs['showWhatsapp'] ?? true ),
			'url'     => ugm_resolve_announcement_social_url( $attrs['whatsappUrl'] ?? '', 'https://api.whatsapp.com/send?text=' . $title . '%20' . $url, 'whatsapp' ),
			'label'   => __( 'WhatsApp', 'ugm-faculty' ),
			'text'    => 'W',
		),
	);

	$links = array_filter(
		$links,
		function ( $link ) {
			return ! empty( $link['enabled'] ) && ! empty( $link['url'] );
		}
	);

	if ( empty( $links ) ) {
		return;
	}
	?>
	<div class="ugm-announcement-mobile-share" aria-label="<?php esc_attr_e( 'Bagikan pengumuman', 'ugm-faculty' ); ?>">
		<?php foreach ( $links as $key => $link ) : ?>
			<a class="ugm-announcement-mobile-share__<?php echo esc_attr( $key ); ?>" href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $link['label'] ); ?>"><?php echo esc_html( $link['text'] ); ?></a>
		<?php endforeach; ?>
	</div>
	<?php
}

function ugm_render_block_announcement_latest_news( $attrs ) {
	$attrs          = is_array( $attrs ) ? $attrs : array();
	$title          = trim( (string) ( $attrs['title'] ?? __( 'Berita Terbaru', 'ugm-faculty' ) ) );
	$posts_per_page = max( 1, min( 10, absint( $attrs['postsPerPage'] ?? 5 ) ) );
	$agenda_ids     = ugm_announcement_term_ids_from_slugs( 'agenda,kegiatan,events,event' );
	$posts          = get_posts(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => $posts_per_page,
			'ignore_sticky_posts' => true,
			'post_status'         => 'publish',
			'orderby'             => 'date',
			'order'               => 'DESC',
			'category__not_in'    => $agenda_ids,
		)
	);

	ob_start();
	?>
	<section class="ugm-announcement-side-section">
		<h2><?php echo esc_html( '' !== $title ? $title : __( 'Berita Terbaru', 'ugm-faculty' ) ); ?></h2>
		<?php foreach ( $posts as $news_post ) : ?>
			<article class="ugm-announcement-side-news">
				<h3><a href="<?php echo esc_url( get_permalink( $news_post ) ); ?>"><?php echo esc_html( get_the_title( $news_post ) ); ?></a></h3>
				<time datetime="<?php echo esc_attr( get_the_date( 'c', $news_post ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y', $news_post ) ); ?></time>
			</article>
		<?php endforeach; ?>
	</section>
	<?php

	return ob_get_clean();
}

function ugm_render_block_announcement_latest_agenda( $attrs ) {
	$attrs          = is_array( $attrs ) ? $attrs : array();
	$title          = trim( (string) ( $attrs['title'] ?? __( 'Agenda Terbaru', 'ugm-faculty' ) ) );
	$category_slug  = trim( (string) ( $attrs['categorySlug'] ?? 'agenda' ) );
	$posts_per_page = max( 1, min( 10, absint( $attrs['postsPerPage'] ?? 3 ) ) );
	$button_label   = trim( (string) ( $attrs['buttonLabel'] ?? __( 'Semua Agenda', 'ugm-faculty' ) ) );
	$button_url     = trim( (string) ( $attrs['buttonUrl'] ?? '/agenda/' ) );
	$agenda_ids     = ugm_announcement_term_ids_from_slugs( '' !== $category_slug ? $category_slug : 'agenda' );
	$posts          = ! empty( $agenda_ids ) ? get_posts(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => max( $posts_per_page, 20 ),
			'ignore_sticky_posts' => true,
			'post_status'         => 'publish',
			'orderby'             => 'date',
			'order'               => 'DESC',
			'category__in'        => $agenda_ids,
		)
	) : array();

	if ( ! empty( $posts ) && function_exists( 'ugm_get_agenda_event_timestamp' ) ) {
		usort(
			$posts,
			function ( $left, $right ) {
				return ugm_get_agenda_event_timestamp( $right->ID ) <=> ugm_get_agenda_event_timestamp( $left->ID );
			}
		);
	}

	$posts = array_slice( $posts, 0, $posts_per_page );

	if ( '' === $button_url ) {
		$button_url = '/agenda/';
	}
	if ( '/agenda/' === $button_url && function_exists( 'ugm_get_agenda_page_url' ) ) {
		$button_href = ugm_get_agenda_page_url();
	} else {
		$button_href = preg_match( '#^https?://#i', $button_url ) ? $button_url : home_url( $button_url );
	}

	ob_start();
	?>
	<section class="ugm-announcement-side-section ugm-announcement-side-section--agenda">
		<h2><?php echo esc_html( '' !== $title ? $title : __( 'Agenda Terbaru', 'ugm-faculty' ) ); ?></h2>
		<?php if ( ! empty( $posts ) ) : ?>
			<?php foreach ( $posts as $agenda_post ) : ?>
				<?php
				$timestamp = function_exists( 'ugm_get_agenda_event_timestamp' )
					? ugm_get_agenda_event_timestamp( $agenda_post->ID )
					: (int) get_post_timestamp( $agenda_post );
				?>
				<article class="ugm-announcement-side-agenda">
					<a class="ugm-announcement-side-agenda__date" href="<?php echo esc_url( get_permalink( $agenda_post ) ); ?>">
						<strong><?php echo esc_html( wp_date( 'd', $timestamp ) ); ?></strong>
						<span><?php echo esc_html( strtoupper( wp_date( 'M', $timestamp ) ) ); ?></span>
					</a>
					<h3><a href="<?php echo esc_url( get_permalink( $agenda_post ) ); ?>"><?php echo esc_html( get_the_title( $agenda_post ) ); ?></a></h3>
				</article>
			<?php endforeach; ?>
		<?php endif; ?>
		<a class="ugm-announcement-side-agenda__all" href="<?php echo esc_url( $button_href ); ?>">
			<?php echo esc_html( '' !== $button_label ? $button_label : __( 'Semua Agenda', 'ugm-faculty' ) ); ?> <span aria-hidden="true">&#8594;</span>
		</a>
	</section>
	<?php

	return ob_get_clean();
}

function ugm_render_block_announcement_page( $attrs ) {
	$attrs          = is_array( $attrs ) ? $attrs : array();
	$title          = trim( (string) ( $attrs['title'] ?? __( 'Pengumuman', 'ugm-faculty' ) ) );
	$category_slug  = trim( (string) ( $attrs['categorySlug'] ?? 'pengumuman' ) );
	$posts_per_page = max( 1, min( 24, absint( $attrs['postsPerPage'] ?? 7 ) ) );
	$paged          = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	$keyword        = isset( $_GET['announcement_keyword'] ) ? sanitize_text_field( wp_unslash( $_GET['announcement_keyword'] ) ) : '';

	$term_ids = ugm_announcement_term_ids_from_slugs( '' !== $category_slug ? $category_slug : 'pengumuman' );

	$query_args = array(
		'post_type'           => 'post',
		'posts_per_page'      => $posts_per_page,
		'paged'               => $paged,
		'ignore_sticky_posts' => true,
		'post_status'         => 'publish',
		'orderby'             => 'date',
		'order'               => 'DESC',
	);

	if ( ! empty( $term_ids ) ) {
		$query_args['category__in'] = $term_ids;
	}

	if ( '' !== $keyword ) {
		$query_args['s'] = $keyword;
	}

	$query        = new WP_Query( $query_args );
	$posts        = $query->posts;
	$featured     = ! empty( $posts ) ? array_shift( $posts ) : null;
	$compact      = array_slice( $posts, 0, 3 );
	$list_items   = array_slice( $posts, 3 );
	ob_start();
	?>
	<section class="ugm-announcement-section" aria-labelledby="ugm-announcement-page-title">
		<nav class="ugm-announcement-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'ugm-faculty' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Berita', 'ugm-faculty' ); ?></a>
			<span aria-hidden="true">&#8250;</span>
			<span aria-current="page"><?php echo esc_html( $title ); ?></span>
		</nav>

		<div class="ugm-announcement-main">
			<header class="ugm-announcement-header">
				<h1 id="ugm-announcement-page-title"><?php echo esc_html( $title ); ?></h1>
				<span aria-hidden="true"></span>
			</header>

				<form class="ugm-announcement-filter" action="<?php echo esc_url( get_permalink() ); ?>" method="get">
					<div class="ugm-announcement-filter__search">
						<input type="search" name="announcement_keyword" value="<?php echo esc_attr( $keyword ); ?>" placeholder="<?php esc_attr_e( 'Pencarian Pengumuman...', 'ugm-faculty' ); ?>">
						<button type="submit" aria-label="<?php esc_attr_e( 'Cari pengumuman', 'ugm-faculty' ); ?>">
							<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M10.8 4.5a6.3 6.3 0 1 1 0 12.6 6.3 6.3 0 0 1 0-12.6zm0-3a9.3 9.3 0 0 0-7.33 15.02 9.3 9.3 0 0 0 12.02 1.08l4.45 4.46 2.12-2.12-4.46-4.45A9.3 9.3 0 0 0 10.8 1.5z"/></svg>
						</button>
					</div>
					<input type="search" name="announcement_keyword_secondary" placeholder="<?php esc_attr_e( 'Kata kunci', 'ugm-faculty' ); ?>" disabled>
					<input type="text" placeholder="<?php esc_attr_e( 'Lokasi', 'ugm-faculty' ); ?>" disabled>
					<select disabled><option><?php esc_html_e( 'Select Date Range', 'ugm-faculty' ); ?></option></select>
					<select disabled><option><?php esc_html_e( 'Choose an Event Category', 'ugm-faculty' ); ?></option></select>
					<select disabled><option><?php esc_html_e( 'Choose an Event Type', 'ugm-faculty' ); ?></option></select>
				</form>

				<div class="ugm-announcement-desktop-list">
					<?php if ( $featured instanceof WP_Post ) : ?>
						<?php ugm_render_announcement_featured_card( $featured ); ?>
					<?php endif; ?>

					<?php if ( ! empty( $compact ) ) : ?>
						<div class="ugm-announcement-compact-grid">
							<?php foreach ( $compact as $compact_post ) : ?>
								<?php ugm_render_announcement_compact_card( $compact_post ); ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<div class="ugm-announcement-list">
						<?php foreach ( $list_items as $list_post ) : ?>
							<?php ugm_render_announcement_list_item( $list_post ); ?>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="ugm-announcement-mobile-list">
					<?php if ( $featured instanceof WP_Post ) : ?>
						<?php ugm_render_announcement_mobile_card( $featured ); ?>
					<?php endif; ?>

					<?php if ( ! empty( $compact ) ) : ?>
						<section class="ugm-announcement-mobile-group">
							<h2><?php esc_html_e( 'Pengumuman Terkini', 'ugm-faculty' ); ?></h2>
							<?php foreach ( $compact as $compact_post ) : ?>
								<?php ugm_render_announcement_mobile_row( $compact_post, 'latest' ); ?>
							<?php endforeach; ?>
						</section>
					<?php endif; ?>

					<?php if ( ! empty( $list_items ) ) : ?>
						<section class="ugm-announcement-mobile-group ugm-announcement-mobile-group--other">
							<h2><?php esc_html_e( 'Pengumuman Lainnya', 'ugm-faculty' ); ?></h2>
							<?php foreach ( array_slice( $list_items, 0, 4 ) as $list_post ) : ?>
								<?php ugm_render_announcement_mobile_row( $list_post, 'other' ); ?>
							<?php endforeach; ?>
						</section>
					<?php endif; ?>
				</div>

				<?php if ( ! $query->have_posts() ) : ?>
					<p class="section-empty"><?php esc_html_e( 'Belum ada pengumuman.', 'ugm-faculty' ); ?></p>
				<?php endif; ?>

				<nav class="ugm-announcement-pagination" aria-label="<?php esc_attr_e( 'Announcement navigation', 'ugm-faculty' ); ?>">
					<?php
					echo paginate_links(
						array(
							'total'     => max( 1, (int) $query->max_num_pages ),
							'current'   => $paged,
							'mid_size'  => 1,
							'prev_text' => '&#8592;',
							'next_text' => '&#8594;',
							'add_args'  => array_filter(
								array(
									'announcement_keyword' => $keyword,
								)
							),
						)
					); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</nav>

				<?php ugm_render_announcement_mobile_share_links( $attrs ); ?>
		</div>
	</section>
	<?php
	wp_reset_postdata();

	return ob_get_clean();
}

add_action( 'init', function () {
	register_block_type( 'ugm/announcement-page', array(
		'title'           => __( 'Daftar Pengumuman', 'ugm-faculty' ),
		'description'     => __( 'Daftar pengumuman utama dengan filter dan pagination.', 'ugm-faculty' ),
		'category'        => 'ugm-announcement-page-sections',
		'render_callback' => 'ugm_render_block_announcement_page',
		'supports'        => array( 'html' => false ),
		'attributes'      => array(
			'title'        => array( 'type' => 'string', 'default' => 'Pengumuman' ),
			'categorySlug' => array( 'type' => 'string', 'default' => 'pengumuman' ),
			'postsPerPage' => array( 'type' => 'number', 'default' => 7 ),
			'socialItems'  => array(
				'type'    => 'array',
				'default' => array(
					array(
						'enabled' => true,
						'type'    => 'facebook',
						'label'   => 'Facebook',
						'icon'    => 'F',
						'iconImageId'  => 0,
						'iconImageUrl' => '',
						'url'     => '',
						'color'   => '#315c9d',
					),
					array(
						'enabled' => true,
						'type'    => 'twitter',
						'label'   => 'Twitter / X',
						'icon'    => 'T',
						'iconImageId'  => 0,
						'iconImageUrl' => '',
						'url'     => '',
						'color'   => '#1da6d8',
					),
					array(
						'enabled' => true,
						'type'    => 'whatsapp',
						'label'   => 'WhatsApp',
						'icon'    => 'W',
						'iconImageId'  => 0,
						'iconImageUrl' => '',
						'url'     => '',
						'color'   => '#13b94f',
					),
				),
			),
			'showFacebook' => array( 'type' => 'boolean', 'default' => true ),
			'facebookUrl'  => array( 'type' => 'string', 'default' => '' ),
			'showTwitter'  => array( 'type' => 'boolean', 'default' => true ),
			'twitterUrl'   => array( 'type' => 'string', 'default' => '' ),
			'showWhatsapp' => array( 'type' => 'boolean', 'default' => true ),
			'whatsappUrl'  => array( 'type' => 'string', 'default' => '' ),
		),
	) );

	register_block_type( 'ugm/announcement-latest-news', array(
		'title'           => __( 'Pengumuman: Berita Terbaru', 'ugm-faculty' ),
		'description'     => __( 'Sidebar berita terbaru untuk halaman pengumuman.', 'ugm-faculty' ),
		'category'        => 'ugm-announcement-page-sections',
		'render_callback' => 'ugm_render_block_announcement_latest_news',
		'supports'        => array( 'html' => false ),
		'attributes'      => array(
			'title'        => array( 'type' => 'string', 'default' => 'Berita Terbaru' ),
			'postsPerPage' => array( 'type' => 'number', 'default' => 5 ),
		),
	) );

	register_block_type( 'ugm/announcement-latest-agenda', array(
		'title'           => __( 'Pengumuman: Agenda Terbaru', 'ugm-faculty' ),
		'description'     => __( 'Sidebar agenda terbaru untuk halaman pengumuman.', 'ugm-faculty' ),
		'category'        => 'ugm-announcement-page-sections',
		'render_callback' => 'ugm_render_block_announcement_latest_agenda',
		'supports'        => array( 'html' => false ),
		'attributes'      => array(
			'title'        => array( 'type' => 'string', 'default' => 'Agenda Terbaru' ),
			'categorySlug' => array( 'type' => 'string', 'default' => 'agenda' ),
			'postsPerPage' => array( 'type' => 'number', 'default' => 3 ),
			'buttonLabel'  => array( 'type' => 'string', 'default' => 'Semua Agenda' ),
			'buttonUrl'    => array( 'type' => 'string', 'default' => '/agenda/' ),
		),
	) );
} );
