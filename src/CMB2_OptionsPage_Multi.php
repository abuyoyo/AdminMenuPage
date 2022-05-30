<?php
namespace WPHelper;

include_once __DIR__ . '/CMB2_Override_Meta.php';

if ( ! class_exists( 'WPHelper\CMB2_OptionsPage_Multi' ) ):
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