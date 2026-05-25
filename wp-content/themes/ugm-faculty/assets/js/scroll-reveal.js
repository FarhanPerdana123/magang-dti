(function () {
	'use strict';

	function revealElements(elements) {
		elements.forEach(function (element) {
			element.classList.add('is-visible');
		});
	}

	function initScrollReveal() {
		document.documentElement.classList.add('js');

		var autoRevealSelectors = [
			'.section-header',
			'.section-arrow-link',
			'.section-empty',
			'.news-card',
			'.academic-card',
			'.list-card',
			'.faculty-card',
			'.category-card',
			'.majalah-card',
			'.faculty-nav',
			'.faculty-pagination',
			'.category-mobile-list'
		];

		autoRevealSelectors.forEach(function (selector) {
			var nodes = document.querySelectorAll(selector);
			nodes.forEach(function (node) {
				node.classList.add('scroll-reveal');
			});
		});

		var directionCounters = {};
		var delayCounters = {};
		var groupCounter = 0;

		function nextDirection(key, options) {
			var current = directionCounters[key] || 0;
			directionCounters[key] = current + 1;
			return options[current % options.length];
		}

		function getGroupKey(element) {
			var section = element.closest('.home-section');
			if (section) {
				if (!section.hasAttribute('data-reveal-group')) {
					section.setAttribute('data-reveal-group', String(groupCounter));
					groupCounter += 1;
				}
				return section.getAttribute('data-reveal-group');
			}
			return 'global';
		}

		function chooseReveal(element) {
			if (element.matches('.section-header')) {
				return 'down';
			}
			if (element.matches('.section-arrow-link')) {
				return 'right';
			}
			if (element.matches('.section-empty')) {
				return 'up';
			}
			if (element.matches('.faculty-nav--prev')) {
				return 'left';
			}
			if (element.matches('.faculty-nav--next')) {
				return 'right';
			}
			if (element.matches('.category-mobile-list')) {
				return 'down';
			}
			if (element.matches('.news-card--featured')) {
				return 'up';
			}
			if (element.matches('.news-card--compact')) {
				return nextDirection('news-compact', ['left', 'right']);
			}
			if (element.matches('.academic-card')) {
				return nextDirection('academic', ['right', 'left']);
			}
			if (element.matches('.list-card--reverse')) {
				return 'right';
			}
			if (element.matches('.list-card')) {
				return nextDirection('list', ['left', 'up']);
			}
			if (element.matches('.faculty-card')) {
				return nextDirection('faculty', ['up', 'left', 'right']);
			}
			if (element.matches('.category-card')) {
				return nextDirection('category', ['left', 'right', 'up']);
			}
			if (element.matches('.majalah-card')) {
				return nextDirection('majalah', ['up', 'right', 'left', 'zoom']);
			}
			return nextDirection('fallback', ['up', 'right', 'left', 'down', 'zoom']);
		}

		var elements = Array.prototype.slice.call(document.querySelectorAll('.scroll-reveal'));
		if (!elements.length) {
			return;
		}

		elements.forEach(function (element) {
			var delay = element.getAttribute('data-reveal-delay');
			if (delay) {
				element.style.setProperty('--reveal-delay', delay + 'ms');
			} else {
				var groupKey = getGroupKey(element);
				var groupIndex = delayCounters[groupKey] || 0;
				delayCounters[groupKey] = groupIndex + 1;
				var computedDelay = Math.min(groupIndex, 4) * 70;
				element.style.setProperty('--reveal-delay', computedDelay + 'ms');
			}

			if (!element.hasAttribute('data-reveal')) {
				element.setAttribute('data-reveal', chooseReveal(element));
			}
		});

		var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		if (prefersReducedMotion || !('IntersectionObserver' in window)) {
			revealElements(elements);
			return;
		}

		var observer = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						entry.target.classList.add('is-visible');
					} else {
						entry.target.classList.remove('is-visible');
					}
				});
			},
			{
				root: null,
				rootMargin: '0px 0px -10% 0px',
				threshold: 0.15
			}
		);

		elements.forEach(function (element) {
			observer.observe(element);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initScrollReveal);
	} else {
		initScrollReveal();
	}
})();
