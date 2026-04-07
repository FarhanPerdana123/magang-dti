<?php

if ( ! function_exists( 'ugm_breadcrumbs' ) ) {
	function ugm_breadcrumbs(){

		/* === OPTIONS === */
		$text['home']     = __( 'Home', 'ugm-theme' ); // text for the 'Home' link
		$text['category'] = '%s'; // text for a category page
		$text['tax'] 	  = '%s'; // text for a taxonomy page
		$text['search']   = __( 'Search Result', 'ugm-theme' ); // text for a search results page
		$text['tag']      = '%s'; // text for a tag page
		$text['author']   = __( 'Post by', 'ugm-theme' ); // text for an author page
		$text['404']      = __( 'Error 404', 'ugm-theme' ); // text for the 404 page
		$showCurrent = 0; // 1 - show current post/page title in breadcrumbs, 0 - don't show
		$showOnHome  = 0; // 1 - show breadcrumbs on the homepage, 0 - don't show
		$delimiter   = ''; // delimiter between crumbs
		$before      = '<li class="active">'; // tag before the current crumb
		$after       = '</li>'; // tag after the current crumb
		/* === END OF OPTIONS === */

		global $post;

		$output 	= '';
		$homeLink 	= home_url();
		$linkBefore = '<li typeof="v:Breadcrumb">';
		$linkAfter 	= '</li>';
		$linkAttr 	= ' rel="v:url" property="v:title"';
		$link = $linkBefore . '<a' . $linkAttr . ' href="%1$s">%2$s</a>' . $linkAfter;

		if (is_home() || is_front_page()) {
			if ($showOnHome == 1) $output .= '<ul class="breadcrumb"><li><a href="' . $homeLink . '">' . $text['home'] . '</a></li></div>';
		} else {
			$output .= '<ul class="breadcrumb" xmlns:v="http://rdf.data-vocabulary.org/#">' . sprintf($link, $homeLink, $text['home']) . $delimiter;

			if ( is_search() ) {
				if ( isset( $_GET['post_type'] ) && in_array( sanitize_text_field( wp_unslash( $_GET['post_type'] ) ), array( 'event', 'gallery' ), true ) ) {
					$post_type = get_post_type_object( get_post_type() );
					$output .= $linkBefore.'<a href="'.get_post_type_archive_link( $post_type->name ).'">'.$post_type->labels->singular_name.'</a>'.$linkAfter;
				}
				$output .= $before . sprintf($text['search'], get_search_query()) . $after;
			} elseif ( is_day() ) {
				$output .= sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . $delimiter;
				$output .= sprintf($link, get_month_link(get_the_time('Y'),get_the_time('m')), get_the_time('F')) . $delimiter;
				$output .= $before . get_the_time('d') . $after;
			} elseif ( is_month() ) {
				$output .= sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . $delimiter;
				$output .= $before . get_the_time('F') . $after;
			} elseif ( is_year() ) {
				$output .= $before . get_the_time('Y') . $after;
			} elseif ( is_single() && !is_attachment() ) {
				if ( get_post_type() != 'post' ) {
					$post_type = get_post_type_object(get_post_type());
					if ($post_type->name != 'post' && $post_type->name != 'page') {
						if($showCurrent == 0) $linkBefore = str_replace('<li', '<li class="active"', $linkBefore);
						$output .= $linkBefore.'<a href="'.get_post_type_archive_link( $post_type->name ).'">'.$post_type->labels->singular_name.'</a>'.$linkAfter;
						if ($showCurrent == 1) $output .= $delimiter . $before . get_the_title() . $after;
					} else {
						$slug = $post_type->rewrite;
						printf($link, $homeLink . '/' . $slug['slug'] . '/', $post_type->labels->singular_name);
						if ($showCurrent == 1) $output .= $delimiter . $before . get_the_title() . $after;
					}
				} else {
					$cat = get_the_category();
					if ( ! empty( $cat ) ) {
						$cat = $cat[0];
						$cats = get_category_parents($cat, TRUE, $delimiter);
						if ($showCurrent == 0) $cats = preg_replace("#^(.+)$delimiter$#", "$1", $cats);
						if ($showCurrent == 0) $linkBefore = str_replace('<li', '<li class="active"', $linkBefore);
						$cats = str_replace('<a', $linkBefore . '<a' . $linkAttr, $cats);
						$cats = str_replace('</a>', '</a>' . $linkAfter, $cats);
						$output .= $cats;
					}
					if ($showCurrent == 1) $output .= $before . get_the_title() . $after;
				}
			} elseif ( ! is_single() && ! is_page() && get_post_type() != 'post' && ! is_404() ) {
				$post_type = get_post_type_object( get_post_type() );
				if ( $post_type ) {
					if ( is_post_type_archive() ) {
						$output .= $before . $post_type->labels->singular_name . $after;
					} elseif ( is_tax() ) {
						$output .= $linkBefore.'<a href="' . get_post_type_archive_link( $post_type->name ) . '">' . $post_type->labels->singular_name . '</a>' . $linkAfter;
					} else {
						$output .= $linkBefore . $post_type->labels->singular_name . $linkAfter;
					}
				}
			} elseif ( is_attachment() ) {
				$parent = get_post($post->post_parent);
				$cat = get_the_category($parent->ID); $cat = $cat[0];
				$cats = get_category_parents($cat, TRUE, $delimiter);
				$cats = str_replace('<a', $linkBefore . '<a' . $linkAttr, $cats);
				$cats = str_replace('</a>', '</a>' . $linkAfter, $cats);
				$output .= $cats;
				printf($link, get_permalink($parent), $parent->post_title);
				if ($showCurrent == 1) $output .= $delimiter . $before . get_the_title() . $after;
			} elseif ( is_page() && get_query_var('paged') ) {
				$linkBefore = str_replace('<li class="active"', '<li', $linkBefore);
				$output .= $linkBefore . get_the_title() . $after;
			} elseif ( is_page() && !$post->post_parent ) {
				if (is_page_template('page-templates/contact-page.php')) {
					$linkBefore = str_replace('<li', '<li class="active"', $linkBefore);
					$output .= $linkBefore.get_the_title().$linkAfter;
				} else if (is_page_template('page-templates/profile-page.php')) {
					$linkBefore = str_replace('<li', '<li class="active"', $linkBefore);
					$output .= $linkBefore.get_the_title().$linkAfter;
				} else if (is_page_template('page-templates/directory-single.php') || is_page_template('page-templates/directory-double.php')) {
					$linkBefore = str_replace('<li', '<li class="active"', $linkBefore);
					$output .= $linkBefore.get_the_title().$linkAfter;
				} else {
					if ($showCurrent == 1) $output .= $before . get_the_title() . $after;
				}
			} elseif ( is_page() && $post->post_parent ) {
				$parent_id  = $post->post_parent;
				$breadcrumbs = array();
				while ($parent_id) {
					$page = get_page($parent_id);
					$breadcrumbs[] = sprintf($link, get_permalink($page->ID), get_the_title($page->ID));
					$parent_id  = $page->post_parent;
				}
				$breadcrumbs = array_reverse($breadcrumbs);
				for ($i = 0; $i < count($breadcrumbs); $i++) {
					$output .= $breadcrumbs[$i];
					if ($i != count($breadcrumbs)-1) $output .= $delimiter;
				}
				if ($showCurrent == 1) $output .= $delimiter . $before . get_the_title() . $after;
			} elseif ( is_tag() ) {
				$output .= $before . sprintf($text['tag'], single_tag_title('', false)) . $after;
			} elseif ( is_author() ) {
				global $author;
				$userdata = get_userdata($author);
				$output .= $before . sprintf($text['author'], $userdata->display_name) . $after;
			} elseif ( is_404() ) {
				$output .= $before . $text['404'] . $after;
			}

			if ( is_category() ) {
				$thisCat = get_category(get_query_var('cat'), false);
				if (!empty($thisCat->parent) && $thisCat->parent != 0) {
					$cats = get_category_parents($thisCat->parent, TRUE, $delimiter);
					$cats = str_replace('<a', $linkBefore . '<a' . $linkAttr, $cats);
					$cats = str_replace('</a>', '</a>' . $linkAfter, $cats);
					$output .= $cats;
				}
				$output .= $before . sprintf($text['category'], single_cat_title('', false)) . $after;
			} elseif( is_tag() ){
				$thisCat = get_tag(get_query_var('tag'), false);
				if (!empty($thisCat->parent) && $thisCat->parent != 0) {
					$cats = get_category_parents($thisCat->parent, TRUE, $delimiter);
					$cats = str_replace('<a', $linkBefore . '<a' . $linkAttr, $cats);
					$cats = str_replace('</a>', '</a>' . $linkAfter, $cats);
					$output .= $cats;
				}
				$output .= $before . sprintf($text['tag'], single_cat_title('', false)) . $after;
			}

			if ( get_query_var('paged') ) {
				// if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() || is_post_type_archive('event') || is_post_type_archive('gallery') ) $output .= ' (';
				$output .= $before . __( 'page.', 'ugm-theme' ) . ' ' . get_query_var('paged') . $after;
				// if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() || is_post_type_archive('event') || is_post_type_archive('gallery') ) $output .= ')';
			}
			$output .= '</ul>';

			echo apply_filters( 'ugm_theme_breadcrumb_output', $output );
		}

	} // end ugm_breadcrumbs()
}

