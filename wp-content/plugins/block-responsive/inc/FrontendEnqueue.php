<?php
/**
 * Frontend enqueue handler.
 *
 * @package BlockResponsive
 */

namespace Ashraf93\BlockResponsive;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FrontendEnqueue class.
 *
 * @class FrontendEnqueue The class that manages the frontend enqueue.
 */
class FrontendEnqueue {

	/**
	 * The main instance var.
	 *
	 * @var FrontendEnqueue
	 */
	public static $instance = null;

	/**
	 * Defining collected CSS
	 *
	 * @var string
	 */
	protected $css = '';

	/**
	 * Constructor for the FrontendEnqueue class.
	 *
	 * Sets up all the appropriate hooks and actions
	 * within our plugin.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize the class.
	 *
	 * Sets up all the appropriate hooks and actions
	 * within our plugin.
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'render_block_data', array( $this, 'collect_block_css' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'wp_loaded', array( $this, 'add_attributes_to_blocks' ) );
		add_filter( 'render_block', array( $this, 'apply_responsive_classes_to_rendered_blocks' ), 10, 2 );
	}

	/**
	 * Check if block is supported by the responsive plugin.
	 *
	 * @param string $block_name The block name.
	 * @return bool
	 */
	private function is_supported_block( $block_name ) {
		if ( ! is_string( $block_name ) || empty( $block_name ) ) {
			return false;
		}
		
		return strpos( $block_name, 'core/' ) === 0 || strpos( $block_name, 'woocommerce/' ) === 0 || strpos( $block_name, 'easy-tabs-block/' ) === 0;
	}

	/**
	 * Collect CSS from blocks as they are rendered.
	 *
	 * @param array $parsed_block The parsed block data.
	 * @return array The modified parsed block data.
	 */
	public function collect_block_css( $parsed_block ) {
		// Only process supported blocks
		if ( ! isset( $parsed_block['blockName'] ) || ! $this->is_supported_block( $parsed_block['blockName'] ) ) {
			return $parsed_block;
		}

		$attributes = $parsed_block['attrs'] ?? array();

		// Only process blocks with responsive settings
		if ( empty( $attributes['uniqueBlockClass'] ) || empty( $attributes['hasBlockResponsive'] ) ) {
			return $parsed_block;
		}

		// Generate and collect CSS for this block
		$css_content = $this->generate_block_css( $attributes['uniqueBlockClass'], $attributes );
		if ( ! empty( $css_content ) ) {
			$this->css .= $css_content;
		}

		return $parsed_block;
	}

	/**
	 * Enqueue frontend assets for the current page.
	 *
	 * @return void
	 */
	public function enqueue_frontend_assets() {
		global $post;

		// For non-block themes, process post content to trigger CSS collection
		if ( ! wp_is_block_theme() && ! empty( $post->post_content ) ) {
			do_blocks( $post->post_content );
		}

		// Register and enqueue style handle
		wp_register_style(
			'blockresponsive_frontend_css',
			false,
			array(),
			BLOCKRESPONSIVE_VERSION
		);
		wp_enqueue_style( 'blockresponsive_frontend_css' );

		// Add collected CSS as inline styles
		if ( ! empty( $this->css ) ) {
			wp_add_inline_style( 'blockresponsive_frontend_css', $this->css );
		}
	}

	/**
	 * Apply responsive classes to rendered blocks.
	 *
	 * @param string $block_content The block content.
	 * @param array  $parsed_block The parsed block data.
	 * @return string
	 */
	public function apply_responsive_classes_to_rendered_blocks( $block_content, $parsed_block ) {
		// Only process supported blocks
		if ( ! $this->is_supported_block( $parsed_block['blockName'] ) ) {
			return $block_content;
		}
	
		$attributes = $parsed_block['attrs'] ?? array();
		
		// Only process blocks with responsive settings
		if ( empty( $attributes['uniqueBlockClass'] ) || empty( $attributes['hasBlockResponsive'] ) ) {
			return $block_content;
		}
	
		// Use WordPress HTML Tag Processor to safely add classes
		if ( class_exists( '\WP_HTML_Tag_Processor' ) ) {
			$processor = new \WP_HTML_Tag_Processor( $block_content );
			
			// Find the first tag and add the class
			if ( $processor->next_tag() ) {
				$processor->add_class( $attributes['uniqueBlockClass'] );
				
				return $processor->get_updated_html();
			}
		}
	
		return $block_content;
	}

