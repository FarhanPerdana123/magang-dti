/**
 * Local slider for the lower announcement list.
 *
 * @package ugm-faculty
 */
( function () {
	'use strict';

	var visibleCount = 3;
	var animationClass = 'is-announcement-sliding';

	function updateSlider( slider, animate ) {
		var track = slider.querySelector( '[data-ugm-announcement-track]' );
		var prev = slider.querySelector( '[data-ugm-announcement-prev]' );
		var next = slider.querySelector( '[data-ugm-announcement-next]' );
		var items = track ? Array.from( track.querySelectorAll( '.ugm-announcement-list-item' ) ) : [];
		var maxIndex = Math.max( 0, items.length - visibleCount );
		var activeIndex = Math.min( Number( slider.dataset.announcementIndex || 0 ), maxIndex );

		slider.dataset.announcementIndex = String( activeIndex );

		items.forEach( function ( item, index ) {
			item.classList.toggle(
				'is-announcement-slide-hidden',
				index < activeIndex || index >= activeIndex + visibleCount
			);
		} );

		if ( prev ) {
			prev.disabled = activeIndex <= 0;
		}

		if ( next ) {
			next.disabled = activeIndex >= maxIndex;
		}

		if ( animate && track ) {
			track.classList.remove( animationClass );
			void track.offsetWidth;
			track.classList.add( animationClass );
		}
	}

	function bindSlider( slider ) {
		var prev = slider.querySelector( '[data-ugm-announcement-prev]' );
		var next = slider.querySelector( '[data-ugm-announcement-next]' );

		if ( slider.dataset.announcementSliderBound ) {
			updateSlider( slider );
			return;
		}

		slider.dataset.announcementSliderBound = 'true';

		if ( prev ) {
			prev.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				event.stopPropagation();
				slider.dataset.announcementIndex = String( Math.max( 0, Number( slider.dataset.announcementIndex || 0 ) - 1 ) );
				updateSlider( slider, true );
			} );
		}

		if ( next ) {
			next.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				event.stopPropagation();
				slider.dataset.announcementIndex = String( Number( slider.dataset.announcementIndex || 0 ) + 1 );
				updateSlider( slider, true );
			} );
		}

		updateSlider( slider );
	}

	function initAnnouncementSliders() {
		document.querySelectorAll( '[data-ugm-announcement-slider]' ).forEach( bindSlider );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initAnnouncementSliders );
	} else {
		initAnnouncementSliders();
	}

	if ( 'MutationObserver' in window ) {
		new MutationObserver( initAnnouncementSliders ).observe( document.documentElement, {
			childList: true,
			subtree: true,
		} );
	}
}() );
