/**
 * UGM Gallery Page block editor registration.
 *
 * @package ugm-faculty
 */
( function () {
	'use strict';

	var registerBlockType = wp.blocks.registerBlockType;
	var el                = wp.element.createElement;
	var Fragment          = wp.element.Fragment;
	var useEffect         = wp.element.useEffect;
	var __                = wp.i18n.__;
	var ServerSideRender  = wp.serverSideRender;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody         = wp.components.PanelBody;
	var TextControl       = wp.components.TextControl;
	var useSelect         = wp.data.useSelect;
	var dispatch          = wp.data.dispatch;

	function GalleryTemplateSeeder() {
		var template = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'template' );
		} );

		var blockCount = useSelect( function ( select ) {
			return select( 'core/block-editor' ).getBlockCount();
		} );

		useEffect( function () {
			if ( template !== 'gallery-page' && template !== 'page-templates/template-gallery.php' ) {
				return;
			}

			if ( blockCount > 0 || ! window.ugmGalleryPageEditor || ! ugmGalleryPageEditor.defaultBlocks ) {
				return;
			}

			var blocks = wp.blocks.parse( ugmGalleryPageEditor.defaultBlocks );
			if ( blocks && blocks.length ) {
				dispatch( 'core/block-editor' ).insertBlocks( blocks );
			}
		}, [ template, blockCount ] );

		return null;
	}

	registerBlockType( 'ugm/gallery-page', {
		title:       __( 'Galeri Page', 'ugm-faculty' ),
		description: __( 'Halaman galeri dengan hero sorotan dan grid kartu galeri.', 'ugm-faculty' ),
		category:    'ugm-gallery-page-sections',
		icon:        'format-gallery',
		supports:    { html: false, multiple: false },
		attributes: {
			title:        { type: 'string', default: 'Galeri' },
			categorySlug: { type: 'string', default: 'galeri' },
			postsPerPage: { type: 'number', default: 12 },
			buttonLabel:  { type: 'string', default: 'Selengkapnya' },
		},
		edit: function ( props ) {
			var attrs   = props.attributes;
			var setAttr = props.setAttributes;

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Pengaturan Halaman Galeri', 'ugm-faculty' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Judul Halaman', 'ugm-faculty' ),
							value: attrs.title || '',
							onChange: function ( value ) { setAttr( { title: value } ); },
						} ),
						el( TextControl, {
							label: __( 'Slug Kategori', 'ugm-faculty' ),
							help: __( 'Gunakan koma untuk beberapa kategori. Contoh: galeri, fasilitas.', 'ugm-faculty' ),
							value: attrs.categorySlug || '',
							onChange: function ( value ) { setAttr( { categorySlug: value } ); },
						} ),
						el( TextControl, {
							label: __( 'Jumlah Kartu', 'ugm-faculty' ),
							type: 'number',
							value: attrs.postsPerPage || 12,
							onChange: function ( value ) {
								setAttr( { postsPerPage: parseInt( value, 10 ) || 12 } );
							},
						} ),
						el( TextControl, {
							label: __( 'Label Tombol Hero', 'ugm-faculty' ),
							value: attrs.buttonLabel || '',
							onChange: function ( value ) { setAttr( { buttonLabel: value } ); },
						} )
					)
				),
				el( ServerSideRender, {
					block: 'ugm/gallery-page',
					attributes: attrs,
					httpMethod: 'POST',
				} )
			);
		},
		save: function () {
			return null;
		},
	} );

	wp.domReady( function () {
		if ( wp.plugins && wp.plugins.registerPlugin ) {
			wp.plugins.registerPlugin( 'ugm-gallery-template-seeder', {
				render: GalleryTemplateSeeder,
			} );
		}
	} );
}() );
