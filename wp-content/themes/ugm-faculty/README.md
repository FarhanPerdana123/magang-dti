# UGM Faculty Theme

A professional, institutional WordPress theme designed for university faculty websites.

## Features

- **Responsive Design**: Mobile-first approach ensuring great display on all devices
- **Customizable Hero Section**: Dynamic front page hero with background image support
- **Multilingual Ready**: Compatible with Polylang and WPML
- **Accessibility**: Built with WCAG guidelines in mind
- **Modular Structure**: Clean, organized code for easy maintenance
- **Widget Areas**: Customizable footer widget zones
- **Theme Customizer**: Easy customization without coding

## Requirements

- WordPress 6.4 or higher
- PHP 7.4 or higher
- Modern web browser

## Installation

1. Download the theme files
2. Go to **Appearance → Themes → Add New → Upload Theme**
3. Upload the theme ZIP file
4. Click **Install Now** and then **Activate**

## Configuration

### Basic Setup

1. **Logo Configuration**
   - Navigate to **Appearance → Customize → UGM Branding**
   - Upload a dark logo (for solid header)
   - Upload a light logo (for transparent header on front page)

2. **Hero Section**
   - Go to **Appearance → Customize → Front Page Hero**
   - Set hero background image
   - Configure headline and description

3. **Menus**
   - Create menus at **Appearance → Menus**
   - Assign to locations:
     - Primary (main navigation)
     - Footer Quick Links
     - Mobile Quick Links

4. **Footer Settings**
   - Customize footer content at **Appearance → Customize → UGM Footer**
   - Add faculty information, contact details, and copyright

### Front Page Sections

Configure front page sections at **Appearance → Customize → Front Page Sections**:

- Featured Post/Category
- Latest News
- Announcements
- Events
- Content Categories

## File Structure

```
ugm-faculty/
├── assets/
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript files
│   │   └── header-scroll.js
│   └── images/           # Theme images
├── inc/
│   ├── theme-setup.php   # Theme configuration
│   ├── enqueue.php       # Asset loading
│   ├── widgets.php       # Widget registration
│   └── customizer.php    # Customizer settings
├── template-parts/
│   ├── header/           # Header components
│   │   ├── site-branding.php
│   │   ├── navigation.php
│   │   ├── language-switcher.php
│   │   └── search.php
│   ├── footer/           # Footer components
│   └── content/          # Content templates
│       └── hero.php
├── functions.php         # Main functions file
├── style.css            # Main stylesheet
├── header.php           # Header template
├── footer.php           # Footer template
├── index.php            # Main template
├── front-page.php       # Front page template
├── single.php           # Single post template
└── page.php             # Page template
```

## Customization

### Adding Custom Styles

Add custom CSS through **Appearance → Customize → Additional CSS** or create a child theme.

### Child Theme

To create a child theme:

1. Create a new folder: `ugm-faculty-child`
2. Create `style.css`:

```css
/*
Theme Name: UGM Faculty Child
Template: ugm-faculty
*/
```

3. Create `functions.php`:

```php
<?php
function ugm_child_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'ugm_child_enqueue_styles' );
```

## Development

### Coding Standards

This theme follows WordPress Coding Standards:
- PHP: [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- CSS: Follows BEM methodology
- JavaScript: ES6+ compatible

### Theme Constants

- `UGM_THEME_VERSION`: Theme version number

## Changelog

### Version 1.0.0
- Initial release
- Modular file structure
- Theme Customizer integration
- Multilingual support
- Responsive design
- Accessibility features

## Credits

- **Developer**: UGM Faculty Development Team
- **License**: GPL v2 or later

## Support

For questions or issues, please contact the development team at your institution.

---

© 2026 Universitas Gadjah Mada. All rights reserved.
