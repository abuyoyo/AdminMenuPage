<?php
/**
 * SettingsPage
 * 
 * Helper class
 * Create WordPress Setting page.
 * 
 * @author  abuyoyo
 * 
 * @todo fix parent_slug - don't override when given - test against wp-head-cleaner and iac-prefs
 * @todo to manipulate tabs see CMB2_Options_Hookup::options_page_output and 'display_cb'
 */
namespace WPHelper;

include_once __DIR__ . '/CMB2_Override_Meta.php';

if ( ! class_exists( 'WPHelper\CMB2_OptionsPage_Multi' ) ):
class CMB2_OptionsPage_Multi extends CMB2_OptionsPage{
	use CMB2_Override_Meta;
}
endif;	