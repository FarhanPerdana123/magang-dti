( function () {
	'use strict';

	function revealElements( elements ) {
		elements.forEach( function ( element ) {
			element.classList.add( 'is-visible' );
		} );
	}

	function isExcluded( element ) {
		return !! element.closest(
			[
				'.site-header',
				'.primary-navigation',
				'.menu-backdrop',
				'.hero',
				'.ugm-home',
				'.ugm-gallery-page',
				'.ugm-gallery-card',
				'.faculty-slider',
				'.faculty-nav',
				'.faculty-pagination'
			].join( ',' )
		);
	}

	function initScrollReveal() {
		document.documentElement.classList.add( 'js' );

		var autoRevealSelectors = [
			'.site-main .page-header',
			'.site-main .entry-header',
			'.site-main .entry-content > *',
			'.site-main .wp-block-group',
			'.site-main .wp-block-columns',
			'.site-main .wp-block-column',
			'.site-main article',
			'.site-main .card',
			'.site-main .portal-card',
			'.site-main .portal-list-card',
			'.site-main .news-grid-card',
			'.site-main .desktop-agenda-card',
			'.site-main .desktop-facility-card',
			'.site-main .majalah-card',
			'.site-main .video-featured',
			'.site-main .video-list-card',
			'.site-main .template-link-card',
			'.site-main .ugm-agenda-card',
			'.site-main .ugm-announcement-featured',
			'.site-main .ugm-announcement-compact',
			'.site-main .ugm-announcement-list-item',
			'.site-main .ugm-announcement-side-news',
			'.site-main .ugm-announcement-side-agenda',
			'.site-main .ugm-rector-greeting',
			'.site-main .ugm-about-sidebar',
			'.site-footer > *'
		];

		autoRevealSelectors.forEach( function ( selector ) {
			document.querySelectorAll( selector ).forEach( function ( node ) {
				if ( isExcluded( node ) || node.classList.contains( 'ugm-scroll-reveal' ) ) {
					return;
				}
				node.classList.add( 'ugm-scroll-reveal' );
			} );
		} );

		var elements = Array.prototype.slice.call( document.querySelectorAll( '.ugm-scroll-reveal' ) ).filter( function ( element ) {
			return ! isExcluded( element );
		} );
		if ( ! elements.length ) {
			return;
		}

		elements.forEach( function ( element, index ) {
			if ( element.style.getPropertyValue( '--ugm-reveal-delay' ) ) {
				return;
			}
			element.style.setProperty( '--ugm-reveal-delay', Math.min( index % 6, 5 ) * 45 + 'ms' );
		} );

		var prefersReducedMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		if ( prefersReducedMotion || !( 'IntersectionObserver' in window ) ) {
			revealElements( elements );
			return;
		}

		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						entry.target.classList.add( 'is-visible' );
					} else {
						entry.target.classList.remove( 'is-visible' );
					}
				} );
			},
			{
				root: null,
				rootMargin: '0px 0px -12% 0px',
				threshold: 0.12
			}
		);

		elements.forEach( function ( element ) {
			observer.observe( element );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initScrollReveal );
	} else {
		initScrollReveal();
	}
}() );
