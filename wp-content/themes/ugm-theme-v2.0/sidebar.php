<?php $site_layout = ugm_get_layout(); ?>

<?php if ($site_layout != 'none') : ?>
<div id="sidebar" class="<?php ugm_layout_class('sidebar') ?>">
	<div class="row">

		<?php
			if ( is_active_sidebar( 'sidebar-widget' ) ) {
				dynamic_sidebar( 'sidebar-widget' );
			}
		?>

	</div> <!-- widget-area -->
</div> <!-- side-bar -->
<?php endif; ?>
<?php if ($site_layout == 'left-right') : ?>
<div id="second-sidebar" class="<?php ugm_layout_class('second_sidebar') ?>">
	<div class="row">

		<?php
			if ( is_active_sidebar( 'secondary-sidebar-widget' ) ) {
				dynamic_sidebar( 'secondary-sidebar-widget' );
			}
		?>

	</div> <!-- widget-area -->
</div> <!-- side-bar -->
<?php endif; ?>