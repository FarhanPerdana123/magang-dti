# UGM Faculty Theme

WordPress theme for faculty websites with a mobile-first landing page, modular templates, and Customizer-driven content sections.

## Requirements

- WordPress 6.4+
- PHP 7.4+

## Theme Architecture

The theme uses a thin-loader pattern at the root:

- Root template files (`front-page.php`, `single.php`, `page.php`, `index.php`, `category.php`) only load files from `templates/`.
- Main logic is organized under `inc/`, `parts/`, and `templates/`.

## Current Folder Structure

```text
ugm-faculty/
|-- assets/
|   |-- css/
|   |   |-- base.css
|   |   |-- header.css
|   |   |-- hero.css
|   |   |-- content.css
|   |   `-- footer.css
|   |-- js/
|   |   |-- header-scroll.js
|   |   `-- faculty-slider.js
|   `-- images/
|
|-- inc/
|   |-- theme-setup.php         # Theme supports + menus
|   |-- theme-routes.php        # Custom query route handling
|   |-- front-page-helpers.php  # Reusable category/query helpers
|   |-- enqueue.php             # CSS/JS enqueue
|   |-- widgets.php             # Widget registration
|   `-- customizer.php          # Customizer sections/settings/controls
|
|-- parts/
|   |-- header/
|   |   |-- site-branding.php
|   |   |-- navigation.php
|   |   |-- language-switcher.php
|   |   `-- search.php
|   `-- content/
|       `-- hero.php
|
|-- templates/
|   |-- front-page.php
|   |-- latest-news.php
|   |-- category.php
|   |-- index.php
|   |-- single.php
|   `-- page.php
|
|-- functions.php
|-- header.php
|-- footer.php
|-- style.css                   # Manifest/header only
`-- readme.txt                  # WordPress theme metadata readme
```

## Template Flow

- `functions.php` loads all modules from `inc/`.
- `header.php` loads header parts and conditional hero.
- `front-page.php` (root) loads `templates/front-page.php`.
- Latest news route:
  - URL: `/?ugm_latest_news=1`
  - Router in `inc/theme-routes.php`
  - Template: `templates/latest-news.php`

## Customizer Sections Used on Landing Page

- `Berita Terbaru (Landing Page)`
- `Berita Akademik (Landing Page)`
- `Profile (Landing Page)`
- `Prestasi (Landing Page)`

Each section supports auto/manual source mode and post count controls.

## Maintenance Conventions

- Keep root templates as loaders only.
- Put reusable logic in `inc/` helpers instead of duplicating in templates.
- Keep visual styles in `assets/css/*` by concern (header, hero, content, footer).
- When adding a new route, register query vars and template routing in `inc/theme-routes.php`.

## Notes

- If permalink settings are changed, re-save **Settings -> Permalinks** once.
- Hard-refresh browser (`Ctrl+F5`) after CSS/template updates.
