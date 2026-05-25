/**
 * Landing Page Editor Plugin
 *
 * Adds a "Template: Landing Page" panel in the Gutenberg Page sidebar.
 * When clicked, opens a full-screen overlay that renders the live template
 * inside an iframe so admins can visually see and check the landing page
 * without leaving the editor.
 *
 * @package ugm-faculty
 */
( function () {
	'use strict';

	/* Guard: ensure all required WP globals are present. */
	if (
		! window.wp ||
		! wp.plugins ||
		! wp.editPost ||
		! wp.element ||
		! wp.data ||
		! wp.components ||
		! wp.blocks
	) {
		return;
	}

	var el           = wp.element.createElement;
	var useState     = wp.element.useState;
	var useEffect    = wp.element.useEffect;
	var Fragment     = wp.element.Fragment;
	var useSelect    = wp.data.useSelect;
	var dispatch     = wp.data.dispatch;
	var registerPlugin              = wp.plugins.registerPlugin;
	var PluginDocumentSettingPanel  = wp.editPost.PluginDocumentSettingPanel;
	var Button                      = wp.components.Button;

	var LANDING_TEMPLATES = [ 'page-templates/template-landing-page.php', 'landing-page' ];

	function isLandingTemplate( template ) {
		return LANDING_TEMPLATES.indexOf( template ) !== -1;
	}

	/* ------------------------------------------------------------------
	 * Vanilla-DOM preview overlay
	 * Using direct DOM manipulation (not a React tree) keeps the overlay
	 * independent of React's lifecycle and avoids teardown issues.
	 * ------------------------------------------------------------------ */

	function openPreview( url ) {
		if ( document.getElementById( 'ugm-preview-overlay' ) ) {
			return; // Already open.
		}

		if ( ! url ) {
			alert( 'Simpan atau publish halaman terlebih dahulu agar URL tersedia.' );
			return;
		}

		var overlay = document.createElement( 'div' );
		overlay.id  = 'ugm-preview-overlay';
		overlay.setAttribute( 'role', 'dialog' );
		overlay.setAttribute( 'aria-label', 'Preview Template Landing Page' );

		overlay.innerHTML =
			'<div class="ugm-preview-bar">' +
			'  <span class="ugm-preview-bar__title">👁 Preview — Template Landing Page</span>' +
			'  <div class="ugm-preview-bar__actions">' +
			'    <a class="ugm-preview-bar__link" href="' + url + '" target="_blank" rel="noopener noreferrer" title="Buka di tab baru">↗ Buka di Tab Baru</a>' +
			'    <button class="ugm-preview-bar__close" id="ugm-preview-close" aria-label="Tutup preview">✕ Tutup Preview</button>' +
			'  </div>' +
			'</div>' +
			'<div class="ugm-preview-loader" id="ugm-preview-loader">Memuat template…</div>' +
			'<iframe' +
			'  class="ugm-preview-frame"' +
			'  id="ugm-preview-frame"' +
			'  src="' + url + '"' +
			'  title="Preview Template Landing Page"' +
			'></iframe>';

		document.body.appendChild( overlay );

		document.getElementById( 'ugm-preview-close' )
			.addEventListener( 'click', closePreview );

		document.getElementById( 'ugm-preview-frame' )
			.addEventListener( 'load', function () {
				setTimeout( function () {
					var loader = document.getElementById( 'ugm-preview-loader' );
					if ( loader ) loader.style.display = 'none';
				}, 300 );
			} );

		/* Trap Escape key to close. */
		overlay._keyHandler = function ( e ) {
			if ( e.key === 'Escape' ) closePreview();
		};
		document.addEventListener( 'keydown', overlay._keyHandler );
	}

	function closePreview() {
		var overlay = document.getElementById( 'ugm-preview-overlay' );
		if ( ! overlay ) return;
		if ( overlay._keyHandler ) {
			document.removeEventListener( 'keydown', overlay._keyHandler );
		}
		overlay.remove();
	}

	/* ------------------------------------------------------------------
	 * Gutenberg Plugin Component
	 * ------------------------------------------------------------------ */

	function LandingPagePlugin() {
		var template = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'template' );
		} );

		var postLink = useSelect( function ( select ) {
			var post = select( 'core/editor' ).getCurrentPost();
			return ( post && post.link ) ? post.link : '';
		} );

		var blockCount = useSelect( function ( select ) {
			return select( 'core/block-editor' ).getBlockCount();
		} );

		useEffect( function () {
			if ( ! isLandingTemplate( template ) || blockCount > 0 ) {
				return;
			}

			if ( ! window.ugmLandingPageEditor || ! ugmLandingPageEditor.defaultBlocks ) {
				return;
			}

			var blocks = wp.blocks.parse( ugmLandingPageEditor.defaultBlocks );
			if ( blocks && blocks.length ) {
				dispatch( 'core/block-editor' ).insertBlocks( blocks );
			}
		}, [ template, blockCount ] );

		/* Close the overlay when the user switches away from Landing template. */
		useEffect( function () {
			if ( ! isLandingTemplate( template ) ) {
				closePreview();
			}
		}, [ template ] );

		/* Remove overlay when component unmounts (navigating away). */
		useEffect( function () {
			return function () { closePreview(); };
		}, [] );

		if ( ! isLandingTemplate( template ) ) {
			return null;
		}

		return el(
			Fragment,
			null,

			/* ------ Sidebar panel ------ */
			el(
				PluginDocumentSettingPanel,
				{
					name:      'ugm-landing-panel',
					title:     '🏠 Template: Landing Page',
					className: 'ugm-landing-setting-panel',
				},

				el( 'p', { className: 'ugm-landing-panel__desc' },
					'Tampilan halaman ini dikendalikan sepenuhnya oleh template PHP. ' +
					'Edit judul dan slug kategori melalui pengaturan block UGM di sidebar.'
				),

				el(
					Button,
					{
						variant:   'primary',
						className: 'ugm-landing-panel__preview-btn',
						onClick:   function () { openPreview( postLink ); },
						disabled:  ! postLink,
						title:     postLink
							? 'Buka full-screen preview template'
							: 'Publish atau simpan draf halaman terlebih dahulu',
					},
					'👁 Preview Template'
				),

				postLink
					? el(
						'a',
						{
							href:     postLink,
							target:   '_blank',
							rel:      'noopener noreferrer',
							className: 'ugm-landing-panel__ext-link',
						},
						'↗ Buka di tab baru'
					)
					: el( 'p', { className: 'ugm-landing-panel__no-link' },
						'Simpan halaman untuk mengaktifkan preview.'
					)
			)
		);
	}

	registerPlugin( 'ugm-landing-page-editor', {
		render: LandingPagePlugin,
	} );
}() );
