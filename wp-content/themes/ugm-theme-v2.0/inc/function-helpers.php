<?php

if ( ! function_exists('is_english') ) {
	function is_english() {
		if (in_array( 'polylang/polylang.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ))) && function_exists('pll_current_language')) {
			if (pll_current_language( 'slug' ) == 'en' || pll_current_language( 'locale' ) == 'en_US') {
				return true;
			}
		}
		return false;
	}
}

// customize ACF function, for polylang implementation.
if ( ! function_exists( 'get_field_poly' ) ) {
	function get_field_poly($field, $post_id = '') {
		$translated_field = $field;
		if ( ( $post_id == 'option' || $post_id == 'options') && in_array( 'polylang/polylang.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			$locale = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : get_locale();
			$translated_field = $field . "_" . $locale;
			$translated_value = get_field($translated_field, $post_id);
			if (!empty($translated_value)) return $translated_value;
		}
		return get_field($field,$post_id);
	}
}

if ( ! function_exists( 'the_field_poly' ) ) {
	function the_field_poly( $field, $post_id = null ) {
		echo get_field_poly( $field, $post_id );
	}
}

if ( ! function_exists('ugm_get_layout') ) {
	function ugm_get_layout() {
		// get post layout.
		if ( is_singular() ) {
			$site_layout = get_field( 'ugm_options_page_layout', get_the_ID() );
		}

		// if post layout not set, get global instead.
		if ( empty( $site_layout ) || $site_layout == 'default') {
			$site_layout = get_field( 'ugm_options_theme_layout', 'option' );
		}

		return $site_layout;
	}
}

if ( ! function_exists( 'ugm_get_related_post' ) ) {
	/**
	 * Get related posts of current post
	 * 
	 * @param  integer   $post_id   Current post ID.
	 * @param  integer   $showposts Post count.
	 * @return WP_Post[]            Related posts.
	 */
	function ugm_get_related_post( $post_id, $showposts = 3 ) {
		$not_in = [ $post_id ];

		$args = array(
			'post_type'    => 'post',
			'showposts'    => $showposts,
			'post_status'  => 'publish',
			'orderby'      => 'date',
			'order'        => 'DESC',
			'post__not_in' => $not_in,
		);

		$tags = wp_get_post_tags( $post_id );
		if( $tags ) {
			$tags_id = [];
			foreach ( $tags as $tag ) {
				if ( isset( $tag->term_id ) ) {
					$tags_id[] = $tag->term_id;
				}
			}
			$args['tag__in'] = $tags_id;
		}

		$related_posts = get_posts( $args );

		if ( $showposts > count( $related_posts ) ) {
			$cats = wp_get_post_categories( $post_id );
			if( $cats ) {
				$cats_id = [];
				foreach ( $cats as $cat ) {
					if ( isset( $cat->term_id ) ) {
						$cats_id[] = $cat->term_id;
					}
				}
				$args['category__in'] = $cats_id;
				if ( isset( $args['tag__in'] ) ) {
					unset( $args['tag__in'] );
				}
				$args['showposts']    = $showposts - count( $related_posts );
				$args['post__not_in'] = array_merge( $args['post__not_in'], array_map( function( $p ) {
					return $p->ID;
				}, $related_posts ) );
				$related_posts = array_merge( $related_posts, get_posts( $args ) );
			}
		}
		return $related_posts;
	}
}

if ( ! function_exists( 'ugm_get_related_event' ) ) {
	/**
	 * Get related events of current event
	 * 
	 * @param  integer   $post_id   Current post ID.
	 * @param  integer   $showposts Post count.
	 * @return WP_Post[]            Related posts.
	 */
	function ugm_get_related_event( $post_id, $showposts = 3 ) {
		$not_in = [ $post_id ];

		$args = array(
			'post_type'    => 'event',
			'showposts'    => $showposts,
			'post_status'  => 'publish',
			'post__not_in' => $not_in,
			'meta_key'     => 'ugm_event_date',
			'meta_value'   => date( "Ymd" ),
			'meta_compare' => '>=',
			'order'        => 'ASC',
			'orderby'      => 'meta_value'
		);

		$tags = wp_get_post_terms( $post_id, 'event-tag' );
		$cats = wp_get_post_terms( $post_id, 'event-category' );
		$tags_id = [];
		$cats_id = [];

		if ( $tags ) {
			foreach ( $tags as $tag ) {
				if ( isset( $tag->term_id ) ) {
					$tags_id[] = $tag->term_id;
				}
			}
		}

		if ( $cats ) {
			foreach ( $cats as $cat ) {
				if ( isset( $cat->term_id ) ) {
					$cats_id[] = $cat->term_id;
				}
			}
		}

		if ( ! empty( $cats_id ) && ! empty( $tags_id ) ) {
			$args['tax_query'] = array(
				'relation' => 'OR',
				array(
					'taxonomy' => 'event-tag',
					'field'    => 'term_id',
					'terms'    => $tags_id
				),
				array(
					'taxonomy' => 'event-category',
					'field'    => 'term_id',
					'terms'    => $cats_id
				)
			);
		} elseif ( ! empty( $tags_id ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'event-tag',
					'field'    => 'term_id',
					'terms'    => $tags_id
				)
			);
		} elseif ( ! empty( $cats_id ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'event-category',
					'field'    => 'term_id',
					'terms'    => $cats_id
				)
			);
		}

		$related_posts = get_posts( $args );

		return $related_posts;
	}
}

if ( ! function_exists( 'ugm_get_related_gallery' ) ) {
	/**
	 * Get related gallerys of current gallery
	 * 
	 * @param  integer   $post_id   Current post ID.
	 * @param  integer   $showposts Post count.
	 * @return WP_Post[]            Related posts.
	 */
	function ugm_get_related_gallery( $post_id, $showposts = 3 ) {
		$not_in = [ $post_id ];

		$args = array(
			'post_type'    => 'gallery',
			'showposts'    => $showposts,
			'post_status'  => 'publish',
			'post__not_in' => $not_in
		);

		$tags = wp_get_post_terms( $post_id, 'gallery-tag' );
		$tags_id = [];

		if ( $tags ) {
			foreach ( $tags as $tag ) {
				if ( isset( $tag->term_id ) ) {
					$tags_id[] = $tag->term_id;
				}
			}
		}

		if ( ! empty( $tags_id ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'gallery-tag',
					'field'    => 'term_id',
					'terms'    => $tags_id
				)
			);
		}

		$related_posts = get_posts( $args );

		return $related_posts;
	}
}

if ( ! function_exists( 'ugm_is_event_page' ) ) {
	function ugm_is_event_page() {
		return is_post_type_archive( 'event' ) || is_singular( 'event' ) || is_tax( 'event-category' ) || is_tax( 'event-tag' );
	}
}

if ( ! function_exists( 'ugm_is_gallery_page' ) ) {
	function ugm_is_gallery_page() {
		return is_post_type_archive( 'gallery' ) || is_singular( 'gallery' ) || is_tax( 'gallery-tag' );
	}
}