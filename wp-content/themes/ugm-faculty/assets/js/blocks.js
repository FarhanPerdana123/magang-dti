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
	var TextControl       = wp.components.TextControl;
	var TextareaControl   = wp.components.TextareaControl;
	var SelectControl     = wp.components.SelectControl;
	var Button            = wp.components.Button;
	var Fragment          = wp.element.Fragment;
	var Placeholder       = wp.components.Placeholder;

	/* ------------------------------------------------------------------
	 * Helper: build the edit() function for a configurable section block
	 * with title + categorySlug attributes shown in Inspector Controls.
	 * ------------------------------------------------------------------ */
	function makeSectionEdit( blockName, defaultTitle, defaultSlug ) {
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
						el( TextControl, {
							label:    __( 'Judul Section', 'ugm-faculty' ),
							value:    attrs.title || '',
							onChange: function ( v ) { setAttr( { title: v } ); },
							help:     __( 'Kosongkan untuk menggunakan judul default dari Customizer.', 'ugm-faculty' ),
						} ),
						defaultSlug !== null
							? el( TextControl, {
								label:    __( 'Slug Kategori', 'ugm-faculty' ),
								value:    attrs.categorySlug || '',
								onChange: function ( v ) { setAttr( { categorySlug: v } ); },
								help:     __( 'Slug kategori WordPress. Kosongkan = default (' + ( defaultSlug || 'semua' ) + ').', 'ugm-faculty' ),
								style:    { fontFamily: 'monospace' },
							} )
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
		return function () {
			return el( Placeholder, {
				icon:  'layout',
				label: label,
			},
				el( ServerSideRender, {
					block:      blockName,
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
						el( TextControl, {
							label: __( 'Judul Section', 'ugm-faculty' ),
							value: attrs.campusTitle || '',
							onChange: function ( v ) { setAttr( { campusTitle: v } ); },
						} ),
						el( TextControl, {
							label: __( 'Slug Kategori', 'ugm-faculty' ),
							value: attrs.campusCategorySlug || '',
							onChange: function ( v ) { setAttr( { campusCategorySlug: v } ); },
							style: { fontFamily: 'monospace' },
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Kabar Fakultas', 'ugm-faculty' ), initialOpen: false },
						el( TextControl, {
							label: __( 'Judul Section', 'ugm-faculty' ),
							value: attrs.facultyTitle || '',
							onChange: function ( v ) { setAttr( { facultyTitle: v } ); },
						} ),
						el( TextControl, {
							label: __( 'Slug Kategori', 'ugm-faculty' ),
							value: attrs.facultyCategorySlug || '',
							onChange: function ( v ) { setAttr( { facultyCategorySlug: v } ); },
							style: { fontFamily: 'monospace' },
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Kerjasama', 'ugm-faculty' ), initialOpen: false },
						el( TextControl, {
							label: __( 'Judul Section', 'ugm-faculty' ),
							value: attrs.partnershipTitle || '',
							onChange: function ( v ) { setAttr( { partnershipTitle: v } ); },
						} ),
						el( TextControl, {
							label: __( 'Slug Kategori', 'ugm-faculty' ),
							value: attrs.partnershipCategorySlug || '',
							onChange: function ( v ) { setAttr( { partnershipCategorySlug: v } ); },
							style: { fontFamily: 'monospace' },
						} )
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
						el( TextControl, {
							label: __( 'Slug Kategori', 'ugm-faculty' ),
							value: attrs.categorySlug || '',
							onChange: function ( v ) { setAttr( { categorySlug: v } ); },
							help: __( 'Isi slug kategori post untuk section ini.', 'ugm-faculty' ),
							style: { fontFamily: 'monospace' },
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
						{ title: __( 'Agenda Kegiatan', 'ugm-faculty' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Judul Section', 'ugm-faculty' ),
							value: attrs.title || '',
							onChange: function ( v ) { setAttr( { title: v } ); },
						} ),
						el( TextControl, {
							label: __( 'Slug Kategori Agenda', 'ugm-faculty' ),
							value: attrs.categorySlug || '',
							onChange: function ( v ) { setAttr( { categorySlug: v } ); },
							help: __( 'Kosongkan = default (agenda).', 'ugm-faculty' ),
							style: { fontFamily: 'monospace' },
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Fasilitas Mahasiswa', 'ugm-faculty' ), initialOpen: false },
						el( TextControl, {
							label: __( 'Judul Section', 'ugm-faculty' ),
							value: attrs.facilityTitle || '',
							onChange: function ( v ) { setAttr( { facilityTitle: v } ); },
						} ),
						el( TextControl, {
							label: __( 'Slug Kategori Fasilitas', 'ugm-faculty' ),
							value: attrs.facilityCategorySlug || '',
							onChange: function ( v ) { setAttr( { facilityCategorySlug: v } ); },
							help: __( 'Kosongkan = default (fasilitas-mahasiswa / fasilitas).', 'ugm-faculty' ),
							style: { fontFamily: 'monospace' },
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
		title:       __( 'Hero Section', 'ugm-faculty' ),
		description: __( 'Gambar latar, judul, dan deskripsi halaman landing.', 'ugm-faculty' ),
		category:    'ugm-sections',
		icon:        'format-image',
		supports:    { html: false, multiple: false },
		attributes: {
			imageId:     { type: 'integer', default: 0 },
			imageUrl:    { type: 'string',  default: '' },
			title:       { type: 'string',  default: '' },
			description: { type: 'string',  default: '' },
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
						{ title: __( 'Gambar Latar Hero', 'ugm-faculty' ), initialOpen: true },
						el(
							MediaUploadCheck,
							null,
							el( MediaUpload, {
								onSelect: function ( media ) {
									setAttr( { imageId: media.id, imageUrl: media.url } );
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
					),
					el(
						PanelBody,
						{ title: __( 'Teks Hero', 'ugm-faculty' ), initialOpen: true },
						el( TextControl, {
							label:    __( 'Judul Hero', 'ugm-faculty' ),
							value:    attrs.title || '',
							onChange: function ( v ) { setAttr( { title: v } ); },
							help:     __( 'Kosongkan = judul default dari Customizer.', 'ugm-faculty' ),
						} ),
						el( TextareaControl, {
							label:    __( 'Deskripsi Hero', 'ugm-faculty' ),
							value:    attrs.description || '',
							onChange: function ( v ) { setAttr( { description: v } ); },
							rows:     3,
							help:     __( 'Kosongkan = deskripsi default dari Customizer.', 'ugm-faculty' ),
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
			title:        __( 'Berita Terbaru', 'ugm-faculty' ),
			description:  __( 'Menampilkan postingan terbaru pada landing page.', 'ugm-faculty' ),
			icon:         'rss',
			defaultTitle: __( 'Berita Terbaru', 'ugm-faculty' ),
			defaultSlug:  '', // Kosong = tampilkan semua post terbaru (semua kategori).
			attrs: {
				title:        { type: 'string', default: '' },
				categorySlug: { type: 'string', default: '' },
			},
		},
		{
			name:         'ugm/academic-news',
			title:        __( 'Berita Akademik', 'ugm-faculty' ),
			description:  __( 'Menampilkan berita berdasarkan kategori akademik.', 'ugm-faculty' ),
			icon:         'book',
			defaultTitle: __( 'Berita Akademik', 'ugm-faculty' ),
			defaultSlug:  'pendidikan',
			attrs: {
				title:        { type: 'string', default: '' },
				categorySlug: { type: 'string', default: '' },
			},
		},
		{
			name:         'ugm/profile-section',
			title:        __( 'Profile', 'ugm-faculty' ),
			description:  __( 'Menampilkan konten kategori profile.', 'ugm-faculty' ),
			icon:         'admin-users',
			defaultTitle: __( 'Profile', 'ugm-faculty' ),
			defaultSlug:  'profile',
			attrs: {
				title:        { type: 'string', default: '' },
				categorySlug: { type: 'string', default: '' },
			},
		},
		{
			name:         'ugm/achievement-section',
			title:        __( 'Prestasi', 'ugm-faculty' ),
			description:  __( 'Menampilkan konten kategori prestasi.', 'ugm-faculty' ),
			icon:         'awards',
			defaultTitle: __( 'Prestasi', 'ugm-faculty' ),
			defaultSlug:  'prestasi',
			attrs: {
				title:        { type: 'string', default: '' },
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
			attrs: {
				title:        { type: 'string', default: '' },
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
			attrs: {
				overline: { type: 'string', default: 'Seputar UGM' },
				title: { type: 'string', default: '' },
				categorySlug: { type: 'string', default: '' },
			},
		},
		{
			name:         'ugm/agenda-section',
			title:        __( 'Agenda Kegiatan', 'ugm-faculty' ),
			description:  __( 'Menampilkan agenda kegiatan berdasarkan kategori.', 'ugm-faculty' ),
			icon:         'calendar',
			defaultTitle: __( 'Agenda Kegiatan', 'ugm-faculty' ),
			defaultSlug:  'agenda',
			attrs: {
				title:                { type: 'string', default: '' },
				categorySlug:         { type: 'string', default: '' },
				facilityTitle:        { type: 'string', default: 'Fasilitas Mahasiswa' },
				facilityCategorySlug: { type: 'string', default: 'fasilitas-mahasiswa' },
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
				title: { type: 'string', default: '' },
			},
		},
		{
			name:         'ugm/video-section',
			title:        __( 'Video', 'ugm-faculty' ),
			description:  __( 'Menampilkan video unggulan dan daftar video berdasarkan kategori.', 'ugm-faculty' ),
			icon:         'video-alt3',
			defaultTitle: __( 'Video', 'ugm-faculty' ),
			defaultSlug:  'video',
			attrs: {
				title:        { type: 'string', default: '' },
				categorySlug: { type: 'string', default: 'video' },
			},
		},
	];

	/* Editor previews - frontend still uses the PHP hardcoded template. */
	sections.forEach( function ( section ) {
		var editFn = makeSectionEdit( section.name, section.defaultTitle, section.defaultSlug );

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
			supports:    { html: false, multiple: false },
			attributes:  section.attrs,
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

	registerBlockType( 'ugm/featured-categories', {
		title: __( 'Sorotan Kategori (Lama)', 'ugm-faculty' ),
		description: __( 'Blok lama — gunakan "Sorotan Kategori Kolom" agar tiap kolom bisa dipindah terpisah.', 'ugm-faculty' ),
		category: 'ugm-sections',
		icon: 'screenoptions',
		supports: { html: false, multiple: false },
		attributes: {
			campusTitle: { type: 'string', default: 'Seputar Kampus' },
			campusCategorySlug: { type: 'string', default: 'seputar-kampus' },
			facultyTitle: { type: 'string', default: 'Kabar Fakultas' },
			facultyCategorySlug: { type: 'string', default: 'kabar-fakultas' },
			partnershipTitle: { type: 'string', default: 'Kerjasama' },
			partnershipCategorySlug: { type: 'string', default: 'kerjasama' },
		},
		edit: makeFeaturedCategoriesEdit(),
		save: function () { return null; },
	} );

	/* ugm/featured-category-column — single reorderable column */
	registerBlockType( 'ugm/featured-category-column', {
		title:       __( 'Sorotan Kategori Kolom', 'ugm-faculty' ),
		description: __( 'Satu kolom sorotan kategori. Tambahkan tiga blok ini berdampingan dan pindah-pindahkan sesuka hati.', 'ugm-faculty' ),
		category:    'ugm-sections',
		icon:        'columns',
		supports:    { html: false },
		attributes: {
			title:        { type: 'string', default: '' },
			categorySlug: { type: 'string', default: '' },
			emptyText:    { type: 'string', default: '' },
		},
		edit: makeSectionEdit( 'ugm/featured-category-column', __( 'Sorotan Kategori', 'ugm-faculty' ), 'seputar-kampus' ),
		save: function () { return null; },
	} );

	// Register static blocks (no configurable attributes).
	[
		{ name: 'ugm/site-header',      title: __( 'Header Situs', 'ugm-faculty' ),   icon: 'admin-site' },
		{ name: 'ugm/site-footer',      title: __( 'Footer Situs', 'ugm-faculty' ),   icon: 'admin-site-alt3' },
		{ name: 'ugm/magazine-section', title: __( 'Majalah Digital', 'ugm-faculty' ), icon: 'media-document' },
	].forEach( function ( block ) {
		registerBlockType( block.name, {
			title:    block.title,
			category: block.name.indexOf( 'header' ) !== -1 || block.name.indexOf( 'footer' ) !== -1
				? 'theme' : 'ugm-sections',
			icon:     block.icon,
			supports: { html: false, multiple: false },
			edit:     makeStaticEdit( block.name, block.title ),
			save:     function () { return null; },
		} );
	} );

	/* ugm/agenda-only — Agenda Kegiatan mandiri */
	registerBlockType( 'ugm/agenda-only', {
		title:       __( 'Agenda Kegiatan', 'ugm-faculty' ),
		description: __( 'Menampilkan daftar agenda kegiatan. Bisa dipindah terpisah dari Fasilitas Mahasiswa.', 'ugm-faculty' ),
		category:    'ugm-sections',
		icon:        'calendar-alt',
		supports:    { html: false },
		attributes: {
			title:        { type: 'string', default: '' },
			categorySlug: { type: 'string', default: '' },
		},
		edit: makeSectionEdit( 'ugm/agenda-only', __( 'Agenda Kegiatan', 'ugm-faculty' ), 'agenda' ),
		save: function () { return null; },
	} );

	/* ugm/facility-only — Fasilitas Mahasiswa mandiri */
	registerBlockType( 'ugm/facility-only', {
		title:       __( 'Fasilitas Mahasiswa', 'ugm-faculty' ),
		description: __( 'Menampilkan grid fasilitas mahasiswa. Bisa dipindah terpisah dari Agenda Kegiatan.', 'ugm-faculty' ),
		category:    'ugm-sections',
		icon:        'building',
		supports:    { html: false },
		attributes: {
			title:        { type: 'string', default: '' },
			categorySlug: { type: 'string', default: '' },
		},
		edit: makeSectionEdit( 'ugm/facility-only', __( 'Fasilitas Mahasiswa', 'ugm-faculty' ), 'fasilitas-mahasiswa' ),
		save: function () { return null; },
	} );
	/* ugm/faculty-list — Fakultas dan Sekolah (Manual / Static) */
	registerBlockType( 'ugm/faculty-list', {
		title:       __( 'Fakultas dan Sekolah (Manual)', 'ugm-faculty' ),
		description: __( 'Daftar fakultas diinput langsung di editor — gambar, nama, link website.', 'ugm-faculty' ),
		category:    'ugm-sections',
		icon:        'building',
		supports:    { html: false },
		attributes: {
			overline: { type: 'string', default: 'Seputar UGM' },
			title:    { type: 'string', default: '' },
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
}() );
