<?php

class UGM_Walker_Comment extends Walker_Comment {

	var $tree_type = 'comment';
	var $db_fields = array( 'parent' => 'comment_parent', 'id' => 'comment_ID' );

	function __construct() {
		/* Empty consctruct */
	}

	/* Start Comment List for children list */
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$GLOBALS['comment_depth'] = $depth + 1;
		echo '<ul class="children">';
	}

	/* End Comment List for children list comments */
	function end_lvl( &$output, $depth = 0, $args = array() ) {
		$GLOBALS['comment_depth'] = $depth + 1;
		echo '</ul><!-- /.children -->';
	}

	/* Start Comment List */
	function start_el( &$output, $comment, $depth = 0, $args = array(), $id = 0 ) {

		$depth++;
		$GLOBALS['comment_depth'] 	= $depth;
		$GLOBALS['comment'] 		= $comment;
		$parent_class 				= ( empty( $args['has_children'] ) ? '' : 'parent' ); ?>

		<li <?php comment_class( $parent_class ); ?> id="comment-<?php comment_ID() ?>">
			<div id="comment-<?php comment_ID() ?>" class="comment-body comment-wrap single-post">

				<div class="comment-avatar">
					<?php echo ( $args['avatar_size'] != 0 ? get_avatar( $comment, $args['avatar_size'] ) :'' ); ?>
				</div>

				<div id="comment-content-<?php comment_ID(); ?>" class="comment-content post-content">

					<!-- Not approved -->
					<?php if ( ! $comment->comment_approved ) : ?>

					<em class="comment-awaiting-moderation">
						<?php _e( 'Your comment is awaiting moderation.', 'ugm-theme' ); ?>
					</em>

					<!-- Approved -->
					<?php else : ?>

						<div class="comment-author">
							<?php echo get_comment_author_link(); ?>
							<span class="meta-time">
								<?php $this->get_lapse_of_time( get_comment_date( 'Y-m-d' ), get_comment_time( 'H:i:s' ) ); ?>
							</span>
						</div>

						<?php comment_text(); ?>

						<div class="comment-meta comment-meta-data">
							<?php edit_comment_link( 'Edit' ); ?>
						</div><!-- /.comment-meta -->

						<div class="reply"> 
							<?php

							$reply_args = array(
								'add_below' => 'comment',
								'depth' 	=> $depth,
								'max_depth' => $args['max_depth']
							);

							comment_reply_link( array_merge( $args, $reply_args ) );

							?>
						</div><!-- /.reply -->

					<?php endif; ?>

				</div><!-- /.comment-content -->

			</div><!-- /.comment-body -->

	<?php }

	/* End Comment List */
	function end_el( &$output, $comment, $depth = 0, $args = array() ) { ?>

		</li><!-- /#comment-' . get_comment_ID() . ' -->

	<?php }

	/* Get lapse time between current time and comment time */
	function get_lapse_of_time( $date = false, $time = false ) {

		$datetime 	  = $date . ' ' . $time;
		$comment_time = date_create( date( 'Y-m-d H:i:s', strtotime( $datetime ) ) );
		$current_time = date_create( date( 'Y-m-d H:i:s' ) );

		$time_format  = array(
			'y'	=> __( 'years', 'ugm-theme' ),
			'm' => __( 'months', 'ugm-theme' ),
			'd'	=> __( 'days', 'ugm-theme' ),
			'h'	=> __( 'hours', 'ugm-theme' ),
			'i'	=> __( 'minutes', 'ugm-theme' )
		);

		$diff = date_diff( $comment_time, $current_time );

		foreach ( $diff as $key => $value ) {
			if ( $value != 0 ) {

				if ( $key == 's' ) {
					echo  __( 'just a moment', 'ugm-theme' );
					return false;
				}

				$format = $time_format[$key];
				// $format = ( $value > 1 ) ? $format . 's' : $format;
				echo sprintf( __( '%1$s %2$s ago', 'ugm-theme' ), $value, $format );

				return false;
			}
		}

	}

	/** DESTRUCTOR
	 * I'm just using this since we needed to use the constructor to reach the top
	 * of the comments list, just seems to balance out nicely:) */
	function __destruct() {
		echo '</ul><!-- /#comment-list -->';
	}
}