	/**
	 * Convert spacing values to CSS custom properties.
	 *
	 * @param string $value The spacing value to convert.
	 * @return string
	 */
	private function convert_spacing_value( $value ) {
		if ( is_string( $value ) && strpos( $value, 'var:preset|spacing|' ) === 0 ) {
			$spacing_value = str_replace( 'var:preset|spacing|', '', $value );
			return 'var(--wp--preset--spacing--' . $spacing_value . ')';
		}
		return $value;
	}

	/**
	 * Helper function to check if a device has any values.
	 *
	 * @param string $device Device type (desktop, tablet, mobile).
	 * @param array  $attributes Block attributes.
	 * @return bool
	 */
	private function has_device_values( $device, $attributes ) {
		if ( true === isset( $attributes['displayControls'][ $device ] ) && $attributes['displayControls'][ $device ] ) {
			return true;
		}

		$controls = array(
			$attributes['alignmentControls'][ $device ] ?? null,
			$attributes['backgroundImageControls'][ $device ] ?? null,
			$attributes['borderAndShadowControls'][ $device ] ?? null,
			$attributes['colorsControls'][ $device ] ?? null,
			$attributes['dimensionsControls'][ $device ] ?? null,
			$attributes['typographyControls'][ $device ] ?? null,
			$attributes['positionAndOverflowControls'][ $device ] ?? null,
		);

		foreach ( $controls as $control ) {
			if ( ! $control ) {
				continue;
			}
			foreach ( $control as $value ) {
				if ( null === $value || '' === $value ) {
					continue;
				}
				if ( is_array( $value ) && empty( $value ) ) {
					continue;
				}
				if ( is_string( $value ) && trim( $value ) === '' ) {
					continue;
				}
				return true;
			}
		}

		return false;
	}

	/**
	 * Generate CSS for a block's responsive settings.
	 *
	 * @param string $unique_class Unique class name for the block.
	 * @param array  $attributes Block attributes containing responsive settings.
	 * @return string
	 */
	public function generate_block_css( $unique_class, $attributes ) {
		$css = '';

		// Extract control arrays for better readability.
		$display_controls                = $attributes['displayControls'] ?? array();
		$alignment_controls              = $attributes['alignmentControls'] ?? array();
		$background_image_controls       = $attributes['backgroundImageControls'] ?? array();
		$border_and_shadow_controls      = $attributes['borderAndShadowControls'] ?? array();
		$colors_controls                 = $attributes['colorsControls'] ?? array();
		$dimensions_controls             = $attributes['dimensionsControls'] ?? array();
		$typography_controls             = $attributes['typographyControls'] ?? array();
		$position_and_overflow_controls  = $attributes['positionAndOverflowControls'] ?? array();

		// Generate CSS for each device type.
		$css .= $this->generate_desktop_css( $unique_class, $display_controls, $alignment_controls, $background_image_controls, $border_and_shadow_controls, $colors_controls, $dimensions_controls, $typography_controls, $position_and_overflow_controls, $attributes );
		$css .= $this->generate_tablet_css( $unique_class, $display_controls, $alignment_controls, $background_image_controls, $border_and_shadow_controls, $colors_controls, $dimensions_controls, $typography_controls, $position_and_overflow_controls, $attributes );
		$css .= $this->generate_mobile_css( $unique_class, $display_controls, $alignment_controls, $background_image_controls, $border_and_shadow_controls, $colors_controls, $dimensions_controls, $typography_controls, $position_and_overflow_controls, $attributes );

		return $css;
	}

