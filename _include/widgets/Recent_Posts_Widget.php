<?php
if ( !class_exists( 'ColorJar__Recent_Posts_Widget' ) ) {
	class ColorJar__Recent_Posts_Widget extends WP_Widget {
		public function __construct() {
			parent::__construct(
				'cj_recent_posts_widget',
				__( 'ColorJar - Recent Posts', 'text_domain' )
			);
		}

		public function widget( $args, $instance ) {
			global $post;

			// Cache the current post object
			$current_post = $post;

			// Get recent posts
			$posts = get_posts( array(
				'posts_per_page' => $instance['numposts'],
			) );
			?>
			<?php if ( !empty( $posts ) ) : ?>
				<div class="widget">
					<h3 class="widgettitle widget-title">Recent Posts</h3>
					<div class="content recent-posts">
						<?php foreach ( $posts as $post ) : ?>
							<?php
							setup_postdata( $post );
							?>
							<div <?php post_class( 'entry-container' ); ?>>
								<div class="entry recent-post">
									<a class="entry-image" href="<?php the_permalink(); ?>">
										<?php echo get_the_post_thumbnail( $post->ID, 'square-small' ); ?>
									</a>
									<?php get_template_part( 'template-parts/_common/entry-meta' ); ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<?php
				// Reset the heading level for other entry meta sections
				?>
			<?php endif; ?>
			<?php
			// Restore current post object
			$post = $current_post;
			setup_postdata( $post );
		}

		public function form( $instance ) {
			$numposts = @$instance['numposts'] ?: 4;
			?>
			<p>
				<label>
					<?php _e( 'Number of Posts:' ); ?>
					<input class="widefat" name="<?php echo $this->get_field_name( 'numposts' ); ?>" type="number" value="<?php echo esc_attr( $numposts ); ?>">
				</label>
			</p>
			<?php
		}

		public function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['numposts'] = is_numeric( @$new_instance['numposts'] ) ? intval( @$new_instance['numposts'] ) : 4;

			return $instance;
		}
	}
}