/**
 * Get column layout class
 *  @param String $column_name
 *  @return String
 */
if ( ! function_exists('ugm_layout_class') ) {
	function ugm_layout_class($column_name = '') {
		
		$site_layout = ugm_get_layout();

		$column_class = array();
		if (is_single()) :
			if ($site_layout == 'right') :
				$column_class['content'] 		= 'col-md-8 col-md-push-1 sidebar-right';
				$column_class['sidebar'] 		= 'col-md-3';
				$column_class['second_sidebar'] = 'hide';
				$column_class['sharer_wrapper'] = 'col-md-1 col-md-pull-8';
			elseif ($site_layout == 'left') :
				$column_class['content']		= 'col-md-8 col-md-push-3 sidebar-left';
				$column_class['sidebar'] 		= 'col-md-3 col-md-pull-8';
				$column_class['second_sidebar'] = 'hide';
				$column_class['sharer_wrapper'] = 'col-md-1 pull-right';
			elseif ($site_layout == 'left-right') :
				$column_class['content']		= 'col-md-6 sidebar-right sidebar-left col-md-push-3';
				$column_class['sidebar'] 		= 'col-md-3 col-md-pull-6';
				$column_class['second_sidebar'] = 'col-md-3';
				$column_class['sharer_wrapper'] = 'hide';
			else :
				$column_class['content']		= 'col-md-12';
				$column_class['sidebar'] 		= 'hide';
				$column_class['second_sidebar'] = 'hide';
				$column_class['sharer_wrapper'] = 'hide';
			endif;
		elseif (is_page()) :
			if ($site_layout == 'right') :
				$column_class['content'] 		= 'col-md-8 col-md-push-1 sidebar-right';
				$column_class['sidebar'] 		= 'col-md-3 col-md-offset-1';
				$column_class['second_sidebar'] = 'hide';
				$column_class['sharer_wrapper'] = 'col-md-1 col-md-pull-8';
			elseif ($site_layout == 'left') :
				$column_class['content']		= 'col-md-8 col-md-push-3 sidebar-left';
				$column_class['sidebar'] 		= 'col-md-3 col-md-pull-8';
				$column_class['second_sidebar'] = 'hide';
				$column_class['sharer_wrapper'] = 'col-md-1 pull-right';
			elseif ($site_layout == 'left-right') :
				$column_class['content']		= 'col-md-6 sidebar-right sidebar-left col-md-push-3';
				$column_class['sidebar'] 		= 'col-md-3 col-md-pull-6';
				$column_class['second_sidebar'] = 'col-md-3';
				$column_class['sharer_wrapper'] = 'hide';
			else :
				$column_class['content']		= 'col-md-12';
				$column_class['sidebar'] 		= 'hide';
				$column_class['second_sidebar'] = 'hide';
				$column_class['sharer_wrapper'] = 'hide';
			endif;
		else:
			if ($site_layout == 'right') :
				$column_class['content'] 		= 'col-md-9 sidebar-right';
				$column_class['sidebar'] 		= 'col-md-3';
				$column_class['second_sidebar'] = 'hide';
			elseif ($site_layout == 'left') :
				$column_class['content']		= 'col-md-9 col-md-push-3 sidebar-left';
				$column_class['sidebar'] 		= 'col-md-3 col-md-pull-9';
				$column_class['second_sidebar'] = 'hide';
			elseif ($site_layout == 'left-right') :
				$column_class['content']		= 'col-md-6 sidebar-right sidebar-left col-md-push-3';
				$column_class['sidebar'] 		= 'col-md-3 col-md-pull-6';
				$column_class['second_sidebar'] = 'col-md-3';
			else :
				$column_class['content']		= 'col-md-12';
				$column_class['sidebar'] 		= 'hide';
				$column_class['second_sidebar'] = 'hide';
			endif;
		endif;

		if (isset($column_class[$column_name])) echo $column_class[$column_name];
	}
}

