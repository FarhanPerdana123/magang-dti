<?php

class UGM_Blocks_Block_File_List extends UGM_Blocks_Block {

	protected $name = 'file-list';

	public function __construct() {
		parent::__construct();
	}

	public function render_block( $attrs, $block_content ) {
		$this->attrs  = $attrs;
		$layout       = isset( $attrs['layout'] ) ? $attrs['layout'] : 'list';
		$post_count   = isset( $attrs['postCount'] ) ? intval( $attrs['postCount'] ) : 3;
		$column_count = isset( $attrs['columnCount'] ) ? intval( $attrs['columnCount'] ) : 3;

		$query = new WP_Query( array(
			'post_type' => 'file',
			'post_status' => 'publish',
			'posts_per_page' => $post_count
		) );

		$classes = array(
			'files',
			"files-" . $layout,
			"column-" . $column_count
		);
		ob_start();
		?>
		<div class="ugm-blocks-block-file-list" <?php $this->inline_style(); ?>>
			<div class="<?php echo implode( ' ', $classes ); ?>">
				<?php while ( $query->have_posts() ) : $query->the_post(); ?>
					<article class="post">
						<div class="post-content">
							<div class="post-title">
								<h3><a href="<?php the_permalink(); ?>"><?php echo get_the_title(); ?></a></h3>
								<span class="post-date"><?php the_time('l, j F Y') ?></span>
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
		<?php
		return ob_get_clean();
	}

}
new UGM_Blocks_Block_File_List();