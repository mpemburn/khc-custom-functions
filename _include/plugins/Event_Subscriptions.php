<?php
// Site-specific plugin class
if ( ! class_exists( 'Event_Subscriptions' ) ) {
	class Event_Subscriptions extends _Base_Class {
		public function __construct() {
			parent::__construct();
		}

		#*** Provide support for both instance and static calls
		public function __call($name, $arguments) {
			if ($name === 'print_subscribers'){
				return static::print_subscribers($this);
			}
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
			$this->add_action( 'init', array($this, 'custom_post_type'), 0 );
			$this->add_action( 'save_post', array($this, 'update_subscriber_metadata') );
			$this->add_action( "admin_init", "add_subscriber_meta_boxes" );
            $this->add_action( 'wp_ajax_nopriv_download_subscribers', [$this, 'download_subscriber_list'] );
            $this->add_action( 'wp_ajax_download_subscribers', [$this, 'download_subscriber_list'] );
		}

		// Our custom post type function
		public function create_posttype() {

			register_post_type( 'event_subscriptions',
				// CPT Options
				array(
					'labels'      => array(
						'name'          => __( 'Subscriptions' ),
						'singular_name' => __( 'Subscription' )
					),
					'public'      => true,
					'has_archive' => true,
					'rewrite'     => array( 'slug' => 'event_subscriptions' ),
				)
			);
		}

		/*
		* Creating a function to create our CPT
		*/

		public function custom_post_type() {

			// Set UI labels for Custom Post Type
			$labels = array(
				'name'               => _x( 'Event Subscriptions', 'Post Type General Name' ),
				'singular_name'      => _x( 'Event Subscription', 'Post Type Singular Name' ),
				'menu_name'          => __( 'Subscriptions' ),
				'parent_item_colon'  => __( 'Parent Subscription' ),
				'all_items'          => __( 'All Subscriptions' ),
				'view_item'          => __( 'View Subscription' ),
				'add_new_item'       => __( 'Add New Subscription' ),
				'add_new'            => __( 'Add New' ),
				'edit_item'          => __( 'Edit Subscription' ),
				'update_item'        => __( 'Update Subscription' ),
				'search_items'       => __( 'Search Subscription' ),
				'not_found'          => __( 'Not Found' ),
				'not_found_in_trash' => __( 'Not found in Trash' ),
			);

			// Set other options for Custom Post Type

			$args = array(
				'label'               => __( 'event_subscriptions' ),
				'description'         => __( 'Subscription listing' ),
				'labels'              => $labels,
				// Features this CPT supports in Post Editor
				'supports'            => array(
					'title',
					'thumbnail',
				),
				// You can associate this CPT with a taxonomy or custom taxonomy.
				//'taxonomies'          => array( 'category' ),
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
				'menu_icon'           => 'dashicons-admin-users',
				'menu_position'       => 5,
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'capability_type'     => 'page',
			);

			// Registering your Custom Post Type
			register_post_type( 'event_subscriptions', $args );

		}

        public function download_subscriber_list()
        {
            $post_id = $_REQUEST['postID'];

            $subscriber_array = get_post_meta( $post_id, "event-subscribers", true );

            if ( !empty( $subscriber_array ) ) {
                $file = fopen('php://output', 'w');

                fputcsv($file, ['Name', 'Email']);

                foreach ($subscriber_array as $subscriber ) {
                    if ($subscriber['name'] && $subscriber['active'] === 'yes') {
                        fputcsv($file, [$subscriber['name'], $subscriber['email']]);
                    }
                }

                $response = fgetcsv($file);

                fclose($file);

                // response output
                echo $response;
            }

            wp_die();
		}

		public static function get_subscribers() {
			global $post;

			$subscriber_array = get_post_meta( $post->ID, "event-subscribers", true );
			echo '<div>';
			echo '<ul id="subscribers">';
			$subscriber_count = 0;
			if ( !empty( $subscriber_array ) ) {
				foreach ( (array) $subscriber_array as $subscriber ) {
					echo Event_Subscriptions::print_subscribers( $subscriber_count, $subscriber );
					$subscriber_count++;
				}
			} else {
				$subscriber_array = array();
			}
			echo '</ul>';
			$print = Event_Subscriptions::print_subscribers('~count~', $subscriber_array);
			?>
			<span id="here"></span>
			<span class="add_subscriber"><?php echo __( 'Add Subscriber' ); ?></span>
			<div class="download_csv"><button id="download_button">Download CSV</button></div>
			<script>
				var subscriber_count = <?php echo $subscriber_count; ?>;
				jQuery(document).ready(function ($) {
					$(".add_subscriber").click(function () {
						var sub = <?php echo "'" . $print . "'"; ?>;
						$('#subscribers').append(sub.replace(/~count~/g, subscriber_count));
						subscriber_count++;
						return false;
					});
					$(".remove_subscriber").off().on('click', function () {
						$(this).parent().remove();
					});
					$('#subscribers').sortable();
					$('#download_button').on('click', function (evt) {
					    evt.preventDefault();

                        jQuery.ajax({
                            type: "post",
                            url: "/wp-admin/admin-ajax.php",
                            data: {
                                action:'download_subscribers',
                                postID: <?php echo $post->ID; ?>

                            },
                            success: function(csv){
                                var rightNow = new Date();
                                var dateString = rightNow.toISOString().slice(0,16).replace(/[-:T]+/g,"");
                                var link = document.createElement('a');
                                link.download = 'subscribers_' + dateString + '.csv';
                                link.href = 'data:text/csv;charset=utf-8,' + csv;
                                link.click();
                            },
                            error: function(msg){
                                console.log(msg);
                            }
                        });
                    });
				});
			</script>
			<style>
				#subscribers {
					list-style: none;
				}
				#subscribers label {
					cursor: move;
				}
				.add_subscriber, .remove_subscriber {
					cursor: pointer;
				}
                .download_csv {
                    text-align: right;
                }
			</style>
			<?php
			echo '</div>';
		}

		public static function print_subscribers( $cnt, $subscriber_array = null ) {
			$subscriber_name = isset($subscriber_array) && array_key_exists('name', (array) $subscriber_array) ? $subscriber_array['name'] : '';
			$subscriber_email = isset($subscriber_array) && array_key_exists('email', (array) $subscriber_array) ? $subscriber_array['email'] : '';
			$subscriber_active = isset($subscriber_array) && array_key_exists('active', (array) $subscriber_array) ? $subscriber_array['active'] : '';
			$is_yes = ($subscriber_active == 'yes') ? 'selected' :  '';
			$is_no = ($subscriber_active == 'no') ? 'selected' :  '';

			$item  = '<li>';
			$item  = '	<label>&#9679; </label>';
			$item .= '  <input type="text" placeholder="Enter name" name="event-subscribers[' . $cnt . '][name]" value="' . $subscriber_name . '" style="width: 20%;"/>';
			$item .= '  <input type="text" placeholder="Enter email" name="event-subscribers[' . $cnt . '][email]" value="' . $subscriber_email . '" style="width: 30%;"/>';
			$item .= '  Active? <select name="event-subscribers[' . $cnt . '][active]" value="' . $subscriber_active . '">';
			$item .= '  <option value="yes" ' . $is_yes .  '>Yes</option>';
			$item .= '  <option value="no"" ' . $is_no .  '>No</option>';
			$item .= '  </select>';
			$item .= '	<span class="remove_subscriber">&nbsp;&nbsp;Remove</span>';
			$item .= '<li>';

			return $item;
		}


		/* Save meta data (custom fields) when you save a post */
		public function update_subscriber_metadata() {
			global $post;
			if (! $post) {
			    return;
            }

			$has_data = false;
			$fields = array( 'event-subscribers' );
			foreach ( $fields as $field ) {
				if ( isset( $_POST[ $field ] ) ) {
					$value = $_POST[ $field ];
					update_post_meta( $post->ID, $field, $value );
					$has_data = true;
				}
			}
			if (!$has_data) {
				update_post_meta( $post->ID, 'event-subscribers' , null );
			}
		}

		public function add_subscriber_meta_boxes() {
			$post_type = 'event_subscriptions';
			add_meta_box( "get_subscriber_id", "Subscriber List:", array('Event_Subscriptions', "get_subscribers"), $post_type, "advanced", "high" );
		}
	}

	$plugin = new Event_Subscriptions();

}