/**
 * Set custom pagination links
 *  @param  array $args  Arguments about pagination configurations
 *  @return -
 */
if ( ! function_exists( 'ugm_theme_paginate_links' ) ) {
	function ugm_theme_paginate_links( $args = array() ) {

		global $wp_query, $wp_rewrite;

		$max_pages	= $wp_query->max_num_pages;

		/* Custom WP_Query */
		if ( isset( $args['used_query'] ) ) {
			$max_pages = $args['used_query']->max_num_pages;
		}

		if ( $max_pages < 2 ) {
			return;
		}

		if ( get_query_var( 'paged' ) ) {
			$paged = get_query_var( 'paged' );
		} elseif ( get_query_var( 'page' ) ) {
			$paged = get_query_var( 'page' );
		} else {
			$paged = 1;
		}

		$big = 999999999; // need an unlikely integer

		$paginate_link_data = apply_filters( 'ugm_theme_set_paginate_link', array(
			'base' 		=> str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format' 	=> '?paged=%#%',
			'current' 	=> max( 1, $paged ),
			'total' 	=> $max_pages,
			'type' 		=> 'array',
			'prev_text' => '',
			'next_text' => ''
		) );
		$paginate_links = paginate_links( $paginate_link_data );

		$paginate = $next_prev = null;
		foreach ( $paginate_links as $links => $value ) {

			$next_prev_a = null;

			/* Current number */
			if ( strpos( $value, 'current' ) ) {
				$value 	= str_replace( 'span', 'a', $value );
			} else {
				$value 	= str_replace( 'page-numbers', 'page-numbers page', $value );
			}

			$paginate 	.= ( ! empty( $value ) ) ? $value : null;

		}

		if (is_post_type_archive('gallery')) {
			$output = '<div class="pagination text-center">' . $paginate . '</div>';
		} else {
			$output = '<div class="pagination">' . $paginate . '</div>';
		}

		echo apply_filters( 'ugm_paginate_output', $output );

	}
}