	/**
	 * Generate desktop CSS for a block.
	 *
	 * @param string $unique_class Unique class name for the block.
	 * @param array  $display_controls Display controls.
	 * @param array  $alignment_controls Alignment controls.
	 * @param array  $background_image_controls Background image controls.
	 * @param array  $border_and_shadow_controls Border and shadow controls.
	 * @param array  $colors_controls Colors controls.
	 * @param array  $dimensions_controls Dimensions controls.
	 * @param array  $typography_controls Typography controls.
	 * @param array  $position_and_overflow_controls Position and overflow controls.
	 * @param array  $attributes Block attributes.
	 * @return string
	 */
	private function generate_desktop_css( $unique_class, $display_controls, $alignment_controls, $background_image_controls, $border_and_shadow_controls, $colors_controls, $dimensions_controls, $typography_controls, $position_and_overflow_controls, $attributes ) {
		$css = '';

		// Only generate if desktop has values.
		if ( ! $this->has_device_values( 'desktop', $attributes ) ) {
			return $css;
		}

		// Generate visibility CSS.
		$css .= $this->generate_display_css( $unique_class, $display_controls, 'desktop' );

		// Start desktop CSS block.
		$css .= '.' . $unique_class . ' {';

		// Generate CSS for each control type.
		$css .= $this->generate_alignment_css( $alignment_controls, 'desktop' );
		$css .= $this->generate_border_and_shadow_css( $border_and_shadow_controls, 'desktop' );
		$css .= $this->generate_background_image_css( $background_image_controls, 'desktop' );
		$css .= $this->generate_dimensions_css( $dimensions_controls, 'desktop' );
		$css .= $this->generate_typography_css( $typography_controls, 'desktop' );
		$css .= $this->generate_colors_css( $colors_controls, 'desktop' );
		$css .= $this->generate_position_and_overflow_css( $position_and_overflow_controls, 'desktop' );

		// Close desktop CSS block.
		$css .= '}';

		// Generate link colors CSS.
		$css .= $this->generate_link_colors_css( $unique_class, $colors_controls, 'desktop' );

		return $css;
	}

	/**
	 * Generate tablet CSS for a block.
	 *
	 * @param string $unique_class Unique class name for the block.
	 * @param array  $display_controls Display controls.
	 * @param array  $alignment_controls Alignment controls.
	 * @param array  $background_image_controls Background image controls.
	 * @param array  $border_and_shadow_controls Border and shadow controls.
	 * @param array  $colors_controls Colors controls.
	 * @param array  $dimensions_controls Dimensions controls.
	 * @param array  $typography_controls Typography controls.
	 * @param array  $position_and_overflow_controls Position and overflow controls.
	 * @param array  $attributes Block attributes.
	 * @return string
	 */
	private function generate_tablet_css( $unique_class, $display_controls, $alignment_controls, $background_image_controls, $border_and_shadow_controls, $colors_controls, $dimensions_controls, $typography_controls, $position_and_overflow_controls, $attributes ) {
		$css = '';

		// Only generate if tablet has values.
		if ( ! $this->has_device_values( 'tablet', $attributes ) ) {
			return $css;
		}

		// Start tablet media query.
		$css .= '@media (min-width: 768px) and (max-width: 1024px) {';
		$css .= '.' . $unique_class . ' {';

		// Generate CSS for each control type.
		$css .= $this->generate_display_css( '', $display_controls, 'tablet' );
		$css .= $this->generate_alignment_css( $alignment_controls, 'tablet' );
		$css .= $this->generate_border_and_shadow_css( $border_and_shadow_controls, 'tablet' );
		$css .= $this->generate_background_image_css( $background_image_controls, 'tablet' );
		$css .= $this->generate_dimensions_css( $dimensions_controls, 'tablet' );
		$css .= $this->generate_typography_css( $typography_controls, 'tablet' );
		$css .= $this->generate_colors_css( $colors_controls, 'tablet' );
		$css .= $this->generate_position_and_overflow_css( $position_and_overflow_controls, 'tablet' );

		// Close tablet block.
		$css .= '}';

		// Generate link colors CSS.
		$css .= $this->generate_link_colors_css( $unique_class, $colors_controls, 'tablet' );

		// Close tablet media query.
		$css .= '}';

		return $css;
	}

