<?php
// Site-specific plugin class
if ( ! class_exists( 'KHC_Shortcodes' ) ) {
	class KHC_Shortcodes extends _Base_Class {
		public function __construct() {
			parent::__construct();
		}

		/**
		 * Called when the class is first initialized
		 *
		 * @return    void
		 */
		public function on_class_init() {
			// Register all shortcodes
			$this->add_action( 'init', 'register_shortcodes' );

		}

		/**
		 * Called when the "init" action is triggered
		 *
		 * @return    void
		 */
		public function on_wp_init() {
		}

		function embed_content_handler( $att ) {
			$permalink  = ( isset( $att['link'] ) ) ? $att['link'] : '';
			$post = get_page_by_path($permalink);

			$content = $post->post_content;
			$content = substr($content, 0, strpos($content, "<!--more-->"));
			$more_link = ' <a href="' . $post->guid . '"><strong>More&hellip;<strong></a>';
			return $content . $more_link;
		}

		function recent_posts_handler() {
			query_posts( array( 'orderby' => 'date', 'order' => 'DESC', 'showposts' => 1 ) );
			if ( have_posts() ) :
				while ( have_posts() ) : the_post();
					$return_string = '<a href="' . get_permalink() . '">' . get_the_title() . '</a>';
				endwhile;
			endif;
			wp_reset_query();

			return $return_string;
		}

		function alink_shortcode_handler( $att, $content ) {
			$output  = "ERROR: Invalid [alink] shortcode.";
			$prefix  = ( isset( $att['prefix'] ) ) ? $att['prefix'] : '';
			$inline  = ( isset( $att['inline'] ) ) ? bool_from_yn( $att['inline'] ) : false;
			$new_tab = ( isset( $att['new_tab'] ) ) ? bool_from_yn( $att['new_tab'] ) : false;
			if ( isset( $att['href'] ) ) {
				$target = ( $new_tab ) ? ' target="_blank"' : '';
				$output = $prefix . '<a href="' . $att['href'] . '"' . $target . '>' . $content . '</a>';
			}
			$output = ( ! $inline ) ? $output : '<div>' . $output . '</div>';

			return $output;
		}

		function register_shortcodes() {
			$this->add_shortcode( 'recent-posts', 'recent_posts_handler' );
			$this->add_shortcode( 'alink', 'alink_shortcode_handler' );
			$this->add_shortcode( 'embed-content', 'embed_content_handler' );
		}

	}
	$plugin = new KHC_Shortcodes();

}
