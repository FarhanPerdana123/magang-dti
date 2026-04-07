<?php
define( 'UGM_BLOCKS_PATH', __DIR__ );

require_once UGM_BLOCKS_PATH . '/inc/class-ugm-blocks-block.php';
require_once UGM_BLOCKS_PATH . '/inc/blocks/class-ugm-blocks-block-post-list.php';
require_once UGM_BLOCKS_PATH . '/inc/blocks/class-ugm-blocks-block-event-list.php';
require_once UGM_BLOCKS_PATH . '/inc/blocks/class-ugm-blocks-block-file-list.php';
require_once UGM_BLOCKS_PATH . '/inc/blocks/class-ugm-blocks-block-post-slider.php';
require_once UGM_BLOCKS_PATH . '/inc/blocks/class-ugm-blocks-block-section-title.php';
require_once UGM_BLOCKS_PATH . '/inc/blocks/class-ugm-blocks-block-icon.php';

class UGM_Blocks {

	public function __construct() {
		add_filter( 'block_categories_all', [ $this, 'register_block_categories' ] );
	}

	public function register_block_categories( $categories ) {
		array_unshift( $categories, array(
			'slug'  => 'ugm',
			'title' => 'UGM Blocks'
		) );
		return $categories;
	}

}
new UGM_Blocks();