	/**
	 * Generate mobile CSS for a block.
	 *
	 * @param string $unique_class Unique class name for the block.
	 * @param array  $display_controls Display controls.
	 * @param array  $alignment_controls Alignment controls.
	 * @param array  $background_image_controls Background image controls.
	 * @param array  $border_and_shadow_controls Border and shadow controls.
	 * @param array  $colors_controls Colors controls.
	 * @param array  $dimensions_controls Dimensions controls.
	 * @param array  $typography_controls Typography controls.
	 * @param array  $position_and_overflow_controls Position and overflow controls.
	 * @param array  $attributes Block attributes.
	 * @return string
	 */
	private function generate_mobile_css( $unique_class, $display_controls, $alignment_controls, $background_image_controls, $border_and_shadow_controls, $colors_controls, $dimensions_controls, $typography_controls, $position_and_overflow_controls, $attributes ) {
		$css = '';

		// Only generate if mobile has values.
		if ( ! $this->has_device_values( 'mobile', $attributes ) ) {
			return $css;
		}

		// Start mobile media query.
		$css .= '@media (max-width: 767px) {';
		$css .= '.' . $unique_class . ' {';

		// Generate CSS for each control type.
		$css .= $this->generate_display_css( '', $display_controls, 'mobile' );
		$css .= $this->generate_alignment_css( $alignment_controls, 'mobile' );
		$css .= $this->generate_border_and_shadow_css( $border_and_shadow_controls, 'mobile' );
		$css .= $this->generate_background_image_css( $background_image_controls, 'mobile' );
		$css .= $this->generate_dimensions_css( $dimensions_controls, 'mobile' );
		$css .= $this->generate_typography_css( $typography_controls, 'mobile' );
		$css .= $this->generate_colors_css( $colors_controls, 'mobile' );
		$css .= $this->generate_position_and_overflow_css( $position_and_overflow_controls, 'mobile' );

		// Close mobile block.
		$css .= '}';

		// Generate link colors CSS.
		$css .= $this->generate_link_colors_css( $unique_class, $colors_controls, 'mobile' );

		// Close mobile media query.
		$css .= '}';

		return $css;
	}

	/**
	 * Generate display CSS for a device.
	 *
	 * @param string $unique_class Unique class name for the block.
	 * @param array  $display_controls Display controls.
	 * @param string $device Device type (desktop, tablet, mobile).
	 * @return string
	 */
	private function generate_display_css( $unique_class, $display_controls, $device ) {
		$css = '';

		if ( empty( $display_controls[ $device ] ) ) {
			return $css;
		}

		// For desktop, wrap in media query.
		if ( 'desktop' === $device ) {
			$css .= '@media (min-width: 1025px) {';
			$css .= '.' . $unique_class . ' {';
			$css .= 'display: ' . ( true === $display_controls[ $device ] ? 'none' : '' ) . ' !important;';
			$css .= '}';
			$css .= '}';
		} else {
			$css .= 'display: ' . ( true === $display_controls[ $device ] ? 'none' : '' ) . ' !important;';
		}

		return $css;
	}

	/**
	 * Generate alignment CSS for a device.
	 *
	 * @param array  $alignment_controls Alignment controls.
	 * @param string $device Device type (desktop, tablet, mobile).
	 * @return string
	 */
	private function generate_alignment_css( $alignment_controls, $device ) {
		$css = '';

		if ( empty( $alignment_controls[ $device ] ) ) {
			return $css;
		}

		$device_controls = $alignment_controls[ $device ];

		if ( ! empty( $device_controls['textAlign'] ) ) {
			$css .= 'text-align: ' . $device_controls['textAlign'] . ' !important;';
		}
		if ( ! empty( $device_controls['direction'] ) ) {
			$css .= 'flex-direction: ' . $device_controls['direction'] . ' !important;';
		}
		if ( ! empty( $device_controls['justifyContent'] ) ) {
			$css .= 'justify-content: ' . $device_controls['justifyContent'] . ' !important;';
		}
		if ( ! empty( $device_controls['alignItems'] ) ) {
			$css .= 'align-items: ' . $device_controls['alignItems'] . ' !important;';
		}

		return $css;
	}

