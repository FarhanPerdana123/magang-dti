/**
 * Faculty slider navigation.
 * Build pages based on viewport:
 * - mobile: 3 cards/page
 * - desktop: 8 cards/page
 */
document.addEventListener( 'DOMContentLoaded', function () {
	const autoSlideDelay = 5000;
	const resumeDelay = 9000;
	const slideDuration = 1500;

	function initFacultySlider( wrapper ) {
		const slider = wrapper.querySelector( '.faculty-slider' );
		const dotsContainer = wrapper.querySelector( '.faculty-pagination' );
		const prevBtn = wrapper.querySelector( '.faculty-nav--prev' );
		const nextBtn = wrapper.querySelector( '.faculty-nav--next' );

		if ( ! slider || ! dotsContainer || ! prevBtn || ! nextBtn ) {
			return;
		}

		const allCards = Array.from( slider.querySelectorAll( '.faculty-card' ) );
		if ( ! allCards.length ) {
			return;
		}

		let currentPage = 0;
		let totalPages = 1;
		let itemsPerPage = 0;
		let scrollTimeout;
		let resizeTimeout;
		let autoSlideTimer;
		let resumeTimer;
		let animationFrame;
		let isPaused = false;

		function getItemsPerPage() {
			return window.matchMedia( '(max-width: 767px)' ).matches ? 3 : 8;
		}

		function getCurrentPageIndex() {
			const pageWidth = slider.clientWidth || 1;
			return Math.round( slider.scrollLeft / pageWidth );
		}

		function setControlsVisibility() {
			const hide = totalPages <= 1;
			prevBtn.style.display = hide ? 'none' : '';
			nextBtn.style.display = hide ? 'none' : '';
			dotsContainer.style.display = hide ? 'none' : '';
		}

		function updateUI() {
			const dots = dotsContainer.querySelectorAll( '.pagination-dot' );
			currentPage = Math.max( 0, Math.min( getCurrentPageIndex(), totalPages - 1 ) );

			dots.forEach( function ( dot, index ) {
				dot.classList.toggle( 'active', index === currentPage );
			} );

			prevBtn.disabled = totalPages <= 1;
			nextBtn.disabled = totalPages <= 1;
		}

		function easeInOutCubic( progress ) {
			return progress < 0.5
				? 4 * progress * progress * progress
				: 1 - Math.pow( -2 * progress + 2, 3 ) / 2;
		}

		function animateToPosition( targetLeft ) {
			const startLeft = slider.scrollLeft;
			const distance = targetLeft - startLeft;
			const startTime = window.performance.now();

			window.cancelAnimationFrame( animationFrame );

			function step( now ) {
				const elapsed = now - startTime;
				const progress = Math.min( elapsed / slideDuration, 1 );

				slider.scrollLeft = startLeft + distance * easeInOutCubic( progress );

				if ( progress < 1 ) {
					animationFrame = window.requestAnimationFrame( step );
					return;
				}

				slider.scrollLeft = targetLeft;
				updateUI();
			}

			animationFrame = window.requestAnimationFrame( step );
		}

		function scrollToPage( pageIndex ) {
			if ( totalPages <= 1 ) {
				return;
			}

			const normalizedPage = ( pageIndex + totalPages ) % totalPages;
			const pageWidth = slider.clientWidth || 1;

			animateToPosition( normalizedPage * pageWidth );
		}

		function stopAutoSlide() {
			window.clearInterval( autoSlideTimer );
			autoSlideTimer = null;
		}

		function startAutoSlide() {
			stopAutoSlide();

			if ( isPaused || totalPages <= 1 ) {
				return;
			}

			autoSlideTimer = window.setInterval( function () {
				scrollToPage( currentPage + 1 );
			}, autoSlideDelay );
		}

		function pauseAutoSlide() {
			isPaused = true;
			stopAutoSlide();
			window.clearTimeout( resumeTimer );
		}

		function resumeAutoSlideSoon() {
			window.clearTimeout( resumeTimer );
			resumeTimer = window.setTimeout( function () {
				isPaused = false;
				startAutoSlide();
			}, resumeDelay );
		}

		function handleManualNavigation( callback ) {
			pauseAutoSlide();
			callback();
			resumeAutoSlideSoon();
		}

		function buildPages( keepCurrent ) {
			window.cancelAnimationFrame( animationFrame );

			const previousPage = keepCurrent ? currentPage : 0;
			itemsPerPage = getItemsPerPage();
			totalPages = Math.max( 1, Math.ceil( allCards.length / itemsPerPage ) );
			currentPage = Math.min( previousPage, totalPages - 1 );

			slider.innerHTML = '';
			dotsContainer.innerHTML = '';

			for ( let i = 0; i < allCards.length; i += itemsPerPage ) {
				const pageIndex = Math.floor( i / itemsPerPage );
				const page = document.createElement( 'div' );
				page.className = 'faculty-page';
				page.setAttribute( 'data-page', String( pageIndex ) );

				allCards.slice( i, i + itemsPerPage ).forEach( function ( card ) {
					page.appendChild( card );
				} );

				slider.appendChild( page );
			}

			for ( let dotIndex = 0; dotIndex < totalPages; dotIndex += 1 ) {
				const dot = document.createElement( 'span' );
				dot.className = 'pagination-dot';
				dot.setAttribute( 'data-page', String( dotIndex ) );
				dotsContainer.appendChild( dot );
			}

			slider.scrollLeft = ( slider.clientWidth || 1 ) * currentPage;
			setControlsVisibility();
			updateUI();
			startAutoSlide();
		}

		prevBtn.addEventListener( 'click', function () {
			handleManualNavigation( function () {
				scrollToPage( currentPage - 1 );
			} );
		} );

		nextBtn.addEventListener( 'click', function () {
			handleManualNavigation( function () {
				scrollToPage( currentPage + 1 );
			} );
		} );

		dotsContainer.addEventListener( 'click', function ( event ) {
			const target = event.target;
			if ( ! ( target instanceof HTMLElement ) ) {
				return;
			}

			if ( ! target.classList.contains( 'pagination-dot' ) ) {
				return;
			}

			const page = Number( target.getAttribute( 'data-page' ) );
			if ( Number.isNaN( page ) ) {
				return;
			}

			handleManualNavigation( function () {
				scrollToPage( page );
			} );
		} );

		slider.addEventListener( 'scroll', function () {
			window.clearTimeout( scrollTimeout );
			scrollTimeout = window.setTimeout( updateUI, 120 );
		} );

		wrapper.addEventListener( 'mouseenter', pauseAutoSlide );
		wrapper.addEventListener( 'mouseleave', function () {
			isPaused = false;
			startAutoSlide();
		} );
		wrapper.addEventListener( 'focusin', pauseAutoSlide );
		wrapper.addEventListener( 'focusout', resumeAutoSlideSoon );

		document.addEventListener( 'visibilitychange', function () {
			if ( document.hidden ) {
				stopAutoSlide();
				return;
			}

			startAutoSlide();
		} );

		window.addEventListener( 'resize', function () {
			window.clearTimeout( resizeTimeout );
			resizeTimeout = window.setTimeout( function () {
				const nextItemsPerPage = getItemsPerPage();
				if ( nextItemsPerPage !== itemsPerPage ) {
					buildPages( true );
					return;
				}

				slider.scrollLeft = ( slider.clientWidth || 1 ) * currentPage;
				updateUI();
			}, 150 );
		} );

		buildPages( false );
	}

	document.querySelectorAll( '.faculty-slider-wrapper' ).forEach( initFacultySlider );
} );
