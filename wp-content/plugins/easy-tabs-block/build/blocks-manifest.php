<?php
// This file is generated. Do not modify it manually.
return array(
	'tab-button' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'easy-tabs-block/tab-button',
		'version' => '1.0.8',
		'title' => 'Tab Button',
		'description' => 'A customizable button for tab navigation, allowing users to switch between different content sections seamlessly.',
		'parent' => array(
			'easy-tabs-block/tab-buttons'
		),
		'supports' => array(
			'html' => false,
			'ariaLabel' => true,
			'color' => array(
				'gradients' => true,
				'__experimentalDefaultControls' => array(
					'background' => true,
					'text' => true
				)
			),
			'typography' => array(
				'fontSize' => true,
				'lineHeight' => true,
				'__experimentalFontFamily' => true,
				'__experimentalFontWeight' => true,
				'__experimentalFontStyle' => true,
				'__experimentalTextTransform' => true,
				'__experimentalTextDecoration' => true,
				'__experimentalLetterSpacing' => true,
				'__experimentalDefaultControls' => array(
					'fontSize' => true
				)
			),
			'reusable' => false,
			'shadow' => true,
			'spacing' => array(
				'margin' => true,
				'padding' => true,
				'__experimentalDefaultControls' => array(
					'padding' => true
				)
			),
			'__experimentalBorder' => array(
				'color' => true,
				'radius' => true,
				'style' => true,
				'width' => true,
				'__experimentalDefaultControls' => array(
					'color' => true,
					'radius' => true,
					'style' => true,
					'width' => true
				)
			)
		),
		'textdomain' => 'easy-tabs-block',
		'editorScript' => 'file:./index.js',
		'style' => 'file:./style-index.css'
	),
	'tab-buttons' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'easy-tabs-block/tab-buttons',
		'version' => '1.0.8',
		'title' => 'Tab Buttons',
		'description' => 'A block for tabbed navigation buttons, improving user experience by organizing content.',
		'ancestor' => array(
			'easy-tabs-block/tabs'
		),
		'supports' => array(
			'anchor' => false,
			'html' => false,
			'ariaLabel' => true,
			'__experimentalExposeControlsToChildren' => true,
			'color' => array(
				'gradients' => true,
				'heading' => true,
				'link' => true,
				'text' => true,
				'__experimentalDefaultControls' => array(
					'text' => true,
					'background' => true
				)
			),
			'shadow' => true,
			'spacing' => array(
				'blockGap' => true,
				'margin' => true,
				'padding' => true,
				'__experimentalDefaultControls' => array(
					'margin' => true,
					'padding' => true,
					'blockGap' => true
				)
			),
			'typography' => array(
				'fontSize' => true,
				'lineHeight' => true,
				'__experimentalFontFamily' => true,
				'__experimentalFontWeight' => true,
				'__experimentalFontStyle' => true,
				'__experimentalTextTransform' => true,
				'__experimentalTextDecoration' => true,
				'__experimentalLetterSpacing' => true,
				'__experimentalDefaultControls' => array(
					'fontSize' => true
				)
			),
			'__experimentalBorder' => array(
				'color' => true,
				'radius' => true,
				'style' => true,
				'width' => true,
				'__experimentalDefaultControls' => array(
					'color' => false,
					'radius' => false,
					'style' => false,
					'width' => false
				)
			),
			'position' => array(
				'sticky' => true
			),
			'layout' => array(
				'allowSwitching' => false,
				'allowInheriting' => false,
				'default' => array(
					'type' => 'flex'
				)
			)
		),
		'textdomain' => 'easy-tabs-block',
		'editorScript' => 'file:./index.js',
		'style' => 'file:./style-index.css'
	),
	'tab-content' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'easy-tabs-block/tab-content',
		'version' => '1.0.8',
		'title' => 'Tab Content',
		'description' => 'A block for displaying content within tabs, allowing users to switch between different sections seamlessly.',
		'supports' => array(
			'anchor' => false,
			'html' => false,
			'ariaLabel' => true,
			'color' => array(
				'gradients' => true,
				'link' => true,
				'text' => true,
				'background' => true,
				'__experimentalDefaultControls' => array(
					'background' => true,
					'text' => true
				)
			),
			'background' => array(
				'backgroundImage' => true,
				'__experimentalDefaultControls' => array(
					'backgroundImage' => true
				)
			),
			'spacing' => array(
				'margin' => true,
				'padding' => true,
				'__experimentalDefaultControls' => array(
					'padding' => true,
					'margin' => false
				)
			),
			'dimensions' => array(
				'minHeight' => true
			),
			'__experimentalBorder' => array(
				'color' => true,
				'radius' => true,
				'style' => true,
				'width' => true,
				'__experimentalDefaultControls' => array(
					'color' => true,
					'radius' => false,
					'style' => true,
					'width' => true
				)
			),
			'typography' => array(
				'fontSize' => true,
				'lineHeight' => true,
				'__experimentalFontFamily' => true,
				'__experimentalFontWeight' => true,
				'__experimentalFontStyle' => true,
				'__experimentalTextTransform' => true,
				'__experimentalTextDecoration' => true,
				'__experimentalLetterSpacing' => true,
				'__experimentalDefaultControls' => array(
					'fontSize' => true
				)
			)
		),
		'parent' => array(
			'easy-tabs-block/tab-contents'
		),
		'textdomain' => 'easy-tabs-block',
		'editorScript' => 'file:./index.js',
		'style' => 'file:./style-index.css'
	),
	'tab-contents' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'easy-tabs-block/tab-contents',
		'version' => '1.0.8',
		'title' => 'Tab Contents',
		'description' => 'This block displays content organized into tabs, enabling users to easily switch between different sections.',
		'supports' => array(
			'anchor' => false,
			'html' => false,
			'ariaLabel' => true,
			'background' => array(
				'backgroundImage' => true,
				'backgroundSize' => true,
				'__experimentalDefaultControls' => array(
					'backgroundImage' => true
				)
			),
			'color' => array(
				'gradients' => true,
				'heading' => true,
				'link' => true,
				'background' => true,
				'text' => true,
				'__experimentalDefaultControls' => array(
					'background' => true,
					'text' => true
				)
			),
			'shadow' => true,
			'spacing' => array(
				'margin' => true,
				'padding' => true,
				'__experimentalDefaultControls' => array(
					'padding' => true,
					'margin' => true
				)
			),
			'__experimentalBorder' => array(
				'color' => true,
				'radius' => true,
				'style' => true,
				'width' => true,
				'__experimentalDefaultControls' => array(
					'color' => true,
					'radius' => true,
					'style' => true,
					'width' => true
				)
			),
			'typography' => array(
				'fontSize' => true,
				'lineHeight' => true,
				'__experimentalFontFamily' => true,
				'__experimentalFontWeight' => true,
				'__experimentalFontStyle' => true,
				'__experimentalTextTransform' => true,
				'__experimentalTextDecoration' => true,
				'__experimentalLetterSpacing' => true,
				'__experimentalDefaultControls' => array(
					'fontSize' => true
				)
			)
		),
		'textdomain' => 'easy-tabs-block',
		'ancestor' => array(
			'easy-tabs-block/tabs'
		),
		'editorScript' => 'file:./index.js',
		'style' => 'file:./style-index.css'
	),
	'tab-icon' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'easy-tabs-block/tab-icon',
		'version' => '1.0.8',
		'title' => 'Tab Icon',
		'icon' => 'marker',
		'description' => 'A block for displaying an icon for a tab.',
		'ancestor' => array(
			'easy-tabs-block/tabs'
		),
		'supports' => array(
			'anchor' => false,
			'html' => false,
			'ariaLabel' => true,
			'spacing' => array(
				'margin' => true,
				'__experimentalDefaultControls' => array(
					'margin' => true
				)
			)
		),
		'textdomain' => 'easy-tabs-block',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'tabs' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'easy-tabs-block/tabs',
		'version' => '1.0.8',
		'title' => 'Tabs',
		'category' => 'widgets',
		'allowedBlocks' => array(
			'core/group',
			'core/columns',
			'easy-tabs-block/tab-buttons',
			'easy-tabs-block/tab-contents'
		),
		'description' => 'Simplify information presentation with tabs—efficiently organize content for a seamless and user-friendly experience.',
		'keywords' => array(
			'wordpress tabs',
			'responsive tabs',
			'tabs',
			'block',
			'gutenberg tabs'
		),
		'supports' => array(
			'align' => array(
				'wide',
				'full'
			),
			'anchor' => true,
			'html' => false,
			'ariaLabel' => true,
			'background' => array(
				'backgroundImage' => true,
				'backgroundSize' => true,
				'__experimentalDefaultControls' => array(
					'backgroundImage' => true
				)
			),
			'color' => array(
				'gradients' => true,
				'link' => false,
				'text' => false,
				'__experimentalDefaultControls' => array(
					'background' => true
				)
			),
			'shadow' => true,
			'spacing' => array(
				'margin' => true,
				'padding' => true,
				'__experimentalDefaultControls' => array(
					'padding' => false,
					'margin' => false
				)
			),
			'dimensions' => array(
				'minHeight' => true,
				'__experimentalDefaultControls' => array(
					'minHeight' => false
				)
			),
			'__experimentalBorder' => array(
				'color' => true,
				'radius' => true,
				'style' => true,
				'width' => true,
				'__experimentalDefaultControls' => array(
					'color' => false,
					'radius' => false,
					'style' => false,
					'width' => false
				)
			),
			'position' => array(
				'sticky' => true
			),
			'layout' => array(
				'allowSizingOnChildren' => true
			)
		),
		'textdomain' => 'easy-tabs-block',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js'
	)
);
