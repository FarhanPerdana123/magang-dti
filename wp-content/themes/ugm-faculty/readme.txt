=== UGM Faculty Theme ===

Contributors: UGM Faculty
Tags: institutional, education, university, faculty, accessibility-ready
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A professional, content-focused WordPress theme designed for faculty websites.

== Description ==

UGM Faculty Theme is a clean, institutional WordPress theme designed specifically for university faculty websites. It features a modular structure, customizable hero section, multilingual support, and accessibility-ready components.

== Features ==

* Responsive design
* Customizable hero section for the front page
* Multilingual support (Polylang and WPML compatible)
* Widget-ready footer areas
* Custom logo support (light and dark versions)
* Accessibility-ready components
* Clean, semantic HTML5 markup
* Theme Customizer integration
* Modular code structure

== Installation ==

1. In your admin panel, go to Appearance > Themes and click the Add New button.
2. Click Upload Theme and Choose File, then select the theme's .zip file. Click Install Now.
3. Click Activate to use your new theme right away.

== Frequently Asked Questions ==

= Does this theme support multilingual sites? =

Yes, the theme is compatible with Polylang and WPML plugins.

= Can I use custom logos? =

Yes, you can upload both a light logo (for transparent headers) and a dark logo (for solid headers) through the Theme Customizer.

= Where can I add widgets? =

The theme provides two footer widget areas:
- Footer Quick Links Widget
- Footer Institutional Widget

== Changelog ==

= 1.0.0 =
* Initial release

== Credits ==

* Designed and developed by UGM Faculty
* Built with WordPress coding standards
* Icons: Custom SVG icons

== Theme Structure ==

```
ugm-faculty/
|-- assets/
|   |-- css/
|   |   |-- core/            # Global frontend styles
|   |   |-- pages/           # Page-template styles
|   |   `-- editor/          # Admin editor preview styles
|   |-- js/
|   |   |-- editor/          # Admin editor helper scripts
|   |   `-- frontend/        # Public-facing interaction scripts
|   `-- images/              # Theme images
|-- inc/
|   |-- class-ugm-theme.php  # Bootstrap and module loader
|   |-- core/                # Setup, routes, enqueue, widgets, Customizer, security
|   |-- helpers/             # Shared helper functions
|   |-- meta/                # Meta registration
|   |-- pages/               # Page-template behavior
|   `-- editor/              # Gutenberg registration and editor integrations
|-- page-templates/          # PHP page templates selectable in WordPress
|-- template-parts/          # Header/content partials
|-- templates/               # Thin template targets loaded by root templates
|-- functions.php            # Loads the theme bootstrap
|-- style.css                # Theme header and legacy styles
|-- header.php               # Header template
|-- footer.php               # Footer template
|-- index.php                # Main template loader
|-- front-page.php           # Front page template loader
|-- single.php               # Single post template loader
`-- page.php                 # Page template loader
```

== Support ==

For support and questions, please contact the theme development team.
