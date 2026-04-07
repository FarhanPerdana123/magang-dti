<?php

class UGM_Blocks_Block_Event_List extends UGM_Blocks_Block {

	protected $name = 'event-list';

	public function __construct() {
		parent::__construct();
		
		// rest modifications.
		add_action( 'rest_api_init', [ $this, 'register_rest_custom_fields' ] );
		add_action( 'rest_event_query', [ $this, 'filter_rest_events' ], 10, 2 );
	}

	public function render_block( $attrs, $block_content ) {
		$this->attrs  = $attrs;
		$layout       = isset( $attrs['layout'] ) ? $attrs['layout'] : 'list';
		$post_count   = isset( $attrs['postCount'] ) ? intval( $attrs['postCount'] ) : 3;
		$column_count = isset( $attrs['columnCount'] ) ? intval( $attrs['columnCount'] ) : 3;

		$query = new WP_Query( array(
			'post_type' => 'event',
			'post_status' => 'publish',
			'posts_per_page' => $post_count,
			'meta_key' => 'ugm_event_date',
			'meta_value' => date( "Ymd" ),
			'meta_compare' => '>=',
			'order' => 'ASC',
			'orderby' => 'meta_value'
		) );
		ob_start();
		?>
		<div class="ugm-blocks-block-event-list" <?php $this->inline_style(); ?>>
			<div class="events events-<?php echo esc_attr( $layout ); ?> column-<?php echo esc_attr( $column_count ); ?>">
				<?php while ( $query->have_posts() ) : $query->the_post(); ?>
					<?php
						if ( $meta_date = get_post_meta( get_the_ID(), 'ugm_event_date', true ) ) {
							$event_date = substr( $meta_date, 6, 2 );
							$dt = DateTime::createFromFormat('!m', substr( $meta_date, 4, 2 ) );
							$event_month = $dt->format('M');
							$meta_date = substr( $meta_date, 0, 4 ) . '-' . substr( $meta_date, 4, 2 ) . '-' . substr( $meta_date, 6, 2 );
						}
					?>
					<article class="post post-event event">
						<div class="event-date">
							<span><?php echo esc_html( $event_date ); ?><strong><?php echo esc_html( $event_month ); ?></strong></span>
						</div>
						<div class="post-content">
							<div class="post-title">
								<h3><a href="<?php the_permalink(); ?>"><?php echo get_the_title(); ?></a></h3>
								<span class="post-date">
									<?php $short_desc = get_field('ugm_event_short_description', get_the_ID()); ?>
									<?php if(!empty($short_desc)) : ?>
										<p><?php the_field('ugm_event_short_description', get_the_ID()); ?></p>
									<?php else: ?>
										<?php echo ugm_berita_excerpt( get_the_ID(), 25 ); ?>
									<?php endif; ?>
								</span>
							</div>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
			<?php if ( $attrs['showAll']['active'] ) : ?>
				<div class="btn-box">
					<a href="<?php echo esc_url( $attrs['showAll']['url'] ); ?>" class="btn btn-more"><?php echo esc_html( $attrs['showAll']['text'] ); ?></a>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	public function register_rest_custom_fields() {
		register_rest_field( 'event', 'event_details',
			array(
				'get_callback'    => [ $this, 'event_custom_fields' ],
				'update_callback' => null,
				'schema'          => null,
			)
		);
	}

	public function event_custom_fields( $post, $field_name, $request ) {
		$metas = [
			'date_start' => get_post_meta( $post['id'], 'ugm_event_date', true ),
			'date_end'   => get_post_meta( $post['id'], 'ugm_event_date_end', true ),
			'comitee'    => get_post_meta( $post['id'], 'ugm_event_comitee', true ),
			'location'   => get_post_meta( $post['id'], 'ugm_event_location', true ),
			'contact'    => get_post_meta( $post['id'], 'ugm_event_contact', true ),
			'website'    => get_post_meta( $post['id'], 'ugm_event_website', true ),
			'short_desc' => get_post_meta( $post['id'], 'ugm_event_short_description', true )
		];
		$metas['description'] = ! empty( $metas['short_desc'] ) ? $metas['short_desc'] : ugm_berita_excerpt( $post['id'], 20 );
		if ( ! empty( $metas['date_start'] ) ) {
			$metas['event_date'] = substr( $metas['date_start'], 6, 2 );
			$dt = DateTime::createFromFormat('!m', substr( $metas['date_start'], 4, 2 ) );
			$metas['event_month'] = $dt->format('M');
			$metas['date_start'] = substr( $metas['date_start'], 0, 4 ) . '-' . substr( $metas['date_start'], 4, 2 ) . '-' . substr( $metas['date_start'], 6, 2 );
		}
		if ( ! empty( $metas['date_end'] ) ) {
			$metas['date_end'] = substr( $metas['date_end'], 0, 4 ) . '-' . substr( $metas['date_end'], 4, 2 ) . '-' . substr( $metas['date_end'], 6, 2 );
		}
		return $metas;
	}

	public function filter_rest_events( $args, $request ) {
		$args['post_status'] = 'publish';
		$args['meta_key'] = 'ugm_event_date';
		$args['meta_value'] = date( "Ymd" );
		$args['meta_compare'] = '>=';
		$args['order'] = 'ASC';
		$args['orderby'] = 'meta_value';
		return $args;
	}

}
new UGM_Blocks_Block_Event_List();