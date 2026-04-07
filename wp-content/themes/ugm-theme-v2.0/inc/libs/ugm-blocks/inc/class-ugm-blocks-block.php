<?php

class UGM_Blocks_Block {

	protected $name;

	protected $attrs;

	public function __construct() {
		add_action( 'init', [ $this, 'register_block' ] );
	}

	public function register_block() {
		register_block_type( UGM_BLOCKS_PATH . '/build/' . $this->name, array(
			'render_callback' => [ $this, 'render_block' ]
		) );
	}

	public function render_block( $attrs, $block_content ) {
		return $block_content;
	}

	public function get_color( $type ) {
		if ( 'link' === $type ) {
			if ( isset( $this->attrs['style']['elements']['link']['color']['text'] ) ) {
				$val = str_replace( 'var:preset|color|', '', $this->attrs['style']['elements']['link']['color']['text'] );
			} else {
				$val = "ugm-secondary";
			}
		} else {
			$val = $this->attrs[ $type . 'Color' ];
		}
		if ( false === stripos( $val, '#' ) ) {
			return 'var(--wp--preset--color--' . $val . ')';
		}
		return $val;
	}

	public function get_margin( $orientation ) {
		if ( isset( $this->attrs['style']['spacing']['margin'] ) ) {
			$margin = $this->attrs['style']['spacing']['margin'];
			if ( isset( $margin[ $orientation ] ) ) {
				if ( false !== stripos( $margin[ $orientation ], 'px' ) ) {
					return $margin[ $orientation ];
				} else {
					return 'var(--wp--' . str_replace( '|', '--', str_replace( 'var:','', $margin[ $orientation ] ) ) . ')';
				}
			}
		}
		return 0;
	}

	public function get_padding( $orientation ) {
		if ( isset( $this->attrs['style']['spacing']['padding'] ) ) {
			$padding = $this->attrs['style']['spacing']['padding'];
			if ( isset( $padding[ $orientation ] ) ) {
				if ( false !== stripos( $padding[ $orientation ], 'px' ) ) {
					return $padding[ $orientation ];
				} else {
					return 'var(--wp--' . str_replace( '|', '--', str_replace( 'var:','', $padding[ $orientation ] ) ) . ')';
				}
			}
		}
		return 0;
	}

	public function inline_style() {
		$style = [];
		$orientations = [ 'top', 'right', 'bottom', 'left' ];
		foreach ( $orientations as $orientation ) {
			$margin = $this->get_margin( $orientation );
			if ( ! empty( $margin ) ) {
				$style[] = 'margin-' . $orientation . ':' . $margin . ';';
			}
			$padding = $this->get_padding( $orientation );
			if ( ! empty( $padding ) ) {
				$style[] = 'padding-' . $orientation . ':' . $padding . ';';
			}
		}
		if ( ! empty( $style ) ) {
			echo 'style="' . implode( ' ', $style ) . '"';
		}
	}

}