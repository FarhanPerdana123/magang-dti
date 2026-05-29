<?php
/**
 * Agenda page template helpers and block registration.
 *
 * Keeps the full Agenda Page implementation separate from the landing-page
 * section blocks so each template has a focused module.
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve the preferred public URL for the full agenda page.
 *
 * @return string
 */
function ugm_get_agenda_page_url() {
	$pages = get_pages(
		array(
			'meta_query'  => array(
				'relation' => 'OR',
				array(
					'key'   => '_wp_page_template',
					'value' => 'page-templates/template-agenda.php',
				),
				array(
					'key'   => '_wp_page_template',
					'value' => 'agenda-page',
				),
			),
			'number'      => 1,
			'post_status' => 'publish',
		)
	);

	if ( ! empty( $pages ) && $pages[0] instanceof WP_Post ) {
		return get_permalink( $pages[0]->ID );
	}

	$agenda_page = get_page_by_path( 'agenda' );
	if ( $agenda_page instanceof WP_Post ) {
		return get_permalink( $agenda_page->ID );
	}

	return home_url( '/' );
}

/**
 * Frontend: render the block-template slug through the PHP page template.
 *
 * The Site Editor template picker stores block templates as short slugs
 * (agenda-page). In the editor we still want the content-only canvas, but on
 * the public site the page must go through get_header()/get_footer().
 *
 * @param string $template Resolved template path.
 * @return string
 */
function ugm_use_php_agenda_template_on_frontend( $template ) {
	if ( is_admin() || ! is_page() ) {
		return $template;
	}

	$page_id = (int) get_queried_object_id();
	if ( $page_id <= 0 || ! ugm_is_agenda_page_template_slug( get_page_template_slug( $page_id ) ) ) {
		return $template;
	}

	$php_template = get_theme_file_path( 'page-templates/template-agenda.php' );
	return file_exists( $php_template ) ? $php_template : $template;
}
add_filter( 'template_include', 'ugm_use_php_agenda_template_on_frontend', 20 );

/**
 * Add a stable body class for agenda pages.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function ugm_agenda_page_body_class( $classes ) {
	if ( is_page() && ugm_is_agenda_page_template_slug( get_page_template_slug( get_queried_object_id() ) ) ) {
		$classes[] = 'ugm-is-agenda-page';
	}

	return $classes;
}
add_filter( 'body_class', 'ugm_agenda_page_body_class' );

/**
 * Return the default block markup used by the Agenda page template.
 *
 * @return string
 */
function ugm_get_default_agenda_page_blocks() {
	return '<!-- wp:ugm/agenda-list-page {"title":"Agenda","categorySlug":"agenda","postsPerPage":12} /-->';
}

/**
 * Return the saved Site Editor block template for the Agenda page.
 *
 * @return string
 */
function ugm_get_agenda_block_template_content() {
	if ( ! function_exists( 'get_block_template' ) ) {
		return '';
	}

	$template = get_block_template( get_stylesheet() . '//agenda-page', 'wp_template' );
	if ( ! $template || empty( $template->content ) || ! is_string( $template->content ) ) {
		return '';
	}

	return trim( $template->content );
}

/**
 * Resolve the block source that should be rendered by the PHP wrapper template.
 *
 * Pages using the Site Editor template slug should render the saved wp_template
 * content, while pages using the legacy PHP template render their own content.
 *
 * @param string $page_content Page post_content.
 * @param string $template_slug Page template slug/path.
 * @return string
 */
function ugm_get_agenda_render_source( $page_content = '', $template_slug = '' ) {
	$template_slug = (string) $template_slug;

	if ( 'agenda-page' === $template_slug ) {
		$template_content = ugm_get_agenda_block_template_content();
		if ( '' !== $template_content && false !== strpos( $template_content, '<!-- wp:' ) ) {
			return $template_content;
		}
	}

	$page_content = (string) $page_content;
	if ( false !== strpos( $page_content, '<!-- wp:' ) || '' !== trim( wp_strip_all_tags( strip_shortcodes( $page_content ) ) ) ) {
		return $page_content;
	}

	return ugm_get_default_agenda_page_blocks();
}

