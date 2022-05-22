<?php
namespace WPHelper;

if ( ! trait_exists('CMB2_Override_Meta') ):
/**
 * CMB2 Options page override meta
 * 
 * Override default cmb2 meta.
 * Saves each field as separate option in wp_options table
 */
trait CMB2_Override_Meta{

	function cmb2_override_fields( $fields ){
		foreach( $fields as $field ){
			add_filter( "cmb2_override_{$field['id']}_meta_value",  [ $this, 'cmb2_override_get' ], 10, 4 );
			add_filter( "cmb2_override_{$field['id']}_meta_save",   [ $this, 'cmb2_override_save' ], 10, 4 );
			add_filter( "cmb2_override_{$field['id']}_meta_remove", [ $this, 'cmb2_override_delete' ], 10, 4 );
		}
	}

	/**
	 * cmb2_override_meta_value
	 * 
	 */
	function cmb2_override_get( $override, $args, $field_args, $field ) {
		return get_option( $field_args['field_id'], '' );
	}

	/**
	 * cmb2_override_meta_save
	 * 
	 */
	function cmb2_override_save( $override, $args, $field_args, $field ) {
		// Here, we're storing the data to the options table, but you can store to any data source here.
		// If to a custom table, you can use the $args['id'] as the reference id.
		$updated = update_option( $field_args['id'], $args['value'], false );
		return !! $updated;
	}

	/**
	 * cmb2_override_meta_remove
	 * 
	 */
	function cmb2_override_delete( $override, $args, $field_args, $field ) {
		// Here, we're removing from the options table, but you can query to remove from any data source here.
		// If from a custom table, you can use the $args['id'] to query against.
		// (If we do "delete_option", then our default value will be re-applied, which isn't desired.)
		$updated = update_option( $field_args['id'], '' );
		// $updated = update_option( $field_args['field_id'], '' );
		return !! $updated;
	}

}
endif;