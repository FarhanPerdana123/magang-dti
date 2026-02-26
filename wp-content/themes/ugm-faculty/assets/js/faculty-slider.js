/**
 * Faculty slider with arrow navigation (3 items per page, horizontal scroll)
 */
document.addEventListener('DOMContentLoaded', function() {
	const slider = document.querySelector('.faculty-slider');
	const pages = document.querySelectorAll('.faculty-page');
	const dots = document.querySelectorAll('.pagination-dot');
	const prevBtn = document.querySelector('.faculty-nav--prev');
	const nextBtn = document.querySelector('.faculty-nav--next');
	
	if (!slider || !pages.length || !dots.length || !prevBtn || !nextBtn) return;
	
	let currentPage = 0;
	const totalPages = pages.length;
	
	// Get current page based on scroll position
	function getCurrentPageIndex() {
		const scrollLeft = slider.scrollLeft;
		const pageWidth = slider.offsetWidth;
		return Math.round(scrollLeft / pageWidth);
	}
	
	// Update active dot and button states
	function updateUI() {
		currentPage = getCurrentPageIndex();
		
		// Update dots
		dots.forEach((dot, index) => {
			if (index === currentPage) {
				dot.classList.add('active');
			} else {
				dot.classList.remove('active');
			}
		});
		
		// Update buttons
		prevBtn.disabled = currentPage === 0;
		nextBtn.disabled = currentPage === totalPages - 1;
	}
	
	// Scroll to specific page
	function scrollToPage(pageIndex) {
		if (pageIndex < 0 || pageIndex >= totalPages) return;
		
		const pageWidth = slider.offsetWidth;
		slider.scrollTo({
			left: pageIndex * pageWidth,
			behavior: 'smooth'
		});
	}
	
	// Previous button
	prevBtn.addEventListener('click', function() {
		scrollToPage(currentPage - 1);
	});
	
	// Next button
	nextBtn.addEventListener('click', function() {
		scrollToPage(currentPage + 1);
	});
	
	// Dot navigation
	dots.forEach((dot, index) => {
		dot.addEventListener('click', function() {
			scrollToPage(index);
		});
	});
	
	// Update UI on scroll
	let scrollTimeout;
	slider.addEventListener('scroll', function() {
		clearTimeout(scrollTimeout);
		scrollTimeout = setTimeout(updateUI, 100);
	});
	
	// Initial state
	updateUI();
});