/**
 * Check whether a template slug refers to an Agenda Page template variant.
 *
 * @param string $template Template slug/path.
 * @return bool
 */
function ugm_is_agenda_page_template_slug( $template ) {
	return in_array(
		(string) $template,
		array(
			'page-templates/template-agenda.php',
			'agenda-page',
		),
		true
	);
}

/**
 * Resolve the agenda label configured on the landing page section.
 *
 * @param string $fallback Fallback label.
 * @return string
 */
function ugm_get_landing_agenda_section_title( $fallback = 'Agenda' ) {
	$fallback = trim( (string) $fallback );
	if ( '' === $fallback ) {
		$fallback = __( 'Agenda', 'ugm-faculty' );
	}

	$page_ids = array();
	$front_id = (int) get_option( 'page_on_front' );
	if ( $front_id > 0 ) {
		$page_ids[] = $front_id;
	}

	$landing_ids = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => 5,
			'fields'         => 'ids',
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'   => '_wp_page_template',
					'value' => 'page-templates/template-landing-page.php',
				),
				array(
					'key'   => '_wp_page_template',
					'value' => 'landing-page',
				),
			),
		)
	);

	$page_ids = array_values( array_unique( array_merge( $page_ids, $landing_ids ) ) );

	foreach ( $page_ids as $page_id ) {
		$page = get_post( $page_id );
		if ( ! $page instanceof WP_Post ) {
			continue;
		}

		foreach ( parse_blocks( (string) $page->post_content ) as $block ) {
			$block_name = $block['blockName'] ?? '';
			if ( ! in_array( $block_name, array( 'ugm/agenda-section', 'ugm/agenda-only' ), true ) ) {
				continue;
			}

			$title = trim( (string) ( $block['attrs']['title'] ?? '' ) );
			if ( '' !== $title ) {
				return $title;
			}
		}
	}

	return $fallback;
}

/**
 * Register agenda metadata so it can be edited from the block editor.
 *
 * @return void
 */
function ugm_register_agenda_post_meta() {
	$meta_fields = array(
		'agenda_event_date',
		'agenda_event_time',
		'agenda_event_end_date',
		'agenda_location',
		'agenda_event_type',
	);

	foreach ( $meta_fields as $meta_key ) {
		register_post_meta(
			'post',
			$meta_key,
			array(
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
			)
		);
	}
}
add_action( 'init', 'ugm_register_agenda_post_meta' );

