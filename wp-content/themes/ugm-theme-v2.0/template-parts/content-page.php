<article class="single-post">
	<div class="post-heading">
		<?php
			if ( is_single() || is_page() ) {
				the_title( '<h1 class="post-title">', '</h1>' );
			} else {
				the_title( sprintf( '<h2><a href="%s">', esc_url( get_permalink() ) ), '</a></h2>' );
				if ( get_the_title() == null ) {
					echo '<h2><a href="' . esc_url( get_permalink() ) . '">View Post</a></h2>'; 
				}
			}
		?>
	</div>
	<div class="post-content">
		<?php the_content(); ?>
	</div>
</article>