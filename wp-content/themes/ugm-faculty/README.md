# UGM Faculty Theme

WordPress theme for faculty websites with modular templates, server-rendered blocks, and Customizer-driven content sections.

## Requirements

- WordPress 6.4+
- PHP 7.4+

## Theme Architecture

The theme uses a thin-loader pattern at the root:

- Root template files (`front-page.php`, `single.php`, `page.php`, `index.php`, `category.php`) only load files from `templates/`.
- Main logic is organized under categorized `inc/` modules, `template-parts/`, `parts/`, `page-templates/`, and `templates/`.

## Current Folder Structure

```text
ugm-faculty/
|-- assets/
|   |-- css/
|   |   |-- core/                  # Global frontend styles
|   |   |-- pages/                 # Page-template styles
|   |   `-- editor/                # Admin editor preview styles
|   |-- js/
|   |   |-- editor/                # Admin editor helper scripts
|   |   `-- frontend/              # Public-facing interaction scripts
|   `-- images/
|
|-- inc/
|   |-- class-ugm-theme.php     # Theme bootstrap and module loader
|   |-- core/                   # Setup, routing, enqueue, widgets, Customizer, security
|   |-- helpers/                # Shared helper functions
|   |-- meta/                   # Post/page meta registration
|   |-- pages/                  # Page-template seeding, routing, and render helpers
|   `-- editor/                 # Gutenberg registration and editor integrations
|
|-- template-parts/
|   |-- header/
|   |   |-- site-branding.php
|   |   |-- navigation.php
|   |   |-- language-switcher.php
|   |   `-- search.php
|   `-- content/
|       `-- hero.php
|
|-- page-templates/
|   |-- template-landing-page.php
|   |-- template-agenda.php
|   |-- template-announcement.php
|   |-- template-gallery.php
|   |-- template-management.php
|   `-- template-rector-greeting.php
|
|-- templates/
|   |-- front-page.php
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

- `functions.php` loads `inc/class-ugm-theme.php`.
- `inc/class-ugm-theme.php` loads modules from `inc/core`, `inc/helpers`, `inc/meta`, `inc/pages`, and `inc/editor`.
- `header.php` loads header parts and conditional hero.
- `front-page.php` (root) loads `templates/front-page.php`.
- Latest news route:
  - URL: `/?ugm_latest_news=1`
  - Router in `inc/core/theme-routes.php`
  - Template: `page-templates/latest-news.php`

## Customizer Sections Used on Landing Page

- `Berita Terbaru (Landing Page)`
- `Berita Akademik (Landing Page)`
- `Profile (Landing Page)`
- `Prestasi (Landing Page)`

Each section supports auto/manual source mode and post count controls.

## Maintenance Conventions

- Keep root templates as loaders only.
- Put reusable logic in `inc/helpers/` instead of duplicating in templates.
- Put page-template behavior in `inc/pages/`.
- Put Gutenberg/editor registration in `inc/editor/`.
- Keep visual styles grouped by scope: `assets/css/core/`, `assets/css/pages/`, and `assets/css/editor/`.
- When adding a new route, register query vars and template routing in `inc/core/theme-routes.php`.

## Notes

- If permalink settings are changed, re-save **Settings -> Permalinks** once.
- Hard-refresh browser (`Ctrl+F5`) after CSS/template updates.
