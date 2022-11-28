<?php
/**
 * @package Custom_Functions
 * @version Alpha
 */
/*
Plugin Name: KHC Custom Functions
Plugin URI:
Description: Theme-agnostic actions, filters, shortcodes, etc..
Author: Mark Pemburn
Version: 1.2
Author URI: http://www.sacredwheel.org/khc
*/

// Find required plugin files - classes, widgets, etc.
$base_path     = plugin_dir_path( __FILE__ );
$glob_path     = $base_path . '{_include,_require}/{*.php,*/*.php}';
$include_files = glob( $glob_path, GLOB_BRACE );

// Include any files that were found
if ( ! empty( $include_files ) ) {
	foreach ( $include_files as $file ) {
		if ( file_exists( $file ) ) {
			require_once( $file );
		}
	}
}
