<?php
/**
 * Template part for displaying language switcher
 *
 * @package ugm-faculty
 */

$languages = array();

// Check for Polylang.
if ( function_exists( 'pll_the_languages' ) ) {
	$pll_languages = pll_the_languages(
		array(
			'raw'           => 1,
			'hide_if_empty' => 0,
		)
	);
	if ( is_array( $pll_languages ) ) {
		foreach ( $pll_languages as $lang ) {
			$languages[] = array(
				'slug'    => isset( $lang['slug'] ) ? strtoupper( (string) $lang['slug'] ) : '',
				'name'    => isset( $lang['name'] ) ? (string) $lang['name'] : '',
				'url'     => isset( $lang['url'] ) ? (string) $lang['url'] : '',
				'current' => ! empty( $lang['current_lang'] ),
				'flag'    => isset( $lang['flag'] ) ? (string) $lang['flag'] : '',
			);
		}
	}
} elseif ( function_exists( 'icl_get_languages' ) ) {
	// Check for WPML.
	$wpml_languages = icl_get_languages( 'skip_missing=0&orderby=code' );
	if ( is_array( $wpml_languages ) ) {
		foreach ( $wpml_languages as $lang ) {
			$languages[] = array(
				'slug'    => isset( $lang['language_code'] ) ? strtoupper( (string) $lang['language_code'] ) : '',
				'name'    => isset( $lang['native_name'] ) ? (string) $lang['native_name'] : '',
				'url'     => isset( $lang['url'] ) ? (string) $lang['url'] : '',
				'current' => ! empty( $lang['active'] ),
				'flag'    => isset( $lang['country_flag_url'] ) ? (string) $lang['country_flag_url'] : '',
			);
		}
	}
}

// Fallback if no multilingual plugin.
if ( empty( $languages ) ) {
	$languages[] = array(
		'slug'    => strtoupper( substr( get_locale(), 0, 2 ) ),
		'name'    => get_locale(),
		'url'     => home_url( '/' ),
		'current' => true,
		'flag'    => '',
	);
}

$current_language = $languages[0];
foreach ( $languages as $language_item ) {
	if ( ! empty( $language_item['current'] ) ) {
		$current_language = $language_item;
		break;
	}
}
?>

<div class="language-switcher">
	<button class="language-switcher__toggle" type="button" aria-expanded="false" aria-controls="language-switcher-list">
		<?php if ( ! empty( $current_language['flag'] ) ) : ?>
			<img class="language-switcher__flag" src="<?php echo esc_url( $current_language['flag'] ); ?>" alt="" aria-hidden="true">
		<?php else : ?>
			<span class="language-switcher__flag language-switcher__flag--fallback" aria-hidden="true"></span>
		<?php endif; ?>
		<span class="language-switcher__code"><?php echo esc_html( $current_language['slug'] ); ?></span>
		<span class="language-switcher__caret" aria-hidden="true"></span>
	</button>
	<ul id="language-switcher-list" class="language-switcher__list" hidden>
		<?php foreach ( $languages as $language_item ) : ?>
			<li>
				<a href="<?php echo esc_url( $language_item['url'] ); ?>"<?php echo ! empty( $language_item['current'] ) ? ' aria-current="true"' : ''; ?>>
					<?php if ( ! empty( $language_item['flag'] ) ) : ?>
						<img class="language-switcher__flag" src="<?php echo esc_url( $language_item['flag'] ); ?>" alt="">
					<?php endif; ?>
					<span><?php echo esc_html( $language_item['slug'] ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
