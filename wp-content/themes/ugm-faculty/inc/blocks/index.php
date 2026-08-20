<?php
/**
 * Block module loader.
 *
 * Keeps feature-specific block registrations out of inc/blocks.php.
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_theme_file_path( 'inc/blocks/rector-greeting.php' );
