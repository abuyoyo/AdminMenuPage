<?php
/**
 * Plugin Name: WPHelper/Admin-Page
 */

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