<?php
/**
 * Dynamic block for the Berita Detail post template.
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ugmsb_primary_category( int $post_id ): ?WP_Term {
	$categories = get_the_category( $post_id );
	if ( empty( $categories ) ) {
		return null;
	}

	foreach ( $categories as $category ) {
		if ( 'uncategorized' !== $category->slug ) {
			return $category;
		}
	}

	return $categories[0];
}

function ugmsb_share_links( int $post_id ): array {
	$url   = rawurlencode( (string) get_permalink( $post_id ) );
	$title = rawurlencode( (string) get_the_title( $post_id ) );

	return array(
		'facebook' => array(
			'label' => __( 'Facebook', 'ugm-faculty' ),
			'url'   => 'https://www.facebook.com/sharer/sharer.php?u=' . $url,
			'text'  => 'f',
		),
		'twitter'  => array(
			'label' => __( 'Twitter', 'ugm-faculty' ),
			'url'   => 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title,
			'text'  => 't',
		),
		'whatsapp' => array(
			'label' => __( 'WhatsApp', 'ugm-faculty' ),
			'url'   => 'https://api.whatsapp.com/send?text=' . $title . '%20' . $url,
			'text'  => 'wa',
		),
	);
}

function ugmsb_render_share( array $links, string $modifier = '' ): string {
	$class = 'ugmsb-share';
	if ( '' !== $modifier ) {
		$class .= ' ugmsb-share--' . $modifier;
	}

	$output = '<div class="' . esc_attr( $class ) . '" aria-label="' . esc_attr__( 'Bagikan artikel', 'ugm-faculty' ) . '">';
	foreach ( $links as $network => $link ) {
		$output .= sprintf(
			'<a class="ugmsb-share__link ugmsb-share__link--%1$s" href="%2$s" target="_blank" rel="noopener noreferrer" aria-label="%3$s">%4$s</a>',
			esc_attr( $network ),
			esc_url( $link['url'] ),
			esc_attr( $link['label'] ),
			esc_html( $link['text'] )
		);
	}
	$output .= '</div>';

	return $output;
}

function ugmsb_render_post_list( WP_Query $query ): string {
	if ( ! $query->have_posts() ) {
		return '';
	}

	ob_start();
	?>
	<ul class="ugmsb-post-list" role="list">
		<?php while ( $query->have_posts() ) : $query->the_post(); ?>
		<li>
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></time>
		</li>
		<?php endwhile; ?>
	</ul>
	<?php
	wp_reset_postdata();

	return (string) ob_get_clean();
}

function ugmsb_render_single_berita_block( array $attrs, string $content, WP_Block $block ): string {
	$post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : (int) get_the_ID();
	$post    = get_post( $post_id );

	if ( ! $post instanceof WP_Post || 'post' !== $post->post_type ) {
		return '';
	}

	$primary_category = ugmsb_primary_category( $post_id );
	$category_ids     = wp_get_post_categories( $post_id );
	$share_links      = ugmsb_share_links( $post_id );
	$writer           = trim( (string) get_post_meta( $post_id, 'ugm_penulis', true ) );
	$editor           = trim( (string) get_post_meta( $post_id, 'ugm_editor', true ) );
	$photo_credit     = trim( (string) get_post_meta( $post_id, 'ugm_foto', true ) );

	if ( '' === $writer ) {
		$writer = get_the_author_meta( 'display_name', (int) $post->post_author );
	}

	$latest_query = new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => 5,
			'post__not_in'        => array( $post_id ),
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'no_found_rows'       => true,
		)
	);

	$related_args = array(
		'post_type'           => 'post',
		'posts_per_page'      => 3,
		'post__not_in'        => array( $post_id ),
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'no_found_rows'       => true,
	);

	if ( ! empty( $category_ids ) ) {
		$related_args['category__in'] = $category_ids;
	}

	$related_query = new WP_Query( $related_args );
	$agenda_cpt    = post_type_exists( 'ugm_event' ) ? 'ugm_event' : 'post';
	$agenda_query  = new WP_Query(
		array(
			'post_type'           => $agenda_cpt,
			'posts_per_page'      => 1,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'no_found_rows'       => true,
		)
	);

	$GLOBALS['post'] = $post;
	setup_postdata( $post );

	ob_start();
	?>
	<main id="primary" class="site-main ugmsb-page">
		<article id="post-<?php echo esc_attr( (string) $post_id ); ?>" <?php post_class( 'ugmsb-article', $post_id ); ?>>
			<div class="ugmsb-container">
				<nav class="ugmsb-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'ugm-faculty' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Berita', 'ugm-faculty' ); ?></a>
					<?php if ( $primary_category instanceof WP_Term ) : ?>
						<span aria-hidden="true">&gt;</span>
						<a href="<?php echo esc_url( get_category_link( $primary_category->term_id ) ); ?>"><?php echo esc_html( $primary_category->name ); ?></a>
					<?php endif; ?>
				</nav>

				<header class="ugmsb-header">
					<h1 class="ugmsb-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
					<div class="ugmsb-meta">
						<?php if ( $primary_category instanceof WP_Term ) : ?>
						<span class="ugmsb-meta__item ugmsb-meta__item--category"><?php echo esc_html( $primary_category->name ); ?></span>
						<?php endif; ?>
						<time class="ugmsb-meta__item ugmsb-meta__item--date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post_id ) ); ?>">
							<?php echo esc_html( get_the_date( 'j F Y, H.i', $post_id ) ); ?>
						</time>
						<span class="ugmsb-meta__item ugmsb-meta__item--author"><?php printf( esc_html__( 'Oleh : %s', 'ugm-faculty' ), esc_html( get_the_author_meta( 'display_name', (int) $post->post_author ) ) ); ?></span>
					</div>
				</header>

				<?php if ( has_post_thumbnail( $post_id ) ) : ?>
				<figure class="ugmsb-featured">
					<?php echo get_the_post_thumbnail( $post_id, 'large', array( 'class' => 'ugmsb-featured__image' ) ); ?>
				</figure>
				<?php endif; ?>

				<div class="ugmsb-layout">
					<div class="ugmsb-content-wrap">
						<?php echo ugmsb_render_share( $share_links, 'vertical' ); ?>
						<div class="ugmsb-content">
							<?php echo apply_filters( 'the_content', $post->post_content ); ?>

							<div class="ugmsb-credits">
								<?php if ( '' !== $writer ) : ?>
								<p><?php printf( esc_html__( 'Penulis : %s', 'ugm-faculty' ), esc_html( $writer ) ); ?></p>
								<?php endif; ?>
								<?php if ( '' !== $editor ) : ?>
								<p><?php printf( esc_html__( 'Editor : %s', 'ugm-faculty' ), esc_html( $editor ) ); ?></p>
								<?php endif; ?>
								<?php if ( '' !== $photo_credit ) : ?>
								<p><?php printf( esc_html__( 'Foto : %s', 'ugm-faculty' ), esc_html( $photo_credit ) ); ?></p>
								<?php endif; ?>
							</div>

							<?php $tags = get_the_tags( $post_id ); ?>
							<?php if ( ! empty( $tags ) ) : ?>
							<div class="ugmsb-tags">
								<span><?php esc_html_e( 'TAGS:', 'ugm-faculty' ); ?></span>
								<?php foreach ( $tags as $tag ) : ?>
								<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>"><?php echo esc_html( $tag->name ); ?></a>
								<?php endforeach; ?>
							</div>
							<?php endif; ?>

							<?php echo ugmsb_render_share( $share_links, 'horizontal' ); ?>

							<?php if ( $related_query->have_posts() ) : ?>
							<section class="ugmsb-related" aria-labelledby="ugmsb-related-title">
								<h2 id="ugmsb-related-title"><?php esc_html_e( 'Berita Terkait', 'ugm-faculty' ); ?></h2>
								<?php echo ugmsb_render_post_list( $related_query ); ?>
							</section>
							<?php endif; ?>
						</div>
					</div>

					<aside class="ugmsb-sidebar" aria-label="<?php esc_attr_e( 'Sidebar berita', 'ugm-faculty' ); ?>">
						<section class="ugmsb-widget" aria-labelledby="ugmsb-latest-title">
							<h2 id="ugmsb-latest-title"><?php esc_html_e( 'Berita Terbaru', 'ugm-faculty' ); ?></h2>
							<?php echo ugmsb_render_post_list( $latest_query ); ?>
						</section>

						<?php if ( $agenda_query->have_posts() ) : ?>
						<section class="ugmsb-widget ugmsb-widget--agenda" aria-labelledby="ugmsb-agenda-title">
							<h2 id="ugmsb-agenda-title"><?php esc_html_e( 'Agenda Terbaru', 'ugm-faculty' ); ?></h2>
							<?php while ( $agenda_query->have_posts() ) : $agenda_query->the_post(); ?>
							<div class="ugmsb-agenda">
								<div class="ugmsb-agenda__date" aria-label="<?php echo esc_attr( get_the_date( 'j F Y' ) ); ?>">
									<span><?php echo esc_html( get_the_date( 'j' ) ); ?></span>
									<small><?php echo esc_html( get_the_date( 'M' ) ); ?></small>
								</div>
								<a class="ugmsb-agenda__title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</div>
							<?php endwhile; wp_reset_postdata(); ?>
							<a class="ugmsb-agenda__button" href="<?php echo esc_url( home_url( '/agenda/' ) ); ?>"><?php esc_html_e( 'Semua Agenda', 'ugm-faculty' ); ?> &rarr;</a>
						</section>
						<?php endif; ?>
					</aside>
				</div>
			</div>
		</article>
	</main>
	<?php
	wp_reset_postdata();

	return (string) ob_get_clean();
}

add_action(
	'init',
	static function () {
		register_block_type(
			'ugm/single-berita-content',
			array(
				'title'           => __( 'Konten Berita Detail', 'ugm-faculty' ),
				'description'     => __( 'Render layout lengkap untuk template post Berita Detail.', 'ugm-faculty' ),
				'category'        => 'ugm-sections',
				'icon'            => 'media-document',
				'render_callback' => 'ugmsb_render_single_berita_block',
				'supports'        => array( 'html' => false ),
				'uses_context'    => array( 'postId', 'postType' ),
			)
		);
	}
);
