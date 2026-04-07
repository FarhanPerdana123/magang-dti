<?php

class UGM_Blocks_Block_Post_List extends UGM_Blocks_Block {

	protected $name = 'post-list';

	public function __construct() {
		parent::__construct();
	}

	public function render_block( $attrs, $block_content ) {
		$this->attrs  = $attrs;
		$layout       = isset( $attrs['layout'] ) ? $attrs['layout'] : 'list';
		$column_count = isset( $attrs['columnCount'] ) ? intval( $attrs['columnCount'] ) : 3;

		$query = new WP_Query( array(
			'post_type' => $attrs['query']['postType'],
			'post_status' => 'publish',
			'posts_per_page' => $attrs['query']['perPage'],
			'order' => $attrs['query']['order'],
			'orderby' => $attrs['query']['orderBy'],
			'ignore_sticky_posts' => true
		) );
		ob_start();

		$classes = array(
			'posts',
			"posts-" . $layout,
			"column-" . $column_count
		);
		if ( $attrs['show']['thumbnail'] ) {
			$classes[] = "show-thumbnail";
		}
		if ( $attrs['show']['date'] ) {
			$classes[] = "show-date";
		}
		if ( $attrs['show']['excerpt'] ) {
			$classes[] = "show-excerpt";
		}
		if ( $attrs['showAll']['active'] ) {
			$classes[] = "show-link";
		}
		?>
		<div class="ugm-blocks-block-post-list" <?php $this->inline_style(); ?>>
			<div class="<?php echo implode( ' ', $classes ); ?>">
				<?php while ( $query->have_posts() ) : $query->the_post(); ?>
					<article class="post">
						<?php if ( $attrs['show']['thumbnail'] ) : ?>
							<div class="post-img">
								<a href="<?php the_permalink(); ?>">
									<?php
									if ( has_post_thumbnail() ) {
										the_post_thumbnail('ugm-archive-thumbnail-small');
									}
									?>
								</a>
							</div>
						<?php endif; ?>
						<div class="post-content">
							<div class="post-title">
								<h3><a href="<?php the_permalink(); ?>"><?php echo get_the_title(); ?></a></h3>
								<?php if ( $attrs['show']['date'] ) : ?>
									<span class="post-date"><?php the_time('l, j F Y') ?></span>
								<?php endif; ?>
							</div>
							<?php if ( $attrs['show']['excerpt'] ) : ?>
								<div class="entry-content"><p><?php echo ugm_berita_excerpt( get_the_ID(), 20 ); ?></p></div>
							<?php endif; ?>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
			<?php if ( $attrs['showAll']['active'] ) : ?>
				<div class="btn-box">
					<a href="<?php echo esc_url( $attrs['showAll']['url'] ); ?>" class="btn btn-more"><?php echo esc_html( $attrs['showAll']['text'] ); ?></a>
				</div>
			<?php endif; ?>
		</div> <?php
		return ob_get_clean();
	}

}
new UGM_Blocks_Block_Post_List();