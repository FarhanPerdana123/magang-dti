<?php

class UGM_Post_Types {

	public function __construct() {
		add_action( 'init', [ $this, 'register_taxonomies' ] );
		add_action( 'init', [ $this, 'register_post_types' ] );
		add_filter( 'manage_event_posts_columns', [ $this, 'event_columns_head' ] );
		add_action( 'manage_event_posts_custom_column', [ $this, 'event_columns_content' ], 9, 2 );
		add_action( 'pre_get_posts', [ $this, 'filter_events' ] );
		add_action( 'pre_get_posts', [ $this, 'search_all_post_types' ] );
		add_filter( 'use_block_editor_for_post_type', [ $this, 'disable_gutenberg' ], 10, 2 );
	}

	public function register_taxonomies() {
		register_taxonomy( 'event-category', 'event',
		    array(
		    	'labels' 		=> array(
		    		'name' 			=> __( 'Event Categories', 'ugm-theme' ),
		    		'singular_name' => __( 'Event Category', 'ugm-theme' ),
		    		'add_new_item' 	=> __( 'Add New Category', 'ugm-theme' ),
		    		'edit_item' 	=> __( 'Edit Category', 'ugm-theme' ),
		    		'new_item' 		=> __( 'New Category', 'ugm-theme' ),
		    		'view_item'		=> __( 'View Category', 'ugm-theme' ),
		    		'search_items' 	=> __( 'Search Category', 'ugm-theme' ),
		    	),
		    	'public' 		=> true,
		    	'hierarchical' 	=> true,
		    	'show_in_rest'  => true
		    )
		);
		register_taxonomy( 'event-tag', 'event',
		    array(
		    	'labels' 		=> array(
		    		'name' 			=> __( 'Event Tags', 'ugm-theme' ),
		    		'singular_name' => __( 'Event Tag', 'ugm-theme' ),
		    		'add_new_item' 	=> __( 'Add New Tag', 'ugm-theme' ),
		    		'edit_item' 	=> __( 'Edit Tag', 'ugm-theme' ),
		    		'new_item' 		=> __( 'New Tag', 'ugm-theme' ),
		    		'view_item'		=> __( 'View Tag', 'ugm-theme' ),
		    		'search_items' 	=> __( 'Search Tag', 'ugm-theme' ),
		    	),
		    	'public' 		=> true,
		    	'hierarchical' 	=> false,
		    	'show_in_rest'  => true,
		    	'rewrite'       =>  array('slug' => 'event-tag', 'with_front' => false),
		    )
		);
		register_taxonomy( 'gallery-tag', 'gallery',
		    array(
		    	'labels' 		=> array(
		    		'name' 			=> __( 'Gallery Tags', 'ugm-theme' ),
		    		'singular_name' => __( 'Gallery Tag', 'ugm-theme' ),
		    		'add_new_item' 	=> __( 'Add New Tag', 'ugm-theme' ),
		    		'edit_item' 	=> __( 'Edit Tag', 'ugm-theme' ),
		    		'new_item' 		=> __( 'New Tag', 'ugm-theme' ),
		    		'view_item'		=> __( 'View Tag', 'ugm-theme' ),
		    		'search_items' 	=> __( 'Search Tag', 'ugm-theme' ),
		    	),
		    	'public' 		=> true,
		    	'hierarchical' 	=> false,
		    	'show_in_rest'  => true,
		    	'rewrite'       =>  array('slug' => 'gallery-tag', 'with_front' => false),
		    )
		);
	}