/**
 * Get a timestamp for the agenda event date metadata.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function ugm_get_agenda_event_timestamp( $post_id ) {
	$post_id = absint( $post_id );
	$date    = trim( (string) get_post_meta( $post_id, 'agenda_event_date', true ) );
	$time    = trim( (string) get_post_meta( $post_id, 'agenda_event_time', true ) );

	if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
		$time      = preg_match( '/^\d{2}:\d{2}$/', $time ) ? $time : '00:00';
		$timestamp = strtotime( $date . ' ' . $time );

		if ( false !== $timestamp ) {
			return (int) $timestamp;
		}
	}

	return (int) get_post_timestamp( $post_id );
}

/**
 * Get the agenda event date text for card metadata.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ugm_get_agenda_event_date_text( $post_id ) {
	$post_id  = absint( $post_id );
	$date     = trim( (string) get_post_meta( $post_id, 'agenda_event_date', true ) );
	$time     = trim( (string) get_post_meta( $post_id, 'agenda_event_time', true ) );
	$end_date = trim( (string) get_post_meta( $post_id, 'agenda_event_end_date', true ) );

	if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
		$date_text = $date;

		if ( preg_match( '/^\d{2}:\d{2}$/', $time ) ) {
			$date_text .= ' @ ' . $time;
		}

		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $end_date ) && $end_date !== $date ) {
			$date_text .= ' - ' . $end_date;
		}

		return $date_text;
	}

	return trim( get_the_date( 'Y-m-d', $post_id ) . ' ' . get_the_time( 'H:i', $post_id ) );
}

/**
 * Get the agenda event location with backwards-compatible meta fallbacks.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ugm_get_agenda_event_location( $post_id ) {
	$post_id  = absint( $post_id );
	$location = trim( (string) get_post_meta( $post_id, 'agenda_location', true ) );

	if ( '' === $location ) {
		$location = trim( (string) get_post_meta( $post_id, '_agenda_location', true ) );
	}
	if ( '' === $location ) {
		$location = trim( (string) get_post_meta( $post_id, 'location', true ) );
	}
	if ( '' === $location ) {
		$location = __( 'Acara Online', 'ugm-faculty' );
	}

	return $location;
}

/**
 * Check whether a post should be treated as an agenda item.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function ugm_is_agenda_post( $post_id ) {
	$post_id = absint( $post_id );
	if ( $post_id <= 0 || 'post' !== get_post_type( $post_id ) ) {
		return false;
	}

	foreach ( get_the_category( $post_id ) as $category ) {
		if ( in_array( $category->slug, array( 'agenda', 'agenda-2', 'agenda-3', 'kegiatan', 'events', 'event' ), true ) ) {
			return true;
		}
	}

	foreach ( array( 'agenda_event_date', 'agenda_location', 'agenda_event_type' ) as $meta_key ) {
		if ( '' !== trim( (string) get_post_meta( $post_id, $meta_key, true ) ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Get the agenda event type label with a category fallback.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ugm_get_agenda_event_type_label( $post_id ) {
	$post_id    = absint( $post_id );
	$type_label = trim( (string) get_post_meta( $post_id, 'agenda_event_type', true ) );

	if ( '' !== $type_label ) {
		return $type_label;
	}

	$type_label = __( 'Seminar or Talk', 'ugm-faculty' );
	$categories = get_the_category( $post_id );

	if ( ! empty( $categories ) ) {
		foreach ( $categories as $category ) {
			if ( ! in_array( $category->slug, array( 'agenda', 'agenda-2', 'agenda-3', 'kegiatan', 'events', 'event' ), true ) ) {
				$type_label = $category->name;
				break;
			}
		}
	}

	return $type_label;
}

/**
 * Populate an empty Agenda Page with the default agenda listing block.
 *
 * @param int $post_id Page ID.
 * @return bool True when content was updated.
 */
function ugm_populate_empty_agenda_page( $post_id ) {
	$post_id = absint( $post_id );
	if ( $post_id <= 0 || 'page' !== get_post_type( $post_id ) ) {
		return false;
	}

	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post || '' !== trim( (string) $post->post_content ) ) {
		return false;
	}

	if ( ! ugm_is_agenda_page_template_slug( get_page_template_slug( $post_id ) ) ) {
		return false;
	}

	remove_action( 'save_post_page', 'ugm_seed_agenda_page_on_save', 20 );
	wp_update_post(
		array(
			'ID'           => $post_id,
			'post_content' => ugm_get_default_agenda_page_blocks(),
		)
	);
	add_action( 'save_post_page', 'ugm_seed_agenda_page_on_save', 20, 3 );

	return true;
}

/**
 * Seed Agenda Page content after template selection is saved.
 *
 * @param int     $post_id Page ID.
 * @param WP_Post $post    Post object.
 * @param bool    $update  Whether this is an update.
 * @return void
 */
function ugm_seed_agenda_page_on_save( $post_id, $post, $update ) {
	unset( $post, $update );

	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}

	ugm_populate_empty_agenda_page( $post_id );
}
add_action( 'save_post_page', 'ugm_seed_agenda_page_on_save', 20, 3 );

/**
 * Repair existing empty pages that already use the Agenda Page template.
 *
 * @return void
 */
function ugm_seed_existing_empty_agenda_pages() {
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
					'value' => 'page-templates/template-agenda.php',
				),
				array(
					'key'   => '_wp_page_template',
					'value' => 'agenda-page',
				),
			),
		)
	);

	foreach ( $pages as $page_id ) {
		ugm_populate_empty_agenda_page( $page_id );
	}
}
add_action( 'admin_init', 'ugm_seed_existing_empty_agenda_pages' );

