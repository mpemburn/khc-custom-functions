<?php

if (!class_exists('_Base_Class')) {
    class _Base_Class {
        protected $instance = null;
        protected $content_level = 'page';
        protected $default_content_level = 'page';
        protected $js_namespace;
        /**
         * @var null
         */

        public function __construct($js_namespace = null) {

            // Make sure we only ever have one instance of the theme class
            if ($this->instance === null) {
                // Store reference to current instance
                $this->instance = $this;

                // Init function
                $this->on_class_init();
            }

            $this->js_namespace = $js_namespace;
        }

        public function on_class_init() {
            $this->log('Base class initialized');
        }

        public function get_function($function) {
            if (!is_callable($function) && is_string($function)) {
                $function = array(&$this, $function);
            }

            if (is_callable($function)) {
                return $function;
            }

            $error_message = 'Could not find function';
            $wp_errors = new WP_Error();
            $wp_errors->add('function_not_found', $error_message);
            $this->log($wp_errors);

            return $wp_errors;
        }

        public function add_action($hook_name, $callback, $priority = 10, $accepted_args = 1) {
            $this->add_hook('action', $hook_name, $callback, $priority, $accepted_args);
        }

        public function add_filter($hook_name, $callback, $priority = 10, $accepted_args = 1) {
            $this->add_hook('filter', $hook_name, $callback, $priority, $accepted_args);
        }

        public function add_hook($hook_type, $hook_name, $callback, $priority = 10, $accepted_args = 1) {
            $callback = $this->get_function($callback);

            if (!is_wp_error($callback)) {
                if ($hook_type == 'action') {
                    add_action($hook_name, $callback, $priority, $accepted_args);
                } else if ($hook_type == 'filter') {
                    add_filter($hook_name, $callback, $priority, $accepted_args);
                }
            }
        }

	    public function add_shortcode($tag, $callback) {
		    $callback = $this->get_function($callback);
		    add_shortcode($tag, $callback);
	    }

        public function remove_action($hook_name, $callback, $priority = 10, $accepted_args = 1) {
            $this->remove_hook('action', $hook_name, $callback, $priority, $accepted_args);
        }

        public function remove_filter($hook_name, $callback, $priority = 10, $accepted_args = 1) {
            $this->remove_hook('filter', $hook_name, $callback, $priority, $accepted_args);
        }

        public function remove_hook($hook_type, $hook_name, $callback, $priority = 10, $accepted_args = 1) {
            $callback = $this->get_function($callback);

            if (!is_wp_error($callback)) {
                if ($hook_type == 'action') {
                    remove_action($hook_name, $callback, $priority, $accepted_args);
                } else if ($hook_type == 'filter') {
                    remove_filter($hook_name, $callback, $priority, $accepted_args);
                }
            }
        }

        public function enqueue_script($script_name, $script_path, $deps = array(), $version = null, $in_footer = false) {
            $script_url = $this->get_asset_url($script_path, 'js');

            if ($script_url) {
                wp_enqueue_script($script_name, $script_url, $deps, $version, $in_footer);
            }
        }

        public function enqueue_style($style_name, $style_path, $deps = array(), $version = null, $media = 'all') {
            $style_url = $this->get_asset_url($style_path, 'css');

            if ($style_url) {
                wp_enqueue_style($style_name, $style_url, $deps, $version, $media);
            }
        }

        public function get_asset_url($asset_location, $asset_type) {
            // Return asset location if it appears to be a URL
            if (substr($asset_location, 0, 4) == 'http' || substr($asset_location, 0, 2) == '//') {
                return $asset_location;
            }

            // For now this function assumes that the asset is
            // located somewhere inside the parent theme folder
            // TODO: Add support for child theme assets
            $asset_url_base = get_template_directory_uri();

            // Check whether asset path is absolute or relative
            $is_absolute_path = (substr($asset_location, 0, 1) == '/');

            // If the path is relative we use the asset's folder as the root path
            // e.g. for CSS it would be /assets/css/, for JS /assets/js/, etc.
            if (!$is_absolute_path) {
                $asset_url_base .= "/assets/{$asset_type}/";
            }

            // Assemble the full asset URL and return it
            // TODO: Check if asset file exists, if not return null or WP_Error
            $asset_url = $asset_url_base . $asset_location;

            return $asset_url;
        }

        public function populate_post_type_labels($labels) {
            return $this->populate_post_type_or_taxonomy_labels($labels);
        }

        public function populate_taxonomy_labels($labels) {
            return $this->populate_post_type_or_taxonomy_labels($labels);
        }

        public function populate_post_type_or_taxonomy_labels($labels) {
            if (!is_array($labels) && !is_string($labels)) {
                return null;
            }
            if (is_string($labels)) {
                $singular_name = $labels;
                $name = $singular_name . 's';
                $labels = array(
                    'name' => $name,
                    'singular_name' => $singular_name,
                );
            }

            $name = '';
            $name_lower = '';
            $singular_name = '';
            $singular_name_lower = '';

            if (!isset($labels['name']) && isset($labels['singular_name'])) {
                $labels['name'] = $labels['singular_name'] . 's';
            }

            if (isset($labels['name'])) {
                $name = $labels['name'];
                $name_lower = strtolower($name);

                if (!isset($labels['search_items'])) {
                    $labels['search_items'] = "Search {$name}";
                }
                if (!isset($labels['popular_items'])) {
                    $labels['popular_items'] = "Popular {$name}";
                }
                if (!isset($labels['all_items'])) {
                    $labels['all_items'] = "All {$name}";
                }
                if (!isset($labels['not_found'])) {
                    $labels['not_found'] = "No {$name_lower} found";
                }
                if (!isset($labels['not_found_in_trash'])) {
                    $labels['not_found_in_trash'] = "No {$name_lower} found in trash";
                }
                if (!isset($labels['menu_name'])) {
                    $labels['menu_name'] = $name;
                }
                if (!isset($labels['separate_items_with_commas'])) {
                    $labels['separate_items_with_commas'] = "Separate {$name_lower} with commas";
                }
                if (!isset($labels['add_or_remove_items'])) {
                    $labels['add_or_remove_items'] = "Add or remove {$name_lower}";
                }
                if (!isset($labels['choose_from_most_used'])) {
                    $labels['choose_from_most_used'] = "Choose from most used {$name_lower}";
                }
            }

            if (isset($labels['singular_name'])) {
                $singular_name = $labels['singular_name'];
                $singular_name_lower = strtolower($singular_name);

                if (!isset($labels['add_new'])) {
                    $labels['add_new'] = "Add New";
                }
                if (!isset($labels['add_new_item'])) {
                    $labels['add_new_item'] = "Add New {$singular_name}";
                }
                if (!isset($labels['edit_item'])) {
                    $labels['edit_item'] = "Edit {$singular_name}";
                }
                if (!isset($labels['update_item'])) {
                    $labels['update_item'] = "Update {$singular_name}";
                }
                if (!isset($labels['new_item'])) {
                    $labels['new_item'] = "New {$singular_name}";
                }
                if (!isset($labels['new_item_name'])) {
                    $labels['new_item_name'] = $labels['new_item'];
                }
                if (!isset($labels['view_item'])) {
                    $labels['view_item'] = "View {$singular_name}";
                }
                if (!isset($labels['parent_item'])) {
                    $labels['parent_item'] = "Parent {$singular_name}";
                }
                if (!isset($labels['parent_item_colon'])) {
                    $labels['parent_item_colon'] = $labels['parent_item'] . ':';
                }
            }

            return $labels;
        }

        public function log($message, $level = E_USER_NOTICE) {
            if (true == WP_DEBUG) {
                $backtrace = debug_backtrace();
                $caller_function = $backtrace[1]['function'];
                $error_message_pre_wrap = '::' . $caller_function . ':: [ "';
                $error_message_post_wrap = '" ]';

                if (is_wp_error($message)) {
                    $errors = $message->get_error_messages();
                    foreach ($errors as $error) {
                        trigger_error($error_message_pre_wrap . $error . $error_message_post_wrap, $level);
                    }
                } else {
                    if (is_array($message) || is_object($message)) {
                        $message = print_r($message, true);
                    }
                    trigger_error($error_message_pre_wrap . $message . $error_message_post_wrap, $level);
                }
            }
        }

        /**
         * Returns true if the current user is on a mobile device, false if not
         *
         * @return    bool $is_mobile
         */
        public function is_mobile_device() {
            $regex_list = array(
                '/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',
                '/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i'
            );
            $useragent = $_SERVER['HTTP_USER_AGENT'];
            $is_mobile = (preg_match($regex_list[0], $useragent) || preg_match($regex_list[1], substr($useragent, 0, 4)));

            return $is_mobile;
        }

        /**
         * Returns the current URL
         *
         * @return    string
         */
        public function get_current_url() {
            return (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }

        /**
         * Echos the current URL
         *
         * @return    void
         */
        public function current_url() {
            echo $this->get_current_url();
        }

        /**
         * Returns the current request URI without the query string
         *
         * @return    string
         */
        public function get_current_url_slug() {
            return strtok($_SERVER['REQUEST_URI'], '?');
        }

        /**
         * Echos the current request URI without the query string
         *
         * @return    void
         */
        public function current_url_slug() {
            echo $this->get_current_url_slug();
        }
    }

    $base_class = new _Base_Class();

    if (!function_exists('is_mobile_device')) {
        function is_mobile_device() {
            global $base_class;
            return $base_class->is_mobile_device();
        }
    }

    if (!function_exists('get_current_url')) {
        function get_current_url() {
            global $base_class;
            return $base_class->get_current_url();
        }

        function current_url() {
            global $base_class;
            return $base_class->current_url();
        }
    }

    if (!function_exists('get_current_url_slug')) {
        function get_current_url_slug() {
            global $base_class;
            return $base_class->get_current_url_slug();
        }

        function current_url_slug() {
            global $base_class;
            return $base_class->current_url_slug();
        }
    }
}
