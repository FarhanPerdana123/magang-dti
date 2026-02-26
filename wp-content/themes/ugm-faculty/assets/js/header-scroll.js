(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var header = document.getElementById('masthead');
		if (!header) {
			return;
		}

		var isFrontPage = header.getAttribute('data-front-page') === '1';
		var menuToggle = header.querySelector('.menu-toggle');
		var menuBackdrop = header.querySelector('.menu-backdrop');
		var nav = document.getElementById('site-navigation');
		var langToggle = header.querySelector('.language-switcher__toggle');
		var langList = document.getElementById('language-switcher-list');
		var searchToggle = header.querySelector('.header-search__toggle');
		var searchPanel = document.getElementById('header-search-form');
		var searchClose = header.querySelector('.header-search__close');

		// Keep header state in sync with scroll position (front page transparent -> solid on scroll).
		function updateScrollState() {
			if (!isFrontPage) {
				header.classList.add('is-scrolled');
				return;
			}
			if (window.scrollY > 10) {
				header.classList.add('is-scrolled');
			} else {
				header.classList.remove('is-scrolled');
			}
		}

		function closeMenu() {
			header.classList.remove('menu-open');
			document.body.classList.remove('no-scroll');
			if (menuToggle) {
				menuToggle.setAttribute('aria-expanded', 'false');
				menuToggle.classList.remove('active');
			}
			if (nav) {
				nav.classList.remove('active');
			}
		}

		// Close language dropdown.
		function closeLanguage() {
			if (langToggle && langList) {
				langToggle.setAttribute('aria-expanded', 'false');
				langList.hidden = true;
			}
		}

		function closeSearch() {
			if (searchToggle && searchPanel) {
				searchToggle.setAttribute('aria-expanded', 'false');
				searchPanel.hidden = true;
				header.classList.remove('search-open');
			}
		}

		updateScrollState();
		window.addEventListener('scroll', updateScrollState, { passive: true });

		if (menuToggle && nav) {
			// Mobile hamburger toggle: slide/fade menu down from top and back up on close.
			menuToggle.addEventListener('click', function () {
				var expanded = menuToggle.getAttribute('aria-expanded') === 'true';
				var isOpening = !expanded;
				menuToggle.setAttribute('aria-expanded', isOpening ? 'true' : 'false');
				menuToggle.classList.toggle('active', isOpening);
				nav.classList.toggle('active', isOpening);
				header.classList.toggle('menu-open', isOpening);
				document.body.classList.toggle('no-scroll', isOpening);
				if (isOpening) {
					closeLanguage();
					closeSearch();
					// Ensure menu close icon stays visible even if a previous search state was left behind.
					header.classList.remove('search-open');
				}
			});

			document.addEventListener('keydown', function (event) {
				if (event.key === 'Escape') {
					closeMenu();
					closeLanguage();
					closeSearch();
					menuToggle.focus();
				}
			});

			document.addEventListener('click', function (event) {
				if (!header.contains(event.target)) {
					closeMenu();
					closeLanguage();
					closeSearch();
				}
			});
		}

		if (menuBackdrop) {
			menuBackdrop.addEventListener('click', function () {
				closeMenu();
				closeLanguage();
				closeSearch();
			});
		}

		if (langToggle && langList) {
			langToggle.addEventListener('click', function () {
				var expanded = langToggle.getAttribute('aria-expanded') === 'true';
				langToggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
				langList.hidden = expanded;
				if (!expanded) {
					closeSearch();
				}
			});
		}

		if (searchToggle && searchPanel) {
			// Toggle search panel and keep it exclusive with menu/language panels.
			searchToggle.addEventListener('click', function () {
				var expanded = searchToggle.getAttribute('aria-expanded') === 'true';
				searchToggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
				searchPanel.hidden = expanded;
				if (!expanded) {
					closeLanguage();
					closeMenu();
					header.classList.add('search-open');
					var field = searchPanel.querySelector('.search-field');
					if (field) {
						field.focus();
					}
				} else {
					header.classList.remove('search-open');
				}
			});
		}

		if (searchClose) {
			searchClose.addEventListener('click', function () {
				closeSearch();
				if (searchToggle) {
					searchToggle.focus();
				}
			});
		}
	});
})();
