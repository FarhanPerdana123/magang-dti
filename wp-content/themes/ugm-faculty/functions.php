<?php
/**
 * UGM Faculty Theme - Functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package ugm-faculty
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once get_template_directory() . '/inc/class-ugm-theme.php';

new UGM_Faculty_Theme();
