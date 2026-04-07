<?php

class UGM_Hooks_Front {

	public function __construct() {
		add_filter( 'get_search_form', [ $this, 'search_form' ], 100 );
		add_filter( 'the_password_form', [ $this, 'protected_post_password_form' ] );
		add_filter( 'nav_menu_link_attributes', [ $this, 'add_menu_atts' ], 10, 3 );

		add_action( 'ugm-set-description', [ $this, 'print_meta_description' ] );
		add_action( 'ugm-set-og-card', [ $this, 'print_opengraph_metas' ] );

		add_filter( 'wp_nav_menu', [ $this, 'wrap_nav_menu' ], 10, 2 );
	}

	public function search_form( $form ) {
		ob_start();
		?>
		<form role="search" method="get" id="searchform" class="searchform" action="<?php echo site_url( '/' ); ?>">
			<div class="input-group btn-group">
				<input type="text" class="form-control" value="<?php echo get_search_query(); ?>" name="s" id="s" placeholder="<?php esc_attr_e( 'Search...', 'ugm-theme' ); ?>" required>
				<button type="submit" class="btn" id="searchsubmit"><i class="fa fa-search"></i></button>
			</div>
		</form>
		<?php
		return ob_get_clean();
	}

	public function protected_post_password_form() {
		global $post;
		$label = 'pwbox-'.( empty( $post->ID ) ? rand() : $post->ID );
		ob_start();
		?>
		<p>Kontent dalam artikel ini dilindungi oleh kata sandi. Untuk dapat melihat, mohon masukkan kata sandi Anda di bawah ini:</p>
		<form class="protected-post-form form-inline" action="<?php echo site_url();  ?>/wp-login.php?action=postpass" method="post">
			<label class="pass-label" for="<?php echo esc_html( $label ); ?>">Password : </label>
			<input name="post_password" id="<?php echo esc_html( $label ); ?>" type="password" class="form-control" />
			<input type="submit" name="Submit" class="btn btn-primary" value="Masuk" />
		</form>
		<?php
		return ob_get_clean();
	}

	public function add_menu_atts( $atts, $item, $args ) {
		if ( in_array( 'pll-parent-menu-item', $item->classes ) ) {
			$atts['title'] = "";
		} else if ( in_array( 'lang-item-id', $item->classes ) ) {
			$atts['title'] = "Bahasa Indonesia";
		} else if ( in_array( 'lang-item-en', $item->classes ) ) {
			$atts['title'] = "English";
		}
	    return $atts;
	}

	public function print_meta_description() {
		global $post;

		if ( empty(  $post ) ) {
			return;
		}
		$enable_seo 	= get_field('ugm_options_enable_custom_title', 'option');
		$description 	= get_field('ugm_seo_description', $post->ID );

		$trully_home 	= true;
		if ( get_query_var( 'page' ) || get_query_var( 'paged' ) ) {
			$trully_home = false;
		}

		if ( is_single() && $enable_seo == 'enable' && ! empty( $description ) ) {
			echo '<meta name="description" content="' . $description . '" />';
		} elseif ( is_home() && $enable_seo == 'enable' && $trully_home == true ) {
			$description = get_field('ugm_options_custom_site_description', 'option');
			if ( ! empty( $description ) ) {
				echo '<meta name="description" content="' . $description . '" />';
			}
		}
	}

	public function print_opengraph_metas() {
		global $post;

		$enable_og_card = get_field('ugm_options_enable_og', 'option');

		if ( is_single() && $enable_og_card == 'enable' ) {

			$blog_name 	= get_field('ugm_options_custom_site_description', 'option');;
			if ( empty( $blog_name ) ) {
				$blog_name = get_bloginfo( 'name' );
			}

			$title  	= $blog_name;
			$raw_title 	= get_field('ugm_seo_title', $post->ID);
			if ( ! empty( $raw_title ) ) {
				$title = $raw_title;
			} elseif ( ! empty( $post->post_title ) ) {
				$title = $post->post_title;
			}

			$description = get_field('ugm_seo_description', $post->ID);
			if ( empty( $description ) ) {
				$description = $post->post_excerpt;
			}

			$url 	= get_permalink( $post );
			$domain = site_url();
			$image 	= wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) );
			$card 	= 'summary';

			/* Set meta content */
			echo '<meta property="og:title" content="' . $title . '"/>';

			if ( ! empty( $description ) ) {
				echo '<meta property="og:description" content="' . $description . '"/>';
			}

			echo '<meta property="og:url" content="' . $url . '"/>';
			echo '<meta property="og:site_name" content="' . $domain . '"/>';

			if ( ! empty( $image ) ) {
				echo '<meta property="og:image" content="' . $image . '"/>';
			}

			echo '<meta property="twitter:card" content="' . $card . '"/>';
			echo '<meta property="twitter:title" content="' . $title . ' | ' . $domain . '"/>';

			if ( ! empty( $description ) ) {
				echo '<meta property="twitter:description" content="' . $description . '"/>';
			}

			echo '<meta property="twitter:domain" content="' . $domain . '"/>';

			if ( ! empty( $image ) ) {
				echo '<meta property="twitter:image" content="' . $image . '"/>';
			}

		}
	}

	public function wrap_nav_menu( $nav_menu, $args ) {
		$header_layout = get_field('ugm_options_header_type','option');
		if ( false !== stripos( $header_layout, 'burger' ) && 'primary' == $args->menu ) {
			$nav_menu = str_replace( '<nav id="navbar" class="navbar navbar-collapse collapse">', '<nav id="navbar" class="navbar navbar-collapse collapse"><div class="container">', $nav_menu );
			$nav_menu = str_replace( '</nav>', '</div></nav>', $nav_menu );
		}
		return $nav_menu;
	}

}
new UGM_Hooks_Front();