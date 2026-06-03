/**
 * UGM Agenda Page block editor registration.
 *
 * Kept separate from the landing-page block definitions so the Agenda Page
 * template can be maintained independently.
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
	var PluginDocumentSettingPanel = wp.editPost && wp.editPost.PluginDocumentSettingPanel;
	var PanelBody         = wp.components.PanelBody;
	var CheckboxControl   = wp.components.CheckboxControl;
	var TextControl       = wp.components.TextControl;
	var useSelect         = wp.data.useSelect;
	var dispatch          = wp.data.dispatch;

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

	function AgendaTemplateSeeder() {
		var template = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'template' );
		} );

		var blockCount = useSelect( function ( select ) {
			return select( 'core/block-editor' ).getBlockCount();
		} );

		useEffect( function () {
			if ( template !== 'agenda-page' && template !== 'page-templates/template-agenda.php' ) {
				return;
			}

			if ( blockCount > 0 || ! window.ugmAgendaPageEditor || ! ugmAgendaPageEditor.defaultBlocks ) {
				return;
			}

			var blocks = wp.blocks.parse( ugmAgendaPageEditor.defaultBlocks );
			if ( blocks && blocks.length ) {
				dispatch( 'core/block-editor' ).insertBlocks( blocks );
			}
		}, [ template, blockCount ] );

		return null;
	}

	function AgendaPostMetaPanel() {
		var postType = useSelect( function ( select ) {
			return select( 'core/editor' ).getCurrentPostType();
		}, [] );

		var template = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'template' ) || '';
		}, [] );

		var meta = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
		}, [] );

		if (
			! PluginDocumentSettingPanel ||
			postType !== 'post' ||
			[ 'single-berita', 'single-berita.php' ].indexOf( template ) !== -1
		) {
			return null;
		}

		function updateMeta( key, value ) {
			var nextMeta = {};
			Object.keys( meta ).forEach( function ( metaKey ) {
				nextMeta[ metaKey ] = meta[ metaKey ];
			} );
			nextMeta[ key ] = value;

			dispatch( 'core/editor' ).editPost( { meta: nextMeta } );
		}

		return el(
			PluginDocumentSettingPanel,
			{
				name: 'ugm-agenda-post-meta',
				title: __( 'Detail Agenda', 'ugm-faculty' ),
				className: 'ugm-agenda-post-meta-panel',
			},
			el( TextControl, {
				label: __( 'Tanggal Mulai', 'ugm-faculty' ),
				type: 'date',
				value: meta.agenda_event_date || '',
				onChange: function ( value ) { updateMeta( 'agenda_event_date', value ); },
			} ),
			el( TextControl, {
				label: __( 'Jam Mulai', 'ugm-faculty' ),
				type: 'time',
				value: meta.agenda_event_time || '',
				onChange: function ( value ) { updateMeta( 'agenda_event_time', value ); },
			} ),
			el( TextControl, {
				label: __( 'Tanggal Selesai', 'ugm-faculty' ),
				type: 'date',
				value: meta.agenda_event_end_date || '',
				onChange: function ( value ) { updateMeta( 'agenda_event_end_date', value ); },
			} ),
			el( TextControl, {
				label: __( 'Lokasi', 'ugm-faculty' ),
				value: meta.agenda_location || '',
				onChange: function ( value ) { updateMeta( 'agenda_location', value ); },
			} ),
			el( TextControl, {
				label: __( 'Jenis Acara', 'ugm-faculty' ),
				help: __( 'Contoh: Workshop, Webinar, Kuliah Umum, Pelatihan.', 'ugm-faculty' ),
				value: meta.agenda_event_type || '',
				onChange: function ( value ) { updateMeta( 'agenda_event_type', value ); },
			} )
		);
	}

	function BeritaDetailPostMetaPanel() {
		var postType = useSelect( function ( select ) {
			return select( 'core/editor' ).getCurrentPostType();
		}, [] );

		var template = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'template' ) || '';
		}, [] );

		var meta = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
		}, [] );

		if (
			! PluginDocumentSettingPanel ||
			postType !== 'post' ||
			[ 'single-berita', 'single-berita.php' ].indexOf( template ) === -1
		) {
			return null;
		}

		function updateMeta( key, value ) {
			var nextMeta = {};
			Object.keys( meta ).forEach( function ( metaKey ) {
				nextMeta[ metaKey ] = meta[ metaKey ];
			} );
			nextMeta[ key ] = value;

			dispatch( 'core/editor' ).editPost( { meta: nextMeta } );
		}

		return el(
			PluginDocumentSettingPanel,
			{
				name: 'ugm-berita-detail-meta',
				title: __( 'Kredit Berita', 'ugm-faculty' ),
				className: 'ugm-berita-detail-meta-panel',
			},
			el( TextControl, {
				label: __( 'Penulis', 'ugm-faculty' ),
				value: meta.ugm_penulis || '',
				onChange: function ( value ) { updateMeta( 'ugm_penulis', value ); },
			} ),
			el( TextControl, {
				label: __( 'Editor', 'ugm-faculty' ),
				value: meta.ugm_editor || '',
				onChange: function ( value ) { updateMeta( 'ugm_editor', value ); },
			} ),
			el( TextControl, {
				label: __( 'Foto', 'ugm-faculty' ),
				help: __( 'Isi nama fotografer atau sumber foto.', 'ugm-faculty' ),
				value: meta.ugm_foto || '',
				onChange: function ( value ) { updateMeta( 'ugm_foto', value ); },
			} ),
			el( TextControl, {
				label: __( 'Link Facebook', 'ugm-faculty' ),
				type: 'url',
				help: __( 'Kosongkan untuk memakai link share otomatis.', 'ugm-faculty' ),
				value: meta.ugm_facebook_url || '',
				onChange: function ( value ) { updateMeta( 'ugm_facebook_url', value ); },
			} ),
			el( TextControl, {
				label: __( 'Link Twitter/X', 'ugm-faculty' ),
				type: 'url',
				help: __( 'Kosongkan untuk memakai link share otomatis.', 'ugm-faculty' ),
				value: meta.ugm_twitter_url || '',
				onChange: function ( value ) { updateMeta( 'ugm_twitter_url', value ); },
			} ),
			el( TextControl, {
				label: __( 'Link WhatsApp', 'ugm-faculty' ),
				type: 'url',
				help: __( 'Kosongkan untuk memakai link share otomatis.', 'ugm-faculty' ),
				value: meta.ugm_whatsapp_url || '',
				onChange: function ( value ) { updateMeta( 'ugm_whatsapp_url', value ); },
			} )
		);
	}

	registerBlockType( 'ugm/agenda-list-page', {
		title:       __( 'Daftar Agenda Lengkap', 'ugm-faculty' ),
		description: __( 'Halaman daftar agenda lengkap dengan filter, kartu agenda, dan pagination.', 'ugm-faculty' ),
		category:    'ugm-agenda-page-sections',
		icon:        'calendar',
		supports:    { html: false, multiple: false },
		attributes: {
			title:        { type: 'string', default: 'Agenda' },
			categorySlug: { type: 'string', default: 'agenda' },
			postsPerPage: { type: 'number', default: 12 },
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
						{ title: __( 'Pengaturan Halaman Agenda', 'ugm-faculty' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Judul Halaman', 'ugm-faculty' ),
							value: attrs.title || '',
							onChange: function ( v ) { setAttr( { title: v } ); },
						} ),
						el( CategoryChecklistControl, {
							label: __( 'Kategori Agenda', 'ugm-faculty' ),
							value: attrs.categorySlug || '',
							onChange: function ( value ) { setAttr( { categorySlug: value } ); },
						} ),
						el( TextControl, {
							label: __( 'Jumlah per halaman', 'ugm-faculty' ),
							type: 'number',
							value: attrs.postsPerPage || 12,
							onChange: function ( v ) {
								setAttr( { postsPerPage: parseInt( v, 10 ) || 12 } );
							},
						} )
					)
				),
				el( ServerSideRender, {
					block: 'ugm/agenda-list-page',
					attributes: attrs,
					httpMethod: 'POST',
				} )
			);
		},
		save: function () { return null; },
	} );

	if ( wp.plugins && wp.plugins.registerPlugin ) {
		wp.plugins.registerPlugin( 'ugm-agenda-page-seeder', {
			render: AgendaTemplateSeeder,
		} );

		wp.plugins.registerPlugin( 'ugm-agenda-post-meta', {
			render: function () {
				return el(
					Fragment,
					null,
					el( AgendaPostMetaPanel ),
					el( BeritaDetailPostMetaPanel )
				);
			},
		} );
	}
}() );
