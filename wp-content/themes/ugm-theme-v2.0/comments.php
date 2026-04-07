<?php
if ( post_password_required() ) {
	return;
}
?>
<div id="comments" class="comments-area">

	<?php

	// If comments are closed and there are comments, let's leave a little note, shall we?
	if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="no-comments"><?php __( 'Comment closed', 'ugm-theme' ); ?></p>
	<?php
	endif;

	$commenter = wp_get_current_commenter();

	$fields =  array(
	    'author' => '<div class="row"><div class="form-group col-md-6"><label for="name">Nama *</label><input type="text" id="name" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" class="form-control"></div>',
	    'email'  => '<div class="form-group col-md-6"><label for="email">Email</label> ' . ( $req ? '<span class="required">*</span>' : '' ) . '<input type="text" id="email" name="email" type="text" value="' . esc_attr( $commenter['comment_author_email'] ) . '" class="form-control"></div></div>',
	);
	 
	$comments_args = array(
	    'fields' 		=> $fields,
	    'title_reply'	=> __( 'Leave A Comment', 'ugm-theme' ),
	    'class_form' 	=> 'comment-form',
	    'comment_field'	=> '<div class="form-group"><label for="comment">'.__( 'Comment', 'ugm-theme' ).' *</label><textarea name="comment" id="comment" rows="3" class="form-control"></textarea></div>',
	    'class_submit'	=> 'btn btn-primary',
	);
	comment_form($comments_args);
	
	// You can start editing here -- including this comment!
	if ( have_comments() ) : ?>
		<div class="entry-comments">
			<div class="comment-header">
				<h3 class="widget-title comment-title"><?=__( 'Comment', 'ugm-theme' );?> (<?php echo get_comments_number() ?>)</h3>
			</div>

			<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
			<nav id="comment-nav-above" class="navigation comment-navigation" role="navigation">
				<h2 class="screen-reader-text"><?php esc_html_e( 'Navigasi komentar', 'ugm-theme' ); ?></h2>
				<div class="nav-links">

					<div class="nav-previous"><?php previous_comments_link( esc_html__( 'Older comments', 'ugm-theme' ) ); ?></div>
					<div class="nav-next"><?php next_comments_link( esc_html__( 'Newer comments', 'ugm-theme' ) ); ?></div>

				</div><!-- .nav-links -->
			</nav><!-- #comment-nav-above -->
			<?php endif; // Check for comment navigation. ?>

			<ol class="comment-list">
				<?php
					$args = array(
						'walker'            => new UGM_Walker_Comment,
						'max_depth'         => '',
						'style'             => 'ul',
						'callback'          => null,
						'end-callback'      => null,
						'type'              => 'all',
						'reply_text'        => __( 'Reply', 'ugm-theme' ),
						'page'              => '',
						'per_page'          => '',
						'avatar_size'       => 70,
						'reverse_top_level' => null,
						'reverse_children'  => '',
						'format'            => 'html5', // or 'xhtml' if no 'HTML5' theme support
						'short_ping'        => false,   // @since 3.6
					     'echo'              => true     // boolean, default is true
					);
					wp_list_comments( $args );
				?>
			</ol><!-- .comment-list -->

			<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
			<nav id="comment-nav-below" class="navigation comment-navigation" role="navigation">
				<h2 class="screen-reader-text"><?php esc_html_e( 'Navgiasi komentar', 'ugm-theme' ); ?></h2>
				<div class="nav-links">

					<div class="nav-previous"><?php previous_comments_link( esc_html__( 'Older comments', 'ugm-theme' ) ); ?></div>
					<div class="nav-next"><?php next_comments_link( esc_html__( 'Newer comments', 'ugm-theme' ) ); ?></div>

				</div><!-- .nav-links -->
			</nav><!-- #comment-nav-below -->
			<?php
			endif; // Check for comment navigation.
			?>
		</div>
	<?php
	endif; // Check for have_comments().

	?>

</div><!-- #comments -->