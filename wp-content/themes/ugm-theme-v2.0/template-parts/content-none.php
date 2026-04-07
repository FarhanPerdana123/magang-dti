<article class="no-results">
	<div class="page-content">
		<?php if ( is_search() ) : ?>

			<p><?php echo is_english() ? 'Nothing found for keyword' : 'Tidak ada hasil untuk pencarian dengan kata kunci' ?> <strong><?php echo get_search_query() ?></strong>.<br><?php echo is_english() ? 'Please try again with another keyword.' : 'Silakan coba lagi dengan kata kunci yang berbeda.' ?></p>
			<div class="input-group btn-group">
				<form action="<?php echo home_url() ?>">
					<input type="text" name="s" class="form-control" placeholder="<?php esc_html_e( 'Keyword...', 'ugm-theme' ); ?>" required>
					<button type="submit" class="btn"><i class="fa fa-search"></i></button>
				</form>
			</div>

		<?php elseif ( is_post_type_archive( "event" ) ) : ?>

			<p><?php esc_html_e( 'Event not found.', 'ugm-theme' ); ?></p>

		<?php else : ?>

			<p><?php esc_html_e( 'Article not found.', 'ugm-theme' ); ?></p>

		<?php endif; ?>
	</div>
</article>