	public function register_post_types() {
		register_post_type( 'event',
		    array(
		    	'labels' 		=> array(
		    		'name' 			=> __( 'Events', 'ugm-theme' ),
		    		'singular_name' => __( 'Event', 'ugm-theme' ),
		    		'add_new_item' 	=> __( 'Add New Event', 'ugm-theme' ),
		    		'edit_item' 	=> __( 'Edit Event', 'ugm-theme' ),
		    		'new_item' 		=> __( 'New Event', 'ugm-theme' ),
		    		'view_item'		=> __( 'View Event', 'ugm-theme' ),
		    		'search_items' 	=> __( 'Search Events', 'ugm-theme' ),
		    	),
		    	'public' 		=> true,
		    	'has_archive' 	=> true,
		    	'menu_icon'		=> 'dashicons-calendar-alt',
		    	'show_in_rest'  => true,
		    	'supports' 		=> array( 'title', 'thumbnail', 'editor', 'comments' )
		    )
		);
		register_post_type( 'gallery',
		    array(
		    	'labels' 		=> array(
		    		'name' 			=> __( 'Galleries', 'ugm-theme' ),
		    		'singular_name' => __( 'Gallery', 'ugm-theme' ),
		    		'add_new_item' 	=> __( 'Add New Gallery', 'ugm-theme' ),
		    		'edit_item' 	=> __( 'Edit Gallery', 'ugm-theme' ),
		    		'new_item' 		=> __( 'New Gallery', 'ugm-theme' ),
		    		'view_item'		=> __( 'View Gallery', 'ugm-theme' ),
		    		'search_items' 	=> __( 'Search Galleries', 'ugm-theme' ),
		    	),
		    	'public' 		=> true,
		    	'has_archive' 	=> true,
		    	'menu_icon'		=> 'dashicons-format-gallery',
		    	'show_in_rest'  => true,
		    	'supports' 		=> array( 'title', 'thumbnail', 'editor', 'comments' )
		    )
		);
		register_post_type( 'file',
		    array(
		    	'labels' 		=> array(
		    		'name' 			=> __( 'Files', 'ugm-theme' ),
		    		'singular_name' => __( 'File', 'ugm-theme' ),
		    		'add_new_item' 	=> __( 'Add New File', 'ugm-theme' ),
		    		'edit_item' 	=> __( 'Edit File', 'ugm-theme' ),
		    		'new_item' 		=> __( 'New File', 'ugm-theme' ),
		    		'view_item'		=> __( 'View File', 'ugm-theme' ),
		    		'search_items' 	=> __( 'Search Files', 'ugm-theme' ),
		    	),
		    	'public' 		=> true,
		    	'has_archive' 	=> true,
		    	'menu_icon'		=> 'dashicons-portfolio',
		    	'supports' 		=> array( 'title', 'thumbnail', 'comments' ),
		    	'show_in_rest'  => true
		    )
		);
	}

	public function event_columns_head( $columns ) {
		$new = array();
	    foreach( $columns as $key => $value ) {
	        if( 'date' === $key ) {  // when we find the date column
	           $new['event_category'] = __( 'Category','ugm-theme' );
	        }    
	        $new[ $key ] = $value;
	    }  

	    return $new;
	}

	public function event_columns_content( $column, $post_id ) {
		if ( 'event_category' === $column ) {
		    $cats = wp_get_post_terms( $post_id, "event-category", array( 'orderby' => 'name', 'order' => 'ASC', 'fields' => 'all' ) );
			if( ! empty( $cats ) ){
				echo implode( ', ', array_map( function( $cat ) {
					return '<a href="' . get_admin_url() . 'edit.php?post_type=event&event-category=' . $cat->slug . '">' . $cat->name . '</a>';
				}, $cats ) );
			}
	    }
	}

	public function filter_events( $query ) {
		if ( ! is_admin() && $query->is_main_query() && ( is_post_type_archive( 'event' ) || is_tax( array( 'event-category', 'event-tag' ) ) ) ) {
			$query->set( 'meta_key', 'ugm_event_date' );
			$query->set( 'meta_value', date( "Ymd" ) );
			$query->set( 'meta_compare', '>=' );
			$query->set( 'order', 'ASC' );
			$query->set( 'orderby', 'meta_value' );
		}
	}

	public function search_all_post_types( $query ) {
		if ( $query->is_search() ) {
			$query->set( 'post_type', [ 'post', 'page', 'event', 'gallery', 'file' ] );
		}
		return $query;
	}

	public function disable_gutenberg( $status, $post_type ) {
		if ( 'post' === $post_type ) {
			return false;
		}
		return $status;
	}

}
new UGM_Post_Types();