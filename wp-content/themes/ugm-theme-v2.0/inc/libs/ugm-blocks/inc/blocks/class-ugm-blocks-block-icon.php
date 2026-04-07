<?php

class UGM_Blocks_Block_Icon extends UGM_Blocks_Block {

	protected $name = 'icon';

	public function __construct() {
		parent::__construct();
	}

	public function render_block( $attrs, $block_content ) {
		// var_dump($attrs['style']);
		return $block_content;
	}

}
new UGM_Blocks_Block_Icon();