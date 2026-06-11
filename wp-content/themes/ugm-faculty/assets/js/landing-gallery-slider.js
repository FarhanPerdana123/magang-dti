/**
 * Landing page gallery slider.
 *
 * @package ugm-faculty
 */
( function () {
	'use strict';

	document.querySelectorAll( '[data-landing-gallery]' ).forEach( function ( gallery ) {
		var slider = gallery.querySelector( '[data-landing-gallery-slider]' );
		var track = gallery.querySelector( '.section-gallery__media-track' );
		var pages = Array.from( gallery.querySelectorAll( '[data-landing-gallery-page]' ) );
		var dots = Array.from( gallery.querySelectorAll( '[data-landing-gallery-slide]' ) );
		var copies = Array.from( gallery.querySelectorAll( '[data-landing-gallery-copy]' ) );
		var activePage = 0;
		var startX = 0;
		var currentX = 0;
		var dragging = false;
		var autoplayTimer = null;
		var autoplayDelay = parseInt( gallery.getAttribute( 'data-autoplay-delay' ), 10 ) || 3000;

		if ( ! slider || ! track ) {
			return;
		}

		if ( pages.length < 2 ) {
			return;
		}

		function pageWidth() {
			return slider.clientWidth || 1;
		}

		function render( animate ) {
			track.style.transition = animate ? '' : 'none';
			track.style.transform = 'translate3d(' + ( activePage * -100 ) + '%, 0, 0)';

			dots.forEach( function ( dot, index ) {
				var isActive = index === activePage;
				dot.classList.toggle( 'is-active', isActive );
				if ( isActive ) {
					dot.setAttribute( 'aria-current', 'true' );
				} else {
					dot.removeAttribute( 'aria-current' );
				}
			} );

			copies.forEach( function ( copy, index ) {
				copy.classList.toggle( 'is-active', index === activePage );
			} );
		}

		function goTo( index ) {
			activePage = ( index + pages.length ) % pages.length;
			render( true );
		}

		function stopAutoplay() {
			if ( autoplayTimer ) {
				window.clearInterval( autoplayTimer );
				autoplayTimer = null;
			}
		}

		function startAutoplay() {
			stopAutoplay();
			autoplayTimer = window.setInterval( function () {
				goTo( activePage + 1 );
			}, autoplayDelay );
		}

		function restartAutoplay() {
			stopAutoplay();
			startAutoplay();
		}

		dots.forEach( function ( dot, index ) {
			dot.addEventListener( 'click', function () {
				goTo( index );
				restartAutoplay();
			} );
		} );

		slider.addEventListener( 'pointerdown', function ( event ) {
			stopAutoplay();
			dragging = true;
			startX = event.clientX;
			currentX = startX;
			slider.classList.add( 'is-dragging' );
			slider.setPointerCapture( event.pointerId );
		} );

		slider.addEventListener( 'pointermove', function ( event ) {
			if ( ! dragging ) {
				return;
			}

			currentX = event.clientX;
			track.style.transform = 'translate3d(' + ( ( currentX - startX ) - ( activePage * pageWidth() ) ) + 'px, 0, 0)';
		} );

		function finishDrag() {
			if ( ! dragging ) {
				return;
			}

			var distance = currentX - startX;
			dragging = false;
			slider.classList.remove( 'is-dragging' );

			if ( Math.abs( distance ) > pageWidth() * 0.12 ) {
				goTo( activePage + ( distance < 0 ? 1 : -1 ) );
			} else {
				render( true );
			}

			startAutoplay();
		}

		slider.addEventListener( 'pointerup', finishDrag );
		slider.addEventListener( 'pointercancel', finishDrag );
		gallery.addEventListener( 'mouseenter', stopAutoplay );
		gallery.addEventListener( 'mouseleave', function () {
			if ( ! dragging ) {
				startAutoplay();
			}
		} );

		render( false );
		startAutoplay();
	} );
}() );
