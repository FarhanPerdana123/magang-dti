/**
 * Sambutan Rektor Gutenberg blocks.
 *
 * Registers editor controls, template seeding, and preview visibility for the Rector Greeting template.
 *
 * @package ugm-faculty
 */
( function () {
	'use strict';

	var registerBlockType          = wp.blocks.registerBlockType;
	var el                         = wp.element.createElement;
	var __                         = wp.i18n.__;
	var ServerSideRender           = wp.serverSideRender;
	var InspectorControls          = wp.blockEditor.InspectorControls;
	var InnerBlocks                = wp.blockEditor.InnerBlocks;
	var useBlockProps              = wp.blockEditor.useBlockProps;
	var MediaUpload                = wp.blockEditor.MediaUpload;
	var MediaUploadCheck           = wp.blockEditor.MediaUploadCheck;
	var PanelBody                  = wp.components.PanelBody;
	var CheckboxControl            = wp.components.CheckboxControl;
	var SelectControl              = wp.components.SelectControl;
	var TextControl                = wp.components.TextControl;
	var TextareaControl            = wp.components.TextareaControl;
	var Button                     = wp.components.Button;
	var Fragment                   = wp.element.Fragment;
	var useEffect                  = wp.element.useEffect;
	var useRef                     = wp.element.useRef;
	var useSelect                  = wp.data.useSelect;
	var dispatch                   = wp.data.dispatch;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;

	function getDefaultRectorBody() {
		return [
			'Selamat datang di Universitas Gadjah Mada (UGM), tempat Anda dapat mulai membuat perubahan nyata.',
			'Sebagai salah satu universitas terkemuka di Indonesia, Universitas Gadjah Mada berupaya untuk memfasilitasi generasi muda dari seluruh penjuru negeri dan dunia untuk mengembangkan diri dan memaksimalkan potensi yang dimiliki. Kami bertekad membekali komunitas yang dinamis dan penuh semangat ini dengan pendidikan berkualitas demi hari esok yang lebih baik.',
			'Keunggulan UGM mencakup spektrum bidang yang luas. Ada lebih dari 270 program studi dan 23 pusat penelitian yang akan membantu para mahasiswa memperluas wawasan dan memperkaya pengalaman dalam penelitian, kolaborasi interdisipliner, dan kehidupan secara umum.',
			'UGM memiliki jaringan kemitraan yang luas dengan institusi pendidikan nasional dan global, lembaga penelitian, lembaga pemerintah, LSM, dan industri. Kami bersinergi dalam pendidikan, pertukaran pengetahuan, transfer teknologi, dan banyak lagi. Saat ini, UGM memiliki lebih dari 120 program dual-degree dengan berbagai universitas terkenal di dunia.',
			'Kampus kami terletak di jantung kota Yogyakarta, sebuah kota yang terkenal akan sejarah dan warisan budayanya. Oleh karenanya, tak hanya pengalaman akademis, di sini, siapa pun Anda, dari mana pun Anda berasal, dapat merasakan secara langsung pengalaman antarbudaya yang kaya. Kami mengundang Anda belajar di kampus kami yang beragam dan inklusif, tempat kita dapat bahu-membahu menciptakan dampak nyata bagi bangsa dan dunia.',
			'Terima kasih telah mengunjungi halaman kami. Semoga kampus UGM memberikan kesan yang manis bagi Anda.',
		].join( '\n\n' );
	}

	function getDefaultAboutSidebarItems() {
		return [
			'Organisasi',
			'Majelis Wali Amanat',
			'Senat Akademik',
			'Dewan Guru Besar',
			'Pimpinan Universitas',
			'Struktur Organisasi',
			'Tentang UGM',
			'Sambutan Rektor',
			'Visi dan Misi',
			'Tugas Pokok dan Fungsi',
			'Sejarah',
			'Makna Lambang',
			'Himne Gadjah Mada',
			'UGM dalam Angka',
			'Peta Kampus',
		].map( function ( label, index ) {
			return {
				label:  label,
				url:    '',
				active: label === 'Sambutan Rektor',
				level:  index >= 7 ? 1 : 0,
			};
		} );
	}

	function isRectorGreetingTemplate( template ) {
		return template === 'rector-greeting-page' ||
			template === 'page-templates/template-rector-greeting.php';
	}

	function getEditorRenderingMode( select ) {
		var editor = select( 'core/editor' );

		return editor && typeof editor.getRenderingMode === 'function'
			? editor.getRenderingMode()
			: '';
	}

	function isRectorGreetingBlockName( blockName ) {
		return blockName === 'ugm/rector-greeting-layout' ||
			blockName === 'ugm/rector-greeting-content' ||
			blockName === 'ugm/about-ugm-sidebar';
	}

	function findEditorBlockByName( blocks, name ) {
		var found = null;
		( blocks || [] ).some( function ( block ) {
			if ( block.name === name ) {
				found = block;
				return true;
			}
			found = findEditorBlockByName( block.innerBlocks, name );
			return !! found;
		} );
		return found;
	}

	function hasRectorGreetingBlock( blocks ) {
		return !! (
			findEditorBlockByName( blocks, 'ugm/rector-greeting-layout' ) ||
			findEditorBlockByName( blocks, 'ugm/rector-greeting-content' ) ||
			findEditorBlockByName( blocks, 'ugm/about-ugm-sidebar' )
		);
	}

	function removeRectorGreetingBlocks( blocks ) {
		return ( Array.isArray( blocks ) ? blocks : [] ).reduce( function ( nextBlocks, block ) {
			if ( block.name === 'ugm/rector-greeting-layout' ) {
				return nextBlocks;
			}

			if ( isRectorGreetingBlockName( block.name ) ) {
				return nextBlocks;
			}

			if ( Array.isArray( block.innerBlocks ) && block.innerBlocks.length ) {
				block = Object.assign( {}, block );
				block.innerBlocks = removeRectorGreetingBlocks( block.innerBlocks );
			}

			nextBlocks.push( block );
			return nextBlocks;
		}, [] );
	}

	function hasMeaningfulEditorContent( content ) {
		content = String( content || '' );
		if (
			content.indexOf( '<!-- wp:ugm/rector-greeting-layout' ) !== -1 ||
			content.indexOf( '<!-- wp:ugm/rector-greeting-content' ) !== -1 ||
			content.indexOf( '<!-- wp:ugm/about-ugm-sidebar' ) !== -1
		) {
			return true;
		}

		return content.replace( /<!--[\s\S]*?-->/g, '' )
			.replace( /<[^>]+>/g, '' )
			.replace( /&nbsp;/g, ' ' )
			.trim() !== '';
	}

	function getEditorBlockSignature( blocks ) {
		return ( Array.isArray( blocks ) ? blocks : [] ).map( function ( block ) {
			return block.name + '[' + getEditorBlockSignature( block.innerBlocks ) + ']';
		} ).join( ',' );
	}

	function RectorGreetingTemplateSeeder() {
		var lastSeedSignature = useRef( '' );
		var state = useSelect( function ( select ) {
			var editor = select( 'core/editor' );
			var blockEditor = select( 'core/block-editor' );
			return {
				template: editor.getEditedPostAttribute( 'template' ),
				content: editor.getEditedPostAttribute( 'content' ) || '',
				blocks: blockEditor.getBlocks(),
			};
		} );

		useEffect( function () {
			var contentBlocks = wp.blocks.parse( state.content || '' );
			var contentHasMeaning = hasMeaningfulEditorContent( state.content );
			var seedSignature = [
				state.template || '',
				contentHasMeaning ? 'meaningful' : 'empty',
				getEditorBlockSignature( contentBlocks ),
				getEditorBlockSignature( state.blocks ),
			].join( '|' );
			var defaultBlocks;
			var postContentBlock;
			var innerBlocks;
			var layoutBlock;
			var cleanedBlocks;

			if ( seedSignature === lastSeedSignature.current ) {
				return;
			}

			lastSeedSignature.current = seedSignature;

			if ( ! isRectorGreetingTemplate( state.template ) ) {
				if ( hasRectorGreetingBlock( contentBlocks ) ) {
					dispatch( 'core/editor' ).editPost( {
						content: wp.blocks.serialize( removeRectorGreetingBlocks( contentBlocks ) ).trim(),
					} );
				}

				if ( hasRectorGreetingBlock( state.blocks ) ) {
					postContentBlock = findEditorBlockByName( state.blocks, 'core/post-content' );
					if ( postContentBlock ) {
						cleanedBlocks = removeRectorGreetingBlocks( postContentBlock.innerBlocks || [] );
						dispatch( 'core/block-editor' ).replaceInnerBlocks(
							postContentBlock.clientId,
							cleanedBlocks,
							false
						);
					} else {
						dispatch( 'core/block-editor' ).resetBlocks( removeRectorGreetingBlocks( state.blocks ) );
					}
				}
				return;
			}

			if ( ! window.ugmRectorGreetingEditor || ! ugmRectorGreetingEditor.defaultBlocks ) {
				return;
			}

			defaultBlocks = wp.blocks.parse( ugmRectorGreetingEditor.defaultBlocks );
			if ( ! defaultBlocks || ! defaultBlocks.length ) {
				return;
			}

			postContentBlock = findEditorBlockByName( state.blocks, 'core/post-content' );
			if ( postContentBlock ) {
				innerBlocks = Array.isArray( postContentBlock.innerBlocks ) ? postContentBlock.innerBlocks : [];

				layoutBlock = innerBlocks.length === 1 && innerBlocks[0].name === 'ugm/rector-greeting-layout'
					? innerBlocks[0]
					: null;
				if (
					layoutBlock &&
					Array.isArray( layoutBlock.innerBlocks ) &&
					layoutBlock.innerBlocks.length
				) {
					dispatch( 'core/block-editor' ).replaceInnerBlocks(
						postContentBlock.clientId,
						layoutBlock.innerBlocks,
						false
					);
					dispatch( 'core/editor' ).editPost( {
						content: wp.blocks.serialize( layoutBlock.innerBlocks ).trim(),
					} );
					return;
				}

				if ( ! contentHasMeaning && ! hasRectorGreetingBlock( innerBlocks ) ) {
					dispatch( 'core/block-editor' ).replaceInnerBlocks(
						postContentBlock.clientId,
						defaultBlocks,
						false
					);
					dispatch( 'core/editor' ).editPost( { content: ugmRectorGreetingEditor.defaultBlocks } );
				}
				return;
			}

			if ( ! contentHasMeaning && ! hasRectorGreetingBlock( state.blocks ) ) {
				dispatch( 'core/block-editor' ).insertBlocks( defaultBlocks );
				dispatch( 'core/editor' ).editPost( { content: ugmRectorGreetingEditor.defaultBlocks } );
			}
		}, [ state.template, state.content, state.blocks ] );

		return null;
	}

	var withRectorGreetingTemplateVisibility = createHigherOrderComponent( function ( BlockListBlock ) {
		return function ( props ) {
			var state = useSelect( function ( select ) {
				var editor = select( 'core/editor' );

				return {
					template:      editor.getEditedPostAttribute( 'template' ),
					renderingMode: getEditorRenderingMode( select ),
				};
			}, [] );

			if (
				props.name === 'ugm/rector-greeting-template-preview' &&
				isRectorGreetingTemplate( state.template )
			) {
				return null;
			}

			if (
				isRectorGreetingBlockName( props.name ) &&
				isRectorGreetingTemplate( state.template ) &&
				state.renderingMode === 'post-only'
			) {
				return null;
			}

			return el( BlockListBlock, props );
		};
	}, 'withRectorGreetingTemplateVisibility' );

	wp.hooks.addFilter(
		'editor.BlockListBlock',
		'ugm-faculty/rector-greeting-template-visibility',
		withRectorGreetingTemplateVisibility
	);

	registerBlockType( 'ugm/rector-greeting-layout', {
		title:       __( 'Layout Sambutan Rektor', 'ugm-faculty' ),
		description: __( 'Wrapper grid untuk konten Sambutan Rektor dan sidebar Tentang UGM.', 'ugm-faculty' ),
		category:    'ugm-sections',
		icon:        'columns',
		supports:    { html: false },
		edit: function () {
			var blockProps = useBlockProps( {
				className: 'ugm-rector-template-layout ugm-rector-greeting-layout ugm-rector-greeting-layout-editor',
			} );

			return el(
				'div',
				blockProps,
				el( InnerBlocks, {
					allowedBlocks: [ 'ugm/rector-greeting-content', 'ugm/about-ugm-sidebar' ],
					template: [
						[ 'ugm/rector-greeting-content', {
							breadcrumbHome: 'Beranda',
							breadcrumbParent: 'Tentang UGM',
							title: 'Sambutan Rektor',
							body: getDefaultRectorBody(),
							rectorName: 'Prof. dr. Ova Emilia, M.MedEd, SpOG (K), PhD',
							rectorRole: 'Rektor UGM',
							photoPosition: 'right',
						} ],
						[ 'ugm/about-ugm-sidebar', {
							title: 'Tentang UGM',
							items: getDefaultAboutSidebarItems(),
						} ],
					],
					templateLock: false,
				} )
			);
		},
		save: function () {
			return el( InnerBlocks.Content );
		},
	} );

	registerBlockType( 'ugm/rector-greeting-content', {
		title:       __( 'Isi Sambutan Rektor', 'ugm-faculty' ),
		description: __( 'Konten utama, foto, nama, dan jabatan rektor.', 'ugm-faculty' ),
		category:    'ugm-sections',
		icon:        'id-alt',
		supports:    { html: false },
		attributes:  {
			breadcrumbHome:   { type: 'string', default: 'Beranda' },
			breadcrumbParent: { type: 'string', default: 'Tentang UGM' },
			title:            { type: 'string', default: 'Sambutan Rektor' },
			body:             { type: 'string', default: getDefaultRectorBody() },
			rectorName:       { type: 'string', default: 'Prof. dr. Ova Emilia, M.MedEd, SpOG (K), PhD' },
			rectorRole:       { type: 'string', default: 'Rektor UGM' },
			photoId:          { type: 'integer', default: 0 },
			photoUrl:         { type: 'string', default: '' },
			photoPosition:    { type: 'string', default: 'right' },
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
						{ title: __( 'Breadcrumb dan Judul', 'ugm-faculty' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Breadcrumb 1', 'ugm-faculty' ),
							value: attrs.breadcrumbHome || '',
							onChange: function ( value ) { setAttr( { breadcrumbHome: value } ); },
						} ),
						el( TextControl, {
							label: __( 'Breadcrumb 2', 'ugm-faculty' ),
							value: attrs.breadcrumbParent || '',
							onChange: function ( value ) { setAttr( { breadcrumbParent: value } ); },
						} ),
						el( TextControl, {
							label: __( 'Judul Halaman', 'ugm-faculty' ),
							value: attrs.title || '',
							onChange: function ( value ) { setAttr( { title: value } ); },
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Isi Sambutan', 'ugm-faculty' ), initialOpen: true },
						el( TextareaControl, {
							label: __( 'Paragraf Sambutan', 'ugm-faculty' ),
							value: attrs.body || '',
							rows: 12,
							onChange: function ( value ) { setAttr( { body: value } ); },
							help: __( 'Pisahkan paragraf dengan satu baris kosong.', 'ugm-faculty' ),
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Foto dan Identitas Rektor', 'ugm-faculty' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Nama Rektor', 'ugm-faculty' ),
							value: attrs.rectorName || '',
							onChange: function ( value ) { setAttr( { rectorName: value } ); },
						} ),
						el( TextControl, {
							label: __( 'Jabatan', 'ugm-faculty' ),
							value: attrs.rectorRole || '',
							onChange: function ( value ) { setAttr( { rectorRole: value } ); },
						} ),
						el( SelectControl, {
							label: __( 'Posisi Foto', 'ugm-faculty' ),
							value: attrs.photoPosition || 'right',
							options: [
								{ label: __( 'Kanan', 'ugm-faculty' ), value: 'right' },
								{ label: __( 'Kiri', 'ugm-faculty' ), value: 'left' },
							],
							onChange: function ( value ) { setAttr( { photoPosition: value } ); },
						} ),
						attrs.photoUrl
							? el( 'img', {
								src: attrs.photoUrl,
								alt: '',
								style: { display: 'block', width: '100%', maxHeight: '180px', objectFit: 'cover', marginBottom: '8px', border: '1px solid #ddd' },
							} )
							: null,
						el( MediaUploadCheck, null,
							el( MediaUpload, {
								allowedTypes: [ 'image' ],
								value: attrs.photoId || 0,
								onSelect: function ( image ) {
									setAttr( { photoId: image.id || 0, photoUrl: image.url || '' } );
								},
								render: function ( ref ) {
									return el(
										'div',
										{ style: { display: 'flex', gap: '6px', flexWrap: 'wrap' } },
										el( Button, { isSecondary: true, onClick: ref.open },
											attrs.photoUrl ? __( 'Ganti Foto', 'ugm-faculty' ) : __( 'Pilih Foto', 'ugm-faculty' )
										),
										attrs.photoUrl
											? el( Button, {
												isDestructive: true,
												onClick: function () { setAttr( { photoId: 0, photoUrl: '' } ); },
											}, __( 'Hapus Foto', 'ugm-faculty' ) )
											: null
									);
								},
							} )
						)
					)
				),
				el( ServerSideRender, {
					block: 'ugm/rector-greeting-content',
					attributes: attrs,
					httpMethod: 'POST',
				} )
			);
		},
		save: function () { return null; },
	} );

	registerBlockType( 'ugm/about-ugm-sidebar', {
		title:       __( 'Sidebar Tentang UGM', 'ugm-faculty' ),
		description: __( 'Menu samping untuk halaman Tentang UGM.', 'ugm-faculty' ),
		category:    'ugm-sections',
		icon:        'menu-alt3',
		supports:    { html: false },
		attributes:  {
			title: { type: 'string', default: 'Tentang UGM' },
			items: { type: 'array', default: getDefaultAboutSidebarItems() },
		},
		edit: function ( props ) {
			var attrs   = props.attributes;
			var setAttr = props.setAttributes;
			var items   = Array.isArray( attrs.items ) && attrs.items.length ? attrs.items : getDefaultAboutSidebarItems();

			function setItems( nextItems ) {
				setAttr( { items: nextItems } );
			}

			function updateItem( index, patch ) {
				setItems( items.map( function ( item, itemIndex ) {
					return itemIndex === index ? Object.assign( {}, item, patch ) : item;
				} ) );
			}

			function moveItem( from, to ) {
				var next;
				var moved;
				if ( to < 0 || to >= items.length ) {
					return;
				}
				next = items.slice();
				moved = next[ from ];
				next[ from ] = next[ to ];
				next[ to ] = moved;
				setItems( next );
			}

			function removeItem( index ) {
				setItems( items.filter( function ( item, itemIndex ) {
					return itemIndex !== index;
				} ) );
			}

			function renderItem( item, index ) {
				return el(
					'div',
					{
						key: 'about-sidebar-item-' + index,
						style: { borderTop: '1px solid #ddd', marginTop: '12px', paddingTop: '12px' },
					},
					el(
						'div',
						{ style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '8px' } },
						el( 'strong', null, __( 'Menu', 'ugm-faculty' ) + ' ' + ( index + 1 ) ),
						el(
							'span',
							{ style: { display: 'flex', gap: '2px' } },
							index > 0 ? el( Button, { isSmall: true, icon: 'arrow-up-alt2', onClick: function () { moveItem( index, index - 1 ); }, label: __( 'Naik', 'ugm-faculty' ) } ) : null,
							index < items.length - 1 ? el( Button, { isSmall: true, icon: 'arrow-down-alt2', onClick: function () { moveItem( index, index + 1 ); }, label: __( 'Turun', 'ugm-faculty' ) } ) : null,
							el( Button, { isSmall: true, isDestructive: true, icon: 'trash', onClick: function () { removeItem( index ); }, label: __( 'Hapus', 'ugm-faculty' ) } )
						)
					),
					el( TextControl, {
						label: __( 'Label Menu', 'ugm-faculty' ),
						value: item.label || '',
						onChange: function ( value ) { updateItem( index, { label: value } ); },
					} ),
					el( TextControl, {
						label: __( 'URL', 'ugm-faculty' ),
						value: item.url || '',
						type: 'url',
						onChange: function ( value ) { updateItem( index, { url: value } ); },
					} ),
					el( SelectControl, {
						label: __( 'Level / Indent', 'ugm-faculty' ),
						value: String( item.level || 0 ),
						options: [
							{ label: __( 'Level 0', 'ugm-faculty' ), value: '0' },
							{ label: __( 'Level 1', 'ugm-faculty' ), value: '1' },
							{ label: __( 'Level 2', 'ugm-faculty' ), value: '2' },
						],
						onChange: function ( value ) { updateItem( index, { level: parseInt( value, 10 ) || 0 } ); },
					} ),
					el( CheckboxControl, {
						label: __( 'Item aktif', 'ugm-faculty' ),
						checked: !! item.active,
						onChange: function ( checked ) { updateItem( index, { active: checked } ); },
					} )
				);
			}

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Pengaturan Sidebar', 'ugm-faculty' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Judul Sidebar', 'ugm-faculty' ),
							value: attrs.title || '',
							onChange: function ( value ) { setAttr( { title: value } ); },
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Daftar Menu (' + items.length + ')', 'ugm-faculty' ), initialOpen: true },
						items.map( renderItem ),
						el( Button, {
							isPrimary: true,
							style: { width: '100%', justifyContent: 'center', marginTop: '12px' },
							onClick: function () {
								setItems( items.concat( [ { label: '', url: '', active: false, level: 0 } ] ) );
							},
						}, __( '+ Tambah Menu', 'ugm-faculty' ) )
					)
				),
				el( ServerSideRender, {
					block: 'ugm/about-ugm-sidebar',
					attributes: Object.assign( {}, attrs, { items: items } ),
					httpMethod: 'POST',
				} )
			);
		},
		save: function () { return null; },
	} );

	registerBlockType( 'ugm/rector-greeting-template-preview', {
		title:    __( 'Sambutan Rektor Page Template Preview', 'ugm-faculty' ),
		category: 'ugm-sections',
		supports: {
			html:     false,
			inserter: false,
		},
		edit: function () {
			return el( ServerSideRender, {
				block:      'ugm/rector-greeting-template-preview',
				attributes: {},
				httpMethod: 'POST',
			} );
		},
		save: function () { return null; },
	} );

	if ( wp.plugins && wp.plugins.registerPlugin ) {
		wp.plugins.registerPlugin( 'ugm-rector-greeting-page-seeder', {
			render: RectorGreetingTemplateSeeder,
		} );
	}}() );