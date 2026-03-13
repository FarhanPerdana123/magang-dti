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
		var mobileDropdownToggles = [];

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
			mobileDropdownToggles.forEach(function (toggle) {
				var item = toggle.closest('li');
				var submenu = item ? item.querySelector(':scope > .sub-menu') : null;
				toggle.setAttribute('aria-expanded', 'false');
				if (item) {
					item.classList.remove('is-open');
				}
				if (submenu) {
					submenu.hidden = true;
				}
			});
		}

		function setupMobileDropdowns() {
			if (!nav) {
				return;
			}

			var dropdownSeed = {
				'Pendaftaran': [
					'Sarjana dan Sarjana Terapan',
					'International Undergraduate Program (IUP)',
					'Pascasarjana',
					'Bantuan Keuangan & Beasiswa',
					'Tinggal di Kampus',
					'Biaya Pendidikan',
					'Pelajar Internasional'
				],
				'Pendidikan': [
					'Fakultas dan Sekolah',
					'Kalender Akademik',
					'Diktisaintek Berdampak'
				],
				'Penelitian': [
					'Penelitian',
					'Sorotan dan Dampak Tinggi',
					'Publikasi',
					'Buku',
					'Produk',
					'Pusat Penelitian',
					'Keahlian'
				],
				'Pengabdian': [
					'Pengabdian',
					'Highlight dan High Impact',
					'KKN PPM',
					'KKN ECL',
					'Desa Binaan',
					'Teknologi Tepat Guna',
					'Pendidikan Untuk Pembangunan Berkelanjutan',
					'Unit Tanggap Bencana',
					'Pusat Regional Keahlian',
					'UMKM'
				],
				'Layanan': [
					'Suara Kita',
					'Aspirasi UGM',
					'Whistleblowing System',
					'lapor.go.id',
					{
						label: 'Layanan Darurat dan Pusat Krisis',
						children: [
							'Layanan Kesehatan Terpadu',
							'Kontak Darurat',
							'Pusat Krisis',
							'Satgas PPKS'
						]
					},
					{
						label: 'Layanan Terpadu',
						children: [
							'University Services',
							'Layanan Laboratorium Terpadu',
							'Layanan Homestay UGM',
							'Asrama Mahasiswa',
							'Layanan Pengadaan',
							'Layanan Terpadu',
							'Layanan Alumni',
							'Campus Visit'
						]
					},
					{
						label: 'Layanan Data dan Informasi',
						children: [
							'Search UGM',
							'Peta Kampus',
							'UGM dalam Angka',
							'Layanan Informasi Publik',
							'Laporan Keuangan',
							'Agenda',
							'Layanan Elektronik/E-Mail',
							'UGM Online',
							'SIMASTER VNext Parents - Android',
							'SIMASTER Vnext Parents - IOS',
							'Virtual Campus Tour'
						]
					}
				],
				'Tentang': [
					'Tentang UGM',
					'Sambutan Rektor',
					'Visi dan Misi',
					'Tugas dan Fungsi',
					'Organisasi',
					'Direktorat dan Unit Kerja',
					'Sejarah',
					'Makna Lambang',
					'Himne Gadjah Mada',
					'Panduan Identitas'
				],
				'SDGs': [
					'SDGs Portal',
					'SDGs Dashboard',
					'SDGs dengan AI'
				]
			};

			function makeFallbackUrl(label) {
				return '/' + label
					.toLowerCase()
					.replace(/[^a-z0-9\s-]/g, '')
					.replace(/\s+/g, '-')
					.replace(/-+/g, '-') + '/';
			}

			function appendSeedChildren(targetList, children) {
				children.forEach(function (childItem) {
					var label = typeof childItem === 'string' ? childItem : (childItem && childItem.label ? childItem.label : '');
					var grandChildren = (childItem && Array.isArray(childItem.children)) ? childItem.children : [];
					if (!label) {
						return;
					}

					var li = document.createElement('li');
					var a = document.createElement('a');
					a.href = makeFallbackUrl(label);
					a.textContent = label;
					li.appendChild(a);

					if (grandChildren.length) {
						li.classList.add('menu-item-has-children');
						var nested = document.createElement('ul');
						nested.className = 'sub-menu';
						nested.hidden = true;
						appendSeedChildren(nested, grandChildren);
						li.appendChild(nested);
					}

					targetList.appendChild(li);
				});
			}

			var topLevelLinks = nav.querySelectorAll('.primary-menu__list > li > a');
			topLevelLinks.forEach(function (link) {
				var item = link.closest('li');
				if (!item) {
					return;
				}

				var label = link.textContent ? link.textContent.trim() : '';
				var children = dropdownSeed[label];
				var existingSubmenu = item.querySelector(':scope > .sub-menu');

				if (!children || existingSubmenu) {
					return;
				}

				item.classList.add('menu-item-has-children');

				var submenu = document.createElement('ul');
				submenu.className = 'sub-menu';
				submenu.hidden = true;
				appendSeedChildren(submenu, children);

				item.appendChild(submenu);
			});

			var parentItems = nav.querySelectorAll('.primary-menu__list li.menu-item-has-children');

			parentItems.forEach(function (item) {
				var submenu = item.querySelector(':scope > .sub-menu');
				if (!submenu) {
					return;
				}

				var toggle = item.querySelector(':scope > .menu-dropdown-toggle');
				if (!toggle) {
					toggle = document.createElement('button');
					toggle.type = 'button';
					toggle.className = 'menu-dropdown-toggle';
					toggle.setAttribute('aria-expanded', 'false');
					toggle.setAttribute('aria-label', 'Tampilkan submenu');
					toggle.innerHTML = '<span aria-hidden="true"></span>';
					item.appendChild(toggle);
				}

				submenu.hidden = true;
				mobileDropdownToggles.push(toggle);

				toggle.addEventListener('click', function (event) {
					event.preventDefault();
					event.stopPropagation();

					var isOpen = toggle.getAttribute('aria-expanded') === 'true';

					mobileDropdownToggles.forEach(function (otherToggle) {
						var otherItem = otherToggle.closest('li');
						var otherSubmenu = otherItem ? otherItem.querySelector(':scope > .sub-menu') : null;
						if (otherToggle === toggle) {
							return;
						}
						otherToggle.setAttribute('aria-expanded', 'false');
						if (otherItem) {
							otherItem.classList.remove('is-open');
						}
						if (otherSubmenu) {
							otherSubmenu.hidden = true;
						}
					});

					toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
					item.classList.toggle('is-open', !isOpen);
					submenu.hidden = isOpen;
				});
			});
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
		setupMobileDropdowns();
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
