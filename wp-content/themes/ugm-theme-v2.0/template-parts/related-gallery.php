<?php
$related_gallery = ugm_get_related_gallery( get_the_ID(), 4 );
?>

<?php if ( ! empty( $related_gallery ) ) : ?>
	<div id="related-gallery">
		<h3 class="widget-title"><?php esc_html_e( 'Related Gallery', 'ugm-theme' ); ?></h3>
		<div class="row row-flex">
			<?php
			foreach ( $related_gallery as $post ) {
				setup_postdata( $post );
				get_template_part( 'template-parts/loop', 'gallery' );
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
<?php endif; ?>