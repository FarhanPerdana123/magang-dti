<?php

/**
 * Custom Recent Post Widget
 *
 * 	Additional info about author post will be showed
 *
 * 	@since 1.0
 */
class UGM_Widget_Recent_Posts extends WP_Widget {

	/**
	 * 	Set recent post instance
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' 	=> 'widget-recent',
			'description' 	=> __( "Your site's most recent  Posts")
		);
		parent::__construct( 'recent-posts', __( 'Recent Posts','ugm-theme'), $widget_ops );
		$this->alt_option_name = 'widget_recent_entries';
	}

	/**
	 * 	Outputs the content for the custom Recent Posts
	 *  @param  array $args     Argument for widgets
	 *  @param  array $instance Widget settings data
	 *  @return -
	 */
	public function widget( $args, $instance ) {

		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Recent Posts','ugm-theme');

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
		if ( ! $number )
			$number = 5;

		$show_thumbnail = isset( $instance['show_thumbnail'] ) ? $instance['show_thumbnail'] : false;
		$show_date 	 = isset( $instance['show_date'] ) ? $instance['show_date'] : false;
		$show_author = isset( $instance['show_author'] ) ? $instance['show_author'] : false;

		$r = new WP_Query( apply_filters( 'widget_posts_args', array(
			'posts_per_page'      => $number,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true
		) ) );

		if ( $r->have_posts() ) :

			echo $args['before_widget'];

			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}

		?>

		<ul id="recent-post-list" class="post-list">
			<?php while ( $r->have_posts() ) : $r->the_post(); ?>
				<li>
					<?php if ( $show_thumbnail ) : ?>
						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink() ?>"><?php the_post_thumbnail('thumbnail', array('class'=>'wp-post-img')); ?></a>
						<?php endif; ?>
					<?php endif; ?>

					<a class="post-title" href="<?php the_permalink(); ?>">
						<?php get_the_title() ? the_title() : the_ID(); ?>
					</a>

					<div class="entry-meta">

						<?php if ( $show_date ) : ?>
							<span class="meta-date"><?php echo get_the_date(); ?></span>
						<?php endif; ?>

						<?php if ( $show_author ) : ?>
							<span class="author"> <?php _e( 'by', 'ugm-theme' ); ?> <?php the_author_posts_link(); ?></span>
						<?php endif; ?>

					</div>
				</li>
			<?php endwhile; ?>
		</ul>

		<?php

			echo $args['after_widget'];

			/* Reset the global $the_post as this query will have stomped on it */
			wp_reset_postdata();

		endif;
	}

	/**
	 * 	Handles updating the settings for the current Recent Posts widget instance.
	 *
	 *  @param 	array $new_instance 	New settings for this instance as input by the user via WP_Widget::form().
	 *  @param 	array $old_instance 	Old settings for this instance.
	 *  @return array 					Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance 				 	= $old_instance;
		$instance['title'] 		 	= sanitize_text_field( $new_instance['title'] );
		$instance['number'] 	 	= (int) $new_instance['number'];
		$instance['show_thumbnail']  = isset( $new_instance['show_thumbnail'] ) ? (bool) $new_instance['show_thumbnail'] : false;
		$instance['show_date'] 	 	= isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
		$instance['show_author'] 	= isset( $new_instance['show_author'] ) ? (bool) $new_instance['show_author'] : false;
		return $instance;
	}

	/**
	 * Outputs the settings form for the Recent Posts widget.
	 *
	 * @param 	array $instance 	Current settings.
	 */
	public function form( $instance ) {
		$title     	 	= isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number    	 	= isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$show_thumbnail	= isset( $instance['show_thumbnail'] ) ? (bool) $instance['show_thumbnail'] : false;
		$show_date 	 	= isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;
		$show_author 	= isset( $instance['show_author'] ) ? (bool) $instance['show_author'] : false;
	?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">
				<?php _e( 'Title:', 'ugm-theme' ); ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>">
				<?php _e( 'Number of posts to show:', 'ugm-theme' ); ?>
			</label>
		<input class="tiny-text" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1" value="<?php echo $number; ?>" size="3" /></p>

		<p><input class="checkbox" type="checkbox"<?php checked( $show_thumbnail ); ?> id="<?php echo $this->get_field_id( 'show_thumbnail' ); ?>" name="<?php echo $this->get_field_name( 'show_thumbnail' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_thumbnail' ); ?>"><?php _e( 'Display thumbnail?', 'ugm-theme' ); ?></label></p>

		<p><input class="checkbox" type="checkbox"<?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date?', 'ugm-theme' ); ?></label></p>

		<!-- <p><input class="checkbox" type="checkbox"<?php checked( $show_author ); ?> id="<?php echo $this->get_field_id( 'show_author' ); ?>" name="<?php echo $this->get_field_name( 'show_author' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_author' ); ?>"><?php _e( 'Display author post?', 'ugm-theme' ); ?></label></p> -->
	<?php
	}
}