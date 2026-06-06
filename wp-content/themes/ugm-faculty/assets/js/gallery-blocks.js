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
	var MediaUpload       = wp.blockEditor.MediaUpload;
	var MediaUploadCheck  = wp.blockEditor.MediaUploadCheck;
	var store             = wp.blockEditor.store;
	var PanelBody         = wp.components.PanelBody;
	var TextControl       = wp.components.TextControl;
	var TextareaControl   = wp.components.TextareaControl;
	var Button            = wp.components.Button;
	var useSelect         = wp.data.useSelect;
	var dispatch          = wp.data.dispatch;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;

	function hasManagementPageBlocks( content ) {
		return content.indexOf( '<!-- wp:ugm/management-hero' ) !== -1 ||
			content.indexOf( '<!-- wp:ugm/management-section' ) !== -1 ||
			content.indexOf( '<!-- wp:ugm/study-program-section' ) !== -1;
	}

	function GalleryTemplateSeeder() {
		var state = useSelect( function ( select ) {
			var editor = select( 'core/editor' );

			return {
				template: editor.getEditedPostAttribute( 'template' ),
				content: editor.getEditedPostAttribute( 'content' ) || '',
			};
		} );

		useEffect( function () {
			if ( state.template !== 'gallery-page' && state.template !== 'page-templates/template-gallery.php' ) {
				return;
			}

			var patch = {};
			if (
				( ! state.content.trim() || hasManagementPageBlocks( state.content ) ) &&
				window.ugmGalleryPageEditor &&
				ugmGalleryPageEditor.defaultBlocks
			) {
				patch.content = ugmGalleryPageEditor.defaultBlocks;
			}

			if ( Object.keys( patch ).length ) {
				dispatch( 'core/editor' ).editPost( patch );
			}
		}, [ state.template, state.content ] );

		return null;
	}

	function findGalleryBlock( candidateBlocks ) {
		var galleryBlock = null;

		candidateBlocks.some( function ( block ) {
			if ( block.name === 'ugm/gallery-page' ) {
				galleryBlock = block;
				return true;
			}

			galleryBlock = Array.isArray( block.innerBlocks ) ? findGalleryBlock( block.innerBlocks ) : null;
			return galleryBlock !== null;
		} );

		return galleryBlock;
	}

	function updateGalleryBlock( candidateBlocks, patch ) {
		return candidateBlocks.some( function ( block ) {
			if ( block.name === 'ugm/gallery-page' ) {
				block.attributes = Object.assign( {}, block.attributes, patch );
				return true;
			}

			return Array.isArray( block.innerBlocks ) && updateGalleryBlock( block.innerBlocks, patch );
		} );
	}

	function galleryItemsControl( attrs, setAttr ) {
		var items = Array.isArray( attrs.galleryItems ) ? attrs.galleryItems : [];

		function updateItem( index, patch ) {
			setAttr( {
				galleryItems: items.map( function ( item, itemIndex ) {
					return itemIndex === index ? Object.assign( {}, item, patch ) : item;
				} ),
			} );
		}

		function removeItem( index ) {
			setAttr( {
				galleryItems: items.filter( function ( item, itemIndex ) {
					return itemIndex !== index;
				} ),
			} );
		}

		return el(
			'div',
			null,
			el( 'p', null, __( 'Tambahkan kartu sebanyak yang diperlukan. Setiap kartu dapat berisi beberapa gambar sekaligus.', 'ugm-faculty' ) ),
			items.map( function ( item, index ) {
				var images = Array.isArray( item.images ) ? item.images : [];

				return el(
					'div',
					{ key: index, style: { borderTop: '1px solid #ddd', marginTop: '12px', paddingTop: '12px' } },
					el( 'strong', null, __( 'Kartu Galeri', 'ugm-faculty' ) + ' ' + ( index + 1 ) ),
					el( TextControl, {
						label: __( 'Judul kartu', 'ugm-faculty' ),
						value: item.title || '',
						onChange: function ( value ) { updateItem( index, { title: value } ); },
					} ),
					el( TextControl, {
						label: __( 'Tanggal', 'ugm-faculty' ),
						value: item.date || '',
						placeholder: 'Monday, 1 June 2026',
						onChange: function ( value ) { updateItem( index, { date: value } ); },
					} ),
					el( TextareaControl, {
						label: __( 'Deskripsi sorotan', 'ugm-faculty' ),
						value: item.description || '',
						rows: 3,
						onChange: function ( value ) { updateItem( index, { description: value } ); },
					} ),
					el( 'p', null, images.length ? images.length + ' ' + __( 'gambar dipilih', 'ugm-faculty' ) : __( 'Belum ada gambar.', 'ugm-faculty' ) ),
					images.length
						? el(
							'div',
							{ style: { display: 'grid', gridTemplateColumns: 'repeat(3, minmax(0, 1fr))', gap: '6px', marginBottom: '10px' } },
							images.map( function ( image, imageIndex ) {
								return el( 'img', {
									key: image.id || image.url || imageIndex,
									src: image.url || '',
									alt: '',
									style: { aspectRatio: '1', display: 'block', objectFit: 'cover', width: '100%' },
								} );
							} )
						)
						: null,
					el(
						MediaUploadCheck,
						null,
						el( MediaUpload, {
							allowedTypes: [ 'image' ],
							multiple: true,
							gallery: true,
							value: images.map( function ( image ) { return image.id; } ),
							onSelect: function ( selectedImages ) {
								updateItem( index, {
									images: ( selectedImages || [] ).map( function ( image ) {
										return { id: image.id || 0, url: image.url || '' };
									} ),
								} );
							},
							render: function ( ref ) {
								return el( Button, { onClick: ref.open, isSecondary: true },
									images.length ? __( 'Ubah kumpulan gambar', 'ugm-faculty' ) : __( 'Pilih banyak gambar', 'ugm-faculty' )
								);
							},
						} )
					),
					el( Button, {
						onClick: function () { removeItem( index ); },
						isDestructive: true,
						style: { marginLeft: '8px' },
					}, __( 'Hapus kartu', 'ugm-faculty' ) )
				);
			} ),
			el( Button, {
				onClick: function () {
					setAttr( {
						galleryItems: items.concat( [ { title: '', date: '', description: '', images: [] } ] ),
					} );
				},
				isPrimary: true,
				style: { marginTop: '14px' },
			}, __( 'Tambah kartu galeri', 'ugm-faculty' ) )
		);
	}

	var withGalleryPostContentControls = createHigherOrderComponent( function ( BlockEdit ) {
		return function ( props ) {
			var state = useSelect( function ( select ) {
				var editor = select( 'core/editor' );
				var blockEditor = select( 'core/block-editor' );
				var settings = blockEditor.getSettings();
				var galleryEditorBlock = findGalleryBlock( blockEditor.getBlocks() );

				return {
					template:                 editor.getEditedPostAttribute( 'template' ),
					content:                  editor.getEditedPostAttribute( 'content' ) || '',
					galleryClientId:          galleryEditorBlock ? galleryEditorBlock.clientId : '',
					isGalleryTemplatePreview: !! settings.isPreviewMode && blockEditor.getBlocks().some( function ( block ) {
						return block.name === 'ugm/gallery-template-preview';
					} ),
				};
			} );
			var galleryBlock = findGalleryBlock( wp.blocks.parse( state.content ) );

			function setAttr( patch ) {
				if ( state.galleryClientId ) {
					dispatch( store ).updateBlockAttributes( state.galleryClientId, patch );
				}

				var nextBlocks = wp.blocks.parse( state.content );
				if ( updateGalleryBlock( nextBlocks, patch ) ) {
					dispatch( 'core/editor' ).editPost( { content: wp.blocks.serialize( nextBlocks ) } );
				}
			}

			return state.isGalleryTemplatePreview && props.name === 'core/post-content'
				? null
				: el(
					Fragment,
					null,
					el( BlockEdit, props ),
					props.name === 'core/post-content' &&
						( state.template === 'gallery-page' || state.template === 'page-templates/template-gallery.php' ) &&
						galleryBlock
						? el( InspectorControls, null, gallerySectionControls( galleryBlock.attributes, setAttr ) )
						: null
				);
		};
	}, 'withGalleryPostContentControls' );

	wp.hooks.addFilter(
		'editor.BlockEdit',
		'ugm-faculty/gallery-post-content-controls',
		withGalleryPostContentControls
	);

	function gallerySectionControls( attrs, setAttr ) {
		return [
			el(
				PanelBody,
				{ key: 'gallery-settings', title: __( 'Pengaturan Section Galeri', 'ugm-faculty' ), initialOpen: true },
				el( TextControl, {
					label: __( 'Judul Halaman', 'ugm-faculty' ),
					value: attrs.title || '',
					onChange: function ( value ) { setAttr( { title: value } ); },
				} ),
				el( TextControl, {
					label: __( 'Label Tombol Hero', 'ugm-faculty' ),
					value: attrs.buttonLabel || '',
					onChange: function ( value ) { setAttr( { buttonLabel: value } ); },
				} )
			),
			el(
				PanelBody,
				{ key: 'gallery-items', title: __( 'Kartu Galeri Manual', 'ugm-faculty' ), initialOpen: true },
				galleryItemsControl( attrs, setAttr )
			),
		];
	}

	registerBlockType( 'ugm/gallery-page', {
		title:       __( 'Galeri Page', 'ugm-faculty' ),
		description: __( 'Halaman galeri dengan hero sorotan dan grid kartu galeri.', 'ugm-faculty' ),
		category:    'ugm-gallery-page-sections',
		icon:        'format-gallery',
		supports:    { html: false, multiple: false },
		attributes: {
			title:        { type: 'string', default: 'Galeri' },
			buttonLabel:  { type: 'string', default: 'Selengkapnya' },
			galleryItems: { type: 'array', default: [] },
			isPreview:    { type: 'boolean', default: false },
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
					gallerySectionControls( attrs, setAttr )
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

	registerBlockType( 'ugm/gallery-template-preview', {
		title:    __( 'Galeri Page Template Preview', 'ugm-faculty' ),
		category: 'ugm-gallery-page-sections',
		supports: {
			html:     false,
			inserter: false,
		},
		edit: function () {
			var isPreviewMode = useSelect( function ( select ) {
				return !! select( 'core/block-editor' ).getSettings().isPreviewMode;
			}, [] );

			return isPreviewMode
				? el( ServerSideRender, {
					block:      'ugm/gallery-page',
					attributes: { title: 'Galeri', isPreview: true },
				} )
				: null;
		},
		save: function () {
			return null;
		},
	} );

	if ( wp.plugins && wp.plugins.registerPlugin ) {
		wp.plugins.registerPlugin( 'ugm-gallery-page-seeder', {
			render: GalleryTemplateSeeder,
		} );
	}

}() );
