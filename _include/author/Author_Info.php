<?php
if ( !class_exists( 'ColorJar__Author_Info' ) ) {
    class ColorJar__Author_Info {

        protected static $default_avatar_size = 30;

        /**
         * Constructor (PHP 5)
         *
         * @author Wes Moberly
         */
        public function __construct() {

        }

        /**
         * Constructor (PHP 4 and earlier)
         *
         * @author Wes Moberly
         */

        public function ColorJar__Author_Info() {
            $this->__construct();
        }

        public static function get_author_data() {
            if ( function_exists( 'get_coauthors' ) ) {
                $authors = get_coauthors();
                if ( !empty( $authors ) ) {
                    $author_data = reset( $authors );
                }
            } else {
                $author_ID = get_the_author_meta( 'ID' );
                if ( $author_ID ) {
                    $author_data = get_user_by( 'ID', $author_ID );
                }
            }

            if ( !empty( $author_data ) ) {
                return $author_data;
            }

            return null;
        }

        public static function is_guest_author( $author_data = null ) {
            if ( !$author_data ) {
                $author_data = self::get_author_data();
            }

            if ( empty( $author_data ) ) {
                return false;
            }

            return ( isset( $author_data->type ) && 'guest-author' == $author_data->type );
        }

        /**
         * Returns author / co-author credit section markup
         *
         * @param array $args
         * @return string
         * @author Wes Moberly
         */
        public static function get_author_credit( $args = array() ) {
            $defaults = array(
                'show_avatar' => false,
                'avatar_size' => self::$default_avatar_size,
            );
            extract( wp_parse_args( $args, $defaults ) );

            $author_data = self::get_author_data();

            if ( empty( $author_data ) ) {
                return '';
            }

            $is_guest_author = self::is_guest_author( $author_data );
            $author_ID = $author_data->ID;

            $author_name_html = $author_data->display_name;
            if ( !$is_guest_author ) {
                $author_url = get_author_posts_url( $author_ID );
                $author_name_html = "<a href='{$author_url}'><i>By</i> {$author_name_html}</a>";
            }

            $output = "<div class='author-credit'>";
                if ( $show_avatar ) {
                    $output .= "<div class='author-image'>";
                        $output .= coauthors_get_avatar( $author_data, $avatar_size );
                    $output .= "</div>";
                }
                $output .= "<div class='author-link'>";
                    $output .= $author_name_html;
                $output .= "</div>";
            $output .= "</div>";

            return $output;
        }

        /**
         * Echos author / co-author credit section markup
         *
         * @param array $args
         * @return void
         * @author Wes Moberly
         */
        public static function author_credit( $args = array() ) {
            echo self::get_author_credit( $args );
        }

        /**
         * Returns author / co-author bio info markup
         *
         * @return string
         * @author Wes Moberly
         */
        public static function get_author_bio( $args = array() ) {
            $defaults = array(
                'show_avatar' => false,
                'avatar_size' => 96,
            );
            extract( wp_parse_args( $args, $defaults ) );

            $author_data = self::get_author_data();
            if ( empty( $author_data ) ) {
                return '';
            }

            $is_guest_author = self::is_guest_author( $author_data );
            $author_ID = $author_data->ID;
            $author_name = $author_data->display_name;
            $author_description = $author_data->description;
            $author_image = $show_avatar ? coauthors_get_avatar( $author_data, $avatar_size ) : '';

            $heading_html = $author_name;
            if ( !$is_guest_author ) {
                $author_url = get_author_posts_url( $author_ID );
                $heading_html = "<a href='{$author_url}'>{$heading_html}</a>";
            }
            $heading_html = "<h4 class='description-heading'>About {$heading_html}</h4>";

            $output = '<div class="author-bio">';
                $output .= '<div class="author-image">';
                    $output .= $author_image;
                $output .= '</div>';
                $output .= '<div class="author-description">';
                    $output .= $heading_html;
                    $output .= wpautop( $author_description );
                $output .= '</div>';
            $output .= '</div>';

            return $output;
        }

        /**
         * Echos author / co-author bio info markup
         *
         * @return void
         * @author Wes Moberly
         */
        public static function author_bio( $args = array() ) {
            echo self::get_author_bio( $args );
        }

        /**
         * Returns author / co-author description markup
         *
         * @return string
         * @author Wes Moberly
         */
        public static function get_author_description( $args = array() ) {
            $defaults = array(
                'show_avatar' => false,
                'avatar_size' => self::$default_avatar_size,
            );
            extract( wp_parse_args( $args, $defaults ) );

            $author_data = self::get_author_data();
            if ( empty( $author_data ) ) {
                return '';
            }

            $is_guest_author = self::is_guest_author( $author_data );
            $author_ID = $author_data->ID;

            $output = '<div class="author-description">';
                if ( $show_avatar ) {
                    $output .= '<div class="author-image">';
                        $output .= coauthors_get_avatar( $author_data, $avatar_size );
                    $output .= '</div>';
                }
                $output .= wpautop( $author_data->description );
            $output .= '</div>';

            return $output;
        }

        /**
         * Echos author / co-author description markup
         *
         * @return void
         * @author Wes Moberly
         */
        public static function author_description( $args = array() ) {
            echo self::get_author_bio( $args );
        }
    }

    /*
    * Aliases for class helper functions
    */
    function cj_get_author_data() {
    	return ColorJar__Author_Info::get_author_data();
    }
    function cj_is_guest_author( $author_data = null ) {
    	return ColorJar__Author_Info::is_guest_author( $author_data );
    }

    /*
    * Template tag aliases for class functions
    */
    function cj_get_author_credit( $args = array() ) {
        return ColorJar__Author_Info::get_author_credit( $args );
    }
    function cj_author_credit( $args = array() ) {
        ColorJar__Author_Info::author_credit( $args );
    }

    function cj_get_author_bio( $args = array() ) {
        return ColorJar__Author_Info::get_author_bio( $args );
    }
    function cj_author_bio( $args = array() ) {
        ColorJar__Author_Info::author_bio( $args );
    }

    function cj_get_author_description( $args = array() ) {
        return ColorJar__Author_Info::get_author_description( $args );
    }
    function cj_author_description( $args = array() ) {
        ColorJar__Author_Info::author_description( $args );
    }
}
