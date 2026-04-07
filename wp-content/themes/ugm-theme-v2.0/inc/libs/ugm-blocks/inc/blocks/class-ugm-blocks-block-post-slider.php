<?php

class UGM_Blocks_Block_Post_Slider extends UGM_Blocks_Block {

	protected $name = 'post-slider';

	public function __construct() {
		parent::__construct();
	}

	public function render_block( $attrs, $block_content ) {
		$this->attrs = $attrs;
		$query = new WP_Query( array(
			'post_type' => $attrs['query']['postType'],
			'post_status' => 'publish',
			'posts_per_page' => $attrs['query']['perPage'],
			'order' => $attrs['query']['order'],
			'orderby' => $attrs['query']['orderBy'],
			'ignore_sticky_posts' => true
		) );
		// var_dump($attrs['style']['spacing']);
		ob_start();
		?>
		<style>
			#<?php echo $attrs['id']; ?> {
				background: <?php echo esc_attr( $this->get_color( 'background' ) ); ?>;
				margin-top: <?php echo esc_attr( $this->get_margin( 'top' ) ); ?>;
				margin-bottom: <?php echo esc_attr( $this->get_margin( 'bottom' ) ); ?>;
			}
			#<?php echo $attrs['id']; ?> .post-content,
			#<?php echo $attrs['id']; ?> .post-content p,
			#<?php echo $attrs['id']; ?> .post-content .post-title a,
			#<?php echo $attrs['id']; ?> .post-content a {
				color: <?php echo esc_attr( $this->get_color( 'text' ) ); ?>;
			}
			#<?php echo $attrs['id']; ?> .post-content a:hover {
				text-decoration: underline;
			}
			#<?php echo $attrs['id']; ?> .post-content a:after {
				border-color: <?php echo esc_attr( $this->get_color( 'text' ) ); ?>;
			}
			#<?php echo $attrs['id']; ?> li button,
			#<?php echo $attrs['id']; ?> .slick-prev:hover,
			#<?php echo $attrs['id']; ?> .slick-next:hover {
				background: <?php echo esc_attr( $this->get_color( 'text' ) ); ?>;
			}
			#<?php echo $attrs['id']; ?> li.slick-active button,
			#<?php echo $attrs['id']; ?> li:hover button,
			#<?php echo $attrs['id']; ?> .slick-prev,
			#<?php echo $attrs['id']; ?> .slick-next {
				background: <?php echo esc_attr( $this->get_color( 'link' ) ); ?>;
			}
			#<?php echo $attrs['id']; ?> .slick-prev::before,
			#<?php echo $attrs['id']; ?> .slick-next::before {
				color: <?php echo esc_attr( $this->get_color( 'background' ) ); ?>;
			}
		</style>
		<div class="ugm-blocks-block-post-slider post-slider image-<?php echo esc_attr( $attrs['imagePosition'] ) ?>" id="<?php echo esc_attr( $attrs['id'] ); ?>">
			<?php while ( $query->have_posts() ) : $query->the_post(); ?>
				<article class="post" id="post-<?php the_ID(); ?>">
					<?php if ( has_post_thumbnail( get_the_ID() ) ) : ?>
						<div class="post-img">
							<a href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail( 'full' ); ?>
							</a>
						</div>
					<?php endif; ?>
					<div class="post-content">
						<div class="post-title">
							<h3><a href="<?php the_permalink(); ?>"><?php echo get_the_title(); ?></a></h3>
						</div>
						<div class="entry-content">
							<p><?php echo ugm_berita_excerpt( get_the_ID(), 25 ); ?></p>
						</div>
						<a href="<?php the_permalink(); ?>" class="btn btn-more">Baca selengkapnya</a>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
		<?php
		return ob_get_clean();
	}

}
new UGM_Blocks_Block_Post_Slider();