<?php
$related_events = ugm_get_related_event( get_the_ID(), 4 );
?>

<?php if ( ! empty( $related_events ) ) : ?>
	<div id="related-events">
		<h3 class="widget-title"><?php esc_html_e( 'Related Events', 'ugm-theme' ); ?></h3>
		<div class="events">
			<?php
			foreach ( $related_events as $post ) {
				setup_postdata( $post );
				get_template_part( 'template-parts/loop', 'event' );
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
<?php endif; ?>