	/**
	 * Generate border and shadow CSS for a device.
	 *
	 * @param array  $border_and_shadow_controls Border and shadow controls.
	 * @param string $device Device type (desktop, tablet, mobile).
	 * @return string
	 */
	private function generate_border_and_shadow_css( $border_and_shadow_controls, $device ) {
		$css = '';

		if ( empty( $border_and_shadow_controls[ $device ] ) ) {
			return $css;
		}

		$device_controls = $border_and_shadow_controls[ $device ];

		// General border properties.
		if ( ! empty( $device_controls['border']['color'] ) ) {
			$css .= 'border-color: ' . $device_controls['border']['color'] . ' !important;';
		}
		if ( ! empty( $device_controls['border']['style'] ) ) {
			$css .= 'border-style: ' . $device_controls['border']['style'] . ' !important;';
		}
		if ( ! empty( $device_controls['border']['width'] ) ) {
			$css .= 'border-width: ' . $device_controls['border']['width'] . ' !important;';
		}

		// Individual border sides.
		$border_sides = array( 'top', 'right', 'bottom', 'left' );
		foreach ( $border_sides as $side ) {
			if ( ! empty( $device_controls['border'][ $side ]['width'] ) ) {
				$css .= 'border-' . $side . '-width: ' . $device_controls['border'][ $side ]['width'] . ' !important;';
			}
			if ( ! empty( $device_controls['border'][ $side ]['color'] ) ) {
				$css .= 'border-' . $side . '-color: ' . $device_controls['border'][ $side ]['color'] . ' !important;';
			}
			if ( ! empty( $device_controls['border'][ $side ]['style'] ) ) {
				$css .= 'border-' . $side . '-style: ' . $device_controls['border'][ $side ]['style'] . ' !important;';
			}
		}

		// Border radius.
		if ( ! empty( $device_controls['borderRadius'] ) && ! is_array( $device_controls['borderRadius'] ) ) {
			$css .= 'border-radius: ' . $device_controls['borderRadius'] . ' !important;';
		}

		// Border radius individual sides.
		$border_radius_sides = array(
			'topLeft'     => 'top-left',
			'topRight'    => 'top-right',
			'bottomRight' => 'bottom-right',
			'bottomLeft'  => 'bottom-left',
		);
		foreach ( $border_radius_sides as $side => $value ) {
			if ( ! empty( $device_controls['borderRadius'][ $side ] ) ) {
				$css .= 'border-' . $value . '-radius: ' . $device_controls['borderRadius'][ $side ] . ' !important;';
			}
		}

		// Box shadow.
		if ( $this->has_box_shadow_values( $device_controls['boxShadow'] ?? array() ) ) {
			$box_shadow = $device_controls['boxShadow'];
			$inset      = ( isset( $box_shadow['inset'] ) && true === $box_shadow['inset'] ) ? 'inset' : '';
			$x          = $box_shadow['x'] ?? '0';
			$y          = $box_shadow['y'] ?? '0';
			$blur       = $box_shadow['blur'] ?? '0';
			$spread     = $box_shadow['spread'] ?? '0';
			$color      = $box_shadow['color'] ?? 'transparent';
			$css       .= 'box-shadow: ' . $inset . ' ' . $x . ' ' . $y . ' ' . $blur . ' ' . $spread . ' ' . $color . ' !important;';
		}

		return $css;
	}

