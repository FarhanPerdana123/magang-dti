/**
 * Guard video media areas from accidental card/link navigation.
 *
 * @package ugm-faculty
 */
( function () {
	'use strict';

	var videoAreaSelector = [
		'.section-video .video-featured__media',
		'.section-video .video-list-card__media',
		'.ugm-article.category-video .wp-block-embed',
		'.ugm-article.category-video .wp-block-embed-youtube',
		'.ugm-article.category-video .is-provider-youtube',
		'.ugm-article.category-video .wp-block-embed__wrapper',
		'.ugm-article.category-video iframe[src*="youtube.com"]',
		'.ugm-article.category-video iframe[src*="youtube-nocookie.com"]',
		'.ugm-article.category-video iframe[src*="youtu.be"]',
	].join( ', ' );

	function buildYouTubeIframe( videoId, title ) {
		var iframe = document.createElement( 'iframe' );

		iframe.src = 'https://www.youtube.com/embed/' + encodeURIComponent( videoId ) + '?autoplay=1&rel=0';
		iframe.title = title || 'YouTube video player';
		iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
		iframe.allowFullscreen = true;
		iframe.setAttribute( 'loading', 'lazy' );

		return iframe;
	}

	function guardVideoAreaClick( event ) {
		var playButton = event.target.closest( '.ugm-video-play' );
		var videoArea = event.target.closest( videoAreaSelector );

		if ( ! videoArea || playButton ) {
			return;
		}

		event.preventDefault();
		event.stopPropagation();
	}

	function initCustomVideoPlayers() {
		document.querySelectorAll( '[data-ugm-video-player][data-youtube-id]' ).forEach( function ( player ) {
			var button = player.querySelector( '.ugm-video-play' );

			if ( ! button || button.dataset.ugmVideoBound ) {
				return;
			}

			button.dataset.ugmVideoBound = 'true';
			button.addEventListener( 'click', function ( event ) {
				var videoId = player.getAttribute( 'data-youtube-id' );

				event.preventDefault();
				event.stopPropagation();

				if ( ! videoId ) {
					return;
				}

				player.replaceChildren( buildYouTubeIframe( videoId, button.getAttribute( 'aria-label' ) ) );
			} );
		} );
	}

	function initVideoGuard() {
		document.addEventListener( 'click', guardVideoAreaClick, true );
		initCustomVideoPlayers();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initVideoGuard );
	} else {
		initVideoGuard();
	}
}() );
