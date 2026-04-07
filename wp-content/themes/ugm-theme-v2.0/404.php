<?php
get_header();
?>

<div id="body">
	<div class="container">

		<div id="content" class="error-page">
			
			<div class="row">
				<div class="col-md-5 col-md-offset-3 col-sm-8 col-sm-offset-2">
					<div class="error-header">
						<h1 class="error-title">404</h1>
						<p class="error-subtitle">
							<?php if(is_english()) : ?>
								<span><?php esc_html_e( 'Page Not Found', 'ugm-theme' ) ?></span>
							<?php else: ?>
								<span><?php esc_html_e( 'Halaman Tidak Ditemukan', 'ugm-theme' ) ?></span>
							<?php endif; ?>
						</p>
					</div>
					<div class="error-message">
						<?php if(is_english()) : ?>
							<p>Wanna try our search?</p>
						<?php else: ?>
							<p>Tidak bisa mengurai request <strong>&ldquo;id/pendidika&rdquo;</strong>. <br>Anda mungkin ingin mencoba pencarian situs:</p>
						<?php endif; ?>
						<div class="input-group btn-group">
							<form action="<?php echo site_url() ?>">
								<input type="text" class="form-control" name="s" placeholder="<?php esc_html_e( 'Keyword...', 'ugm-theme' ); ?>">
								<button type="submit" class="btn"><i class="fa fa-search"></i></button>
							</form>
						</div>
					</div>
				</div>
			</div>

		</div>

	</div>
</div>

<?php
get_footer();
?>