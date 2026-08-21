<?php
/**
 * Helper functions for front-page query building.
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Find first category term by preferred slugs.
 *
 * @param string[] $slugs Category slugs sorted by priority.
 * @return WP_Term|null
 */
function ugm_get_category_root_by_slugs( $slugs ) {
	foreach ( (array) $slugs as $slug ) {
		$term = get_category_by_slug( (string) $slug );
		if ( $term instanceof WP_Term ) {
			return $term;
		}
	}

	return null;
}

/**
 * Get root category ID plus all descendant category IDs.
 *
 * @param int $root_id Root category term ID.
 * @return int[]
 */
function ugm_get_category_tree_ids( $root_id ) {
	$root_id = absint( $root_id );

	if ( $root_id < 1 ) {
		return array();
	}

	$term_ids = array( $root_id );
	$children = get_term_children( $root_id, 'category' );

	if ( ! is_wp_error( $children ) && ! empty( $children ) ) {
		$term_ids = array_merge( $term_ids, array_map( 'absint', $children ) );
	}

	return array_values( array_unique( array_filter( $term_ids ) ) );
}

/**
 * Get uploaded PDF URL for magazine post.
 *
 * Accepts meta value as attachment ID or direct URL.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ugm_get_magazine_pdf_url( $post_id ) {
	$post_id = absint( $post_id );
	if ( $post_id < 1 ) {
		return '';
	}

	$pdf_value = get_post_meta( $post_id, '_ugm_majalah_pdf', true );
	if ( '' === $pdf_value || null === $pdf_value ) {
		// Backward compatibility for previous key.
		$pdf_value = get_post_meta( $post_id, '_ugm_magazine_pdf', true );
	}

	if ( '' === $pdf_value || null === $pdf_value ) {
		return '';
	}

	if ( is_numeric( $pdf_value ) && (int) $pdf_value > 0 ) {
		$url = wp_get_attachment_url( (int) $pdf_value );
		return $url ? esc_url_raw( $url ) : '';
	}

	return esc_url_raw( (string) $pdf_value );
}

/**
 * Get edition text for magazine post.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ugm_get_magazine_edition( $post_id ) {
	$post_id = absint( $post_id );
	if ( $post_id < 1 ) {
		return '';
	}

	$edition = get_post_meta( $post_id, '_ugm_majalah_edisi', true );
	return is_string( $edition ) ? sanitize_text_field( $edition ) : '';
}

/**
 * Render a visual skeleton placeholder for empty landing page sections.
 *
 * @param string $layout  Skeleton layout key.
 * @param string $message Accessible fallback message.
 * @param array  $args    Optional configuration.
 * @return string
 */
