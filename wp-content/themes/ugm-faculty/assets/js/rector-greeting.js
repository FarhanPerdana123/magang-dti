( function () {
	'use strict';

	document.querySelectorAll( '.ugm-about-sidebar' ).forEach( function ( sidebar ) {
		var toggle = sidebar.querySelector( '.ugm-about-sidebar__mobile-toggle' );

		if ( ! toggle ) {
			return;
		}

		toggle.addEventListener( 'click', function () {
			var isOpen = sidebar.classList.toggle( 'is-mobile-open' );
			toggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
		} );
	} );
}() );
