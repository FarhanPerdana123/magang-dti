<?php
/**
 * The template for displaying comments.
 *
 * This is the template that displays the area of the page that contains both the
 * current comments and the comment form.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ugm-faculty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="ugm-comments__wrapper">

	<?php if ( have_comments() ) : ?>
		<h2 class="ugm-comments__title">
			<?php
			$ugm_comment_count = get_comments_number();
			if ( '1' === $ugm_comment_count ) {
				printf(
					/* translators: 1: title. */
					esc_html__( 'Satu komentar pada &ldquo;%1$s&rdquo;', 'ugm-faculty' ),
					'<span>' . wp_kses_post( get_the_title() ) . '</span>'
				);
			} else {
				printf(
					/* translators: 1: comment count number, 2: title. */
					esc_html( _nx( '%1$s komentar pada &ldquo;%2$s&rdquo;', '%1$s komentar pada &ldquo;%2$s&rdquo;', $ugm_comment_count, 'comments title', 'ugm-faculty' ) ),
					number_format_i18n( $ugm_comment_count ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'<span>' . wp_kses_post( get_the_title() ) . '</span>'
				);
			}
			?>
		</h2>

		<?php the_comments_navigation(); ?>

		<ol class="ugm-comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 50,
					'callback'    => 'ugm_comment_callback',
				)
			);
			?>
		</ol>

		<?php the_comments_navigation(); ?>

	<?php endif; // have_comments() ?>

	<?php
	// If comments are closed and there are comments, let's leave a little note, shall we?
	if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
		?>
		<p class="ugm-comments__closed">
			<?php esc_html_e( 'Komentar ditutup.', 'ugm-faculty' ); ?>
		</p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'title_reply'          => esc_html__( 'Tinggalkan Komentar', 'ugm-faculty' ),
			'title_reply_before'   => '<h2 id="reply-title" class="ugm-comment-form__title">',
			'title_reply_after'    => '</h2>',
			'comment_notes_before' => '<p class="ugm-comment-form__notes">' . esc_html__( 'Alamat email Anda tidak akan dipublikasikan. Kolom yang wajib diisi ditandai *', 'ugm-faculty' ) . '</p>',
			'label_submit'         => esc_html__( 'Kirim Komentar', 'ugm-faculty' ),
			'class_submit'         => 'ugm-btn ugm-btn--primary',
			'class_form'           => 'ugm-comment-form',
		)
	);
	?>

</div><!-- #comments -->