function ugm_render_empty_skeleton( $layout, $message = '', $args = array() ) {
	$layout = sanitize_key( (string) $layout );
	$args   = wp_parse_args(
		is_array( $args ) ? $args : array(),
		array(
			'count' => 4,
		)
	);
	$count  = max( 1, absint( $args['count'] ) );

	ob_start();
	?>
	<div class="ugm-empty-skeleton ugm-empty-skeleton--<?php echo esc_attr( $layout ); ?>">
		<?php if ( '' !== $message ) : ?>
			<span class="screen-reader-text"><?php echo esc_html( $message ); ?></span>
		<?php endif; ?>

		<?php if ( 'latest-news' === $layout ) : ?>
			<div class="ugm-skeleton-news" aria-hidden="true">
				<div class="ugm-skeleton-news__featured">
					<span class="ugm-skeleton-box ugm-skeleton-box--media"></span>
					<div class="ugm-skeleton-news__content">
						<span class="ugm-skeleton-line ugm-skeleton-line--short"></span>
						<span class="ugm-skeleton-line ugm-skeleton-line--title"></span>
						<span class="ugm-skeleton-line ugm-skeleton-line--meta"></span>
						<span class="ugm-skeleton-line"></span>
						<span class="ugm-skeleton-line ugm-skeleton-line--wide"></span>
					</div>
				</div>
				<div class="ugm-skeleton-news__list">
					<?php for ( $i = 0; $i < 3; $i++ ) : ?>
						<div class="ugm-skeleton-card ugm-skeleton-card--news">
							<span class="ugm-skeleton-box ugm-skeleton-box--thumb"></span>
							<div class="ugm-skeleton-card__body">
								<span class="ugm-skeleton-line ugm-skeleton-line--short"></span>
								<span class="ugm-skeleton-line"></span>
								<span class="ugm-skeleton-line ugm-skeleton-line--meta"></span>
							</div>
						</div>
					<?php endfor; ?>
				</div>
			</div>
		<?php elseif ( 'portal-column' === $layout ) : ?>
			<div class="portal-column portal-column--skeleton" aria-hidden="true">
				<article class="portal-card portal-card--featured portal-card--skeleton">
					<div class="portal-card__media portal-card__media--skeleton">
						<span class="ugm-skeleton-box ugm-skeleton-box--thumb"></span>
					</div>
					<div class="portal-card__body">
						<span class="ugm-skeleton-line ugm-skeleton-line--title"></span>
						<span class="ugm-skeleton-line ugm-skeleton-line--wide"></span>
						<span class="ugm-skeleton-line ugm-skeleton-line--meta"></span>
					</div>
				</article>
				<div class="portal-column__list">
					<?php for ( $i = 0; $i < 2; $i++ ) : ?>
						<article class="portal-list-card portal-list-card--skeleton">
							<div class="portal-list-card__media portal-list-card__media--skeleton">
								<span class="ugm-skeleton-box ugm-skeleton-box--mini-thumb"></span>
							</div>
							<div class="portal-list-card__body">
								<span class="ugm-skeleton-line ugm-skeleton-line--title"></span>
								<span class="ugm-skeleton-line"></span>
								<span class="ugm-skeleton-line ugm-skeleton-line--meta"></span>
							</div>
						</article>
					<?php endfor; ?>
				</div>
			</div>
		<?php elseif ( 'featured-category' === $layout ) : ?>
			<div class="ugm-skeleton-featured-category" aria-hidden="true">
				<span class="ugm-skeleton-box ugm-skeleton-box--featured-category"></span>
				<div class="ugm-skeleton-featured-category__body">
					<span class="ugm-skeleton-line ugm-skeleton-line--title"></span>
					<span class="ugm-skeleton-line"></span>
					<span class="ugm-skeleton-line ugm-skeleton-line--wide"></span>
					<span class="ugm-skeleton-line ugm-skeleton-line--short"></span>
				</div>
			</div>
		<?php elseif ( 'category-grid' === $layout ) : ?>
			<div class="category-grid category-grid--skeleton" aria-hidden="true">
				<?php for ( $i = 0; $i < $count; $i++ ) : ?>
					<div class="category-card category-card--skeleton">
						<span class="ugm-skeleton-line ugm-skeleton-line--medium"></span>
						<span class="ugm-skeleton-line ugm-skeleton-line--meta"></span>
					</div>
				<?php endfor; ?>
			</div>
		<?php elseif ( 'faculty-grid' === $layout ) : ?>
			<div class="faculty-slider-wrapper faculty-slider-wrapper--skeleton" aria-hidden="true">
				<div class="faculty-page faculty-page--skeleton">
					<?php for ( $i = 0; $i < $count; $i++ ) : ?>
						<div class="faculty-card faculty-card--skeleton">
							<span class="ugm-skeleton-box ugm-skeleton-box--faculty"></span>
						</div>
					<?php endfor; ?>
				</div>
			</div>
		<?php elseif ( 'agenda-list' === $layout ) : ?>
			<div class="desktop-agenda-list desktop-agenda-list--skeleton" aria-hidden="true">
				<?php for ( $i = 0; $i < $count; $i++ ) : ?>
					<div class="desktop-agenda-card desktop-agenda-card--skeleton">
						<span class="ugm-skeleton-box ugm-skeleton-box--agenda-date"></span>
						<div class="desktop-agenda-card__body">
							<span class="ugm-skeleton-line"></span>
							<span class="ugm-skeleton-line ugm-skeleton-line--meta"></span>
						</div>
					</div>
				<?php endfor; ?>
			</div>
		<?php elseif ( 'facility-grid' === $layout ) : ?>
			<div class="desktop-facility-grid desktop-facility-grid--count-4 desktop-facility-grid--skeleton" aria-hidden="true">
				<?php for ( $i = 0; $i < $count; $i++ ) : ?>
					<div class="desktop-facility-card desktop-facility-card--skeleton">
						<div class="desktop-facility-card__media desktop-facility-card__media--skeleton">
							<span class="ugm-skeleton-box ugm-skeleton-box--facility"></span>
							<span class="ugm-skeleton-line ugm-skeleton-line--facility-title"></span>
						</div>
					</div>
				<?php endfor; ?>
			</div>
		<?php elseif ( 'magazine-grid' === $layout ) : ?>
			<div class="majalah-grid majalah-grid--skeleton" aria-hidden="true">
				<?php for ( $i = 0; $i < $count; $i++ ) : ?>
					<div class="majalah-card majalah-card--skeleton">
						<span class="ugm-skeleton-box ugm-skeleton-box--magazine"></span>
					</div>
				<?php endfor; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php

	return ob_get_clean();
}

