/**
 * UGM Landing Page — Block Editor JavaScript
 *
 * Registers all custom landing page section blocks in the block editor so they:
 * 1. Appear in the block inserter under "UGM — Landing Page Sections"
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
						{ title: __( 'Event & Agenda', 'ugm-faculty' ), initialOpen: true },
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
						{ title: __( 'Fasilitas Kampus', 'ugm-faculty' ), initialOpen: false },
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
		title:       __( 'Banner Utama', 'ugm-faculty' ),
		description: __( 'Gambar latar, judul, dan deskripsi halaman landing.', 'ugm-faculty' ),
		category:    'ugm-sections',
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
			title:        __( 'Highlight Informasi', 'ugm-faculty' ),
			description:  __( 'Menampilkan postingan terbaru pada landing page.', 'ugm-faculty' ),
			icon:         'rss',
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
			title:        __( 'Informasi Akademik', 'ugm-faculty' ),
			description:  __( 'Menampilkan berita berdasarkan kategori akademik.', 'ugm-faculty' ),
			icon:         'book',
			defaultTitle: __( 'Berita Akademik', 'ugm-faculty' ),
			defaultSlug:  'pendidikan',
			attrs: {
				title:        { type: 'string', default: 'Informasi Akademik' },
				categorySlug: { type: 'string', default: '' },
			},
		},
		{
			name:         'ugm/profile-section',
			title:        __( 'Informasi Umum', 'ugm-faculty' ),
			description:  __( 'Menampilkan konten kategori profil / informasi umum.', 'ugm-faculty' ),
			icon:         'admin-users',
			defaultTitle: __( 'Profile', 'ugm-faculty' ),
			defaultSlug:  'profile',
			attrs: {
				title:        { type: 'string', default: 'Informasi Umum' },
				categorySlug: { type: 'string', default: '' },
			},
		},
		{
			name:         'ugm/achievement-section',
			title:        __( 'Pencapaian', 'ugm-faculty' ),
			description:  __( 'Menampilkan konten pencapaian / prestasi berdasarkan kategori.', 'ugm-faculty' ),
			icon:         'awards',
			defaultTitle: __( 'Prestasi', 'ugm-faculty' ),
			defaultSlug:  'prestasi',
			attrs: {
				title:        { type: 'string', default: 'Pencapaian' },
				categorySlug: { type: 'string', default: '' },
			},
		},
		{
			name:         'ugm/facility-section',
			title:        __( 'Fasilitas', 'ugm-faculty' ),
			description:  __( 'Menampilkan konten kategori fasilitas.', 'ugm-faculty' ),
			icon:         'building',
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
			title:        __( 'Fakultas dan Sekolah', 'ugm-faculty' ),
			description:  __( 'Slider foto/logo fakultas dan sekolah.', 'ugm-faculty' ),
			icon:         'art',
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
			title:        __( 'Event & Agenda', 'ugm-faculty' ),
			description:  __( 'Menampilkan agenda kegiatan berdasarkan kategori.', 'ugm-faculty' ),
			icon:         'calendar',
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
			title:        __( 'Kategori', 'ugm-faculty' ),
			description:  __( 'Menampilkan daftar kategori pada landing page.', 'ugm-faculty' ),
			icon:         'category',
			defaultTitle: __( 'Kategori', 'ugm-faculty' ),
			defaultSlug:  null,
			attrs: {
				title: { type: 'string', default: 'Kategori' },
			},
		},
		{
			name:         'ugm/video-section',
			title:        __( 'Media Video', 'ugm-faculty' ),
			description:  __( 'Menampilkan video unggulan dan daftar video berdasarkan kategori.', 'ugm-faculty' ),
			icon:         'video-alt3',
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
			icon:        section.icon,
			supports:    section.supports || { html: false, multiple: false },
			attributes:  Object.assign( { visibility: { type: 'string', default: 'all' } }, section.attrs ),
			edit:        editFn,
			save:        function () { return null; },
		} );
	} );

	registerBlockType( 'ugm/featured-categories', {
		title: __( 'Sorotan Kategori (Lama)', 'ugm-faculty' ),
		description: __( 'Blok lama — gunakan "Sorotan Kategori Kolom" agar tiap kolom bisa dipindah terpisah.', 'ugm-faculty' ),
		category: 'ugm-sections',
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
		title:       __( 'Highlight Konten', 'ugm-faculty' ),
		description: __( 'Satu kolom highlight konten. Tambahkan tiga blok ini berdampingan dan pindah-pindahkan sesuka hati.', 'ugm-faculty' ),
		category:    'ugm-sections',
		icon:        'columns',
		supports:    { html: false },
		attributes: {
			visibility:   { type: 'string', default: 'all' },
			title:        { type: 'string', default: 'Highlight Konten' },
			categorySlug: { type: 'string', default: '' },
			emptyText:    { type: 'string', default: '' },
		},
		edit: makeSectionEdit( 'ugm/featured-category-column', __( 'Highlight Konten', 'ugm-faculty' ), 'seputar-kampus' ),
		save: function () { return null; },
	} );

	// Register static blocks (no configurable attributes).
	[
		{ name: 'ugm/site-header',      title: __( 'Header Situs', 'ugm-faculty' ),   icon: 'admin-site' },
		{ name: 'ugm/site-footer',      title: __( 'Footer Situs', 'ugm-faculty' ),   icon: 'admin-site-alt3' },
		{ name: 'ugm/magazine-section', title: __( 'E-Magazine', 'ugm-faculty' ), icon: 'media-document' },
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
		description: __( 'Logo institusi untuk area widget footer.', 'ugm-faculty' ),
		category: 'widgets',
		icon: 'format-image',
		supports: { html: false, multiple: false },
		attributes: {
			imageId: { type: 'integer', default: 0 },
			imageUrl: { type: 'string', default: '' },
			alt: { type: 'string', default: 'Universitas Gadjah Mada' },
		},
		edit: makeFooterImageEdit( 'ugm/footer-brand', __( 'Logo Institusi', 'ugm-faculty' ) ),
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
		},
		edit: makeFooterFieldsEdit( 'ugm/footer-contact', __( 'Kontak Institusi', 'ugm-faculty' ), [
			{ key: 'address', label: __( 'Alamat', 'ugm-faculty' ), type: 'textarea', rows: 4 },
			{ key: 'email', label: __( 'Email', 'ugm-faculty' ) },
			{ key: 'phone', label: __( 'Telepon', 'ugm-faculty' ) },
			{ key: 'fax', label: __( 'Faks', 'ugm-faculty' ) },
			{ key: 'whatsapp', label: __( 'WhatsApp', 'ugm-faculty' ) },
		] ),
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
		title:       __( 'Event & Agenda', 'ugm-faculty' ),
		description: __( 'Menampilkan daftar agenda kegiatan. Bisa dipindah terpisah dari Fasilitas Kampus.', 'ugm-faculty' ),
		category:    'ugm-sections',
		icon:        'calendar-alt',
		supports:    { html: false },
		attributes: {
			visibility:  { type: 'string', default: 'all' },
			title:        { type: 'string', default: 'Event & Agenda' },
			categorySlug: { type: 'string', default: '' },
		},
		edit: makeSectionEdit( 'ugm/agenda-only', __( 'Event & Agenda', 'ugm-faculty' ), 'agenda' ),
		save: function () { return null; },
	} );

	/* ugm/facility-only — Fasilitas Mahasiswa mandiri */
	registerBlockType( 'ugm/facility-only', {
		title:       __( 'Fasilitas Kampus', 'ugm-faculty' ),
		description: __( 'Menampilkan grid fasilitas kampus. Bisa dipindah terpisah dari Event & Agenda.', 'ugm-faculty' ),
		category:    'ugm-sections',
		icon:        'building',
		supports:    { html: false },
		attributes: {
			visibility:  { type: 'string', default: 'all' },
			title:        { type: 'string', default: 'Fasilitas Kampus' },
			categorySlug: { type: 'string', default: '' },
		},
		edit: makeSectionEdit( 'ugm/facility-only', __( 'Fasilitas Kampus', 'ugm-faculty' ), 'fasilitas-mahasiswa' ),
		save: function () { return null; },
	} );
	/* ugm/faculty-list — Fakultas dan Sekolah (Manual / Static) */
	registerBlockType( 'ugm/faculty-list', {
		title:       __( 'Struktur Akademik', 'ugm-faculty' ),
		description: __( 'Daftar fakultas/sekolah diinput langsung di editor — gambar, nama, link website.', 'ugm-faculty' ),
		category:    'ugm-sections',
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
		title:       __( 'Layanan Pilihan', 'ugm-faculty' ),
		description: __( 'Grid kartu layanan pilihan dengan ikon, label, dan URL. Data diinput manual di editor.', 'ugm-faculty' ),
		category:    'ugm-sections',
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
}() );
