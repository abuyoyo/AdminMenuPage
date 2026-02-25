<?php
/**
 * Plugin Name: WPHelper/Admin-Page
 */
defined( 'ABSPATH' ) || die( 'No soup for you!' );

if ( ! function_exists( 'wph_extra_plugin_headers' ) ):
/**
 * WPHelper Extra Plugin Headers
 *
 * Adds 'Last Update' and 'Release Date' header option to plugins
 * Used in plugin info-box
 * Can be used by all plugins
 */
function wph_extra_plugin_headers( $headers ){

	if ( empty( $headers ) ){
		$headers = [];
	}

	if ( ! in_array( 'Last Update', $headers ) ){
		$headers[] = 'Last Update';
	}

	if ( ! in_array( 'Release Date', $headers ) ){
		$headers[] = 'Release Date';
	}

	return $headers;

}
add_filter( 'extra_plugin_headers', 'wph_extra_plugin_headers' );
add_filter( 'extra_theme_headers',  'wph_extra_plugin_headers' );
endif;

if ( ! function_exists( 'wph_reduce_path' ) ):
/**
 * Custom function for WPH_DEBUG plugin-info meta-box
 * 
 * Show relative path to known directories WP_CONTENT_DIR or ABSPATH
 * 
 * WPH_KNOWN_LOCATIONS can be defined in wp-config.php
 * 
 * @since 0.32
 * @since 0.42 Move function from /tpl/meta-box-wphelper-debug.php => /wph_admin_page.php
 * @since 0.42 Support WPH_KNOWN_LOCATIONS
 */
function wph_reduce_path($path) {
	return str_replace(
		array_map(
			fn($reduce_path) => wp_normalize_path( trailingslashit( $reduce_path ) ), // normalize + trailing slash all reduce paths
			( // allow reducing custom locations
				defined( 'WPH_KNOWN_LOCATIONS' ) // can be either array of strings or single string
				? array_map(
					'dirname', // we want to show the basename for known locations
					is_array( WPH_KNOWN_LOCATIONS ) ? WPH_KNOWN_LOCATIONS : [ WPH_KNOWN_LOCATIONS ]
				)
				: []
			) // must be in parentheses to work with + operand
			+
			[ // default locations
				WP_CONTENT_DIR,
				ABSPATH,
			]
		),
		'',
		wp_normalize_path( trailingslashit( $path ) )
	);
}
endif;
