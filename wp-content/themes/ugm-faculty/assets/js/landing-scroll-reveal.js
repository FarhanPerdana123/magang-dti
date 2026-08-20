/**
 * Lightweight scroll reveal for landing page sections.
 *
 * @package ugm-faculty
 */
( function () {
	'use strict';

	function initLandingReveal() {
		var root = document.querySelector( '.ugm-home' );
		if ( ! root || window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			return;
		}

		var revealItems = root.querySelectorAll(
			'.home-section, .news-featured, .portal-card, .portal-list-card, .news-grid-card, .featured-category-card, .faculty-card, .desktop-agenda-card, .desktop-facility-card, .majalah-card, .video-featured, .video-list-card, .template-link-card'
		);

		if ( ! revealItems.length ) {
			return;
		}

		revealItems.forEach( function ( item, index ) {
			item.classList.add( 'ugm-scroll-reveal' );
			item.style.setProperty( '--ugm-reveal-delay', Math.min( index % 6, 5 ) * 45 + 'ms' );
		} );

		if ( ! ( 'IntersectionObserver' in window ) ) {
			revealItems.forEach( function ( item ) {
				item.classList.add( 'is-visible' );
			} );
			return;
		}

		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						entry.target.classList.add( 'is-visible' );
						return;
					}

					entry.target.classList.remove( 'is-visible' );
				} );
			},
			{
				root: null,
				rootMargin: '0px 0px -12% 0px',
				threshold: 0.12,
			}
		);

		revealItems.forEach( function ( item ) {
			observer.observe( item );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initLandingReveal );
	} else {
		initLandingReveal();
	}
}() );
