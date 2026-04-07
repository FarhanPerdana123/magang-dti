<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php $favicon = get_field('ugm_options_favicon','option'); ?>
	<link rel="shortcut icon" type='image/x-icon' href="<?php echo !empty($favicon) ? $favicon : UGM_THEME_URI_ASSETS.'/images/ugm_favicon.png' ?>">
	<?php do_action( 'ugm-set-description' ); ?>
	<?php do_action( 'ugm-set-og-card' ); ?>
	<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php endif; ?>
	<?php wp_head(); ?>
	<?php 
		$custom_css = get_field('ugm_options_custom_css','option');
		if (!empty($custom_css)) :
			echo '<style type="text/css">'.$custom_css.'</style>';
		endif;
	?>
</head>
<body <?php body_class(); ?> >
	<?php
	$header_layout = get_field('ugm_options_header_type','option');
	$header_class = '';
	if ( 'default' === $header_layout ) {
		$header_class = 'header-compact';
	} elseif ( 'default_burger' === $header_layout ) {
		$header_class = 'header-burger';
	} elseif ( 'light' === $header_layout ) {
		$header_class = 'header-compact header-light';
	} elseif ( 'light_burger' === $header_layout ) {
		$header_class = 'header-light header-burger';
	}
	?>
	<header id="header" class="<?php echo $header_class; ?>">
		<nav class="navbar navbar-default topbar">
			<div class="container">
				<?php
					if ( has_nav_menu( 'top' ) ) :

		                $top = array(
		                    'theme_location'  	=> 'top',
		                    'menu'            	=> 'top',
		                    'menu_class'      	=> 'nav navbar-nav',
		                    'container'			=> '',
		                    'container_class'	=> '',
		                    'container_id'		=> 'top-menu',
		                    'fallback_cb'     	=> 'UGM_Walker_Nav::fallback',
		                    'items_wrap'		=> '<div class="header-nav"><button class="nav-more"><i class="ion ion-android-more-vertical"></i></button><ul id="%1$s" class="%2$s">%3$s</ul></div>',
		                    'walker'          	=> new UGM_Walker_Nav()
		                );

		                wp_nav_menu( $top );

		            endif;
				?>
				<div class="header-options">
					<?php if (get_field('ugm_options_header_search_field','option')) : ?>
						<form action="<?php echo function_exists( 'pll_home_url' ) ? pll_home_url() : home_url(); ?>" class="search-form">
							<div class="input-group btn-group">
								<input type="text" class="form-control" name="s" value="<?php echo get_search_query(); ?>" placeholder="<?php esc_attr_e( 'Search...', 'ugm-theme' ); ?>" required>
								<button type="submit" class="btn"><i class="fa fa-search"></i></button>
							</div>
						</form>
					<?php endif; ?>
				</div>
			</div>
		</nav>
		<div class="container">
			<div class="navbar-header">
				<a href="<?php echo home_url(); ?>" class="navbar-brand">

					<?php
						$header_logo = get_field('ugm_options_header_logo','option');
						if (!empty($header_logo)) :
							$header_logo_img = $header_logo;
						else:
							$header_logo_img = UGM_THEME_URI_ASSETS . "/images/ugm_logo.png";
						endif;
					?>
					<img src="<?php echo $header_logo_img ?>" alt="Universitas Gadjah Mada" class="img-responsive">

					<?php
						$header_text = get_field_poly('ugm_options_header_text', 'option');
						$header_subtext = get_field_poly('ugm_options_header_sub_text','option');
						$class = '';
						if ( 6 <= str_word_count( $header_text ) ) {
							$class = "small";
						}
					?>
					<span class="<?php echo $class; ?>">
						<?php echo $header_text; ?><br><?php echo $header_subtext; ?>
					</span>
					
				</a>
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
			</div>
			<?php
				if ( has_nav_menu( 'primary' ) ) :

					$class = 'UGM_Walker_Nav';
					if ( false !== stripos( $header_class, 'burger' ) ) {
						$class = 'UGM_Walker_Nav_Burger';
					}

	                $primary = array(
	                    'theme_location'  	=> 'primary',
	                    'menu'            	=> 'primary',
	                    'menu_class'      	=> 'nav navbar-nav',
	                    'container'			=> 'nav',
	                    'container_class'	=> 'navbar navbar-collapse collapse',
	                    'container_id'		=> 'navbar',
	                    'fallback_cb'     	=> $class . '::fallback',
	                    'walker'          	=> new $class()
	                );
	            	if($header_layout == "below") {
	            		$primary["container_class"] = "navbar navbar-collapse collapse navbar-default";
	            	}

	                wp_nav_menu( $primary );

	            endif;
			?>
			<?php //if ( 'light' === $header_layout ) : ?>
				<!-- <button type="button" class="btn btn-toggle-search"><i class="fa fa-search"></i></button>
				<button type="button" class="btn btn-toggle-accessbility"><i class="fa fa-wheelchair"></i></button> -->
			<?php //endif; ?>
		</div>
	</header>

