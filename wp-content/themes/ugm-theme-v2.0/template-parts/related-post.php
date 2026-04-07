<?php
$related_posts = ugm_get_related_post( get_the_ID(), 4 );
?>

<?php if ( ! empty( $related_posts ) ) : ?>
	<div id="related-posts">
		<h3 class="widget-title"><?php esc_html_e( 'Related Posts', 'ugm-theme' ); ?></h3>
		<div class="posts">
			<?php
			foreach ( $related_posts as $post ) {
				setup_postdata( $post );
				get_template_part( 'template-parts/loop', 'post' );
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
<?php endif; ?>