/**
 * Display social media sharer
 *  @param -
 *  @return -
 */
if (!function_exists('ugm_social_sharer')) {
	function ugm_social_sharer() {
		?>
		<ul class="share-box">
			<li><a href="http://www.facebook.com/sharer.php?u=<?php echo esc_url(get_the_permalink()) ?>" class="facebook"><i class="fa fa-facebook"></i></a></li>
			<li><a href="https://twitter.com/share?url=<?php echo esc_url(get_the_permalink()) ?>" class="twitter"><i class="fa fa-twitter"></i></a></li>
			<li><a href="whatsapp://send?text=<?php echo esc_url(get_the_permalink()); ?>" class="whatsapp"><i class="fa fa-whatsapp"></i></a></li>
		</ul>
		<?php
	}
}

if ( ! function_exists( 'ugm_berita_excerpt' ) ) {
	function ugm_berita_excerpt( $post_id, $min_par = 15 ) {
		if ( ! has_excerpt( $post_id ) ) {
			$forbidden_words = array( 'prof.', 'dr.', 'drs.', 'h.', 'hj.' );
			$excerpt = get_the_excerpt( $post_id );
			$sentences = explode( '. ', $excerpt );
			$desc = '';
			$i = 0;
			while ( $min_par > str_word_count( $desc ) ) {
				if ( isset( $sentences[ $i ] ) ) {
					$desc .= ' ' . $sentences[ $i ] . '.';
					$pieces = explode( ' ', $desc );
					$last_word = array_pop($pieces);
					if ( in_array( strtolower( $last_word ), $forbidden_words ) && isset( $sentences[ $i + 1 ] ) ) {
						$desc .= ' ' . $sentences[ $i + 1 ] . '.';
						$i++;
					}
				} else {
					break;
				}
				$i++;
			}
		} else {
			$desc = get_the_excerpt( $post_id );
		}
		return $desc;
	}
}

if ( ! function_exists( 'the_event_category' ) ) {
	function the_event_category( $separator = '' ) {
		global $post;
		$categories = get_the_terms( $post, 'event-category' );
		if ( ! is_wp_error( $categories ) && false !== $categories ) {
			$categories = array_map( function( $category ) {
				return '<a href="' . get_term_link( $category, 'event-category' ) . '">' . esc_html( $category->name ) . '</a>';
			}, $categories );
			echo implode( $separator, $categories );
		}
	}
}