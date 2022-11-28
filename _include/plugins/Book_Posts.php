<?php
// Site-specific plugin class
if ( ! class_exists( 'Book_Posts_Plugin' ) ) {
	class Book_Posts_Plugin extends _Base_Class {
		public function __construct() {
			parent::__construct();
		}

		/**
		 * Called when the class is first initialized
		 *
		 * @return    void
		 */
		public function on_class_init() {
			// Hooking up our function to theme setup
			$this->add_action( 'init', 'create_posttype' );

			/* Hook into the 'init' action so that the function
			* Containing our post type registration is not
			* unnecessarily executed.
			*/
			$this->add_action( 'init', 'custom_post_type', 0 );
			$this->add_shortcode( 'list-books', 'list_books_handler' );
		}

		/**
		 * Called when the "init" action is triggered
		 *
		 * @return    void
		 */
		public function on_wp_init() {
		}


		// Our custom post type function
		function create_posttype() {

			register_post_type( 'books',
				// CPT Options
				array(
					'labels'      => array(
						'name'          => __( 'Books' ),
						'singular_name' => __( 'Book' )
					),
					'public'      => true,
					'has_archive' => true,
					'rewrite'     => array( 'slug' => 'books' ),
				)
			);
		}

		/*
		* Creating a function to create our CPT
		*/

		function custom_post_type() {

			// Set UI labels for Custom Post Type
			$labels = array(
				'name'               => _x( 'Books', 'Post Type General Name' ),
				'singular_name'      => _x( 'Book', 'Post Type Singular Name' ),
				'menu_name'          => __( 'Books' ),
				'parent_item_colon'  => __( 'Parent Book' ),
				'all_items'          => __( 'All Books' ),
				'view_item'          => __( 'View Book' ),
				'add_new_item'       => __( 'Add New Book' ),
				'add_new'            => __( 'Add New' ),
				'edit_item'          => __( 'Edit Book' ),
				'update_item'        => __( 'Update Book' ),
				'search_items'       => __( 'Search Book' ),
				'not_found'          => __( 'Not Found' ),
				'not_found_in_trash' => __( 'Not found in Trash' ),
			);

			// Set other options for Custom Post Type

			$args = array(
				'label'               => __( 'books' ),
				'description'         => __( 'Book listing' ),
				'labels'              => $labels,
				// Features this CPT supports in Post Editor
				'supports'            => array(
					'title',
					'editor',
					'excerpt',
					'author',
					'thumbnail',
					'comments',
					'revisions',
					'custom-fields',
				),
				// You can associate this CPT with a taxonomy or custom taxonomy.
				'taxonomies'          => array( 'category' ),
				/* A hierarchical CPT is like Pages and can have
				* Parent and child items. A non-hierarchical CPT
				* is like Posts.
				*/
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => 5,
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'capability_type'     => 'page',
			);

			// Registering your Custom Post Type
			register_post_type( 'books', $args );

		}

		function list_books_handler() {
			$html = '';
			$cat_args = array(
				'type'                     => 'post',
				'child_of'                 => 0,
				'parent'                   => '',
				'orderby'                  => 'id',
				'order'                    => 'ASC',
				'hide_empty'               => 0,
				'hierarchical'             => 1,
				'taxonomy'                 => 'category',
				'pad_counts'               => false

			);
			$categories = get_categories($cat_args);
			// Loop over categories
			foreach ($categories as $category) {
				$category_name = $category->name;
				$post_args = array(
					'post_type' => 'books',
					'nopaging'  => true,
					'category_name'    => $category_name,
					'orderby'          => 'date',
				);
				$book_posts_query = new WP_Query( $post_args );
				if ($book_posts_query->post_count > 0) {
					$html .= '<div class="category"><h3>' . $category_name . '</h3>' . PHP_EOL;
					// Loop over book posts
					while ( $book_posts_query->have_posts() ) {
						$book_posts_query->the_post();
						$book = $book_posts_query->post;
						$book_author = get_post_meta($book->ID, '_BookAuthor')[0];
						$html .= '<a href="' . $book->guid . '">' . PHP_EOL;
						$html .= '<div class="book"><strong>' . $book->post_title . '</strong>';
						$html .= '</a>' . ' by ' . $book_author . PHP_EOL;
						$html .= '</div>' . PHP_EOL;
					}
					$html .= '</div>' . PHP_EOL;
				}
			}

			// Reset your post data to what it was before the loop
			wp_reset_postdata();

			return $html;
		}

	}

	$plugin = new Book_Posts_Plugin();

}
