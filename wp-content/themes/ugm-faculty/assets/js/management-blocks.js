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
	var useRef                     = wp.element.useRef;
	var __                         = wp.i18n.__;
	var ServerSideRender           = wp.serverSideRender;
	var InspectorControls          = wp.blockEditor.InspectorControls;
	var useBlockProps              = wp.blockEditor.useBlockProps;
	var MediaUpload                = wp.blockEditor.MediaUpload;
	var MediaUploadCheck           = wp.blockEditor.MediaUploadCheck;
	var PanelBody                  = wp.components.PanelBody;
	var TextControl                = wp.components.TextControl;
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
			findBlock( blocks, 'ugm/study-program-section' ) ||
			findBlock( blocks, 'ugm/management-share-section' )
		);
	}

	function isManagementBlockName( blockName ) {
		return blockName === 'ugm/management-hero' ||
			blockName === 'ugm/management-section' ||
			blockName === 'ugm/study-program-section' ||
			blockName === 'ugm/management-share-section';
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
			content.indexOf( '<!-- wp:ugm/study-program-section' ) !== -1 ||
			content.indexOf( '<!-- wp:ugm/management-share-section' ) !== -1
		) {
			return true;
		}

		return content.replace( /<!--[\s\S]*?-->/g, '' )
			.replace( /<[^>]+>/g, '' )
			.replace( /&nbsp;/g, ' ' )
			.trim() !== '';
	}

	function getBlockStructureSignature( blocks ) {
		return ( Array.isArray( blocks ) ? blocks : [] ).map( function ( block ) {
			return block.name + '[' + getBlockStructureSignature( block.innerBlocks ) + ']';
		} ).join( ',' );
	}

	/* ------------------------------------------------------------------
	 * Template Seeder — seed blok default ke halaman manajemen kosong
	 * (sama persis dengan AgendaTemplateSeeder / GalleryTemplateSeeder)
	 * ------------------------------------------------------------------ */
	function ManagementTemplateSeeder() {
		var lastSeedSignature = useRef( '' );
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
			var contentBlocks = wp.blocks.parse( state.content || '' );
			var contentHasMeaning = hasMeaningfulContent( state.content );
			var needsLegacyUpgrade =
				state.content.indexOf( '<!-- wp:ugm/management-section' ) !== -1 &&
				state.content.indexOf( '<!-- wp:ugm/management-hero' ) === -1;
			var seedSignature = [
				state.template || '',
				contentHasMeaning ? 'meaningful' : 'empty',
				needsLegacyUpgrade ? 'legacy' : 'current',
				getBlockStructureSignature( contentBlocks ),
				getBlockStructureSignature( state.blocks ),
			].join( '|' );

			if ( seedSignature === lastSeedSignature.current ) {
				return;
			}

			lastSeedSignature.current = seedSignature;

			if ( ! isManagementTemplate( state.template ) ) {
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
				if ( ! contentHasMeaning && ! hasManagementBlock( innerBlocks ) && hasOnlyEmptyEditorBlocks( innerBlocks ) ) {
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
				onChange: function ( v ) {
					onChange( { name: v } );
				},
			} ),
			el( TextControl, {
				label:    __( 'Jabatan', 'ugm-faculty' ),
				value:    person.role || '',
				onChange: function ( v ) {
					onChange( { role: v } );
				},
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
				onChange: function ( v ) {
						setAttr( { title: v } );
					},
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
				} ),
				el( 'p', { style: { margin: '14px 0 6px' } }, __( 'Gambar Background Hero', 'ugm-faculty' ) ),
				attrs.backgroundImageUrl
					? el( 'img', {
						src:   attrs.backgroundImageUrl,
						alt:   '',
						style: { display: 'block', width: '100%', maxHeight: '120px', objectFit: 'cover', marginBottom: '8px', border: '1px solid #ddd' },
					} )
					: null,
				el( MediaUploadCheck, null,
					el( MediaUpload, {
						allowedTypes: [ 'image' ],
						value:        attrs.backgroundImageId || 0,
						onSelect: function ( image ) {
							setAttr( {
								backgroundImageId:  image.id || 0,
								backgroundImageUrl: image.url || '',
							} );
						},
						render: function ( ref ) {
							return el(
								'div',
								{ style: { display: 'flex', gap: '6px', flexWrap: 'wrap' } },
								el( Button, { isSecondary: true, isSmall: true, onClick: ref.open },
									attrs.backgroundImageUrl ? __( 'Ganti gambar background', 'ugm-faculty' ) : __( 'Pilih gambar background', 'ugm-faculty' )
								),
								attrs.backgroundImageUrl
									? el( Button, {
										isDestructive: true,
										isSmall:       true,
										onClick: function () {
											setAttr( { backgroundImageId: 0, backgroundImageUrl: '' } );
										},
									}, __( 'Hapus gambar', 'ugm-faculty' ) )
									: null
							);
						},
					} )
				)
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
				onChange: function ( v ) {
						setAttr( { title: v } );
					},
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
				onChange: function ( v ) {
						setAttr( { title: v } );
					},
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
						onChange: function ( v ) {
							updProg( pi, { title: v } );
						},
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

	function managementShareControls( attrs, setAttr ) {
		var links = getShareLinks( attrs );

		function updateLink( index, patch ) {
			setAttr( {
				links: links.map( function ( link, linkIndex ) {
					return linkIndex === index ? Object.assign( {}, link, patch ) : link;
				} ),
			} );
		}

		return [
			el(
				PanelBody,
				{ key: 'share-settings', title: __( 'Pengaturan Share', 'ugm-faculty' ), initialOpen: true },
				el( TextControl, {
					label:    __( 'Judul Share', 'ugm-faculty' ),
					value:    attrs.title || '',
					onChange: function ( v ) {
						setAttr( { title: v } );
					},
				} )
			),
		].concat(
			links.map( function ( link, index ) {
				return el(
					PanelBody,
					{
						key:         'share-link-' + link.className,
						title:       link.label || link.defaultLabel,
						initialOpen: index === 0,
					},
					el( TextControl, {
						label:    __( 'Label', 'ugm-faculty' ),
						value:    link.label || '',
						onChange: function ( v ) {
							updateLink( index, { label: v } );
						},
					} ),
					el( TextControl, {
						label:    __( 'Icon Fallback', 'ugm-faculty' ),
						value:    link.icon || '',
						help:     __( 'Teks pendek jika gambar ikon belum dipilih.', 'ugm-faculty' ),
						onChange: function ( v ) {
							updateLink( index, { icon: v } );
						},
					} ),
					link.iconUrl
						? el( 'img', {
							src:   link.iconUrl,
							alt:   '',
							style: { display: 'block', width: '36px', height: '36px', objectFit: 'contain', marginBottom: '8px', border: '1px solid #ddd', padding: '4px' },
						} )
						: null,
					el( MediaUploadCheck, null,
						el( MediaUpload, {
							allowedTypes: [ 'image' ],
							value:        link.iconId || 0,
							onSelect: function ( image ) {
								updateLink( index, {
									iconId:  image.id || 0,
									iconUrl: image.url || '',
								} );
							},
							render: function ( ref ) {
								return el(
									'div',
									{ style: { display: 'flex', gap: '6px', flexWrap: 'wrap', marginBottom: '12px' } },
									el( Button, { isSecondary: true, isSmall: true, onClick: ref.open },
										link.iconUrl ? __( 'Ganti gambar icon', 'ugm-faculty' ) : __( 'Pilih gambar icon', 'ugm-faculty' )
									),
									link.iconUrl
										? el( Button, {
											isDestructive: true,
											isSmall:       true,
											onClick: function () {
												updateLink( index, { iconId: 0, iconUrl: '' } );
											},
										}, __( 'Hapus gambar', 'ugm-faculty' ) )
										: null
								);
							},
						} )
					),
					el( TextControl, {
						label:    __( 'URL', 'ugm-faculty' ),
						value:    link.url || '',
						help:     __( 'Kosongkan untuk memakai URL share otomatis.', 'ugm-faculty' ),
						onChange: function ( v ) {
							updateLink( index, { url: v } );
						},
					} )
				);
			} )
		);
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

	function updateManagementBlockAttributes( props ) {
		return function ( patch ) {
			props.setAttributes( patch );
		};
	}

	function normalizeEditorPeople( people ) {
		return ( Array.isArray( people ) ? people : [] ).filter( function ( person ) {
			if ( ! person || typeof person !== 'object' ) {
				return false;
			}

			return !! (
				( person.name && String( person.name ).trim() ) ||
				( person.role && String( person.role ).trim() ) ||
				( person.imageUrl && String( person.imageUrl ).trim() )
			);
		} );
	}

	function renderManagementPersonPreview( person, modifier ) {
		var className = 'ugm-management-person';

		if ( modifier ) {
			className += ' ugm-management-person--' + modifier;
		}

		return el(
			'article',
			{ className: className },
			el(
				'div',
				{ className: 'ugm-management-person__photo' },
				person.imageUrl
					? el( 'img', { src: person.imageUrl, alt: person.name || '', loading: 'lazy', decoding: 'async' } )
					: el( 'span', { 'aria-hidden': true } )
			),
			el(
				'div',
				{ className: 'ugm-management-person__info' },
				person.name ? el( 'h3', null, person.name ) : null,
				person.role ? el( 'p', null, person.role ) : null
			)
		);
	}

	function renderManagementHeroPreview( attrs ) {
		var heroStyle = { backgroundColor: attrs.background || '#dceef6' };
		if ( attrs.backgroundImageUrl ) {
			heroStyle.backgroundImage = 'url(' + attrs.backgroundImageUrl + ')';
		}
		return el(
			'header',
			{
				className: 'ugm-management-page__hero',
				style:     heroStyle,
			},
			el( 'h1', null, attrs.title || 'Manajemen Organisasi' )
		);
	}

	function renderManagementSectionPreview( attrs ) {
		var people = normalizeEditorPeople( attrs.people );

		return el(
			'section',
			{ className: 'ugm-management-section', 'aria-labelledby': 'ugm-management-section-title' },
			el(
				'header',
				{ className: 'ugm-management-section__heading' },
				el( 'h2', { id: 'ugm-management-section-title' }, attrs.title || 'Manajemen Fakultas' )
			),
			people.length
				? [
					el(
						'div',
						{ key: 'leader', className: 'ugm-management-section__leader' },
						renderManagementPersonPreview( people[0], 'leader' )
					),
					people.length > 1
						? el(
							'div',
							{ key: 'team', className: 'ugm-management-section__team' },
							people.slice( 1 ).map( function ( person, index ) {
								return el(
									Fragment,
									{ key: index },
									renderManagementPersonPreview( person )
								);
							} )
						)
						: null,
				]
				: el( 'p', { className: 'ugm-management-empty' }, __( 'Tambahkan kartu pimpinan fakultas melalui sidebar editor.', 'ugm-faculty' ) )
		);
	}

	function normalizeEditorPrograms( programs ) {
		return ( Array.isArray( programs ) ? programs : [] ).map( function ( program ) {
			var people = normalizeEditorPeople( program && program.people );
			var title = program && program.title ? String( program.title ).trim() : '';

			if ( ! title && ! people.length ) {
				return null;
			}

			return {
				title:  title,
				people: people,
			};
		} ).filter( Boolean );
	}

	function renderStudyProgramPreview( attrs ) {
		var programs = normalizeEditorPrograms( attrs.programs );

		return el(
			'section',
			{ className: 'ugm-study-program-section', 'aria-labelledby': 'ugm-study-program-section-title' },
			el(
				'header',
				{ className: 'ugm-management-section__heading' },
				el( 'h2', { id: 'ugm-study-program-section-title' }, attrs.title || 'Program Studi' )
			),
			programs.length
				? el(
					'div',
					{ className: 'ugm-study-program-list' },
					programs.map( function ( program, programIndex ) {
						return el(
							'section',
							{ key: programIndex, className: 'ugm-study-program' },
							el( 'h3', null, program.title || __( 'Program Studi', 'ugm-faculty' ) ),
							el(
								'div',
								{ className: 'ugm-study-program__people' },
								program.people.map( function ( person, personIndex ) {
									return el(
										Fragment,
										{ key: personIndex },
										renderManagementPersonPreview( person, 'program' )
									);
								} )
							)
						);
					} )
				)
				: el( 'p', { className: 'ugm-management-empty' }, __( 'Tambahkan program studi dan pengelolanya melalui sidebar editor.', 'ugm-faculty' ) )
		);
	}

	function getShareLinks( attrs ) {
		var defaults = [
			{ className: 'facebook', icon: 'f', iconId: 0, iconUrl: '', label: 'Facebook', defaultLabel: 'Facebook', url: '' },
			{ className: 'twitter', icon: 't', iconId: 0, iconUrl: '', label: 'Twitter', defaultLabel: 'Twitter', url: '' },
			{ className: 'linkedin', icon: 'in', iconId: 0, iconUrl: '', label: 'LinkedIn', defaultLabel: 'LinkedIn', url: '' },
			{ className: 'whatsapp', icon: 'wa', iconId: 0, iconUrl: '', label: 'WhatsApp', defaultLabel: 'WhatsApp', url: '' },
			{ className: 'email', icon: '@', iconId: 0, iconUrl: '', label: 'Email', defaultLabel: 'Email', url: '' },
		];
		var links = Array.isArray( attrs.links ) ? attrs.links : [];

		return defaults.map( function ( defaultLink, index ) {
			var custom = links[ index ] && typeof links[ index ] === 'object' ? links[ index ] : {};

			return Object.assign( {}, defaultLink, {
				label: custom.label !== undefined ? custom.label : defaultLink.label,
				icon:  custom.icon !== undefined ? custom.icon : defaultLink.icon,
				iconId: custom.iconId !== undefined ? custom.iconId : defaultLink.iconId,
				iconUrl: custom.iconUrl !== undefined ? custom.iconUrl : defaultLink.iconUrl,
				url:   custom.url !== undefined ? custom.url : defaultLink.url,
			} );
		} );
	}

	function renderManagementSharePreview( attrs ) {
		var links = getShareLinks( attrs );

		return el(
			'section',
			{ className: 'ugm-management-share', 'aria-label': attrs.title || 'Share This Page' },
			el( 'span', { className: 'ugm-management-share__label' }, attrs.title || 'Share This Page' ),
			el(
				'div',
				{ className: 'ugm-management-share__links' },
				links.map( function ( link ) {
					return el(
						'a',
						{
							key:       link.className,
							className: 'ugm-management-share__button ugm-management-share__button--' + link.className,
							href:      link.url || '#',
							onClick:   function ( event ) { event.preventDefault(); },
							'aria-label': link.label,
						},
						link.iconUrl
							? el( 'img', { src: link.iconUrl, alt: '', loading: 'lazy', decoding: 'async' } )
							: link.icon
					);
				} )
			)
		);
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
			backgroundImageId:  { type: 'number', default: 0 },
			backgroundImageUrl: { type: 'string', default: '' },
			_templateSlug: { type: 'string', default: '' },
		},
		edit: function ( props ) {
			var attrs   = props.attributes;
			var setAttr = updateManagementBlockAttributes( props );
			var renderState = useManagementRenderState( attrs );
			var blockProps = useBlockProps( {
				className: 'ugm-management-editor-block ugm-management-editor-block--hero',
			} );
			if ( ! renderState.isVisible ) {
				return null;
			}

			return el(
				Fragment,
				null,
				el( InspectorControls, null, managementHeroControls( attrs, setAttr ) ),
				el(
					'div',
					blockProps,
					renderManagementHeroPreview( attrs )
				)
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
			var setAttr = updateManagementBlockAttributes( props );
			var renderState = useManagementRenderState( attrs );
			var blockProps = useBlockProps( {
				className: 'ugm-management-editor-block ugm-management-editor-block--faculty',
			} );
			if ( ! renderState.isVisible ) {
				return null;
			}

			return el(
				Fragment,
				null,
				el( InspectorControls, null, managementSectionControls( attrs, setAttr ) ),
				el(
					'div',
					blockProps,
					renderManagementSectionPreview( attrs )
				)
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
			var setAttr = updateManagementBlockAttributes( props );
			var renderState = useManagementRenderState( attrs );
			var blockProps = useBlockProps( {
				className: 'ugm-management-editor-block ugm-management-editor-block--study-program',
			} );
			if ( ! renderState.isVisible ) {
				return null;
			}

			return el(
				Fragment,
				null,
				el( InspectorControls, null, studyProgramSectionControls( attrs, setAttr ) ),
				el(
					'div',
					blockProps,
					renderStudyProgramPreview( attrs )
				)
			);
		},
		save: function () { return null; },
		} );

	registerBlockType( 'ugm/management-share-section', {
		title:       __( 'Share This Page', 'ugm-faculty' ),
		description: __( 'Tombol share halaman manajemen.', 'ugm-faculty' ),
		category:    'ugm-management-page-sections',
		icon:        'share',
		supports:    { html: false, multiple: false },
		attributes:  {
			title:         { type: 'string', default: 'Share This Page' },
			links:         { type: 'array', default: [] },
			_templateSlug: { type: 'string', default: '' },
		},
		edit: function ( props ) {
			var attrs   = props.attributes;
			var setAttr = updateManagementBlockAttributes( props );
			var renderState = useManagementRenderState( attrs );
			var blockProps = useBlockProps( {
				className: 'ugm-management-editor-block ugm-management-editor-block--share',
			} );
			if ( ! renderState.isVisible ) {
				return null;
			}

			return el(
				Fragment,
				null,
				el( InspectorControls, null, managementShareControls( attrs, setAttr ) ),
				el(
					'div',
					blockProps,
					renderManagementSharePreview( attrs )
				)
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
