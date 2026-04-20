/**
 * Faculty slider navigation.
 * Build pages based on viewport:
 * - mobile: 3 cards/page
 * - desktop: 8 cards/page
 */
document.addEventListener('DOMContentLoaded', function () {
	const slider = document.querySelector('.faculty-slider');
	const dotsContainer = document.querySelector('.faculty-pagination');
	const prevBtn = document.querySelector('.faculty-nav--prev');
	const nextBtn = document.querySelector('.faculty-nav--next');

	if (!slider || !dotsContainer || !prevBtn || !nextBtn) {
		return;
	}

	const allCards = Array.from(slider.querySelectorAll('.faculty-card'));
	if (!allCards.length) {
		return;
	}

	let currentPage = 0;
	let totalPages = 1;
	let itemsPerPage = 0;
	let scrollTimeout;
	let resizeTimeout;

	function getItemsPerPage() {
		return window.matchMedia('(max-width: 767px)').matches ? 3 : 8;
	}

	function getCurrentPageIndex() {
		const pageWidth = slider.clientWidth || 1;
		return Math.round(slider.scrollLeft / pageWidth);
	}

	function setControlsVisibility() {
		const hide = totalPages <= 1;
		prevBtn.style.display = hide ? 'none' : '';
		nextBtn.style.display = hide ? 'none' : '';
		dotsContainer.style.display = hide ? 'none' : '';
	}

	function updateUI() {
		const dots = dotsContainer.querySelectorAll('.pagination-dot');
		currentPage = Math.max(0, Math.min(getCurrentPageIndex(), totalPages - 1));

		dots.forEach(function (dot, index) {
			dot.classList.toggle('active', index === currentPage);
		});

		prevBtn.disabled = currentPage === 0;
		nextBtn.disabled = currentPage === totalPages - 1;
	}

	function scrollToPage(pageIndex) {
		if (pageIndex < 0 || pageIndex >= totalPages) {
			return;
		}

		const pageWidth = slider.clientWidth || 1;
		slider.scrollTo({
			left: pageIndex * pageWidth,
			behavior: 'smooth',
		});
	}

	function buildPages(keepCurrent) {
		const previousPage = keepCurrent ? currentPage : 0;
		itemsPerPage = getItemsPerPage();
		totalPages = Math.max(1, Math.ceil(allCards.length / itemsPerPage));
		currentPage = Math.min(previousPage, totalPages - 1);

		slider.innerHTML = '';
		dotsContainer.innerHTML = '';

		for (let i = 0; i < allCards.length; i += itemsPerPage) {
			const pageIndex = Math.floor(i / itemsPerPage);
			const page = document.createElement('div');
			page.className = 'faculty-page';
			page.setAttribute('data-page', String(pageIndex));

			allCards.slice(i, i + itemsPerPage).forEach(function (card) {
				page.appendChild(card);
			});

			slider.appendChild(page);
		}

		for (let dotIndex = 0; dotIndex < totalPages; dotIndex += 1) {
			const dot = document.createElement('span');
			dot.className = 'pagination-dot';
			dot.setAttribute('data-page', String(dotIndex));
			dotsContainer.appendChild(dot);
		}

		slider.scrollLeft = (slider.clientWidth || 1) * currentPage;
		setControlsVisibility();
		updateUI();
	}

	prevBtn.addEventListener('click', function () {
		scrollToPage(currentPage - 1);
	});

	nextBtn.addEventListener('click', function () {
		scrollToPage(currentPage + 1);
	});

	dotsContainer.addEventListener('click', function (event) {
		const target = event.target;
		if (!(target instanceof HTMLElement)) {
			return;
		}

		if (!target.classList.contains('pagination-dot')) {
			return;
		}

		const page = Number(target.getAttribute('data-page'));
		if (Number.isNaN(page)) {
			return;
		}

		scrollToPage(page);
	});

	slider.addEventListener('scroll', function () {
		clearTimeout(scrollTimeout);
		scrollTimeout = setTimeout(updateUI, 80);
	});

	window.addEventListener('resize', function () {
		clearTimeout(resizeTimeout);
		resizeTimeout = setTimeout(function () {
			const nextItemsPerPage = getItemsPerPage();
			if (nextItemsPerPage !== itemsPerPage) {
				buildPages(true);
				return;
			}

			slider.scrollLeft = (slider.clientWidth || 1) * currentPage;
			updateUI();
		}, 150);
	});

	buildPages(false);
});
