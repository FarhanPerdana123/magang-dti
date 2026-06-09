/**
 * UGM Landing Page — Block Editor JavaScript
 *
 * Registers all custom landing page section blocks in the block editor so they:
 * 1. Appear in the block inserter under "UGM — General Sections"
 * 2. Show a live Server-Side Render (SSR) preview inside the editor canvas
 * 3. Expose Inspector Controls (sidebar settings) for title, category, hero image etc.
 *
 * No JSX / build step needed — uses WP global variables (ES5 compatible).
 *
 * @package ugm-faculty
 */
( function () {
	'use strict';

	var registerBlockType = wp.blocks.registerBlockType;
	var el                = wp.element.createElement;
	var __                = wp.i18n.__;
	var ServerSideRender  = wp.serverSideRender;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var MediaUpload       = wp.blockEditor.MediaUpload;
	var MediaUploadCheck  = wp.blockEditor.MediaUploadCheck;
	var PanelBody         = wp.components.PanelBody;
	var CheckboxControl   = wp.components.CheckboxControl;
	var SelectControl     = wp.components.SelectControl;
	var TextControl       = wp.components.TextControl;
	var TextareaControl   = wp.components.TextareaControl;
	var SelectControl     = wp.components.SelectControl;
	var Button            = wp.components.Button;
	var Fragment          = wp.element.Fragment;
	var Placeholder       = wp.components.Placeholder;
	var useSelect         = wp.data.useSelect;

	function parseCategorySlugValue( value ) {
		return ( value || '' )
			.split( ',' )
			.map( function ( slug ) { return slug.trim(); } )
			.filter( function ( slug ) { return slug !== ''; } );
	}

	function CategoryChecklistControl( props ) {
		var categories = useSelect( function ( select ) {
			return select( 'core' ).getEntityRecords( 'taxonomy', 'category', {
				per_page: 100,
				hide_empty: false,
				orderby: 'name',
				order: 'asc',
			} );
		}, [] );
		var selectedSlugs = parseCategorySlugValue( props.value );

		function toggleSlug( slug, checked ) {
			var next = selectedSlugs.filter( function ( selectedSlug ) {
				return selectedSlug !== slug;
			} );

			if ( checked ) {
				next.push( slug );
			}

			props.onChange( next.join( ', ' ) );
		}

		return el(
			'div',
			{ className: 'ugm-category-checklist-control' },
			el(
				'p',
				{ style: { fontSize: '11px', fontWeight: '600', margin: '0 0 8px', textTransform: 'uppercase' } },
				props.label
			),
			Array.isArray( categories ) && categories.length > 0
				? categories.map( function ( category ) {
					return el( CheckboxControl, {
						key: category.id,
						label: category.name,
						checked: selectedSlugs.indexOf( category.slug ) !== -1,
						onChange: function ( checked ) {
							toggleSlug( category.slug, checked );
						},
					} );
				} )
				: el(
					'p',
					{ style: { color: '#757575', fontSize: '12px', margin: '0 0 8px' } },
					__( 'Memuat kategori...', 'ugm-faculty' )
				),
			el(
				'p',
				{ style: { color: '#757575', fontSize: '12px', margin: '8px 0 0' } },
				props.help
			)
		);
	}

	function renderCategoryChecklistControl( attrs, setAttr, attributeKey, label, defaultSlug, helpText ) {
		return el( CategoryChecklistControl, {
			label: label,
			value: attrs[ attributeKey ] || '',
			onChange: function ( value ) {
				var patch = {};
				patch[ attributeKey ] = value;
				setAttr( patch );
			},
			help: helpText || __( 'Pilih satu atau beberapa kategori. Kosongkan = default (' + ( defaultSlug || 'semua' ) + ').', 'ugm-faculty' ),
		} );
	}

	function renderVisibilityControl( attrs, setAttr ) {
		return el( SelectControl, {
			label: __( 'Tampilkan di', 'ugm-faculty' ),
			value: attrs.visibility || 'all',
			options: [
				{ label: __( 'Semua perangkat', 'ugm-faculty' ), value: 'all' },
				{ label: __( 'Desktop saja', 'ugm-faculty' ), value: 'desktop' },
				{ label: __( 'Mobile saja', 'ugm-faculty' ), value: 'mobile' },
			],
			onChange: function ( v ) { setAttr( { visibility: v } ); },
			help: __( 'Gunakan ini untuk memisahkan block desktop vs mobile. Di frontend & preview editor akan otomatis tersembunyi sesuai ukuran layar.', 'ugm-faculty' ),
		} );
	}

	/* ------------------------------------------------------------------
	 * Helper: build the edit() function for a configurable section block
	 * with title + categorySlug attributes shown in Inspector Controls.
	 * ------------------------------------------------------------------ */
	function makeSectionEdit( blockName, defaultTitle, defaultSlug, categoryHelp ) {
		return function ( props ) {
			var attrs   = props.attributes;
			var setAttr = props.setAttributes;

			return el(
				Fragment,
				null,

				// Inspector Controls (sidebar settings).
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Pengaturan Section', 'ugm-faculty' ), initialOpen: true },
						renderVisibilityControl( attrs, setAttr ),
						el( TextControl, {
							label:    __( 'Judul Section', 'ugm-faculty' ),
							value:    attrs.title || '',
							onChange: function ( v ) { setAttr( { title: v } ); },
							help:     __( 'Kosongkan jika tidak ingin menampilkan judul section.', 'ugm-faculty' ),
						} ),
						defaultSlug !== null
							? renderCategoryChecklistControl( attrs, setAttr, 'categorySlug', __( 'Kategori', 'ugm-faculty' ), defaultSlug, categoryHelp )
							: null
					)
				),

				// Live SSR preview: rendered from PHP render_callback via REST.
				el( ServerSideRender, {
					block:      blockName,
					attributes: attrs,
					httpMethod: 'POST',
				} )
			);
		};
	}

	/* ------------------------------------------------------------------
	 * Helper: build edit() for blocks with NO configurable attributes
	 * (header, footer, magazine).
	 * ------------------------------------------------------------------ */
	function makeStaticEdit( blockName, label ) {
		return function ( props ) {
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
						{ title: __( 'Pengaturan Section', 'ugm-faculty' ), initialOpen: true },
						renderVisibilityControl( attrs, setAttr )
					)
				),
				el( Placeholder, {
					icon:  'layout',
					label: label,
				},
					el( ServerSideRender, {
						block:      blockName,
						attributes: attrs,
						httpMethod: 'POST',
					} )
				)
			);
		};
	}

	function makeFooterFieldsEdit( blockName, panelTitle, fields ) {
		return function ( props ) {
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
						{ title: panelTitle, initialOpen: true },
						fields.map( function ( field ) {
							var Control = 'textarea' === field.type ? TextareaControl : TextControl;
							return el( Control, {
								key: field.key,
								label: field.label,
								value: attrs[ field.key ] || '',
								rows: field.rows || 3,
								onChange: function ( value ) {
									var patch = {};
									patch[ field.key ] = value;
									setAttr( patch );
								},
							} );
						} )
					)
				),
				el( ServerSideRender, {
					block: blockName,
					attributes: attrs,
					httpMethod: 'POST',
				} )
			);
		};
	}

	function makeFooterImageEdit( blockName, label ) {
		return function ( props ) {
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
						{ title: label, initialOpen: true },
						el(
							MediaUploadCheck,
							null,
							el( MediaUpload, {
								allowedTypes: [ 'image' ],
								value: attrs.imageId || 0,
								onSelect: function ( image ) {
									setAttr( { imageId: image.id || 0, imageUrl: image.url || '' } );
								},
								render: function ( ref ) {
									return el( Button, { onClick: ref.open, isSecondary: true },
										attrs.imageUrl ? __( 'Ganti gambar', 'ugm-faculty' ) : __( 'Pilih gambar', 'ugm-faculty' )
									);
								},
							} )
						),
						attrs.imageUrl
							? el( Button, {
								onClick: function () { setAttr( { imageId: 0, imageUrl: '' } ); },
								isDestructive: true,
								style: { marginLeft: '8px' },
							}, __( 'Gunakan gambar bawaan', 'ugm-faculty' ) )
							: null,
						el( TextControl, {
							label: __( 'Teks alternatif gambar', 'ugm-faculty' ),
							value: attrs.alt || '',
							onChange: function ( value ) { setAttr( { alt: value } ); },
						} )
					)
				),
				el( ServerSideRender, {
					block: blockName,
					attributes: attrs,
					httpMethod: 'POST',
				} )
			);
		};
	}

	function makeFooterBrandEdit() {
		return function ( props ) {
			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Logo Institusi', 'ugm-faculty' ), initialOpen: true },
						el( 'p', null,
							__( 'Block ini mengikuti logo dan teks dari Customizer > UGM Branding. Hapus block ini dari area widget footer jika tidak ingin menampilkannya.', 'ugm-faculty' )
						)
					)
				),
				el( ServerSideRender, {
					block: 'ugm/footer-brand',
					attributes: props.attributes,
					httpMethod: 'POST',
				} )
			);
		};
	}

	function makeFooterContactEdit() {
		return function ( props ) {
			var attrs   = props.attributes;
			var setAttr = props.setAttributes;

			function renderIconControl( label, idKey, urlKey ) {
				return el(
					'div',
					{ style: { marginTop: '12px' } },
					el( 'p', { style: { fontSize: '12px', fontWeight: '600', margin: '0 0 6px' } }, label ),
					attrs[ urlKey ]
						? el( 'img', {
							src: attrs[ urlKey ],
							alt: '',
							style: {
								display: 'block',
								width: '34px',
								height: '34px',
								objectFit: 'contain',
								marginBottom: '6px',
								background: '#0b4b72',
							},
						} )
						: null,
					el(
						MediaUploadCheck,
						null,
						el( MediaUpload, {
							allowedTypes: [ 'image' ],
							value: attrs[ idKey ] || 0,
							onSelect: function ( image ) {
								var patch = {};
								patch[ idKey ] = image.id || 0;
								patch[ urlKey ] = image.url || '';
								setAttr( patch );
							},
							render: function ( ref ) {
								return el( Button, { onClick: ref.open, isSecondary: true, isSmall: true },
									attrs[ urlKey ] ? __( 'Ganti ikon', 'ugm-faculty' ) : __( 'Pilih ikon', 'ugm-faculty' )
								);
							},
						} )
					),
					attrs[ urlKey ]
						? el( Button, {
							onClick: function () {
								var patch = {};
								patch[ idKey ] = 0;
								patch[ urlKey ] = '';
								setAttr( patch );
							},
							isDestructive: true,
							isSmall: true,
							style: { marginLeft: '6px' },
						}, __( 'Gunakan ikon bawaan', 'ugm-faculty' ) )
						: null
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
						{ title: __( 'Kontak Institusi', 'ugm-faculty' ), initialOpen: true },
						el( TextareaControl, {
							label: __( 'Alamat', 'ugm-faculty' ),
							value: attrs.address || '',
							rows: 4,
							onChange: function ( value ) { setAttr( { address: value } ); },
						} ),
						el( TextControl, {
							label: __( 'Email', 'ugm-faculty' ),
							value: attrs.email || '',
							onChange: function ( value ) { setAttr( { email: value } ); },
						} ),
						el( TextControl, {
							label: __( 'WhatsApp', 'ugm-faculty' ),
							value: attrs.whatsapp || '',
							onChange: function ( value ) { setAttr( { whatsapp: value } ); },
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Ikon Kontak', 'ugm-faculty' ), initialOpen: false },
						renderIconControl( __( 'Ikon Alamat', 'ugm-faculty' ), 'addressIconId', 'addressIconUrl' ),
						renderIconControl( __( 'Ikon Email', 'ugm-faculty' ), 'emailIconId', 'emailIconUrl' ),
						renderIconControl( __( 'Ikon WhatsApp', 'ugm-faculty' ), 'whatsappIconId', 'whatsappIconUrl' )
					)
				),
				el( ServerSideRender, {
					block: 'ugm/footer-contact',
					attributes: attrs,
					httpMethod: 'POST',
				} )
			);
		};
	}

	function makeFeaturedCategoriesEdit() {
		return function ( props ) {
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
						{ title: __( 'Seputar Kampus', 'ugm-faculty' ), initialOpen: true },
						renderVisibilityControl( attrs, setAttr ),
						el( TextControl, {
							label: __( 'Judul Section', 'ugm-faculty' ),
							value: attrs.campusTitle || '',
							onChange: function ( v ) { setAttr( { campusTitle: v } ); },
						} ),
						renderCategoryChecklistControl( attrs, setAttr, 'campusCategorySlug', __( 'Kategori', 'ugm-faculty' ), 'seputar-kampus' )
					),
					el(
						PanelBody,
						{ title: __( 'Kabar Fakultas', 'ugm-faculty' ), initialOpen: false },
						el( TextControl, {
							label: __( 'Judul Section', 'ugm-faculty' ),
							value: attrs.facultyTitle || '',
							onChange: function ( v ) { setAttr( { facultyTitle: v } ); },
						} ),
						renderCategoryChecklistControl( attrs, setAttr, 'facultyCategorySlug', __( 'Kategori', 'ugm-faculty' ), 'kabar-fakultas' )
					),
					el(
						PanelBody,
						{ title: __( 'Kerjasama', 'ugm-faculty' ), initialOpen: false },
						el( TextControl, {
							label: __( 'Judul Section', 'ugm-faculty' ),
							value: attrs.partnershipTitle || '',
							onChange: function ( v ) { setAttr( { partnershipTitle: v } ); },
						} ),
						renderCategoryChecklistControl( attrs, setAttr, 'partnershipCategorySlug', __( 'Kategori', 'ugm-faculty' ), 'kerjasama' )
					)
				),
				el( ServerSideRender, {
					block: 'ugm/featured-categories',
					attributes: attrs,
					httpMethod: 'POST',
				} )
			);
		};
	}

	function makeFacultyEdit( blockName ) {
		return function ( props ) {
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
						{ title: __( 'Seputar UGM', 'ugm-faculty' ), initialOpen: true },
						renderVisibilityControl( attrs, setAttr ),
						el( TextControl, {
							label: __( 'Judul Overline', 'ugm-faculty' ),
							value: attrs.overline || '',
							onChange: function ( v ) { setAttr( { overline: v } ); },
							help: __( 'Teks kecil di atas judul utama section.', 'ugm-faculty' ),
						} ),
						el( TextControl, {
							label: __( 'Judul Section', 'ugm-faculty' ),
							value: attrs.title || '',
							onChange: function ( v ) { setAttr( { title: v } ); },
						} ),
						renderCategoryChecklistControl( attrs, setAttr, 'categorySlug', __( 'Kategori', 'ugm-faculty' ), 'semua' )
					)
				),
				el( ServerSideRender, {
					block: blockName,
					attributes: attrs,
					httpMethod: 'POST',
				} )
			);
		};
	}

	function makeAgendaDesktopEdit( blockName ) {
		return function ( props ) {
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
						{ title: __( 'Events & Agenda', 'ugm-faculty' ), initialOpen: true },
						renderVisibilityControl( attrs, setAttr ),
						el( TextControl, {
							label: __( 'Judul Section', 'ugm-faculty' ),
							value: attrs.title || '',
							onChange: function ( v ) { setAttr( { title: v } ); },
						} ),
						renderCategoryChecklistControl( attrs, setAttr, 'categorySlug', __( 'Kategori Agenda', 'ugm-faculty' ), 'agenda' )
					),
					el(
						PanelBody,
						{ title: __( 'Facilities', 'ugm-faculty' ), initialOpen: false },
						el( TextControl, {
							label: __( 'Judul Section', 'ugm-faculty' ),
							value: attrs.facilityTitle || '',
							onChange: function ( v ) { setAttr( { facilityTitle: v } ); },
						} ),
						renderCategoryChecklistControl( attrs, setAttr, 'facilityCategorySlug', __( 'Kategori Fasilitas', 'ugm-faculty' ), 'fasilitas-mahasiswa' )
					)
				),
				el( ServerSideRender, {
					block: blockName,
					attributes: attrs,
					httpMethod: 'POST',
				} )
			);
		};
	}

	function makeGallerySectionEdit() {
		return function ( props ) {
			var attrs        = props.attributes;
			var setAttr      = props.setAttributes;
			var galleryPages = useSelect( function ( select ) {
				var records = select( 'core' ).getEntityRecords( 'postType', 'page', {
					per_page: 100,
					status: 'publish',
					orderby: 'title',
					order: 'asc',
				} );

				if ( ! Array.isArray( records ) ) {
					return null;
				}

				return records.filter( function ( page ) {
					var template = page.template || '';
					var content = page.content && page.content.raw ? page.content.raw : '';

					return template === 'gallery-page' ||
						template === 'page-templates/template-gallery.php' ||
						content.indexOf( '<!-- wp:ugm/gallery-page' ) !== -1;
				} );
			}, [] );

			function pageLabel( page ) {
				return page.title && page.title.rendered
					? page.title.rendered.replace( /<[^>]*>/g, '' )
					: __( '(Untitled)', 'ugm-faculty' );
			}

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Section Settings', 'ugm-faculty' ), initialOpen: true },
						renderVisibilityControl( attrs, setAttr ),
						el( SelectControl, {
							label: __( 'Gallery page source', 'ugm-faculty' ),
							value: String( attrs.galleryPageId || 0 ),
							options: [
								{ label: __( 'Auto-detect Gallery page', 'ugm-faculty' ), value: '0' },
							].concat(
								Array.isArray( galleryPages )
									? galleryPages.map( function ( page ) {
										return { label: pageLabel( page ), value: String( page.id ) };
									} )
									: []
							),
							onChange: function ( value ) {
								setAttr( { galleryPageId: parseInt( value, 10 ) || 0 } );
							},
							help: Array.isArray( galleryPages )
								? __( 'Landing Gallery reads cards and images from the selected Gallery page.', 'ugm-faculty' )
								: __( 'Loading Gallery pages...', 'ugm-faculty' ),
						} ),
						el( TextControl, {
							label: __( 'Section title', 'ugm-faculty' ),
							value: attrs.sectionTitle || '',
							onChange: function ( value ) { setAttr( { sectionTitle: value } ); },
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Links', 'ugm-faculty' ), initialOpen: false },
						el( TextControl, {
							label: __( 'Detail button label', 'ugm-faculty' ),
							value: attrs.detailLabel || '',
							onChange: function ( value ) { setAttr( { detailLabel: value } ); },
						} ),
						el( TextControl, {
							label: __( 'View All label', 'ugm-faculty' ),
							value: attrs.viewAllLabel || '',
							onChange: function ( value ) { setAttr( { viewAllLabel: value } ); },
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Fallback Content', 'ugm-faculty' ), initialOpen: false },
						el( 'p', { style: { color: '#757575', fontSize: '12px', marginTop: 0 } },
							__( 'Used only when no Gallery page or Gallery card is available.', 'ugm-faculty' )
						),
						el( TextControl, {
							label: __( 'Date', 'ugm-faculty' ),
							value: attrs.date || '',
							onChange: function ( value ) { setAttr( { date: value } ); },
						} ),
						el( TextControl, {
							label: __( 'Gallery title', 'ugm-faculty' ),
							value: attrs.galleryTitle || '',
							onChange: function ( value ) { setAttr( { galleryTitle: value } ); },
						} ),
						el( TextareaControl, {
							label: __( 'Description', 'ugm-faculty' ),
							value: attrs.description || '',
							rows: 3,
							onChange: function ( value ) { setAttr( { description: value } ); },
						} ),
						el( TextControl, {
							label: __( 'Detail fallback URL', 'ugm-faculty' ),
							type: 'url',
							value: attrs.detailUrl || '',
							onChange: function ( value ) { setAttr( { detailUrl: value } ); },
						} ),
						el( TextControl, {
							label: __( 'View All fallback URL', 'ugm-faculty' ),
							type: 'url',
							value: attrs.viewAllUrl || '',
							onChange: function ( value ) { setAttr( { viewAllUrl: value } ); },
						} )
					)
				),
				el( ServerSideRender, {
					block: 'ugm/gallery-section',
					attributes: attrs,
					httpMethod: 'POST',
				} )
			);
		};
	}

	function makeTemplateManagedEdit( label ) {
		return function () {
			return el(
				Placeholder,
				{
					icon:  'layout',
					label: label,
				},
				el(
					'p',
					null,
					__( 'Section ini sekarang dirender oleh template PHP hardcoded. Gunakan Preview Template untuk melihat desain final.', 'ugm-faculty' )
				)
			);
		};
	}

	/* ------------------------------------------------------------------
	 * ugm/hero-section — Hero with MediaUpload image picker
	 * Allows changing the background image, headline, and description
	 * directly from the page editor sidebar.
	 * ------------------------------------------------------------------ */
	registerBlockType( 'ugm/hero-section', {
		title:       __( 'Hero Banner', 'ugm-faculty' ),
		description: __( 'Display a prominent page banner with media, heading, and supporting text.', 'ugm-faculty' ),
		category:    'ugm-sections',
		keywords:    [ __( 'hero', 'ugm-faculty' ), __( 'banner', 'ugm-faculty' ), __( 'media', 'ugm-faculty' ) ],
		icon:        'format-image',
		supports:    { html: false, multiple: false },
		attributes: {
			imageId:     { type: 'integer', default: 0 },
			imageUrl:    { type: 'string',  default: '' },
			mediaType:   { type: 'string',  default: 'image' },
			slideImages: { type: 'array',   default: [] },
			videoId:     { type: 'integer', default: 0 },
			videoUrl:    { type: 'string',  default: '' },
			title:       { type: 'string',  default: '' },
			description: { type: 'string',  default: '' },
			visibility:  { type: 'string',  default: 'all' },
		},
		edit: function ( props ) {
			var attrs   = props.attributes;
			var setAttr = props.setAttributes;

			return el(
				Fragment,
				null,

				// ── Inspector Controls ──────────────────────────────────
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Media Latar Hero', 'ugm-faculty' ), initialOpen: true },
						renderVisibilityControl( attrs, setAttr ),
						el( SelectControl, {
							label: __( 'Jenis Media', 'ugm-faculty' ),
							value: attrs.mediaType || 'image',
							options: [
								{ label: __( 'Gambar', 'ugm-faculty' ), value: 'image' },
								{ label: __( 'Slider Gambar', 'ugm-faculty' ), value: 'slider' },
								{ label: __( 'Video', 'ugm-faculty' ), value: 'video' },
							],
							onChange: function ( v ) { setAttr( { mediaType: v } ); },
							help: __( 'Slider gambar akan berjalan otomatis. Video akan berjalan otomatis dengan mode muted dan loop.', 'ugm-faculty' ),
						} ),
						( attrs.mediaType || 'image' ) === 'video'
							? el(
								MediaUploadCheck,
								null,
								el( MediaUpload, {
									onSelect: function ( media ) {
										setAttr( { mediaType: 'video', videoId: media.id, videoUrl: media.url } );
									},
									allowedTypes: [ 'video' ],
									value: attrs.videoId || 0,
									render: function ( ref ) {
										return el(
											'div',
											{ style: { marginBottom: '8px' } },
											attrs.videoUrl
												? el( 'video', {
													src: attrs.videoUrl,
													muted: true,
													loop: true,
													autoPlay: true,
													playsInline: true,
													controls: true,
													style: { width: '100%', borderRadius: '4px', marginBottom: '6px' },
												} )
												: el( 'p', { style: { color: '#999', marginBottom: '6px' } },
													__( 'Belum ada video hero. Pilih video dari Media Library.', 'ugm-faculty' )
												),
											el( Button, {
												onClick:     ref.open,
												isSecondary: true,
												style:       { marginRight: '6px' },
											}, attrs.videoUrl
												? __( 'Ganti Video', 'ugm-faculty' )
												: __( 'Pilih Video', 'ugm-faculty' )
											),
											attrs.videoUrl
												? el( Button, {
													onClick:       function () { setAttr( { videoId: 0, videoUrl: '' } ); },
													isDestructive: true,
												}, __( 'Hapus', 'ugm-faculty' ) )
												: null
										);
									},
								} )
							)
							: null,
						( attrs.mediaType || 'image' ) === 'image'
							? el(
							MediaUploadCheck,
							null,
							el( MediaUpload, {
								onSelect: function ( media ) {
									setAttr( { mediaType: 'image', imageId: media.id, imageUrl: media.url } );
								},
								allowedTypes: [ 'image' ],
								value: attrs.imageId || 0,
								render: function ( ref ) {
									return el(
										'div',
										{ style: { marginBottom: '8px' } },
										attrs.imageUrl
											? el( 'img', {
												src:   attrs.imageUrl,
												alt:   '',
												style: { width: '100%', borderRadius: '4px', marginBottom: '6px' },
											} )
											: el( 'p', { style: { color: '#999', marginBottom: '6px' } },
												__( 'Belum ada gambar hero. Gunakan gambar default dari Customizer.', 'ugm-faculty' )
											),
										el( Button, {
											onClick:     ref.open,
											isSecondary: true,
											style:       { marginRight: '6px' },
										}, attrs.imageUrl
											? __( 'Ganti Gambar', 'ugm-faculty' )
											: __( 'Pilih Gambar', 'ugm-faculty' )
										),
										attrs.imageUrl
											? el( Button, {
												onClick:       function () { setAttr( { imageId: 0, imageUrl: '' } ); },
												isDestructive: true,
											}, __( 'Hapus', 'ugm-faculty' ) )
											: null
									);
								},
							} )
						)
							: null
						,
						( attrs.mediaType || 'image' ) === 'slider'
							? el(
								MediaUploadCheck,
								null,
								el( MediaUpload, {
									onSelect: function ( mediaItems ) {
										var items = Array.isArray( mediaItems ) ? mediaItems : [ mediaItems ];
										var slides = items
											.filter( function ( media ) { return media && media.url; } )
											.map( function ( media ) {
												return {
													id:  media.id || 0,
													url: media.url || '',
												};
											} );

										setAttr( {
											mediaType: 'slider',
											slideImages: slides,
											imageId: slides.length ? slides[0].id : attrs.imageId,
											imageUrl: slides.length ? slides[0].url : attrs.imageUrl,
										} );
									},
									allowedTypes: [ 'image' ],
									multiple: true,
									gallery: true,
									value: ( attrs.slideImages || [] ).map( function ( image ) { return image.id; } ),
									render: function ( ref ) {
										var slideImages = attrs.slideImages || [];
										return el(
											'div',
											{ style: { marginBottom: '8px' } },
											slideImages.length
												? el(
													'div',
													{
														style: {
															display: 'grid',
															gridTemplateColumns: 'repeat(3, minmax(0, 1fr))',
															gap: '6px',
															marginBottom: '8px',
														},
													},
													slideImages.map( function ( image, index ) {
														return el( 'img', {
															key: index,
															src: image.url,
															alt: '',
															style: {
																width: '100%',
																aspectRatio: '16 / 9',
																objectFit: 'cover',
																borderRadius: '4px',
															},
														} );
													} )
												)
												: el( 'p', { style: { color: '#999', marginBottom: '6px' } },
													__( 'Belum ada gambar slider. Pilih beberapa gambar dari Media Library.', 'ugm-faculty' )
												),
											el( Button, {
												onClick: ref.open,
												isSecondary: true,
												style: { marginRight: '6px' },
											}, slideImages.length
												? __( 'Ganti Gambar Slider', 'ugm-faculty' )
												: __( 'Pilih Gambar Slider', 'ugm-faculty' )
											),
											slideImages.length
												? el( Button, {
													onClick: function () { setAttr( { slideImages: [] } ); },
													isDestructive: true,
												}, __( 'Hapus', 'ugm-faculty' ) )
												: null
										);
									},
								} )
							)
							: null
					),
					el(
						PanelBody,
						{ title: __( 'Teks Hero', 'ugm-faculty' ), initialOpen: true },
						el( TextControl, {
							label:    __( 'Judul Hero', 'ugm-faculty' ),
							value:    attrs.title || '',
							onChange: function ( v ) { setAttr( { title: v } ); },
							help:     __( 'Kosongkan jika tidak ingin menampilkan judul hero.', 'ugm-faculty' ),
						} ),
						el( TextareaControl, {
							label:    __( 'Deskripsi Hero', 'ugm-faculty' ),
							value:    attrs.description || '',
							onChange: function ( v ) { setAttr( { description: v } ); },
							rows:     3,
							help:     __( 'Kosongkan jika tidak ingin menampilkan deskripsi hero.', 'ugm-faculty' ),
						} )
					)
				),

				// ── Live SSR preview ───────────────────────────────────
				el( ServerSideRender, {
					block:      'ugm/hero-section',
					attributes: attrs,
					httpMethod: 'POST',
				} )
			);
		},
		save: function () { return null; },
	} );

	/* ------------------------------------------------------------------
	 * Block definitions — configurable section blocks
	 * ------------------------------------------------------------------ */

	var sections = [
		{
			name:         'ugm/latest-news',
			title:        __( 'Information Highlights', 'ugm-faculty' ),
			description:  __( 'Display selected information or news items.', 'ugm-faculty' ),
			icon:         'rss',
			keywords:     [ __( 'information', 'ugm-faculty' ), __( 'news', 'ugm-faculty' ), __( 'highlights', 'ugm-faculty' ) ],
			defaultTitle: __( 'Berita Terbaru', 'ugm-faculty' ),
			defaultSlug:  '',
			categoryHelp: __( 'Biarkan kosong agar otomatis mengikuti gabungan kategori dari Informasi Akademik, Informasi Umum, dan Pencapaian.', 'ugm-faculty' ),
			attrs: {
				title:        { type: 'string', default: 'Highlight Informasi' },
				categorySlug: { type: 'string', default: '' },
			},
		},
		{
			name:         'ugm/academic-news',
			title:        __( 'Academic Information', 'ugm-faculty' ),
			description:  __( 'Display academic information or education-related posts.', 'ugm-faculty' ),
			icon:         'book',
			keywords:     [ __( 'academic', 'ugm-faculty' ), __( 'education', 'ugm-faculty' ), __( 'information', 'ugm-faculty' ) ],
			defaultTitle: __( 'Berita Akademik', 'ugm-faculty' ),
			defaultSlug:  'pendidikan',
			attrs: {
				title:        { type: 'string', default: 'Informasi Akademik' },
				categorySlug: { type: 'string', default: '' },
			},
		},
		{
			name:         'ugm/profile-section',
			title:        __( 'General Information', 'ugm-faculty' ),
			description:  __( 'Display general profile, overview, or informational posts.', 'ugm-faculty' ),
			icon:         'admin-users',
			keywords:     [ __( 'general', 'ugm-faculty' ), __( 'profile', 'ugm-faculty' ), __( 'information', 'ugm-faculty' ) ],
			defaultTitle: __( 'Profile', 'ugm-faculty' ),
			defaultSlug:  'profile',
			attrs: {
				title:        { type: 'string', default: 'Informasi Umum' },
				categorySlug: { type: 'string', default: '' },
			},
		},
		{
			name:         'ugm/achievement-section',
			title:        __( 'Achievements', 'ugm-faculty' ),
			description:  __( 'Display achievement, recognition, or milestone posts.', 'ugm-faculty' ),
			icon:         'awards',
			keywords:     [ __( 'achievements', 'ugm-faculty' ), __( 'awards', 'ugm-faculty' ), __( 'milestones', 'ugm-faculty' ) ],
			defaultTitle: __( 'Prestasi', 'ugm-faculty' ),
			defaultSlug:  'prestasi',
			attrs: {
				title:        { type: 'string', default: 'Pencapaian' },
				categorySlug: { type: 'string', default: '' },
			},
		},
		{
			name:         'ugm/facility-section',
			title:        __( 'Facilities', 'ugm-faculty' ),
			description:  __( 'Display facility-related content.', 'ugm-faculty' ),
			icon:         'building',
			keywords:     [ __( 'facilities', 'ugm-faculty' ), __( 'resources', 'ugm-faculty' ), __( 'services', 'ugm-faculty' ) ],
			defaultTitle: __( 'Fasilitas', 'ugm-faculty' ),
			defaultSlug:  'fasilitas',
			supports:     { html: false, multiple: false, inserter: false },
			attrs: {
				title:        { type: 'string', default: 'Fasilitas' },
				categorySlug: { type: 'string', default: '' },
			},
		},
		{
			name:         'ugm/faculty-section',
			title:        __( 'Organization Structure', 'ugm-faculty' ),
			description:  __( 'Display organizational units, departments, or related structures.', 'ugm-faculty' ),
			icon:         'art',
			keywords:     [ __( 'organization', 'ugm-faculty' ), __( 'structure', 'ugm-faculty' ), __( 'departments', 'ugm-faculty' ) ],
			defaultTitle: __( 'Fakultas dan Sekolah', 'ugm-faculty' ),
			defaultSlug:  null, // No category — data from Customizer.
			supports:     { html: false, multiple: false, inserter: false },
			attrs: {
				overline: { type: 'string', default: 'Seputar UGM' },
				title: { type: 'string', default: 'Fakultas dan Sekolah' },
				categorySlug: { type: 'string', default: '' },
			},
		},
		{
			name:         'ugm/agenda-section',
			title:        __( 'Events & Agenda', 'ugm-faculty' ),
			description:  __( 'Display event or agenda items from a selected category.', 'ugm-faculty' ),
			icon:         'calendar',
			keywords:     [ __( 'events', 'ugm-faculty' ), __( 'agenda', 'ugm-faculty' ), __( 'calendar', 'ugm-faculty' ) ],
			defaultTitle: __( 'Agenda Kegiatan', 'ugm-faculty' ),
			defaultSlug:  'agenda',
			supports:     { html: false, multiple: false, inserter: false },
			attrs: {
				title:                { type: 'string', default: 'Event & Agenda' },
				categorySlug:         { type: 'string', default: '' },
				facilityTitle:        { type: 'string', default: '' },
				facilityCategorySlug: { type: 'string', default: '' },
			},
		},
		{
			name:         'ugm/category-section',
			title:        __( 'Content Categories', 'ugm-faculty' ),
			description:  __( 'Display a section of content categories.', 'ugm-faculty' ),
			icon:         'category',
			keywords:     [ __( 'categories', 'ugm-faculty' ), __( 'content', 'ugm-faculty' ), __( 'taxonomy', 'ugm-faculty' ) ],
			defaultTitle: __( 'Kategori', 'ugm-faculty' ),
			defaultSlug:  null,
			attrs: {
				title: { type: 'string', default: 'Kategori' },
			},
		},
		{
			name:         'ugm/video-section',
			title:        __( 'Featured Video', 'ugm-faculty' ),
			description:  __( 'Display a featured video and related video items from a selected category.', 'ugm-faculty' ),
			icon:         'video-alt3',
			keywords:     [ __( 'video', 'ugm-faculty' ), __( 'media', 'ugm-faculty' ), __( 'featured', 'ugm-faculty' ) ],
			defaultTitle: __( 'Video', 'ugm-faculty' ),
			defaultSlug:  'video',
			attrs: {
				title:        { type: 'string', default: 'Media Video' },
				categorySlug: { type: 'string', default: '' },
			},
		},
	];

	/* Editor previews - frontend still uses the PHP hardcoded template. */
	sections.forEach( function ( section ) {
		var editFn = makeSectionEdit( section.name, section.defaultTitle, section.defaultSlug, section.categoryHelp );

		if ( 'ugm/faculty-section' === section.name ) {
			editFn = makeFacultyEdit( section.name );
		} else if ( 'ugm/agenda-section' === section.name ) {
			editFn = makeAgendaDesktopEdit( section.name );
		}

		registerBlockType( section.name, {
			title:       section.title,
			description: section.description,
			category:    'ugm-sections',
			keywords:    section.keywords || [],
			icon:        section.icon,
			supports:    section.supports || { html: false, multiple: false },
			attributes:  Object.assign( { visibility: { type: 'string', default: 'all' } }, section.attrs ),
			edit:        editFn,
			save:        function () { return null; },
		} );
	} );

	/* ==================================================================
	 * UGM — BERITA TERBARU SECTIONS
	 * Register custom blocks for the "Berita Terbaru" page template.
	 * Users add/remove these blocks to control which sections appear.
	 * ================================================================== */

	// Register the "UGM — Berita Terbaru" category (client-side).
	if ( wp.blocks.getCategories && wp.blocks.setCategories ) {
		var existingCategories = wp.blocks.getCategories();
		var hasBeritaCategory  = existingCategories.some( function ( c ) {
			return c.slug === 'ugm-berita-terbaru';
		} );
		if ( ! hasBeritaCategory ) {
			var ugmSectionsIdx = existingCategories.findIndex( function ( c ) {
				return c.slug === 'ugm-sections';
			} );
			var newCategory = { slug: 'ugm-berita-terbaru', title: __( 'UGM — Berita Terbaru', 'ugm-faculty' ), icon: 'admin-post' };
			var updatedCats = existingCategories.slice();
			if ( ugmSectionsIdx !== -1 ) {
				updatedCats.splice( ugmSectionsIdx + 1, 0, newCategory );
			} else {
				updatedCats.unshift( newCategory );
			}
			wp.blocks.setCategories( updatedCats );
		}
	}

	/**
	 * Build edit() for a Berita Terbaru section block.
	 * Shows InspectorControls with configurable fields + SSR preview.
	 * Preview is wrapped in .ugmbt-page so CSS custom properties apply.
	 *
	 * @param {string}   blockName  Full block name, e.g. 'ugm/bt-hero-post'.
	 * @param {Array}    fields     Array of {key, label, type ('text'|'number'|'select'), help, default}.
	 * @return {Function}
	 */
	function makeBeritaEdit( blockName, fields ) {
		return function ( props ) {
			var attrs   = props.attributes;
			var setAttr = props.setAttributes;

			var controls = fields.map( function ( field ) {
				if ( field.type === 'select' ) {
					return el( SelectControl, {
						key:      field.key,
						label:    field.label,
						value:    attrs[ field.key ] || field.default,
						options:  field.options || [],
						onChange: function ( v ) {
							var obj = {};
							obj[ field.key ] = v;
							setAttr( obj );
						},
						help: field.help || '',
					} );
				}
				if ( field.type === 'number' ) {
					return el( TextControl, {
						key:      field.key,
						label:    field.label,
						value:    String( attrs[ field.key ] !== undefined ? attrs[ field.key ] : field.default ),
						type:     'number',
						onChange: function ( v ) {
							var obj = {};
							obj[ field.key ] = parseInt( v, 10 ) || field.default;
							setAttr( obj );
						},
						help: field.help || '',
					} );
				}
				if ( field.type === 'image' ) {
					return el(
						'div',
						{ key: field.key, className: 'ugmbt-editor-image-control' },
						el( 'p', { className: 'ugmbt-editor-image-control__label' }, field.label ),
						attrs[ field.urlKey ]
							? el( 'img', {
								src: attrs[ field.urlKey ],
								alt: '',
								style: { width: '100%', height: 'auto', marginBottom: '8px' },
							} )
							: el( 'p', { style: { color: '#757575' } }, field.help || '' ),
						el(
							MediaUploadCheck,
							null,
							el( MediaUpload, {
								onSelect: function ( media ) {
									var obj = {};
									obj[ field.idKey ]  = media.id || 0;
									obj[ field.urlKey ] = media.url || '';
									obj[ field.altKey ] = media.alt || media.title || '';
									setAttr( obj );
								},
								allowedTypes: [ 'image' ],
								value: attrs[ field.idKey ] || 0,
								render: function ( ref ) {
									return el(
										Fragment,
										null,
										el( Button, {
											onClick: ref.open,
											variant: 'secondary',
											style: { marginRight: '8px' },
										}, attrs[ field.urlKey ] ? __( 'Ganti Poster', 'ugm-faculty' ) : __( 'Pilih Poster', 'ugm-faculty' ) ),
										attrs[ field.urlKey ]
											? el( Button, {
												onClick: function () {
													var obj = {};
													obj[ field.idKey ]  = 0;
													obj[ field.urlKey ] = '';
													obj[ field.altKey ] = '';
													setAttr( obj );
												},
												isDestructive: true,
											}, __( 'Hapus', 'ugm-faculty' ) )
											: null
									);
								},
							} )
						)
					);
				}
				return el( TextControl, {
					key:      field.key,
					label:    field.label,
					value:    attrs[ field.key ] || '',
					onChange: function ( v ) {
						var obj = {};
						obj[ field.key ] = v;
						setAttr( obj );
					},
					help: field.help || '',
					style: field.mono ? { fontFamily: 'monospace' } : {},
				} );
			} );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el( PanelBody, { title: __( 'Pengaturan Section', 'ugm-faculty' ), initialOpen: true },
						controls
					)
				),
				// Wrap dengan .ugmbt-page agar CSS variables aktif di editor.
				el( 'div', { className: 'ugmbt-page' },
					el( ServerSideRender, {
						block:      blockName,
						attributes: attrs,
						httpMethod: 'POST',
					} )
				)
			);
		};
	}

	/* --- Berita Terbaru section blocks --- */
	var beritaBlocks = [
		{
			name:        'ugm/bt-news-section',
			title:       __( 'Kumpulan Berita', 'ugm-faculty' ),
			description: __( 'Beberapa section berita: 1 berita besar + 1 berita samping + grid berita.', 'ugm-faculty' ),
			icon:        'layout',
			attributes: {
				sectionsCount: { type: 'integer', default: 4 },
				postsPerRow:   { type: 'integer', default: 3 },
				categorySlug:  { type: 'string',  default: '' },
			},
			fields: [
				{ key: 'sectionsCount', label: __( 'Jumlah Section Berita', 'ugm-faculty' ),
				  type: 'number', default: 4,
				  help: __( 'Jumlah pola berita yang tampil dalam satu halaman. Minimal 1, maksimal 8.', 'ugm-faculty' ) },
				{ key: 'postsPerRow', label: __( 'Artikel Grid per Section', 'ugm-faculty' ),
				  type: 'number', default: 3,
				  help: __( 'Jumlah kartu grid di bawah berita utama. Minimal 2, maksimal 4.', 'ugm-faculty' ) },
				{ key: 'categorySlug', label: __( 'Slug Kategori (opsional)', 'ugm-faculty' ),
				  help: __( 'Kosongkan = berita terbaru dari semua kategori.', 'ugm-faculty' ), mono: true },
			],
		},
		{
			name:        'ugm/bt-featured-row',
			title:       __( 'Baris Utama Berita', 'ugm-faculty' ),
			description: __( '1 berita besar di kiri + 1 berita pendukung di kanan dalam satu baris.', 'ugm-faculty' ),
			icon:        'format-image',
			attributes: {
				categorySlug: { type: 'string', default: '' },
			},
			fields: [
				{ key: 'categorySlug', label: __( 'Slug Kategori (opsional)', 'ugm-faculty' ),
				  help: __( 'Kosongkan = berita terbaru dari semua kategori.', 'ugm-faculty' ), mono: true },
			],
		},
		{
			name:        'ugm/bt-news-grid',
			title:       __( 'Grid Kolom Berita', 'ugm-faculty' ),
			description: __( 'Baris kartu berita berukuran sama (default 3 kolom).', 'ugm-faculty' ),
			icon:        'grid-view',
			attributes: {
				postsCount:   { type: 'integer', default: 3 },
				categorySlug: { type: 'string',  default: '' },
			},
			fields: [
				{ key: 'postsCount',   label: __( 'Jumlah Kolom / Artikel', 'ugm-faculty' ),
				  type: 'number', default: 3,
				  help: __( 'Minimal 2, maksimal 6 artikel dalam satu baris grid.', 'ugm-faculty' ) },
				{ key: 'categorySlug', label: __( 'Slug Kategori (opsional)', 'ugm-faculty' ),
				  help: __( 'Kosongkan = semua kategori.', 'ugm-faculty' ), mono: true },
			],
		},
		{
			name:        'ugm/bt-sidebar-promo',
			title:       __( 'Sidebar: Poster & Update', 'ugm-faculty' ),
			description: __( 'Tombol UGM Peduli Bencana dan poster informasi di sidebar.', 'ugm-faculty' ),
			icon:        'format-image',
			attributes: {
				buttonText:    { type: 'string',  default: 'UGM Peduli Bencana - Update' },
				buttonUrl:     { type: 'string',  default: '/peduli-bencana/' },
				posterOneId:   { type: 'integer', default: 0 },
				posterOneUrl:  { type: 'string',  default: '' },
				posterOneAlt:  { type: 'string',  default: '' },
				posterOneLink: { type: 'string',  default: '' },
				posterTwoId:   { type: 'integer', default: 0 },
				posterTwoUrl:  { type: 'string',  default: '' },
				posterTwoAlt:  { type: 'string',  default: '' },
				posterTwoLink: { type: 'string',  default: '' },
			},
			fields: [
				{ key: 'buttonText', label: __( 'Teks Tombol', 'ugm-faculty' ) },
				{ key: 'buttonUrl', label: __( 'URL Tombol', 'ugm-faculty' ),
				  help: __( 'Contoh: /peduli-bencana/ atau URL lengkap.', 'ugm-faculty' ), mono: true },
				{ key: 'posterOneImage', label: __( 'Poster 1', 'ugm-faculty' ),
				  type: 'image', idKey: 'posterOneId', urlKey: 'posterOneUrl', altKey: 'posterOneAlt',
				  help: __( 'Pilih gambar poster pertama dari Media Library.', 'ugm-faculty' ) },
				{ key: 'posterOneLink', label: __( 'Link Poster 1 (opsional)', 'ugm-faculty' ), mono: true },
				{ key: 'posterTwoImage', label: __( 'Poster 2', 'ugm-faculty' ),
				  type: 'image', idKey: 'posterTwoId', urlKey: 'posterTwoUrl', altKey: 'posterTwoAlt',
				  help: __( 'Pilih gambar poster kedua dari Media Library.', 'ugm-faculty' ) },
				{ key: 'posterTwoLink', label: __( 'Link Poster 2 (opsional)', 'ugm-faculty' ), mono: true },
			],
		},
		{
			name:        'ugm/bt-sidebar-news',
			title:       __( 'Sidebar: Berita Terbaru', 'ugm-faculty' ),
			description: __( 'Widget sidebar berisi daftar judul berita terbaru.', 'ugm-faculty' ),
			icon:        'list-view',
			attributes: {
				widgetTitle: { type: 'string',  default: '' },
				postsCount:  { type: 'integer', default: 5 },
			},
			fields: [
				{ key: 'widgetTitle', label: __( 'Judul Widget', 'ugm-faculty' ),
				  help: __( 'Kosongkan = "Berita Terbaru".', 'ugm-faculty' ) },
				{ key: 'postsCount',  label: __( 'Jumlah Berita', 'ugm-faculty' ),
				  type: 'number', default: 5 },
			],
		},
		{
			name:        'ugm/bt-sidebar-agenda',
			title:       __( 'Sidebar: Agenda Terbaru', 'ugm-faculty' ),
			description: __( 'Widget sidebar agenda dengan kotak tanggal navy dan tombol "Semua Agenda".', 'ugm-faculty' ),
			icon:        'calendar-alt',
			attributes: {
				widgetTitle: { type: 'string',  default: '' },
				postsCount:  { type: 'integer', default: 3 },
				agendaUrl:   { type: 'string',  default: '/agenda/' },
			},
			fields: [
				{ key: 'widgetTitle', label: __( 'Judul Widget', 'ugm-faculty' ),
				  help: __( 'Kosongkan = "Agenda Terbaru".', 'ugm-faculty' ) },
				{ key: 'postsCount',  label: __( 'Jumlah Agenda', 'ugm-faculty' ),
				  type: 'number', default: 3 },
				{ key: 'agendaUrl',   label: __( 'URL Tombol "Semua Agenda"', 'ugm-faculty' ),
				  help: __( 'Contoh: /agenda/ atau /kegiatan/', 'ugm-faculty' ), mono: true },
			],
		},
		{
			name:        'ugm/bt-sidebar-categories',
			title:       __( 'Sidebar: Kategori', 'ugm-faculty' ),
			description: __( 'Widget sidebar berisi semua kategori berita beserta jumlah artikelnya.', 'ugm-faculty' ),
			icon:        'category',
			attributes: {
				widgetTitle: { type: 'string', default: '' },
			},
			fields: [
				{ key: 'widgetTitle', label: __( 'Judul Widget', 'ugm-faculty' ),
				  help: __( 'Kosongkan = "Kategori".', 'ugm-faculty' ) },
			],
		},
	];

	beritaBlocks.forEach( function ( block ) {
		registerBlockType( block.name, {
			title:       block.title,
			description: block.description,
			category:    'ugm-berita-terbaru',
			icon:        block.icon,
			supports:    { html: false, multiple: false },
			attributes:  block.attributes,
			edit:        makeBeritaEdit( block.name, block.fields ),
			save:        function () { return null; },
		} );
	} );

	registerBlockType( 'ugm/gallery-section', {
		title:       __( 'Gallery', 'ugm-faculty' ),
		description: __( 'Display a manually managed gallery highlight section.', 'ugm-faculty' ),
		category:    'ugm-sections',
		keywords:    [ __( 'gallery', 'ugm-faculty' ), __( 'images', 'ugm-faculty' ), __( 'media', 'ugm-faculty' ) ],
		icon:        'format-gallery',
		supports:    { html: false, multiple: false },
		attributes: {
			visibility:    { type: 'string', default: 'all' },
			sectionTitle:  { type: 'string', default: 'Gallery' },
			galleryPageId: { type: 'integer', default: 0 },
			date:          { type: 'string', default: '' },
			galleryTitle:  { type: 'string', default: 'Gallery Highlight' },
			description:   { type: 'string', default: '' },
			detailLabel:   { type: 'string', default: 'View Details' },
			detailUrl:     { type: 'string', default: '' },
			viewAllLabel:  { type: 'string', default: 'View All' },
			viewAllUrl:    { type: 'string', default: '' },
			images:        { type: 'array', default: [] },
		},
		edit: makeGallerySectionEdit(),
		save: function () { return null; },
	} );

	registerBlockType( 'ugm/featured-categories', {
		title: __( 'Content Categories (Legacy)', 'ugm-faculty' ),
		description: __( 'Legacy grouped category highlights. Use Featured Content for movable individual columns.', 'ugm-faculty' ),
		category: 'ugm-sections',
		keywords: [ __( 'categories', 'ugm-faculty' ), __( 'legacy', 'ugm-faculty' ), __( 'content', 'ugm-faculty' ) ],
		icon: 'screenoptions',
		supports: { html: false, multiple: false, inserter: false },
		attributes: {
			visibility: { type: 'string', default: 'all' },
			campusTitle: { type: 'string', default: '' },
			campusCategorySlug: { type: 'string', default: '' },
			facultyTitle: { type: 'string', default: '' },
			facultyCategorySlug: { type: 'string', default: '' },
			partnershipTitle: { type: 'string', default: '' },
			partnershipCategorySlug: { type: 'string', default: '' },
		},
		edit: makeFeaturedCategoriesEdit(),
		save: function () { return null; },
	} );

	/* ugm/featured-category-column — single reorderable column */
	registerBlockType( 'ugm/featured-category-column', {
		title:       __( 'Featured Content', 'ugm-faculty' ),
		description: __( 'Display a single highlighted content column based on a selected category.', 'ugm-faculty' ),
		category:    'ugm-sections',
		keywords:    [ __( 'featured', 'ugm-faculty' ), __( 'content', 'ugm-faculty' ), __( 'category', 'ugm-faculty' ) ],
		icon:        'columns',
		supports:    { html: false },
		attributes: {
			visibility:   { type: 'string', default: 'all' },
			title:        { type: 'string', default: 'Highlight Konten' },
			categorySlug: { type: 'string', default: '' },
			emptyText:    { type: 'string', default: '' },
		},
		edit: makeSectionEdit( 'ugm/featured-category-column', __( 'Featured Content', 'ugm-faculty' ), 'seputar-kampus' ),
		save: function () { return null; },
	} );

	// Register static blocks (no configurable attributes).
	[
		{ name: 'ugm/site-header',      title: __( 'Header Situs', 'ugm-faculty' ),   icon: 'admin-site' },
		{ name: 'ugm/site-footer',      title: __( 'Footer Situs', 'ugm-faculty' ),   icon: 'admin-site-alt3' },
		{ name: 'ugm/magazine-section', title: __( 'Digital Magazine', 'ugm-faculty' ), icon: 'media-document' },
	].forEach( function ( block ) {
		registerBlockType( block.name, {
			title:    block.title,
			category: block.name.indexOf( 'header' ) !== -1 || block.name.indexOf( 'footer' ) !== -1
				? 'theme' : 'ugm-sections',
			icon:     block.icon,
			supports: { html: false, multiple: false },
			attributes: { visibility: { type: 'string', default: 'all' } },
			edit:     makeStaticEdit( block.name, block.title ),
			save:     function () { return null; },
		} );
	} );

	/* ugm/agenda-only — Agenda Kegiatan mandiri */
	registerBlockType( 'ugm/footer-social', {
		title: __( 'Footer - Social Media', 'ugm-faculty' ),
		description: __( 'Ikon dan tautan media sosial untuk area widget footer.', 'ugm-faculty' ),
		category: 'widgets',
		icon: 'share',
		supports: { html: false, multiple: false },
		attributes: {
			instagramUrl: { type: 'string', default: 'https://www.instagram.com/' },
			youtubeUrl: { type: 'string', default: 'https://www.youtube.com/' },
			facebookUrl: { type: 'string', default: 'https://www.facebook.com/' },
			xUrl: { type: 'string', default: 'https://x.com/' },
			linkedinUrl: { type: 'string', default: 'https://www.linkedin.com/' },
			tiktokUrl: { type: 'string', default: 'https://www.tiktok.com/' },
		},
		edit: makeFooterFieldsEdit( 'ugm/footer-social', __( 'Tautan Media Sosial', 'ugm-faculty' ), [
			{ key: 'instagramUrl', label: 'Instagram URL' },
			{ key: 'youtubeUrl', label: 'YouTube URL' },
			{ key: 'facebookUrl', label: 'Facebook URL' },
			{ key: 'xUrl', label: 'X URL' },
			{ key: 'linkedinUrl', label: 'LinkedIn URL' },
			{ key: 'tiktokUrl', label: 'TikTok URL' },
		] ),
		save: function () { return null; },
	} );

	registerBlockType( 'ugm/footer-brand', {
		title: __( 'Footer - Brand / Logo', 'ugm-faculty' ),
		description: __( 'Logo dan teks institusi yang mengikuti UGM Branding.', 'ugm-faculty' ),
		category: 'widgets',
		icon: 'format-image',
		supports: { html: false, multiple: false },
		attributes: {
			imageId: { type: 'integer', default: 0 },
			imageUrl: { type: 'string', default: '' },
			alt: { type: 'string', default: 'Universitas Gadjah Mada' },
		},
		edit: makeFooterBrandEdit(),
		save: function () { return null; },
	} );

	registerBlockType( 'ugm/footer-contact', {
		title: __( 'Footer - Kontak & Alamat', 'ugm-faculty' ),
		description: __( 'Alamat, email, telepon, faks, dan WhatsApp institusi.', 'ugm-faculty' ),
		category: 'widgets',
		icon: 'location',
		supports: { html: false, multiple: false },
		attributes: {
			address: { type: 'string', default: 'Bulaksumur, Caturtunggal, Kec. Depok,\nKabupaten Sleman, Daerah Istimewa\nYogyakarta 55281' },
			email: { type: 'string', default: 'info@ugm.ac.id' },
			phone: { type: 'string', default: '+62(274)588688' },
			fax: { type: 'string', default: '+62(274)565223' },
			whatsapp: { type: 'string', default: '+628112869988' },
			addressIconId: { type: 'integer', default: 0 },
			addressIconUrl: { type: 'string', default: '' },
			emailIconId: { type: 'integer', default: 0 },
			emailIconUrl: { type: 'string', default: '' },
			whatsappIconId: { type: 'integer', default: 0 },
			whatsappIconUrl: { type: 'string', default: '' },
		},
		edit: makeFooterContactEdit(),
		save: function () { return null; },
	} );

	registerBlockType( 'ugm/footer-banner', {
		title: __( 'Footer - Banner Bawah', 'ugm-faculty' ),
		description: __( 'Gambar panorama di bagian paling bawah footer.', 'ugm-faculty' ),
		category: 'widgets',
		icon: 'format-gallery',
		supports: { html: false, multiple: false },
		attributes: {
			imageId: { type: 'integer', default: 0 },
			imageUrl: { type: 'string', default: '' },
			alt: { type: 'string', default: 'Kampus Universitas Gadjah Mada' },
		},
		edit: makeFooterImageEdit( 'ugm/footer-banner', __( 'Banner Bawah', 'ugm-faculty' ) ),
		save: function () { return null; },
	} );

	registerBlockType( 'ugm/agenda-only', {
		title:       __( 'Events & Agenda', 'ugm-faculty' ),
		description: __( 'Display event or agenda items from a selected category.', 'ugm-faculty' ),
		category:    'ugm-sections',
		keywords:    [ __( 'events', 'ugm-faculty' ), __( 'agenda', 'ugm-faculty' ), __( 'calendar', 'ugm-faculty' ) ],
		icon:        'calendar-alt',
		supports:    { html: false },
		attributes: {
			visibility:  { type: 'string', default: 'all' },
			title:        { type: 'string', default: 'Event & Agenda' },
			categorySlug: { type: 'string', default: '' },
		},
		edit: makeSectionEdit( 'ugm/agenda-only', __( 'Events & Agenda', 'ugm-faculty' ), 'agenda' ),
		save: function () { return null; },
	} );

	/* ugm/facility-only — Fasilitas Mahasiswa mandiri */
	registerBlockType( 'ugm/facility-only', {
		title:       __( 'Facilities', 'ugm-faculty' ),
		description: __( 'Display facility items from a selected category.', 'ugm-faculty' ),
		category:    'ugm-sections',
		keywords:    [ __( 'facilities', 'ugm-faculty' ), __( 'services', 'ugm-faculty' ), __( 'resources', 'ugm-faculty' ) ],
		icon:        'building',
		supports:    { html: false },
		attributes: {
			visibility:  { type: 'string', default: 'all' },
			title:        { type: 'string', default: 'Fasilitas Kampus' },
			categorySlug: { type: 'string', default: '' },
		},
		edit: makeSectionEdit( 'ugm/facility-only', __( 'Facilities', 'ugm-faculty' ), 'fasilitas-mahasiswa' ),
		save: function () { return null; },
	} );
	/* ugm/faculty-list — Fakultas dan Sekolah (Manual / Static) */
	registerBlockType( 'ugm/faculty-list', {
		title:       __( 'Organization Structure', 'ugm-faculty' ),
		description: __( 'Display a manually managed list of organizational units with images and links.', 'ugm-faculty' ),
		category:    'ugm-sections',
		keywords:    [ __( 'organization', 'ugm-faculty' ), __( 'departments', 'ugm-faculty' ), __( 'structure', 'ugm-faculty' ) ],
		icon:        'building',
		supports:    { html: false },
		attributes: {
			visibility: { type: 'string', default: 'all' },
			overline: { type: 'string', default: 'Seputar UGM' },
			title:    { type: 'string', default: 'Struktur Akademik' },
			items:    { type: 'array',  default: [] },
		},

		edit: function ( props ) {
			var attrs      = props.attributes;
			var setAttr    = props.setAttributes;
			var items      = Array.isArray( attrs.items ) ? attrs.items : [];
			var Component  = wp.element.Component;

			/* ---- Render each item card in the inspector ---- */
			function renderItemRow( item, index ) {
				return el(
					'div',
					{
						style: {
							border: '1px solid #ddd', borderRadius: '4px',
							padding: '10px', marginBottom: '10px', background: '#fafafa',
						},
					},

					/* Header row */
					el( 'div',
						{ style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '6px' } },
						el( 'strong', null, 'Fakultas #' + ( index + 1 ) ),
						el( Button, {
							isDestructive: true, isSmall: true,
							onClick: function () {
								var next = items.filter( function ( _, i ) { return i !== index; } );
								setAttr( { items: next } );
							},
						}, 'Hapus' )
					),

					/* Name */
					el( TextControl, {
						label: 'Nama Fakultas',
						value: item.name || '',
						onChange: function ( v ) {
							var next = items.map( function ( it, i ) {
								return i === index ? { name: v, imageUrl: it.imageUrl || '', link: it.link || '' } : it;
							} );
							setAttr( { items: next } );
						},
					} ),

					/* Image via MediaUpload */
					el( 'div', { style: { marginBottom: '8px' } },
						el( 'p', { style: { fontSize: '12px', fontWeight: '600', marginBottom: '4px' } }, 'Gambar Fakultas' ),
						item.imageUrl
							? el( 'div', null,
								el( 'img', {
									src: item.imageUrl, alt: '',
									style: { width: '100%', height: '70px', objectFit: 'cover', borderRadius: '3px', display: 'block', marginBottom: '4px' },
								} ),
								el( Button, {
									isDestructive: true, isSmall: true,
									onClick: function () {
										var next = items.map( function ( it, i ) {
											return i === index ? { name: it.name || '', imageUrl: '', link: it.link || '' } : it;
										} );
										setAttr( { items: next } );
									},
								}, 'Hapus Gambar' )
							)
							: null,
						el( MediaUploadCheck, null,
							el( MediaUpload, {
								allowedTypes: [ 'image' ],
								value: item.imageUrl ? item.imageUrl : null,
								onSelect: function ( media ) {
									var next = items.map( function ( it, i ) {
										return i === index ? { name: it.name || '', imageUrl: media.url, link: it.link || '' } : it;
									} );
									setAttr( { items: next } );
								},
								render: function ( ref ) {
									return el( Button, { isSecondary: true, isSmall: true, onClick: ref.open },
										item.imageUrl ? 'Ganti Gambar' : 'Pilih Gambar'
									);
								},
							} )
						)
					),

					/* Link */
					el( TextControl, {
						label: 'Link Website',
						value: item.link || '',
						type: 'url',
						help: 'Contoh: https://biologi.ugm.ac.id',
						onChange: function ( v ) {
							var next = items.map( function ( it, i ) {
								return i === index ? { name: it.name || '', imageUrl: it.imageUrl || '', link: v } : it;
							} );
							setAttr( { items: next } );
						},
					} )
				);
			}

			return el( Fragment, null,

				/* Inspector Controls */
				el( InspectorControls, null,

					/* Section settings */
					el( PanelBody, { title: 'Pengaturan Section', initialOpen: true },
						renderVisibilityControl( attrs, setAttr ),
						el( TextControl, {
							label: 'Overline (teks kecil)',
							value: attrs.overline || '',
							onChange: function ( v ) { setAttr( { overline: v } ); },
						} ),
						el( TextControl, {
							label: 'Judul Section',
							value: attrs.title || '',
							onChange: function ( v ) { setAttr( { title: v } ); },
						} )
					),

					/* Faculty items */
					el( PanelBody, { title: 'Daftar Fakultas (' + items.length + ')', initialOpen: true },
						items.length > 0
							? items.map( function ( item, index ) { return renderItemRow( item, index ); } )
							: el( 'p', { style: { fontSize: '12px', color: '#777', fontStyle: 'italic' } },
								'Belum ada fakultas. Klik "+ Tambah" di bawah.'
							),
						el( Button, {
							isPrimary: true,
							style: { width: '100%', justifyContent: 'center', marginTop: '8px' },
							onClick: function () {
								setAttr( { items: items.concat( [ { name: '', imageUrl: '', link: '' } ] ) } );
							},
						}, '+ Tambah Fakultas' )
					)
				),

				/* Live preview */
				el( ServerSideRender, {
					block: 'ugm/faculty-list',
					attributes: attrs,
					httpMethod: 'POST',
				} )
			);
		},

		save: function () { return null; },
	} );

	/* ------------------------------------------------------------------
	 * ugm/template-links — Tautan Layanan (Grid Kartu Manual)
	 *
	 * Manually-configured grid of link cards matching the UGM portal
	 * navigation style: dark-navy card, icon image, bold label, sublabel,
	 * optional background image, and an external URL.
	 * ------------------------------------------------------------------ */
	registerBlockType( 'ugm/template-links', {
		title:       __( 'Featured Services', 'ugm-faculty' ),
		description: __( 'Display a manually managed grid of service or resource links.', 'ugm-faculty' ),
		category:    'ugm-sections',
		keywords:    [ __( 'services', 'ugm-faculty' ), __( 'links', 'ugm-faculty' ), __( 'resources', 'ugm-faculty' ) ],
		icon:        'admin-links',
		supports:    { html: false },
		attributes: {
			visibility: { type: 'string', default: 'all' },
			title: { type: 'string', default: 'Layanan Pilihan' },
			items: { type: 'array',  default: [] },
		},

		edit: function ( props ) {
			var attrs   = props.attributes;
			var setAttr = props.setAttributes;
			var items   = Array.isArray( attrs.items ) ? attrs.items : [];

			function renderSectionSettings() {
				return el( PanelBody, { title: __( 'Pengaturan Section', 'ugm-faculty' ), initialOpen: true },
					renderVisibilityControl( attrs, setAttr ),
					el( TextControl, {
						label: __( 'Judul Section', 'ugm-faculty' ),
						value: attrs.title || '',
						onChange: function ( v ) { setAttr( { title: v } ); },
					} )
				);
			}

			/* ---- Render one item row in the inspector ---- */
			function renderItemRow( item, index ) {
				function updateItem( patch ) {
					var next = items.map( function ( it, i ) {
						return i === index ? Object.assign( {}, it, patch ) : it;
					} );
					setAttr( { items: next } );
				}

				return el(
					'div',
					{
						key: 'tl-item-' + index,
						style: {
							border: '1px solid #ddd', borderRadius: '4px',
							padding: '10px', marginBottom: '10px', background: '#fafafa',
						},
					},

					/* Row header */
					el( 'div',
						{ style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '8px' } },
						el( 'strong', null, __( 'Tautan #', 'ugm-faculty' ) + ( index + 1 ) ),
						el( Button, {
							isDestructive: true, isSmall: true,
							onClick: function () {
								setAttr( { items: items.filter( function ( _, i ) { return i !== index; } ) } );
							},
						}, __( 'Hapus', 'ugm-faculty' ) )
					),

					/* Label */
					el( TextControl, {
						label:    __( 'Label (Judul)', 'ugm-faculty' ),
						value:    item.label || '',
						onChange: function ( v ) { updateItem( { label: v } ); },
					} ),

					/* Sublabel */
					el( TextControl, {
						label:    __( 'Sub-label / Teks Kecil', 'ugm-faculty' ),
						value:    item.sublabel || '',
						onChange: function ( v ) { updateItem( { sublabel: v } ); },
						help:     __( 'Contoh: aspirasi.ugm.ac.id', 'ugm-faculty' ),
					} ),

					/* Link URL */
					el( TextControl, {
						label:    __( 'URL Tujuan', 'ugm-faculty' ),
						value:    item.link || '',
						type:     'url',
						onChange: function ( v ) { updateItem( { link: v } ); },
						help:     __( 'Link dibuka di tab baru. Contoh: https://ppid.ugm.ac.id', 'ugm-faculty' ),
						style:    { fontFamily: 'monospace' },
					} ),

					/* Icon image */
					el( 'div', { style: { marginBottom: '10px' } },
						el( 'p', { style: { fontSize: '12px', fontWeight: '600', margin: '0 0 4px' } },
							__( 'Ikon / Logo', 'ugm-faculty' )
						),
						item.iconUrl
							? el( 'div', { style: { marginBottom: '6px' } },
								el( 'img', {
									src: item.iconUrl, alt: '',
									style: {
										width: '52px', height: '52px', objectFit: 'contain',
										borderRadius: '4px', display: 'block', marginBottom: '4px',
										background: '#1a3a5c', padding: '4px',
									},
								} ),
								el( Button, {
									isDestructive: true, isSmall: true, style: { marginBottom: '4px' },
									onClick: function () { updateItem( { iconUrl: '', iconId: 0 } ); },
								}, __( 'Hapus Ikon', 'ugm-faculty' ) )
							)
							: null,
						el( MediaUploadCheck, null,
							el( MediaUpload, {
								allowedTypes: [ 'image' ],
								value:        item.iconId || null,
								onSelect:     function ( media ) { updateItem( { iconUrl: media.url, iconId: media.id } ); },
								render:       function ( ref ) {
									return el( Button, { isSecondary: true, isSmall: true, onClick: ref.open },
										item.iconUrl ? __( 'Ganti Ikon', 'ugm-faculty' ) : __( 'Pilih Ikon', 'ugm-faculty' )
									);
								},
							} )
						)
					),

					/* Background image */
					el( 'div', null,
						el( 'p', { style: { fontSize: '12px', fontWeight: '600', margin: '0 0 4px' } },
							__( 'Gambar Latar Kartu (opsional)', 'ugm-faculty' )
						),
						item.bgUrl
							? el( 'div', { style: { marginBottom: '6px' } },
								el( 'img', {
									src: item.bgUrl, alt: '',
									style: { width: '100%', height: '52px', objectFit: 'cover', borderRadius: '3px', display: 'block', marginBottom: '4px' },
								} ),
								el( Button, {
									isDestructive: true, isSmall: true, style: { marginBottom: '4px' },
									onClick: function () { updateItem( { bgUrl: '', bgId: 0 } ); },
								}, __( 'Hapus Gambar Latar', 'ugm-faculty' ) )
							)
							: null,
						el( MediaUploadCheck, null,
							el( MediaUpload, {
								allowedTypes: [ 'image' ],
								value:        item.bgId || null,
								onSelect:     function ( media ) { updateItem( { bgUrl: media.url, bgId: media.id } ); },
								render:       function ( ref ) {
									return el( Button, { isSecondary: true, isSmall: true, onClick: ref.open },
										item.bgUrl ? __( 'Ganti Gambar Latar', 'ugm-faculty' ) : __( 'Pilih Gambar Latar', 'ugm-faculty' )
									);
								},
							} )
						)
					)
				);
			}

			return el( Fragment, null,

				/* Inspector Controls */
				el( InspectorControls, null,
					renderSectionSettings(),

					el( PanelBody, { title: __( 'Daftar Tautan (' + items.length + ')', 'ugm-faculty' ), initialOpen: true },
						items.length > 0
							? items.map( function ( item, index ) { return renderItemRow( item, index ); } )
							: el( 'p', { style: { fontSize: '12px', color: '#777', fontStyle: 'italic' } },
								__( 'Belum ada tautan. Klik "+ Tambah" di bawah.', 'ugm-faculty' )
							),
						el( Button, {
							isPrimary: true,
							style: { width: '100%', justifyContent: 'center', marginTop: '8px' },
							onClick: function () {
								setAttr( { items: items.concat( [ { label: '', sublabel: '', link: '', iconUrl: '', iconId: 0, bgUrl: '', bgId: 0 } ] ) } );
							},
						}, __( '+ Tambah Tautan', 'ugm-faculty' ) )
					)
				),

				/* Live SSR preview */
				el( ServerSideRender, {
					block:      'ugm/template-links',
					attributes: attrs,
					httpMethod: 'POST',
				} )
			);
		},

		save: function () { return null; },
	} );

	registerBlockType( 'ugm/berita-terbaru-template-preview', {
		title:    __( 'Berita Terbaru Template Preview', 'ugm-faculty' ),
		category: 'ugm-berita-terbaru',
		supports: {
			html:     false,
			inserter: false,
		},
		edit: function () {
			var isPreviewMode = useSelect( function ( select ) {
				return !! select( 'core/block-editor' ).getSettings().isPreviewMode;
			}, [] );

			if ( ! isPreviewMode ) {
				return null;
			}

			return el(
				Fragment,
				null,
				el( 'style', null, '.editor-styles-wrapper .wp-block[data-type="ugm/berita-terbaru-template-preview"] ~ .wp-block[data-type="core/post-content"]{display:none!important;}' ),
				el(
					'main',
					{ className: 'site-main ugmbt-page ugmbt-template-preview' },
					el(
						'div',
						{ className: 'ugmbt-container' },
						el(
							'nav',
							{ className: 'ugmbt-breadcrumb', 'aria-label': __( 'Breadcrumb', 'ugm-faculty' ) },
							el( 'span', null, __( 'Berita', 'ugm-faculty' ) ),
							el( 'span', { 'aria-hidden': 'true' }, '›' ),
							el( 'span', { 'aria-current': 'page' }, __( 'Berita Terbaru', 'ugm-faculty' ) )
						),
						el( 'h1', { className: 'ugmbt-page-title' }, __( 'Berita Terbaru', 'ugm-faculty' ) ),
						el(
							'div',
							{ className: 'ugmbt-layout' },
							el(
								'div',
								{ className: 'ugmbt-main' },
								el( ServerSideRender, {
									block: 'ugm/bt-news-section',
									attributes: {
										sectionsCount: 2,
										postsPerRow: 3,
										categorySlug: '',
									},
									httpMethod: 'POST',
								} )
							),
							el(
								'aside',
								{ className: 'ugmbt-sidebar', 'aria-label': __( 'Sidebar', 'ugm-faculty' ) },
								el( ServerSideRender, {
									block: 'ugm/bt-sidebar-promo',
									attributes: {
										buttonText: 'UGM Peduli Bencana - Update',
										buttonUrl: '/peduli-bencana/',
									},
									httpMethod: 'POST',
								} ),
								el( ServerSideRender, {
									block: 'ugm/bt-sidebar-news',
									attributes: {
										widgetTitle: 'Berita Terbaru',
										postsCount: 5,
									},
									httpMethod: 'POST',
								} ),
								el( ServerSideRender, {
									block: 'ugm/bt-sidebar-agenda',
									attributes: {
										widgetTitle: 'Agenda Terbaru',
										postsCount: 3,
										agendaUrl: '/agenda/',
									},
									httpMethod: 'POST',
								} )
							)
						)
					)
				)
			);
		},
		save: function () {
			return null;
		},
	} );

}() );
