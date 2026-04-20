<?php
/**
 * Template part for displaying search functionality
 *
 * @package ugm-faculty
 */
?>

<div class="header-search">
	<button class="header-search__toggle" type="button" aria-expanded="false" aria-controls="header-search-form">
		<span class="screen-reader-text"><?php esc_html_e( 'Search', 'ugm-faculty' ); ?></span>
		<svg class="header-search__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
			<circle cx="11" cy="11" r="7"></circle>
			<line x1="16.65" y1="16.65" x2="21" y2="21"></line>
		</svg>
	</button>
</div>
