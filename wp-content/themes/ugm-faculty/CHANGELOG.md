# CHANGELOG

All notable changes to the UGM Faculty Theme will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed
- Reorganized internal code responsibilities:
  - moved custom route hooks from `inc/theme-setup.php` to `inc/theme-routes.php`
  - added `inc/front-page-helpers.php` for reusable category tree helpers
- Updated `functions.php` module loading order to match responsibilities.
- Refined front-page section query code to use shared helper functions.
- Rewrote `README.md` to match the actual folder structure and runtime flow.

## [1.0.0] - 2026-02-20

### Added
- Initial theme release
- Modular file structure with organized `/inc/` and `/template-parts/` directories
- Theme setup configuration (`inc/theme-setup.php`)
- Scripts and styles enqueuing (`inc/enqueue.php`)
- Widget areas registration (`inc/widgets.php`)
- Theme Customizer settings (`inc/customizer.php`)
- Header template parts:
  - Site branding component
  - Navigation component
  - Language switcher component
  - Search component
- Content template parts:
  - Hero section component
- Responsive design with mobile-first approach
- Support for custom logos (light and dark variants)
- Multilingual support (Polylang and WPML compatible)
- Widget-ready footer areas
- Front page hero section with customizable background
- Theme Customizer integration for easy configuration
- Accessibility-ready components
- Clean, semantic HTML5 markup
- Documentation files (README.md, readme.txt, CHANGELOG.md)
- EditorConfig for consistent coding styles

### Changed
- Reorganized theme structure for better maintainability
- Separated functions.php into modular components
- Improved code organization following WordPress coding standards

### Technical Details
- WordPress 6.4+ compatibility
- PHP 7.4+ requirement
- BEM methodology for CSS class naming
- Proper escaping and sanitization
- Translation-ready with text domain 'ugm-faculty'

---

## Future Releases

### Planned Features
- Additional template parts for common components
- Enhanced customizer options
- Pattern library
- Block editor support enhancements
- Performance optimizations
