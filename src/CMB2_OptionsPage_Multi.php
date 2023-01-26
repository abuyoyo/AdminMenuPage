<?php
namespace WPHelper;

defined( 'ABSPATH' ) || die( 'No soup for you!' );

if ( ! class_exists( CMB2_OptionsPage_Multi::class ) ):

// Require dependency CMB2_Override_Meta
if ( ! trait_exists( CMB2_Override_Meta::class ) ) {
	require_once __DIR__ . '/CMB2_Override_Meta.php';
}

/**
 * CMB2_OptionsPage - MULTI
 * 
 * Helper class
 * Custom CMB2 Options page - saves each field as separate option in Options table.
 * Create WordPress Setting page using CMB2 Options Hookup.
 * 
 * @author  abuyoyo
 * 
 * @todo - Rename class from MULTI to something more descriptive
 */
class CMB2_OptionsPage_Multi extends CMB2_OptionsPage{
	use CMB2_Override_Meta;

	function __construct( $admin_page )
	{
		parent::__construct( $admin_page );

		$this->cmb2_override_fields( $this->fields );
	}
}
endif;