	/**
	 * Generate background image CSS for a device.
	 *
	 * @param array  $background_image_controls Background image controls.
	 * @param string $device Device type (desktop, tablet, mobile).
	 * @return string
	 */
	private function generate_background_image_css( $background_image_controls, $device ) {
		$css = '';

		if ( empty( $background_image_controls[ $device ] ) ) {
			return $css;
		}

		$device_controls = $background_image_controls[ $device ];

		// Background image URL.
		if ( ! empty( $device_controls['bgUrl'] ) ) {
			$css .= 'background-image: url(' . $device_controls['bgUrl'] . ') !important;';
		}

		// Background size.
		if ( ! empty( $device_controls['bgSize'] ) ) {
			$bg_width = ! empty( $device_controls['bgWidth'] ) ? $device_controls['bgWidth'] : 'auto';
			$css     .= 'background-size: ' . ( 'tile' === $device_controls['bgSize'] ? $bg_width : $device_controls['bgSize'] ) . ' !important;';
		}

		// Background focal point.
		if ( ! empty( $device_controls['bgFocalPoint'] ) ) {
			$x    = round( $device_controls['bgFocalPoint']['x'] * 100 );
			$y    = round( $device_controls['bgFocalPoint']['y'] * 100 );
			$css .= 'background-position: ' . $x . '% ' . $y . '% !important;';
		}

		// Background attachment.
		if ( isset( $device_controls['bgFixed'] ) ) {
			if ( true === $device_controls['bgFixed'] ) {
				$css .= 'background-attachment: fixed !important;';
			} elseif ( false === $device_controls['bgFixed'] ) {
				$css .= 'background-attachment: scroll !important;';
			}
		}

		// Background repeat.
		if ( isset( $device_controls['bgRepeat'] ) ) {
			if ( true === $device_controls['bgRepeat'] ) {
				$css .= 'background-repeat: repeat !important;';
			} elseif ( false === $device_controls['bgRepeat'] ) {
				$css .= 'background-repeat: no-repeat !important;';
			}
		}

		return $css;
	}

	/**
	 * Generate dimensions CSS for a device.
	 *
	 * @param array  $dimensions_controls Dimensions controls.
	 * @param string $device Device type (desktop, tablet, mobile).
	 * @return string
	 */
	private function generate_dimensions_css( $dimensions_controls, $device ) {
		$css = '';

		if ( empty( $dimensions_controls[ $device ] ) ) {
			return $css;
		}

		$device_controls = $dimensions_controls[ $device ];

		// Padding.
		$padding_sides = array( 'top', 'right', 'bottom', 'left' );
		foreach ( $padding_sides as $side ) {
			if ( isset( $device_controls['padding'][ $side ] ) ) {
				$value = $device_controls['padding'][ $side ];
				if ( '0' === $value ) {
					$css .= 'padding-' . $side . ': 0 !important;';
				} else {
					$css .= 'padding-' . $side . ': ' . $this->convert_spacing_value( $value ) . ' !important;';
				}
			}
		}

		// Margin.
		$margin_sides = array( 'top', 'right', 'bottom', 'left' );
		foreach ( $margin_sides as $side ) {
			if ( isset( $device_controls['margin'][ $side ] ) ) {
				$value = $device_controls['margin'][ $side ];
				if ( '0' === $value ) {
					$css .= 'margin-' . $side . ': 0 !important;';
				} else {
					$css .= 'margin-' . $side . ': ' . $this->convert_spacing_value( $value ) . ' !important;';
				}
			}
		}

		// Block spacing.
		if ( isset( $device_controls['blockSpacing']['top'] ) ) {
			$value = $device_controls['blockSpacing']['top'];
			if ( '0' === $value ) {
				$css .= 'row-gap: 0 !important;';
			} else {
				$css .= 'row-gap: ' . $this->convert_spacing_value( $value ) . ' !important;';
			}
		}
		if ( isset( $device_controls['blockSpacing']['right'] ) ) {
			$value = $device_controls['blockSpacing']['right'];
			if ( '0' === $value ) {
				$css .= 'column-gap: 0 !important;';
			} else {
				$css .= 'column-gap: ' . $this->convert_spacing_value( $value ) . ' !important;';
			}
		}

		// Width and height.
		$dimension_properties = array( 'width', 'minWidth', 'maxWidth', 'height', 'minHeight', 'maxHeight' );
		foreach ( $dimension_properties as $property ) {
			if ( ! empty( $device_controls[ $property ] ) ) {
				$css .= str_replace( 'Width', '-width', str_replace( 'Height', '-height', $property ) ) . ': ' . $device_controls[ $property ] . ' !important;';
			}
		}

		return $css;
	}