/**
 * Render repeated skeleton items to fill remaining slots in a partially filled section.
 *
 * @param string $layout Skeleton item layout key.
 * @param int    $count  Number of skeleton items to render.
 * @return string
 */
function ugm_render_partial_skeleton_items( $layout, $count ) {
	$layout = sanitize_key( (string) $layout );
	$count  = max( 0, absint( $count ) );

	if ( $count < 1 ) {
		return '';
	}

	ob_start();

	for ( $i = 0; $i < $count; $i++ ) {
		if ( 'latest-news-card' === $layout ) :
			?>
			<article class="news-grid-card news-grid-card--list news-grid-card--skeleton" aria-hidden="true">
				<div class="news-grid-card__media news-grid-card__media--skeleton">
					<span class="ugm-skeleton-box ugm-skeleton-box--thumb"></span>
				</div>
				<div class="news-grid-card__body">
					<span class="ugm-skeleton-line ugm-skeleton-line--short"></span>
					<span class="ugm-skeleton-line"></span>
					<span class="ugm-skeleton-line ugm-skeleton-line--meta"></span>
				</div>
			</article>
			<?php
		elseif ( 'portal-list-card' === $layout ) :
			?>
			<article class="portal-list-card portal-list-card--skeleton" aria-hidden="true">
				<div class="portal-list-card__media portal-list-card__media--skeleton">
					<span class="ugm-skeleton-box ugm-skeleton-box--mini-thumb"></span>
				</div>
				<div class="portal-list-card__body">
					<span class="ugm-skeleton-line ugm-skeleton-line--short"></span>
					<span class="ugm-skeleton-line"></span>
					<span class="ugm-skeleton-line ugm-skeleton-line--meta"></span>
				</div>
			</article>
			<?php
		elseif ( 'magazine-card' === $layout ) :
			?>
			<article class="majalah-card majalah-card--skeleton" aria-hidden="true">
				<div class="majalah-card__link">
					<div class="majalah-card__media majalah-card__media--skeleton">
						<span class="ugm-skeleton-box ugm-skeleton-box--magazine"></span>
					</div>
					<div class="majalah-card__title majalah-card__title--skeleton">
						<span class="ugm-skeleton-line ugm-skeleton-line--magazine-title"></span>
					</div>
				</div>
			</article>
			<?php
		elseif ( 'category-card' === $layout ) :
			?>
			<div class="category-card category-card--skeleton" aria-hidden="true">
				<span class="ugm-skeleton-line ugm-skeleton-line--medium"></span>
				<span class="ugm-skeleton-line ugm-skeleton-line--meta"></span>
			</div>
			<?php
		elseif ( 'faculty-card' === $layout ) :
			?>
			<article class="faculty-card faculty-card--skeleton" aria-hidden="true">
				<div class="faculty-card__image faculty-card__image--skeleton">
					<span class="ugm-skeleton-box ugm-skeleton-box--faculty"></span>
				</div>
				<div class="faculty-card__overlay">
					<div class="faculty-card__title faculty-card__title--skeleton">
						<span class="ugm-skeleton-line ugm-skeleton-line--faculty-title"></span>
					</div>
				</div>
			</article>
			<?php
		elseif ( 'agenda-card' === $layout ) :
			?>
			<div class="desktop-agenda-card desktop-agenda-card--skeleton" aria-hidden="true">
				<span class="ugm-skeleton-box ugm-skeleton-box--agenda-date"></span>
				<div class="desktop-agenda-card__body">
					<span class="ugm-skeleton-line"></span>
					<span class="ugm-skeleton-line ugm-skeleton-line--meta"></span>
				</div>
			</div>
			<?php
		elseif ( 'facility-card' === $layout ) :
			?>
			<div class="desktop-facility-card desktop-facility-card--skeleton" aria-hidden="true">
				<div class="desktop-facility-card__media desktop-facility-card__media--skeleton">
					<span class="ugm-skeleton-box ugm-skeleton-box--facility"></span>
					<span class="ugm-skeleton-line ugm-skeleton-line--facility-title"></span>
				</div>
			</div>
			<?php
		elseif ( 'video-list-card' === $layout ) :
			?>
			<article class="video-list-card video-list-card--skeleton" aria-hidden="true">
				<div class="video-list-card__media video-list-card__media--skeleton">
					<span class="ugm-skeleton-box ugm-skeleton-box--mini-thumb"></span>
				</div>
				<div class="video-list-card__body">
					<span class="ugm-skeleton-line ugm-skeleton-line--short"></span>
					<span class="ugm-skeleton-line"></span>
					<span class="ugm-skeleton-line ugm-skeleton-line--meta"></span>
				</div>
			</article>
			<?php
		endif;
	}

	return ob_get_clean();
}
