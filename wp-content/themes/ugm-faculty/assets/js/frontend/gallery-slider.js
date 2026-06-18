/**
 * Gallery hero drag and swipe navigation.
 *
 * @package ugm-faculty
 */
( function () {
	'use strict';

	document.querySelectorAll( '[data-gallery-detail]' ).forEach( function ( detail ) {
		var images = Array.from( detail.querySelectorAll( '[data-gallery-detail-image]' ) );
		var thumbs = Array.from( detail.querySelectorAll( '[data-gallery-detail-thumb]' ) );
		var previous = detail.querySelector( '[data-gallery-detail-prev]' );
		var next = detail.querySelector( '[data-gallery-detail-next]' );
		var activeIndex = 0;

		function showImage( index ) {
			activeIndex = ( index + images.length ) % images.length;
			images.forEach( function ( image, imageIndex ) {
				image.classList.toggle( 'is-active', imageIndex === activeIndex );
			} );
			thumbs.forEach( function ( thumb, thumbIndex ) {
				thumb.classList.toggle( 'is-active', thumbIndex === activeIndex );
			} );
		}

		if ( ! images.length ) {
			return;
		}

		thumbs.forEach( function ( thumb, index ) {
			thumb.addEventListener( 'click', function () {
				showImage( index );
			} );
		} );

		if ( previous ) {
			previous.addEventListener( 'click', function () {
				showImage( activeIndex - 1 );
			} );
		}

		if ( next ) {
			next.addEventListener( 'click', function () {
				showImage( activeIndex + 1 );
			} );
		}
	} );

	document.querySelectorAll( '[data-gallery-slider]' ).forEach( function ( slider ) {
		var track = slider.querySelector( '.ugm-gallery-hero__media-track' );
		var pages = Array.from( slider.querySelectorAll( '[data-gallery-page]' ) );
		var hero = slider.closest( '.ugm-gallery-hero' );
		var dots = hero ? Array.from( hero.querySelectorAll( '[data-gallery-slide]' ) ) : [];
		var copies = hero ? Array.from( hero.querySelectorAll( '[data-gallery-copy]' ) ) : [];
		var activePage = 0;
		var startX = 0;
		var currentX = 0;
		var dragging = false;
		var autoplayTimer = null;
		var autoplayDelay = 5000;

		if ( ! track || pages.length < 2 ) {
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
			activePage = Math.max( 0, Math.min( pages.length - 1, index ) );
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
				goTo( ( activePage + 1 ) % pages.length );
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
			var offset = ( currentX - startX ) - ( activePage * pageWidth() );
			track.style.transform = 'translate3d(' + offset + 'px, 0, 0)';
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
		slider.addEventListener( 'mouseenter', stopAutoplay );
		slider.addEventListener( 'mouseleave', function () {
			if ( ! dragging ) {
				startAutoplay();
			}
		} );
		render( false );
		startAutoplay();
	} );
}() );
