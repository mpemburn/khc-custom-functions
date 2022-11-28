<?php
if ( !class_exists( 'ColorJar__Ad_Widget' ) ) {
	class ColorJar__Ad_Widget extends WP_Widget {
		public function __construct() {
			parent::__construct(
				'cj_ad_widget',
				__( 'ColorJar - Ad Block', 'text_domain' ),
				array(
					'description' => __( 'Outputs ad block with the specified ID', 'text_domain' ),
				)
			);
		}

		public function widget( $args, $instance ) {
			$container_id = @$instance['container_id'];
			$is_mobile_ad = @$instance['is_mobile_ad'];
			?>
			<?php if ( ( $is_mobile_ad && is_mobile_device() ) || ( !$is_mobile_ad && !is_mobile_device() ) ) : ?>
				<div class="widget ad-block">
					<?php if ( $container_id ) : ?>
						<div id="<?php echo $container_id; ?>"></div>
					<?php else : ?>
						<?php if ( current_user_can( 'manage_options' ) ) : ?>
							<p>
								A container ID is required but has not been set.
								Please go to <code>Appearance > Widgets</code>
								and set a container ID for this ad block.
							</p>
							<p>
								Note: this message is only shown for admin users.
								Standard users will not see this message when
								viewing the site.
							</p>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php
		}

		public function form( $instance ) {
			$container_id = @$instance['container_id'];
			$is_mobile_ad = @$instance['is_mobile_ad'];
			$title = ( $is_mobile_ad ) ? 'Mobile Ad' : 'Standard Ad';
			?>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $title; ?>" type="hidden">
			<p>
				<label>
					<input id="<?php echo $this->get_field_id( 'is_mobile_ad' ) ?>" name="<?php echo $this->get_field_name( 'is_mobile_ad' ); ?>" type="checkbox" value="yes" <?php if ( $is_mobile_ad ) echo 'checked'; ?>>
					<?php _e( 'Show only on mobile devices' ); ?>
				</label>
			</p>
			<p>
				<label>
					<?php _e( 'Container ID:' ); ?>
					<input class="widefat" name="<?php echo $this->get_field_name( 'container_id' ); ?>" type="text" value="<?php echo esc_attr( $container_id ); ?>">
				</label>
			</p>
			<?php
		}

		public function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['title'] = @$new_instance['title'];
			$instance['container_id'] = is_string( @$new_instance['container_id'] ) ? @$new_instance['container_id'] : @$old_instance['container_id'];
			$instance['is_mobile_ad'] = ( 'yes' === @$new_instance['is_mobile_ad'] );

			return $instance;
		}
	}
}