	/**
	 * Generate typography CSS for a device.
	 *
	 * @param array  $typography_controls Typography controls.
	 * @param string $device Device type (desktop, tablet, mobile).
	 * @return string
	 */
	private function generate_typography_css( $typography_controls, $device ) {
		$css = '';

		if ( empty( $typography_controls[ $device ] ) ) {
			return $css;
		}

		$device_controls = $typography_controls[ $device ];

		// Font family.
		if ( ! empty( $device_controls['fontFamily'] ) ) {
			$css .= 'font-family: ' . $device_controls['fontFamily'] . ' !important;';
		}

		// Font size.
		if ( ! empty( $device_controls['fontSize'] ) ) {
			$css .= 'font-size: ' . $device_controls['fontSize'] . ' !important;';
		}

		// Font appearance.
		if ( ! empty( $device_controls['fontAppearance']['fontWeight'] ) ) {
			$css .= 'font-weight: ' . $device_controls['fontAppearance']['fontWeight'] . ' !important;';
		}
		if ( ! empty( $device_controls['fontAppearance']['fontStyle'] ) ) {
			$css .= 'font-style: ' . $device_controls['fontAppearance']['fontStyle'] . ' !important;';
		}

		// Line height.
		if ( ! empty( $device_controls['lineHeight'] ) ) {
			$css .= 'line-height: ' . $device_controls['lineHeight'] . ' !important;';
		}

		// Letter spacing.
		if ( ! empty( $device_controls['letterSpacing'] ) ) {
			$css .= 'letter-spacing: ' . $device_controls['letterSpacing'] . ' !important;';
		}

		// Text decoration.
		if ( ! empty( $device_controls['textDecoration'] ) ) {
			$css .= 'text-decoration: ' . $device_controls['textDecoration'] . ' !important;';
		}

		// Text transform.
		if ( ! empty( $device_controls['textTransform'] ) ) {
			$css .= 'text-transform: ' . $device_controls['textTransform'] . ' !important;';
		}

		return $css;
	}

	/**
	 * Generate colors CSS for a device.
	 *
	 * @param array  $colors_controls Colors controls.
	 * @param string $device Device type (desktop, tablet, mobile).
	 * @return string
	 */
	private function generate_colors_css( $colors_controls, $device ) {
		$css = '';

		if ( empty( $colors_controls[ $device ] ) ) {
			return $css;
		}

		$device_controls = $colors_controls[ $device ];

		// Text color.
		if ( ! empty( $device_controls['textColor'] ) ) {
			$css .= 'color: ' . $device_controls['textColor'] . ' !important;';
		}

		// Background color.
		if ( ! empty( $device_controls['backgroundColor'] ) ) {
			if ( strpos( $device_controls['backgroundColor'], 'linear-gradient' ) === 0 ) {
				$css .= 'background-image: ' . $device_controls['backgroundColor'] . ' !important;';
			} elseif ( strpos( $device_controls['backgroundColor'], '#' ) === 0 ) {
				$css .= 'background-color: ' . $device_controls['backgroundColor'] . ' !important;';
			}
		}

		return $css;
	}

	/**
	 * Generate position and overflow CSS for a device.
	 *
	 * @param array  $position_and_overflow_controls Position and overflow controls.
	 * @param string $device Device type (desktop, tablet, mobile).
	 * @return string
	 */
	private function generate_position_and_overflow_css( $position_and_overflow_controls, $device ) {
		$css = '';

		if ( empty( $position_and_overflow_controls[ $device ] ) ) {
			return $css;
		}

		$device_controls = $position_and_overflow_controls[ $device ];

		// Position.
		if ( ! empty( $device_controls['position'] ) ) {
			$css .= 'position: ' . $device_controls['position'] . ' !important;';
		}

		// Position offset.
		if ( ! empty( $device_controls['positionOffset'] ) ) {
			$position_sides = array( 'top', 'right', 'bottom', 'left' );
			foreach ( $position_sides as $side ) {
				if ( isset( $device_controls['positionOffset'][ $side ] ) && '' !== $device_controls['positionOffset'][ $side ] ) {
					$css .= $side . ': ' . $device_controls['positionOffset'][ $side ] . ' !important;';
				}
			}
		}

		// Z-Index.
		if ( isset( $device_controls['zIndex'] ) && '' !== $device_controls['zIndex'] ) {
			$css .= 'z-index: ' . $device_controls['zIndex'] . ' !important;';
		}

		// Overflow.
		if ( ! empty( $device_controls['overflow'] ) ) {
			$css .= 'overflow: ' . $device_controls['overflow'] . ' !important;';
		}

		// Overflow X.
		if ( ! empty( $device_controls['overflowX'] ) ) {
			$css .= 'overflow-x: ' . $device_controls['overflowX'] . ' !important;';
		}

		// Overflow Y.
		if ( ! empty( $device_controls['overflowY'] ) ) {
			$css .= 'overflow-y: ' . $device_controls['overflowY'] . ' !important;';
		}

		return $css;
	}