/**
 * Render one card for the full agenda listing.
 *
 * @return void
 */
function ugm_render_agenda_listing_card() {
	$post_id   = get_the_ID();
	$timestamp = ugm_get_agenda_event_timestamp( $post_id );
	$date_text = ugm_get_agenda_event_date_text( $post_id );
	$location  = ugm_get_agenda_event_location( $post_id );
	$type_label = ugm_get_agenda_event_type_label( $post_id );
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'ugm-agenda-card card h-100 rounded-0' ); ?>>
		<?php /* Badge diposisikan di luar <a> agar menjadi anak langsung article */ ?>
		<span class="ugm-agenda-card__date" aria-hidden="true">
			<strong><?php echo esc_html( wp_date( 'd', $timestamp ) ); ?></strong>
			<span><?php echo esc_html( strtoupper( wp_date( 'M', $timestamp ) ) ); ?></span>
		</span>
		<a class="ugm-agenda-card__media-link" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
			<div class="ugm-agenda-card__media">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'medium_large' ); ?>
				<?php else : ?>
					<div class="ugm-agenda-card__placeholder" aria-hidden="true"></div>
				<?php endif; ?>
			</div>
		</a>
		<div class="ugm-agenda-card__body card-body">
			<h2 class="ugm-agenda-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<ul class="ugm-agenda-card__meta list-unstyled">
				<li>
					<span class="ugm-agenda-card__meta-icon" aria-hidden="true">&#9711;</span>
					<?php echo esc_html( $date_text ); ?>
				</li>
				<li>
					<span class="ugm-agenda-card__meta-icon" aria-hidden="true">&#9906;</span>
					<?php echo esc_html( $location ); ?>
				</li>
			</ul>
			<span class="ugm-agenda-card__tag"><?php echo esc_html( $type_label ); ?></span>
		</div>
	</article>
	<?php
}

/**
 * Render the full agenda page block.
 *
 * @param array $attrs Block attributes.
 * @return string
 */
