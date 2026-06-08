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
			'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer vitae lectus at massa dictum fermentum. Donec sed augue non erat porta tempor.',
			'Praesent euismod, lorem at facilisis consequat, sem lorem tincidunt nibh, vitae luctus neque erat vitae urna. Sed non mauris vel nibh bibendum posuere.',
			'Aliquam erat volutpat. Curabitur vitae libero in ipsum porta vulputate. Suspendisse potenti. Nam vitae risus eget augue feugiat faucibus.',
			'Morbi consequat, sapien sed dignissim malesuada, justo arcu volutpat mi, sed finibus neque lorem vitae erat. Pellentesque habitant morbi tristique senectus et netus.',
		].join( '\n\n' );
	}

	function getLegacyRectorBody() {
		return [
			'Selamat datang di Universitas Gadjah Mada (UGM), tempat Anda dapat mulai membuat perubahan nyata.',
			'Sebagai salah satu universitas terkemuka di Indonesia, Universitas Gadjah Mada berupaya untuk memfasilitasi generasi muda dari seluruh penjuru negeri dan dunia untuk mengembangkan diri dan memaksimalkan potensi yang dimiliki. Kami bertekad membekali komunitas yang dinamis dan penuh semangat ini dengan pendidikan berkualitas demi hari esok yang lebih baik.',
			'Keunggulan UGM mencakup spektrum bidang yang luas. Ada lebih dari 270 program studi dan 23 pusat penelitian yang akan membantu para mahasiswa memperluas wawasan dan memperkaya pengalaman dalam penelitian, kolaborasi interdisipliner, dan kehidupan secara umum.',
			'UGM memiliki jaringan kemitraan yang luas dengan institusi pendidikan nasional dan global, lembaga penelitian, lembaga pemerintah, LSM, dan industri. Kami bersinergi dalam pendidikan, pertukaran pengetahuan, transfer teknologi, dan banyak lagi. Saat ini, UGM memiliki lebih dari 120 program dual-degree dengan berbagai universitas terkenal di dunia.',
			'Kampus kami terletak di jantung kota Yogyakarta, sebuah kota yang terkenal akan sejarah dan warisan budayanya. Oleh karenanya, tak hanya pengalaman akademis, di sini, siapa pun Anda, dari mana pun Anda berasal, dapat merasakan secara langsung pengalaman antarbudaya yang kaya. Kami mengundang Anda belajar di kampus kami yang beragam dan inklusif, tempat kita dapat bahu-membahu menciptakan dampak nyata bagi bangsa dan dunia.',
			'Terima kasih telah mengunjungi halaman kami. Semoga kampus UGM memberikan kesan yang manis bagi Anda.',
		].join( '\n\n' );
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
							breadcrumbHome: 'Lorem Ipsum',
							breadcrumbParent: 'Lorem Ipsum',
							title: 'Lorem Ipsum',
							body: getDefaultRectorBody(),
							rectorName: 'Nama Rektor',
							rectorRole: 'Jabatan Rektor',
							photoPosition: 'right',
							showPhotoFrame: true,
						} ],
						[ 'ugm/about-ugm-sidebar', {
							title: 'Tentang UGM',
							menuLocation: 'sidebar-tentang-ugm',
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
			breadcrumbHome:   { type: 'string', default: 'Lorem Ipsum' },
			breadcrumbParent: { type: 'string', default: 'Lorem Ipsum' },
			title:            { type: 'string', default: 'Lorem Ipsum' },
			body:             { type: 'string', default: getDefaultRectorBody() },
			rectorName:       { type: 'string', default: 'Nama Rektor' },
			rectorRole:       { type: 'string', default: 'Jabatan Rektor' },
			photoId:          { type: 'integer', default: 0 },
			photoUrl:         { type: 'string', default: '' },
			photoPosition:    { type: 'string', default: 'right' },
			showPhotoFrame:   { type: 'boolean', default: true },
		},
		edit: function ( props ) {
			var attrs   = props.attributes;
			var setAttr = props.setAttributes;
			var hasPhoto = ( parseInt( attrs.photoId, 10 ) || 0 ) > 0 || !! attrs.photoUrl;
			var showPhotoFrame = attrs.showPhotoFrame !== false;

			useEffect( function () {
				var nextAttrs = {};

				if ( attrs.breadcrumbHome === 'Beranda' ) {
					nextAttrs.breadcrumbHome = 'Lorem Ipsum';
				}
				if ( attrs.breadcrumbParent === 'Tentang UGM' ) {
					nextAttrs.breadcrumbParent = 'Lorem Ipsum';
				}
				if ( attrs.title === 'Sambutan Rektor' ) {
					nextAttrs.title = 'Lorem Ipsum';
				}
				if ( attrs.body === getLegacyRectorBody() ) {
					nextAttrs.body = getDefaultRectorBody();
				}
				if ( attrs.rectorName === 'Prof. dr. Ova Emilia, M.MedEd, SpOG (K), PhD' ) {
					nextAttrs.rectorName = 'Nama Rektor';
				}
				if ( attrs.rectorRole === 'Rektor UGM' ) {
					nextAttrs.rectorRole = 'Jabatan Rektor';
				}

				if ( Object.keys( nextAttrs ).length ) {
					setAttr( nextAttrs );
				}
			}, [] );

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
									setAttr( { photoId: image.id || 0, photoUrl: image.url || '', showPhotoFrame: true } );
								},
								render: function ( ref ) {
									return el(
										'div',
										{ style: { display: 'grid', gap: '8px' } },
										el( Button, { isSecondary: true, onClick: ref.open },
											hasPhoto ? __( 'Ganti Foto', 'ugm-faculty' ) : __( 'Pilih Foto', 'ugm-faculty' )
										),
										showPhotoFrame
											? el( Button, {
												isSecondary: true,
												isDestructive: true,
												onClick: function () { setAttr( { photoId: 0, photoUrl: '', showPhotoFrame: false } ); },
											}, __( 'Hapus Foto', 'ugm-faculty' ) )
											: el( Button, {
												isSecondary: true,
												onClick: function () { setAttr( { showPhotoFrame: true } ); },
											}, __( 'Tampilkan Frame Foto', 'ugm-faculty' ) )
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
			title:        { type: 'string', default: 'Tentang UGM' },
			menuLocation: { type: 'string', default: 'sidebar-tentang-ugm' },
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
						{ title: __( 'Pengaturan Sidebar', 'ugm-faculty' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Judul Sidebar', 'ugm-faculty' ),
							value: attrs.title || '',
							onChange: function ( value ) { setAttr( { title: value } ); },
						} ),
						el( SelectControl, {
							label: __( 'Lokasi Menu', 'ugm-faculty' ),
							value: attrs.menuLocation || 'sidebar-tentang-ugm',
							options: [
								{ label: __( 'Sidebar Tentang UGM', 'ugm-faculty' ), value: 'sidebar-tentang-ugm' },
							],
							onChange: function ( value ) { setAttr( { menuLocation: value } ); },
							help: __( 'Item sidebar mengikuti menu pada Appearance > Menus untuk lokasi ini.', 'ugm-faculty' ),
						} )
					)
				),
				el( ServerSideRender, {
					block: 'ugm/about-ugm-sidebar',
					attributes: attrs,
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
