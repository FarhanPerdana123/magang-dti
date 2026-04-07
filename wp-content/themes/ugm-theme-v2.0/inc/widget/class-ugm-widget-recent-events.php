<?php

/**
 * Custom Recent Event Widget
 *
 * 	Additional info about author post will be showed
 *
 * 	@since 1.0
 */
class UGM_Widget_Recent_Events extends WP_Widget {

	/**
	 * 	Set recent post instance
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' 	=> 'widget-event',
			'description' 	=> __( "Tampilkan Daftar Agenda", 'ugm-theme' )
		);
		parent::__construct( 'recent-events', __( 'Recent Events', 'ugm-theme'  ), $widget_ops );
		$this->alt_option_name = 'widget_recent_events';
	}

	/**
	 * 	Outputs the content for the custom Recent Events
	 *  @param  array $args     Argument for widgets
	 *  @param  array $instance Widget settings data
	 *  @return -
	 */
	public function widget( $args, $instance ) {

		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Recent Events' );

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
		if ( ! $number )
			$number = 5;

		$r = new WP_Query( apply_filters( 'widget_posts_args', array(
			'posts_per_page'      => $number,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'post_type'			  => 'event',
			'meta_key'			  => 'ugm_event_date',
			'orderby'			  => array('meta_value_num' => 'ASC'),
			'meta_query'		  => array(
				array(
					'key'		=> 'ugm_event_date',
					'value'		=> (int)date('Ymd'),
					'compare'	=> '>='
				)
			)
		) ) );

		if ( $r->have_posts() ) :

			echo $args['before_widget'];

			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}

		?>

		<ul id="recent-event-list" class="event-list">
			<?php while ( $r->have_posts() ) : $r->the_post(); ?>
				<li>

					<?php $date = get_field('ugm_event_date', get_the_ID()); ?>

					<a href="<?php the_permalink() ?>">
						<span class="event-date"><?php echo date_i18n('d', strtotime($date)); ?><strong><?php echo date_i18n('M', strtotime($date)); ?></strong></span>
						<span class="event-title"><?php the_title() ?></span>
					</a>

				</li>
			<?php endwhile; ?>
		</ul>
		<div class="btn-box">
			<a href="<?php echo get_post_type_archive_link('event'); ?>" class="btn btn-more"><?php esc_html_e( 'All Events', 'ugm-theme' ) ?></a>
		</div>

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
	<?php
	}
}