function ugm_render_block_agenda_list_page( $attrs ) {
	$attrs          = is_array( $attrs ) ? $attrs : array();
	$title          = ugm_resolve_section_title( $attrs, __( 'Agenda', 'ugm-faculty' ) );
	$category_slug  = trim( (string) ( $attrs['categorySlug'] ?? 'agenda' ) );
	$posts_per_page = max( 1, min( 24, absint( $attrs['postsPerPage'] ?? 12 ) ) );
	$paged          = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

	$keyword = isset( $_GET['agenda_keyword'] ) ? sanitize_text_field( wp_unslash( $_GET['agenda_keyword'] ) ) : '';
	if ( '' === $keyword && isset( $_GET['agenda_keyword_mobile'] ) ) {
		$keyword = sanitize_text_field( wp_unslash( $_GET['agenda_keyword_mobile'] ) );
	}

	$location      = isset( $_GET['agenda_location'] ) ? sanitize_text_field( wp_unslash( $_GET['agenda_location'] ) ) : '';
	$date_range    = isset( $_GET['agenda_date_range'] ) ? sanitize_key( wp_unslash( $_GET['agenda_date_range'] ) ) : '';
	$category_pick = isset( $_GET['agenda_category'] ) ? sanitize_key( wp_unslash( $_GET['agenda_category'] ) ) : '';
	$type_pick     = isset( $_GET['agenda_type'] ) ? sanitize_key( wp_unslash( $_GET['agenda_type'] ) ) : '';

	$agenda_term_ids = ugm_resolve_agenda_exclude_ids( $category_slug );
	$tax_ids         = $agenda_term_ids;

	foreach ( array( $category_pick, $type_pick ) as $picked_slug ) {
		if ( '' === $picked_slug ) {
			continue;
		}

		$picked_ids = ugm_resolve_multiple_slugs_to_ids( array( $picked_slug ) );
		if ( ! empty( $picked_ids ) ) {
			$tax_ids = empty( $tax_ids ) ? $picked_ids : array_values( array_intersect( $tax_ids, $picked_ids ) );
		}
	}

	$query_args = array(
		'post_type'           => 'post',
		'posts_per_page'      => $posts_per_page,
		'paged'               => $paged,
		'ignore_sticky_posts' => true,
		'post_status'         => 'publish',
		'orderby'             => 'date',
		'order'               => 'DESC',
	);

	if ( '' !== $keyword ) {
		$query_args['s'] = $keyword;
	}

	if ( ! empty( $tax_ids ) ) {
		$query_args['category__in'] = $tax_ids;
	} else {
		$query_args['post__in'] = array( 0 );
	}

	if ( '' !== $location ) {
		$query_args['meta_query'] = array(
			'relation' => 'OR',
			array(
				'key'     => 'agenda_location',
				'value'   => $location,
				'compare' => 'LIKE',
			),
			array(
				'key'     => '_agenda_location',
				'value'   => $location,
				'compare' => 'LIKE',
			),
			array(
				'key'     => 'location',
				'value'   => $location,
				'compare' => 'LIKE',
			),
		);
	}

	if ( 'this-month' === $date_range ) {
		$query_args['date_query'] = array(
			array(
				'after'     => wp_date( 'Y-m-01 00:00:00' ),
				'before'    => wp_date( 'Y-m-t 23:59:59' ),
				'inclusive' => true,
			),
		);
	} elseif ( 'upcoming' === $date_range ) {
		$query_args['date_query'] = array(
			array(
				'after'     => wp_date( 'Y-m-d 00:00:00' ),
				'inclusive' => true,
			),
		);
		$query_args['order'] = 'ASC';
	} elseif ( 'past' === $date_range ) {
		$query_args['date_query'] = array(
			array(
				'before'    => wp_date( 'Y-m-d 00:00:00' ),
				'inclusive' => false,
			),
		);
	}

	$agenda_query = new WP_Query( $query_args );
	$breadcrumb_label = ugm_get_landing_agenda_section_title( $title );
	$category_options = ! empty( $agenda_term_ids ) ? get_categories(
		array(
			'include'    => $agenda_term_ids,
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	) : array();

	ob_start();
	?>
	<section class="ugm-agenda-page-section" aria-labelledby="ugm-agenda-page-title">
		<nav class="ugm-agenda-page__breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'ugm-faculty' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Berita', 'ugm-faculty' ); ?></a>
			<span aria-hidden="true">&#8250;</span>
			<span aria-current="page"><?php echo esc_html( $breadcrumb_label ); ?></span>
		</nav>

		<header class="ugm-agenda-page__header">
			<h1 id="ugm-agenda-page-title" class="ugm-agenda-page__title"><?php echo esc_html( $title ); ?></h1>
			<span class="ugm-agenda-page__line" aria-hidden="true"></span>
		</header>

		<form class="ugm-agenda-filter" action="<?php echo esc_url( get_permalink() ); ?>" method="get">
			<div class="ugm-agenda-filter__mobile input-group">
				<input class="form-control" type="search" name="agenda_keyword_mobile" value="<?php echo esc_attr( $keyword ); ?>" placeholder="<?php esc_attr_e( 'Pencarian Agenda...', 'ugm-faculty' ); ?>">
				<button class="btn btn-warning" type="submit" aria-label="<?php esc_attr_e( 'Cari agenda', 'ugm-faculty' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
						<path d="M10.8 4.5a6.3 6.3 0 1 1 0 12.6 6.3 6.3 0 0 1 0-12.6zm0-3a9.3 9.3 0 0 0-7.33 15.02 9.3 9.3 0 0 0 12.02 1.08l4.45 4.46 2.12-2.12-4.46-4.45A9.3 9.3 0 0 0 10.8 1.5z"/>
					</svg>
				</button>
			</div>

			<div class="row g-3">
				<div class="col-12 col-md-4">
					<input class="form-control" type="search" name="agenda_keyword" value="<?php echo esc_attr( $keyword ); ?>" placeholder="<?php esc_attr_e( 'Kata kunci', 'ugm-faculty' ); ?>">
				</div>
				<div class="col-12 col-md-4">
					<input class="form-control" type="text" name="agenda_location" value="<?php echo esc_attr( $location ); ?>" placeholder="<?php esc_attr_e( 'Lokasi', 'ugm-faculty' ); ?>">
				</div>
				<div class="col-12 col-md-4">
					<select class="form-select" name="agenda_date_range" onchange="this.form.submit()">
						<option value=""><?php esc_html_e( 'Select Date Range', 'ugm-faculty' ); ?></option>
						<option value="upcoming" <?php selected( $date_range, 'upcoming' ); ?>><?php esc_html_e( 'Upcoming', 'ugm-faculty' ); ?></option>
						<option value="this-month" <?php selected( $date_range, 'this-month' ); ?>><?php esc_html_e( 'This Month', 'ugm-faculty' ); ?></option>
						<option value="past" <?php selected( $date_range, 'past' ); ?>><?php esc_html_e( 'Past', 'ugm-faculty' ); ?></option>
					</select>
				</div>
				<div class="col-12 col-md-6">
					<select class="form-select" name="agenda_category" onchange="this.form.submit()">
						<option value=""><?php esc_html_e( 'Choose an Event Category', 'ugm-faculty' ); ?></option>
						<?php foreach ( $category_options as $term ) : ?>
							<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $category_pick, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-12 col-md-6">
					<select class="form-select" name="agenda_type" onchange="this.form.submit()">
						<option value=""><?php esc_html_e( 'Choose an Event Type', 'ugm-faculty' ); ?></option>
						<?php foreach ( $category_options as $term ) : ?>
							<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $type_pick, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<noscript><button class="btn btn-primary mt-3" type="submit"><?php esc_html_e( 'Terapkan Filter', 'ugm-faculty' ); ?></button></noscript>
		</form>

		<h2 class="ugm-agenda-page__subheading"><?php esc_html_e( 'Acara-acara', 'ugm-faculty' ); ?></h2>

		<?php if ( $agenda_query->have_posts() ) : ?>
			<div class="ugm-agenda-grid row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
				<?php while ( $agenda_query->have_posts() ) : ?>
					<?php $agenda_query->the_post(); ?>
					<div class="col">
						<?php ugm_render_agenda_listing_card(); ?>
					</div>
				<?php endwhile; ?>
			</div>

			<nav class="ugm-agenda-pagination" aria-label="<?php esc_attr_e( 'Agenda navigation', 'ugm-faculty' ); ?>">
				<?php
				echo paginate_links(
					array(
						'total'     => max( 1, (int) $agenda_query->max_num_pages ),
						'current'   => $paged,
						'mid_size'  => 1,
						'prev_text' => '&#8592;',
						'next_text' => '&#8594;',
						'add_args'  => array_filter(
							array(
								'agenda_keyword'    => $keyword,
								'agenda_location'   => $location,
								'agenda_date_range' => $date_range,
								'agenda_category'   => $category_pick,
								'agenda_type'       => $type_pick,
							)
						),
					)
				); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</nav>
		<?php else : ?>
			<p class="section-empty"><?php esc_html_e( 'Belum ada agenda yang sesuai filter.', 'ugm-faculty' ); ?></p>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
	</section>
	<?php

	return ob_get_clean();
}

add_action( 'init', function () {
	register_block_type( 'ugm/agenda-list-page', array(
		'title'           => __( 'Daftar Agenda Lengkap', 'ugm-faculty' ),
		'description'     => __( 'Halaman daftar agenda lengkap dengan filter dan pagination.', 'ugm-faculty' ),
		'category'        => 'ugm-agenda-page-sections',
		'render_callback' => 'ugm_render_block_agenda_list_page',
		'supports'        => array( 'html' => false ),
		'attributes'      => array(
			'title'        => array( 'type' => 'string', 'default' => 'Agenda' ),
			'categorySlug' => array( 'type' => 'string', 'default' => 'agenda' ),
			'postsPerPage' => array( 'type' => 'number', 'default' => 12 ),
		),
	) );
} );