	/**
	 * Generate link colors CSS for a device.
	 *
	 * @param string $unique_class Unique class name for the block.
	 * @param array  $colors_controls Colors controls.
	 * @param string $device Device type (desktop, tablet, mobile).
	 * @return string
	 */
	private function generate_link_colors_css( $unique_class, $colors_controls, $device ) {
		$css = '';

		if ( empty( $colors_controls[ $device ] ) ) {
			return $css;
		}

		$device_controls = $colors_controls[ $device ];

		// Link color.
		if ( ! empty( $device_controls['linkColor'] ) ) {
			$css .= '.' . $unique_class . ' a {';
			$css .= 'color: ' . $device_controls['linkColor'] . ' !important;';
			$css .= '}';
		}

		// Link hover color.
		if ( ! empty( $device_controls['linkHoverColor'] ) ) {
			$css .= '.' . $unique_class . ' a:hover {';
			$css .= 'color: ' . $device_controls['linkHoverColor'] . ' !important;';
			$css .= '}';
		}

		return $css;
	}

	/**
	 * Check if box shadow has values.
	 *
	 * @param array $box_shadow Box shadow controls.
	 * @return bool
	 */
	private function has_box_shadow_values( $box_shadow ) {
		return ! empty( $box_shadow['x'] ) ||
				! empty( $box_shadow['y'] ) ||
				! empty( $box_shadow['blur'] ) ||
				! empty( $box_shadow['spread'] ) ||
				! empty( $box_shadow['color'] );
	}

	/**
	 * Adds the `hasBlockResponsive`, `uniqueBlockClass`,
	 * `displayControls`, `alignmentControls`,
	 * `backgroundImageControls`, `borderAndShadowControls`,
	 * `colorsControls`, `dimensionsControls` and
	 * `typographyControls` attributes to all blocks,
	 * to avoid `Invalid parameter(s): attributes` error
	 * in Gutenberg.
	 *
	 * @return void
	 */
	public function add_attributes_to_blocks() {
		$registered_blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();

		foreach ( $registered_blocks as $name => $block ) {
			if ( $this->is_supported_block( $name ) ) {
				$block->attributes['hasBlockResponsive']           = array(
					'type'    => 'boolean',
					'default' => false,
				);
				$block->attributes['uniqueBlockClass']             = array(
					'type'    => 'string',
					'default' => '',
				);
				$block->attributes['displayControls']              = array(
					'type'    => 'object',
					'default' => array(),
				);
				$block->attributes['alignmentControls']            = array(
					'type'    => 'object',
					'default' => array(),
				);
				$block->attributes['backgroundImageControls']      = array(
					'type'    => 'object',
					'default' => array(),
				);
				$block->attributes['borderAndShadowControls']      = array(
					'type'    => 'object',
					'default' => array(),
				);
				$block->attributes['colorsControls']               = array(
					'type'    => 'object',
					'default' => array(),
				);
				$block->attributes['dimensionsControls']           = array(
					'type'    => 'object',
					'default' => array(),
				);
				$block->attributes['typographyControls']           = array(
					'type'    => 'object',
					'default' => array(),
				);
				$block->attributes['positionAndOverflowControls']  = array(
					'type'    => 'object',
					'default' => array(),
				);
			}
		}
	}

	/**
	 * The instance method for the static class.
	 *
	 * Defines and returns the instance of the static class.
	 *
	 * @return FrontendEnqueue
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->init();
		}

		return self::$instance;
	}
}

FrontendEnqueue::instance();
