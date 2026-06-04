/**
 * UGM Management Page block editor registration.
 *
 * Pola IDENTIK dengan gallery-blocks.js dan agenda-blocks.js:
 *  - registerBlockType  → SSR preview di canvas + InspectorControls di sidebar
 *  - registerPlugin     → ManagementTemplateSeeder (seed blok ke konten kosong)
 *
 * No build step — ES5 + WP globals.
 *
 * @package ugm-faculty
 */
( function () {
	'use strict';

	var registerBlockType          = wp.blocks.registerBlockType;
	var el                         = wp.element.createElement;
	var Fragment                   = wp.element.Fragment;
	var useEffect                  = wp.element.useEffect;
	var useState                   = wp.element.useState;
	var __                         = wp.i18n.__;
	var ServerSideRender           = wp.serverSideRender;
	var InspectorControls          = wp.blockEditor.InspectorControls;
	var MediaUpload                = wp.blockEditor.MediaUpload;
	var MediaUploadCheck           = wp.blockEditor.MediaUploadCheck;
	var PanelBody                  = wp.components.PanelBody;
	var TextControl                = wp.components.TextControl;
	var SelectControl              = wp.components.SelectControl;
	var ColorPalette               = wp.components.ColorPalette;
	var Button                     = wp.components.Button;
	var useSelect                  = wp.data.useSelect;
	var dispatch                   = wp.data.dispatch;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;

	function isManagementTemplate( template ) {
		return template === 'management-page' ||
			template === 'page-templates/template-management.php';
	}

	function getEditorRenderingMode( select ) {
		var editor = select( 'core/editor' );

		return editor && typeof editor.getRenderingMode === 'function'
			? editor.getRenderingMode()
			: '';
	}

	function hasManagementBlock( blocks ) {
		return !! (
			findBlock( blocks, 'ugm/management-hero' ) ||
			findBlock( blocks, 'ugm/management-section' ) ||
			findBlock( blocks, 'ugm/study-program-section' )
		);
	}

	function isManagementBlockName( blockName ) {
		return blockName === 'ugm/management-hero' ||
			blockName === 'ugm/management-section' ||
			blockName === 'ugm/study-program-section';
	}

	function removeManagementBlocks( blocks ) {
		return ( Array.isArray( blocks ) ? blocks : [] ).filter( function ( block ) {
			return ! isManagementBlockName( block.name );
		} ).map( function ( block ) {
			if ( Array.isArray( block.innerBlocks ) && block.innerBlocks.length ) {
				block = Object.assign( {}, block );
				block.innerBlocks = removeManagementBlocks( block.innerBlocks );
			}
			return block;
		} );
	}

	function isEmptyEditorBlock( block ) {
		if ( ! block ) { return true; }
		if ( block.name === 'core/paragraph' ) {
			return ! ( block.attributes && block.attributes.content && String( block.attributes.content ).trim() );
		}
		return false;
	}

	function hasOnlyEmptyEditorBlocks( blocks ) {
		blocks = Array.isArray( blocks ) ? blocks : [];
		return blocks.length === 0 || blocks.every( isEmptyEditorBlock );
	}

	function hasMeaningfulContent( content ) {
		content = String( content || '' );
		if (
			content.indexOf( '<!-- wp:ugm/management-hero' ) !== -1 ||
			content.indexOf( '<!-- wp:ugm/management-section' ) !== -1 ||
			content.indexOf( '<!-- wp:ugm/study-program-section' ) !== -1
		) {
			return true;
		}

		return content.replace( /<!--[\s\S]*?-->/g, '' )
			.replace( /<[^>]+>/g, '' )
			.replace( /&nbsp;/g, ' ' )
			.trim() !== '';
	}

	/* ------------------------------------------------------------------
	 * Template Seeder — seed blok default ke halaman manajemen kosong
	 * (sama persis dengan AgendaTemplateSeeder / GalleryTemplateSeeder)
	 * ------------------------------------------------------------------ */
	function ManagementTemplateSeeder() {
		var state = useSelect( function ( select ) {
			var editor = select( 'core/editor' );
			var blockEditor = select( 'core/block-editor' );
			return {
				template: editor.getEditedPostAttribute( 'template' ),
				content:  editor.getEditedPostAttribute( 'content' ) || '',
				blocks:   blockEditor.getBlocks(),
			};
		} );

		useEffect( function () {
			if ( ! isManagementTemplate( state.template ) ) {
				var contentBlocks = wp.blocks.parse( state.content || '' );
				if ( hasManagementBlock( contentBlocks ) ) {
					dispatch( 'core/editor' ).editPost( {
						content: wp.blocks.serialize( removeManagementBlocks( contentBlocks ) ).trim(),
					} );
				}

				if ( hasManagementBlock( state.blocks ) ) {
					var postContentBlock = findBlock( state.blocks, 'core/post-content' );
					if ( postContentBlock ) {
						dispatch( 'core/block-editor' ).replaceInnerBlocks(
							postContentBlock.clientId,
							removeManagementBlocks( postContentBlock.innerBlocks || [] ),
							false
						);
					} else {
						dispatch( 'core/block-editor' ).resetBlocks( removeManagementBlocks( state.blocks ) );
					}
				}
				return;
			}

			if ( ! window.ugmManagementPageEditor || ! ugmManagementPageEditor.defaultBlocks ) { return; }

			var defaultBlocks = wp.blocks.parse( ugmManagementPageEditor.defaultBlocks );
			if ( ! defaultBlocks || ! defaultBlocks.length ) { return; }

			var postContent = findBlock( state.blocks, 'core/post-content' );
			if ( postContent ) {
				var innerBlocks = Array.isArray( postContent.innerBlocks ) ? postContent.innerBlocks : [];
				if ( ! hasManagementBlock( innerBlocks ) && hasOnlyEmptyEditorBlocks( innerBlocks ) ) {
					dispatch( 'core/block-editor' ).replaceInnerBlocks(
						postContent.clientId,
						defaultBlocks,
						false
					);
					dispatch( 'core/editor' ).editPost( {
						content: ugmManagementPageEditor.defaultBlocks,
					} );
				}
				return;
			}

			if ( ! hasMeaningfulContent( state.content ) ) {
				if ( ! hasManagementBlock( state.blocks ) ) {
					dispatch( 'core/block-editor' ).insertBlocks( defaultBlocks );
				}
				dispatch( 'core/editor' ).editPost( {
					content: ugmManagementPageEditor.defaultBlocks,
				} );
				return;
			}

			var upgraded = upgradeManagementContent( state.content );
			if ( upgraded !== state.content ) {
				dispatch( 'core/editor' ).editPost( { content: upgraded } );
			}
		}, [ state.template, state.content, state.blocks ] );

		return null;
	}

	function upgradeManagementContent( content ) {
		if (
			content.indexOf( '<!-- wp:ugm/management-section' ) === -1 ||
			content.indexOf( '<!-- wp:ugm/management-hero' ) !== -1
		) {
			return content;
		}

		var blocks = wp.blocks.parse( content );
		blocks.some( function ( block, index ) {
			if ( block.name !== 'ugm/management-section' ) {
				return false;
			}

			var attrs = Object.assign( {}, block.attributes );
			var hero = wp.blocks.createBlock( 'ugm/management-hero', {
				title:      attrs.heroTitle || 'Manajemen Organisasi',
				background: attrs.heroBackground || '#dceef6',
			} );

			delete attrs.heroTitle;
			delete attrs.heroBackground;
			block.attributes = attrs;
			blocks.splice( index, 0, hero );
			return true;
		} );

		return wp.blocks.serialize( blocks );
	}

	/* ------------------------------------------------------------------
	 * Helper: satu baris personel di sidebar
	 * ------------------------------------------------------------------ */
	function personRow( person, index, onChange, onRemove, onMoveUp, onMoveDown ) {
		return el(
			'div',
			{
				key:   index,
				style: { borderTop: '1px solid #ddd', marginTop: '12px', paddingTop: '12px' },
			},
			el(
				'div',
				{ style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '6px' } },
				el( 'strong', null,
					__( 'Personel', 'ugm-faculty' ) + ' ' + ( index + 1 ) +
					( index === 0 ? ' (' + __( 'Pimpinan Utama', 'ugm-faculty' ) + ')' : '' )
				),
				el(
					'span',
					{ style: { display: 'flex', gap: '2px' } },
					onMoveUp   ? el( Button, { isSmall: true, icon: 'arrow-up-alt2',   onClick: onMoveUp,   label: __( 'Naik', 'ugm-faculty' )  } ) : null,
					onMoveDown ? el( Button, { isSmall: true, icon: 'arrow-down-alt2', onClick: onMoveDown, label: __( 'Turun', 'ugm-faculty' ) } ) : null,
					el( Button, { isSmall: true, isDestructive: true, icon: 'trash', onClick: onRemove, label: __( 'Hapus', 'ugm-faculty' ) } )
				)
			),
			el( TextControl, {
				label:    __( 'Nama', 'ugm-faculty' ),
				value:    person.name || '',
				onChange: function ( v ) { onChange( { name: v } ); },
			} ),
			el( TextControl, {
				label:    __( 'Jabatan', 'ugm-faculty' ),
				value:    person.role || '',
				onChange: function ( v ) { onChange( { role: v } ); },
			} ),
			person.imageUrl
				? el( 'img', {
					src:   person.imageUrl,
					alt:   '',
					style: { display: 'block', width: '80px', height: '80px', objectFit: 'cover', borderRadius: '3px', marginBottom: '6px', border: '1px solid #ddd' },
				} )
				: null,
			el( MediaUploadCheck, null,
				el( MediaUpload, {
					allowedTypes: [ 'image' ],
					value:        person.imageId || 0,
					onSelect: function ( img ) { onChange( { imageId: img.id || 0, imageUrl: img.url || '' } ); },
					render: function ( ref ) {
						return el(
							'div',
							{ style: { display: 'flex', gap: '6px', flexWrap: 'wrap' } },
							el( Button, { isSecondary: true, isSmall: true, onClick: ref.open },
								person.imageUrl ? __( 'Ganti foto', 'ugm-faculty' ) : __( 'Pilih foto', 'ugm-faculty' )
							),
							person.imageUrl
								? el( Button, { isDestructive: true, isSmall: true, onClick: function () { onChange( { imageId: 0, imageUrl: '' } ); } },
									__( 'Hapus foto', 'ugm-faculty' )
								)
								: null
						);
					},
				} )
			)
		);
	}

	/* ------------------------------------------------------------------
	 * Helper: daftar personel
	 * ------------------------------------------------------------------ */
	function peopleControl( people, setPeople, addLabel ) {
		people = Array.isArray( people ) ? people : [];

		function upd( idx, patch ) {
			setPeople( people.map( function ( p, i ) { return i === idx ? Object.assign( {}, p, patch ) : p; } ) );
		}
		function rem( idx ) {
			setPeople( people.filter( function ( p, i ) { return i !== idx; } ) );
		}
		function mov( from, to ) {
			if ( to < 0 || to >= people.length ) { return; }
			var arr = people.slice(), tmp = arr[ from ];
			arr[ from ] = arr[ to ]; arr[ to ] = tmp;
			setPeople( arr );
		}

		return el(
			'div',
			null,
			people.map( function ( person, idx ) {
				return personRow(
					person, idx,
					function ( patch ) { upd( idx, patch ); },
					function () { rem( idx ); },
					idx > 0               ? function () { mov( idx, idx - 1 ); } : null,
					idx < people.length - 1 ? function () { mov( idx, idx + 1 ); } : null
				);
			} ),
			el( Button, {
				isPrimary: true,
				style:     { marginTop: '14px' },
				onClick:   function () { setPeople( people.concat( [ { name: '', role: '', imageId: 0, imageUrl: '' } ] ) ); },
			}, '+ ' + ( addLabel || __( 'Tambah personel', 'ugm-faculty' ) ) )
		);
	}

	function managementHeroControls( attrs, setAttr ) {
		return [
			el(
				PanelBody,
				{ key: 'hero-settings', title: __( 'Pengaturan Hero', 'ugm-faculty' ), initialOpen: true },
				el( TextControl, {
					label:    __( 'Judul Hero', 'ugm-faculty' ),
					value:    attrs.title || '',
					onChange: function ( v ) { setAttr( { title: v } ); },
				} ),
				el( 'p', { style: { margin: '0 0 6px' } }, __( 'Warna Background Hero', 'ugm-faculty' ) ),
				el( ColorPalette, {
					value:    attrs.background || '#dceef6',
					onChange: function ( v ) { setAttr( { background: v || '#dceef6' } ); },
					colors: [
						{ name: __( 'Biru Muda', 'ugm-faculty' ), color: '#dceef6' },
						{ name: __( 'Putih', 'ugm-faculty' ), color: '#ffffff' },
						{ name: __( 'Biru UGM', 'ugm-faculty' ), color: '#074a73' },
					],
				} )
			),
		];
	}

	/* ------------------------------------------------------------------
	 * Sidebar panels untuk ugm/management-section
	 * ------------------------------------------------------------------ */
	function managementSectionControls( attrs, setAttr ) {
		var people = Array.isArray( attrs.people ) ? attrs.people : [];
		return [
			el(
				PanelBody,
				{ key: 'mgmt-settings', title: __( 'Pengaturan Section', 'ugm-faculty' ), initialOpen: true },
				el( TextControl, {
					label:    __( 'Judul Section', 'ugm-faculty' ),
					value:    attrs.title || '',
					onChange: function ( v ) { setAttr( { title: v } ); },
				} )
			),
			el(
				PanelBody,
				{ key: 'mgmt-people', title: __( 'Daftar Pimpinan', 'ugm-faculty' ), initialOpen: true },
				el( 'p', { style: { margin: '0 0 8px', fontSize: '12px', color: '#666' } },
					__( 'Personel pertama = Dekan (tengah). Selanjutnya = Wakil Dekan (grid 3 kolom).', 'ugm-faculty' )
				),
				peopleControl(
					people,
					function ( next ) { setAttr( { people: next } ); },
					__( 'Tambah Pimpinan', 'ugm-faculty' )
				)
			),
		];
	}

	/* ------------------------------------------------------------------
	 * Sidebar panels untuk ugm/study-program-section
	 * ------------------------------------------------------------------ */
	function studyProgramSectionControls( attrs, setAttr ) {
		var programs = Array.isArray( attrs.programs ) ? attrs.programs : [];

		function setPrograms( next ) { setAttr( { programs: next } ); }
		function updProg( pi, patch ) {
			setPrograms( programs.map( function ( p, i ) { return i === pi ? Object.assign( {}, p, patch ) : p; } ) );
		}
		function remProg( pi ) { setPrograms( programs.filter( function ( p, i ) { return i !== pi; } ) ); }
		function movProg( from, to ) {
			if ( to < 0 || to >= programs.length ) { return; }
			var arr = programs.slice(), tmp = arr[ from ];
			arr[ from ] = arr[ to ]; arr[ to ] = tmp;
			setPrograms( arr );
		}

		return [
			el(
				PanelBody,
				{ key: 'prodi-settings', title: __( 'Pengaturan Section', 'ugm-faculty' ), initialOpen: true },
				el( TextControl, {
					label:    __( 'Judul Section', 'ugm-faculty' ),
					value:    attrs.title || '',
					onChange: function ( v ) { setAttr( { title: v } ); },
				} )
			),
		].concat(
			programs.map( function ( program, pi ) {
				var people = Array.isArray( program.people ) ? program.people : [];
				return el(
					PanelBody,
					{
						key:         'prodi-' + pi,
						title:       program.title || ( __( 'Program Studi', 'ugm-faculty' ) + ' ' + ( pi + 1 ) ),
						initialOpen: pi === 0,
					},
					el( TextControl, {
						label:    __( 'Nama Program Studi', 'ugm-faculty' ),
						value:    program.title || '',
						onChange: function ( v ) { updProg( pi, { title: v } ); },
					} ),
					el(
						'div',
						{ style: { display: 'flex', gap: '4px', marginBottom: '8px' } },
						pi > 0 ? el( Button, { isSecondary: true, isSmall: true, icon: 'arrow-up-alt2',
							onClick: function () { movProg( pi, pi - 1 ); },
							label: __( 'Pindah ke atas', 'ugm-faculty' ),
						} ) : null,
						pi < programs.length - 1 ? el( Button, { isSecondary: true, isSmall: true, icon: 'arrow-down-alt2',
							onClick: function () { movProg( pi, pi + 1 ); },
							label: __( 'Pindah ke bawah', 'ugm-faculty' ),
						} ) : null,
						el( Button, { isDestructive: true, isSmall: true, icon: 'trash',
							onClick: function () { remProg( pi ); },
							label: __( 'Hapus program studi', 'ugm-faculty' ),
						} )
					),
					peopleControl(
						people,
						function ( next ) { updProg( pi, { people: next } ); },
						__( 'Tambah Pengelola', 'ugm-faculty' )
					)
				);
			} )
		).concat( [
			el(
				'div',
				{ key: 'prodi-add', style: { padding: '8px 16px 16px' } },
				el( Button, {
					isPrimary: true,
					style:     { width: '100%', justifyContent: 'center' },
					onClick:   function () { setPrograms( programs.concat( [ { title: '', people: [] } ] ) ); },
				}, '+ ' + __( 'Tambah Program Studi', 'ugm-faculty' ) )
			),
		] );
	}

	/* ------------------------------------------------------------------
	 * registerBlockType — pola identik dengan agenda-blocks.js
	 * ------------------------------------------------------------------ */
	function findBlock( blocks, name ) {
		var found = null;
		( blocks || [] ).some( function ( block ) {
			if ( block.name === name ) { found = block; return true; }
			found = findBlock( block.innerBlocks, name );
			return found !== null;
		} );
		return found;
	}

	function updateBlock( blocks, name, patch ) {
		return ( blocks || [] ).some( function ( block ) {
			if ( block.name === name ) {
				block.attributes = Object.assign( {}, block.attributes, patch );
				return true;
			}
			return updateBlock( block.innerBlocks, name, patch );
		} );
	}

	var withManagementContentControls = createHigherOrderComponent( function ( BlockEdit ) {
		return function ( props ) {
			var activeState = useState( 'hero' );
			var activeSection = activeState[0];
			var setActiveSection = activeState[1];
			var state = useSelect( function ( select ) {
				var editor = select( 'core/editor' );
				return {
					template: editor.getEditedPostAttribute( 'template' ),
					content:  editor.getEditedPostAttribute( 'content' ) || '',
				};
			} );

			var isManagement =
				state.template === 'management-page' ||
				state.template === 'page-templates/template-management.php';

			if ( ! isManagement || props.name !== 'core/post-content' ) {
				return el( BlockEdit, props );
			}

			var blocks = wp.blocks.parse( state.content );
			var sections = {
				hero:   { name: 'ugm/management-hero', block: findBlock( blocks, 'ugm/management-hero' ) },
				people: { name: 'ugm/management-section', block: findBlock( blocks, 'ugm/management-section' ) },
				study:  { name: 'ugm/study-program-section', block: findBlock( blocks, 'ugm/study-program-section' ) },
			};
			var current = sections[ activeSection ];

			function setAttr( patch ) {
				var nextBlocks = wp.blocks.parse( state.content );
				if ( current && updateBlock( nextBlocks, current.name, patch ) ) {
					dispatch( 'core/editor' ).editPost( { content: wp.blocks.serialize( nextBlocks ) } );
				}
			}

			var controls = [];
			if ( current && current.block ) {
				if ( activeSection === 'hero' ) {
					controls = managementHeroControls( current.block.attributes, setAttr );
				} else if ( activeSection === 'people' ) {
					controls = managementSectionControls( current.block.attributes, setAttr );
				} else {
					controls = studyProgramSectionControls( current.block.attributes, setAttr );
				}
			}

			return el(
				Fragment,
				null,
				el( BlockEdit, props ),
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Section Manajemen', 'ugm-faculty' ), initialOpen: true },
						el( SelectControl, {
							label:    __( 'Section yang Diedit', 'ugm-faculty' ),
							value:    activeSection,
							options:  [
								{ label: __( 'Hero', 'ugm-faculty' ), value: 'hero' },
								{ label: __( 'Daftar Pimpinan', 'ugm-faculty' ), value: 'people' },
								{ label: __( 'Program Studi', 'ugm-faculty' ), value: 'study' },
							],
							onChange: setActiveSection,
						} )
					),
					controls
				)
			);
		};
	}, 'withManagementContentControls' );

	wp.hooks.addFilter(
		'editor.BlockEdit',
		'ugm-faculty/management-content-controls',
		withManagementContentControls
	);

	var withManagementTemplateVisibility = createHigherOrderComponent( function ( BlockListBlock ) {
		return function ( props ) {
			var state = useSelect( function ( select ) {
				var editor = select( 'core/editor' );

				return {
					template:      editor.getEditedPostAttribute( 'template' ),
					renderingMode: getEditorRenderingMode( select ),
				};
			}, [] );

			if (
				props.name === 'ugm/management-template-preview' &&
				isManagementTemplate( state.template )
			) {
				return null;
			}

			if (
				isManagementBlockName( props.name ) &&
				isManagementTemplate( state.template ) &&
				state.renderingMode === 'post-only'
			) {
				return null;
			}

			return el( BlockListBlock, props );
		};
	}, 'withManagementTemplateVisibility' );

	wp.hooks.addFilter(
		'editor.BlockListBlock',
		'ugm-faculty/management-template-visibility',
		withManagementTemplateVisibility
	);

	function useManagementRenderState( attrs ) {
		var state = useSelect( function ( select ) {
			var editor = select( 'core/editor' );

			return {
				template:      editor.getEditedPostAttribute( 'template' ) || '',
				renderingMode: getEditorRenderingMode( select ),
			};
		}, [] );

		return {
			isVisible:  isManagementTemplate( state.template ) && state.renderingMode !== 'post-only',
			attributes: Object.assign( {}, attrs, { _templateSlug: state.template } ),
		};
	}

	registerBlockType( 'ugm/management-hero', {
		title:       __( 'Hero Manajemen', 'ugm-faculty' ),
		description: __( 'Hero halaman manajemen yang dikelola manual.', 'ugm-faculty' ),
		category:    'ugm-management-page-sections',
		icon:        'cover-image',
		supports:    { html: false, multiple: false },
		attributes:  {
			title:         { type: 'string', default: 'Manajemen Organisasi' },
			background:    { type: 'string', default: '#dceef6' },
			_templateSlug: { type: 'string', default: '' },
		},
		edit: function ( props ) {
			var attrs   = props.attributes;
			var setAttr = props.setAttributes;
			var renderState = useManagementRenderState( attrs );
			if ( ! renderState.isVisible ) {
				return null;
			}

			return el(
				Fragment,
				null,
				el( InspectorControls, null, managementHeroControls( attrs, setAttr ) ),
				el( ServerSideRender, { block: 'ugm/management-hero', attributes: renderState.attributes, httpMethod: 'POST' } )
			);
		},
		save: function () { return null; },
		} );

	registerBlockType( 'ugm/management-section', {
		title:       __( 'Manajemen Fakultas', 'ugm-faculty' ),
		description: __( 'Kartu pimpinan fakultas yang dikelola manual.', 'ugm-faculty' ),
		category:    'ugm-management-page-sections',
		icon:        'groups',
		supports:    { html: false, multiple: false },
		attributes:  {
			title:         { type: 'string', default: 'Manajemen Fakultas' },
			people:        { type: 'array',  default: [] },
			_templateSlug: { type: 'string', default: '' },
		},
		edit: function ( props ) {
			var attrs   = props.attributes;
			var setAttr = props.setAttributes;
			var renderState = useManagementRenderState( attrs );
			if ( ! renderState.isVisible ) {
				return null;
			}

			return el(
				Fragment,
				null,
				el( InspectorControls, null, managementSectionControls( attrs, setAttr ) ),
				el( ServerSideRender, { block: 'ugm/management-section', attributes: renderState.attributes, httpMethod: 'POST' } )
			);
		},
		save: function () { return null; },
		} );

	registerBlockType( 'ugm/study-program-section', {
		title:       __( 'Program Studi', 'ugm-faculty' ),
		description: __( 'Daftar program studi dan pengelolanya yang dikelola manual.', 'ugm-faculty' ),
		category:    'ugm-management-page-sections',
		icon:        'welcome-learn-more',
		supports:    { html: false, multiple: false },
		attributes:  {
			title:         { type: 'string', default: 'Program Studi' },
			programs:      { type: 'array',  default: [] },
			_templateSlug: { type: 'string', default: '' },
		},
		edit: function ( props ) {
			var attrs   = props.attributes;
			var setAttr = props.setAttributes;
			var renderState = useManagementRenderState( attrs );
			if ( ! renderState.isVisible ) {
				return null;
			}

			return el(
				Fragment,
				null,
				el( InspectorControls, null, studyProgramSectionControls( attrs, setAttr ) ),
				el( ServerSideRender, { block: 'ugm/study-program-section', attributes: renderState.attributes, httpMethod: 'POST' } )
			);
		},
		save: function () { return null; },
		} );

	registerBlockType( 'ugm/management-template-preview', {
		title:    __( 'Manajemen Page Template Preview', 'ugm-faculty' ),
		category: 'ugm-management-page-sections',
		supports: {
			html:     false,
			inserter: false,
		},
		edit: function () {
			return el( ServerSideRender, {
				block:      'ugm/management-template-preview',
				attributes: {},
				httpMethod: 'POST',
			} );
		},
		save: function () { return null; },
	} );

	/* ------------------------------------------------------------------
	 * registerPlugin — seed blok ke konten kosong (pola agenda/gallery)
	 * ------------------------------------------------------------------ */
	if ( wp.plugins && wp.plugins.registerPlugin ) {
		wp.plugins.registerPlugin( 'ugm-management-page-seeder', {
			render: ManagementTemplateSeeder,
		} );
	}

}() );
