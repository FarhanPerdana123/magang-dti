( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.data || ! wp.domReady || ! wp.i18n ) {
		return;
	}

	var __           = wp.i18n.__;
	var dispatch     = wp.data.dispatch;
	var noticeStore  = 'core/notices';
	var NOTICE_ID    = 'ugm-template-editor-content-notice';
	var TEMPLATE_IDS = {
		page: true,
		'landing-page': true,
	};

	function getCurrentTemplateSlug() {
		var params    = new URLSearchParams( window.location.search || '' );
		var postType  = params.get( 'postType' );
		var postId    = params.get( 'postId' );
		var decodedId = '';
		var parts;

		if ( 'wp_template' !== postType || ! postId ) {
			return '';
		}

		try {
			decodedId = decodeURIComponent( postId );
		} catch ( err ) {
			decodedId = postId;
		}

		parts = decodedId.split( '//' );

		return parts.length > 1 ? parts[1] : '';
	}

	function maybeShowTemplateNotice() {
		var templateSlug = getCurrentTemplateSlug();

		if ( ! TEMPLATE_IDS[ templateSlug ] ) {
			return;
		}

		dispatch( noticeStore ).removeNotice( NOTICE_ID );
		dispatch( noticeStore ).createNotice(
			'info',
			__(
				'Anda sedang mengedit template. Area ini hanya mengatur kerangka halaman. Konten section landing page diedit dari Page Editor, bukan dari Template Editor.',
				'ugm-faculty'
			),
			{
				id: NOTICE_ID,
				isDismissible: false,
				type: 'snackbar',
			}
		);
	}

	wp.domReady( function () {
		maybeShowTemplateNotice();
	} );
}( window.wp ) );
