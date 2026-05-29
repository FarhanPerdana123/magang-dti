<?php
/**
 * Template Name: Halaman Galeri
 * Template Post Type: page
 *
 * Full-width gallery page shell. If the editor content is empty, the default
 * gallery block is rendered automatically.
 *
 * @package ugm-faculty
 */

get_header();
?>

<main id="primary" class="site-main ugm-gallery-template">
	<?php while ( have_posts() ) : ?>
		<?php
		the_post();
		$content = trim( (string) get_the_content() );
		?>

		<?php if ( '' !== $content ) : ?>
			<?php the_content(); ?>
		<?php else : ?>
			<?php
			echo do_blocks( '<!-- wp:ugm/gallery-page {"title":"Galeri","breadcrumbParent":"Berita","categorySlug":"galeri","postsPerPage":12,"showFeatured":true} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		<?php endif; ?>
	<?php endwhile; ?>
</main>

<?php
get_footer();
