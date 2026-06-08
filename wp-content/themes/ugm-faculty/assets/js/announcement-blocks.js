/**
 * UGM Announcement Page block editor registration.
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
	var MediaUpload      = wp.blockEditor.MediaUpload;
	var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
	var PanelBody         = wp.components.PanelBody;
	var CheckboxControl   = wp.components.CheckboxControl;
	var TextControl       = wp.components.TextControl;
	var Button            = wp.components.Button;
	var useSelect         = wp.data.useSelect;
	var dispatch          = wp.data.dispatch;

	function getDefaultSocialItems() {
		return [
			{ enabled: true, type: 'facebook', label: 'Facebook', icon: 'F', iconImageId: 0, iconImageUrl: '', url: '', color: '#315c9d' },
			{ enabled: true, type: 'twitter', label: 'Twitter / X', icon: 'T', iconImageId: 0, iconImageUrl: '', url: '', color: '#1da6d8' },
			{ enabled: true, type: 'whatsapp', label: 'WhatsApp', icon: 'W', iconImageId: 0, iconImageUrl: '', url: '', color: '#13b94f' },
		];
	}

	function normalizeSocialItems( items ) {
		if ( ! Array.isArray( items ) || ! items.length ) {
			return getDefaultSocialItems();
		}

		return items.map( function ( item ) {
			item = item || {};
			return {
				enabled: item.enabled !== false,
				type: item.type || '',
				label: item.label || '',
				icon: item.icon || '',
				iconImageId: parseInt( item.iconImageId, 10 ) || 0,
				iconImageUrl: item.iconImageUrl || '',
				url: item.url || '',
				color: item.color || '#083b60',
			};
		} );
	}

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
				)
		);
	}

	function AnnouncementTemplateSeeder() {
		return null;
	}

	registerBlockType( 'ugm/announcement-page', {
		title:       __( 'Daftar Pengumuman', 'ugm-faculty' ),
		description: __( 'Daftar pengumuman utama dengan filter dan slider daftar bawah.', 'ugm-faculty' ),
		category:    'ugm-announcement-page-sections',
		icon:        'megaphone',
		supports:    { html: false, multiple: false },
		attributes: {
			title:        { type: 'string', default: 'Pengumuman' },
			categorySlug: { type: 'string', default: 'pengumuman' },
			socialItems:  { type: 'array', default: getDefaultSocialItems() },
			showFacebook: { type: 'boolean', default: true },
			facebookUrl:  { type: 'string', default: '' },
			showTwitter:  { type: 'boolean', default: true },
			twitterUrl:   { type: 'string', default: '' },
			showWhatsapp: { type: 'boolean', default: true },
			whatsappUrl:  { type: 'string', default: '' },
		},
		edit: function ( props ) {
			var attrs   = props.attributes;
			var setAttr = props.setAttributes;
			var socialItems = normalizeSocialItems( attrs.socialItems );

			function updateSocialItem( index, key, value ) {
				var next = socialItems.map( function ( item ) {
					return {
						enabled: item.enabled,
						type: item.type || '',
						label: item.label,
						icon: item.icon,
						iconImageId: item.iconImageId || 0,
						iconImageUrl: item.iconImageUrl || '',
						url: item.url,
						color: item.color,
					};
				} );

				next[ index ][ key ] = value;
				setAttr( { socialItems: next } );
			}

			function removeSocialItem( index ) {
				setAttr( {
					socialItems: socialItems.filter( function ( item, itemIndex ) {
						return itemIndex !== index;
					} ),
				} );
			}

			function addSocialItem() {
				setAttr( {
					socialItems: socialItems.concat( [
						{ enabled: true, type: '', label: __( 'Media Sosial', 'ugm-faculty' ), icon: 'S', iconImageId: 0, iconImageUrl: '', url: '', color: '#083b60' },
					] ),
				} );
			}

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Pengaturan Halaman Pengumuman', 'ugm-faculty' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Judul Halaman', 'ugm-faculty' ),
							value: attrs.title || '',
							onChange: function ( v ) { setAttr( { title: v } ); },
						} ),
						el( CategoryChecklistControl, {
							label: __( 'Kategori Pengumuman', 'ugm-faculty' ),
							value: attrs.categorySlug || '',
							onChange: function ( value ) { setAttr( { categorySlug: value } ); },
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Kontak / Sosial Media', 'ugm-faculty' ), initialOpen: false },
						socialItems.map( function ( item, index ) {
							return el(
								'div',
								{
									key: index,
									style: {
										borderBottom: '1px solid #ddd',
										marginBottom: '14px',
										paddingBottom: '14px',
									},
								},
								el( CheckboxControl, {
									label: item.label || __( 'Media Sosial', 'ugm-faculty' ),
									checked: item.enabled !== false,
									onChange: function ( checked ) { updateSocialItem( index, 'enabled', checked ); },
								} ),
								el( TextControl, {
									label: __( 'Nama Media', 'ugm-faculty' ),
									value: item.label || '',
									onChange: function ( v ) { updateSocialItem( index, 'label', v ); },
								} ),
								el( TextControl, {
									label: __( 'Fallback Teks Logo', 'ugm-faculty' ),
									help: __( 'Dipakai hanya kalau gambar logo belum dipilih.', 'ugm-faculty' ),
									value: item.icon || '',
									onChange: function ( v ) { updateSocialItem( index, 'icon', v ); },
								} ),
								MediaUpload && MediaUploadCheck && el(
									MediaUploadCheck,
									null,
									el( MediaUpload, {
										allowedTypes: [ 'image' ],
										value: item.iconImageId || 0,
										onSelect: function ( media ) {
											updateSocialItem( index, 'iconImageId', media && media.id ? media.id : 0 );
											updateSocialItem( index, 'iconImageUrl', media && media.url ? media.url : '' );
										},
										render: function ( uploadProps ) {
											return el(
												'div',
												{ style: { marginBottom: '16px' } },
												item.iconImageUrl && el( 'img', {
													src: item.iconImageUrl,
													alt: '',
													style: {
														display: 'block',
														width: '42px',
														height: '42px',
														objectFit: 'contain',
														marginBottom: '8px',
													},
												} ),
												el(
													Button,
													{
														variant: 'secondary',
														onClick: uploadProps.open,
													},
													item.iconImageUrl ? __( 'Ganti Logo', 'ugm-faculty' ) : __( 'Pilih Logo', 'ugm-faculty' )
												),
												item.iconImageUrl && el(
													Button,
													{
														isDestructive: true,
														variant: 'link',
														onClick: function () {
															updateSocialItem( index, 'iconImageId', 0 );
															updateSocialItem( index, 'iconImageUrl', '' );
														},
														style: { marginLeft: '8px' },
													},
													__( 'Hapus Logo', 'ugm-faculty' )
												)
											);
										},
									} )
								),
								el( TextControl, {
									label: __( 'URL / Nomor Kontak', 'ugm-faculty' ),
									help: __( 'Isi URL lengkap, domain, atau nomor WhatsApp.', 'ugm-faculty' ),
									value: item.url || '',
									onChange: function ( v ) { updateSocialItem( index, 'url', v ); },
								} ),
								el( TextControl, {
									label: __( 'Warna Tombol', 'ugm-faculty' ),
									type: 'color',
									value: item.color || '#083b60',
									onChange: function ( v ) { updateSocialItem( index, 'color', v ); },
								} ),
								el(
									Button,
									{
										isDestructive: true,
										variant: 'secondary',
										onClick: function () { removeSocialItem( index ); },
									},
									__( 'Hapus Media', 'ugm-faculty' )
								)
							);
						} ),
						el(
							Button,
							{
								variant: 'primary',
								onClick: addSocialItem,
							},
							__( 'Tambah Media Sosial', 'ugm-faculty' )
						)
					)
				),
				el( ServerSideRender, {
					block: 'ugm/announcement-page',
					attributes: attrs,
					httpMethod: 'POST',
				} )
			);
		},
		save: function () { return null; },
	} );

	registerBlockType( 'ugm/announcement-latest-news', {
		title:       __( 'Pengumuman: Berita Terbaru', 'ugm-faculty' ),
		description: __( 'Block sidebar berita terbaru untuk halaman pengumuman.', 'ugm-faculty' ),
		category:    'ugm-announcement-page-sections',
		icon:        'list-view',
		supports:    { html: false },
		attributes: {
			title:        { type: 'string', default: 'Berita Terbaru' },
			postsPerPage: { type: 'number', default: 5 },
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
						{ title: __( 'Pengaturan Berita Terbaru', 'ugm-faculty' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Judul Block', 'ugm-faculty' ),
							value: attrs.title || '',
							onChange: function ( v ) { setAttr( { title: v } ); },
						} ),
						el( TextControl, {
							label: __( 'Jumlah berita', 'ugm-faculty' ),
							type: 'number',
							value: attrs.postsPerPage || 5,
							onChange: function ( v ) {
								setAttr( { postsPerPage: parseInt( v, 10 ) || 5 } );
							},
						} )
					)
				),
				el( ServerSideRender, {
					block: 'ugm/announcement-latest-news',
					attributes: attrs,
					httpMethod: 'POST',
				} )
			);
		},
		save: function () { return null; },
	} );

	registerBlockType( 'ugm/announcement-latest-agenda', {
		title:       __( 'Pengumuman: Agenda Terbaru', 'ugm-faculty' ),
		description: __( 'Block sidebar agenda terbaru untuk halaman pengumuman.', 'ugm-faculty' ),
		category:    'ugm-announcement-page-sections',
		icon:        'calendar-alt',
		supports:    { html: false },
		attributes: {
			title:        { type: 'string', default: 'Agenda Terbaru' },
			categorySlug: { type: 'string', default: 'agenda' },
			postsPerPage: { type: 'number', default: 3 },
			buttonLabel:  { type: 'string', default: 'Semua Agenda' },
			buttonUrl:    { type: 'string', default: '/agenda/' },
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
						{ title: __( 'Pengaturan Agenda Terbaru', 'ugm-faculty' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Judul Block', 'ugm-faculty' ),
							value: attrs.title || '',
							onChange: function ( v ) { setAttr( { title: v } ); },
						} ),
						el( CategoryChecklistControl, {
							label: __( 'Kategori Agenda', 'ugm-faculty' ),
							value: attrs.categorySlug || '',
							onChange: function ( value ) { setAttr( { categorySlug: value } ); },
						} ),
						el( TextControl, {
							label: __( 'Jumlah agenda', 'ugm-faculty' ),
							type: 'number',
							min: 1,
							max: 10,
							value: attrs.postsPerPage || 3,
							onChange: function ( v ) {
								setAttr( { postsPerPage: parseInt( v, 10 ) || 3 } );
							},
						} ),
						el( TextControl, {
							label: __( 'Label Tombol', 'ugm-faculty' ),
							value: attrs.buttonLabel || '',
							onChange: function ( v ) { setAttr( { buttonLabel: v } ); },
						} ),
						el( TextControl, {
							label: __( 'URL Tombol', 'ugm-faculty' ),
							value: attrs.buttonUrl || '',
							onChange: function ( v ) { setAttr( { buttonUrl: v } ); },
						} )
					)
				),
				el( ServerSideRender, {
					block: 'ugm/announcement-latest-agenda',
					attributes: attrs,
					httpMethod: 'POST',
				} )
			);
		},
		save: function () { return null; },
	} );

	if ( wp.plugins && wp.plugins.registerPlugin ) {
		// Do not auto-insert default blocks after users intentionally clear the page.
	}
}() );
