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

use CMB2;
use CMB2_Options_Hook;

// include_once __DIR__ . '/CMB2_Override_Meta.php';

if ( ! class_exists( 'WPHelper\CMB2_OptionsPage' ) ):
class CMB2_OptionsPage{
	// use CMB2_Override_Meta;

	/**
	 * @var AdminPage $admin_page
	 */
	public $admin_page;

	/**
	 * @var CMB2 $cmb
	 */
	private $cmb;

	/**
	 * @param AdminPage $admin_page
	 */
	function __construct( $admin_page ){

		$this->admin_page = $admin_page;

		$admin_options = $this->admin_page->options();

		$settings = $admin_options['settings'];

		$settings['object_types'] = array( 'options-page' );
		$settings['display_cb'] = $settings['display_cb'] ?: [ $this, 'options_page_output' ];

		$settings['option_key']  = $settings['option_key']  ?? ( $settings['option_name'] ?? ( $settings['id'] ?? $admin_options['slug'] ) );
		$settings['title']       = $settings['title']       ?? $admin_options['title'];
		$settings['menu_title']  = $settings['menu_title']  ?? $admin_options['menu_title'];
		$settings['parent_slug'] = $settings['parent_slug'] ?? $admin_options['parent'];
		$settings['position']    = $settings['position']    ?? $admin_options['position'];
		$settings['icon_url']    = $settings['icon_url']    ?? $admin_options['icon_url'];
		$settings['capability']  = $settings['capability']  ?? $admin_options['capability'];

		/**
		 * CMB2 must have admin menu page slug same as option key :(
		 */
		$settings['id'] = $settings['option_key'];
		
		unset( $settings['option_name'] );

		/**
		 * CMB2 only accepts url slug
		 * 
		 * @todo account for all menu slugs.
		 */
		// wp_die_arr($settings['parent_slug']);
		switch ( $settings['parent_slug'] ) {
			case 'options':
				$settings['parent_slug'] = 'options-general.php';
			break;
			case 'tools':
				$settings['parent_slug'] = 'tools.php';
			break;
			case null:
			break;
			default:
				// $settings['parent_slug'] = 'admin.php';
			break;
		}
		
		if ( $admin_options['render'] == 'cmb2-tabs' ){
			if ( ! isset($settings['tab_group'] ) ){
				if ( isset($settings['parent_slug'] ) ){
					$settings['tab_group'] = $settings['parent_slug'];
				}else{
					$settings['tab_group'] = $settings['id'];
				}
			}
			if ( ! isset($settings['tab_title'] ) ){
				$settings['tab_title'] = $settings['menu_title'];
			}
		}

		if ( isset( $settings['fields'] ) ){
			$this->fields = $settings['fields'];
			unset( $settings['fields'] );
		}

		if ( isset( $settings['sections'] ) ){
			$this->fields = [];
			foreach ( $settings['sections'] as $section ){
				$title_field = [];
				if ( $id = $section['id'] ?? $section['slug'] ){
					$title_field['id'] = $id;
				}
				if ( $name = $section['name'] ?? $section['title'] ){
					$title_field['name'] = $name;
				}
				if ( $desc = $section['desc'] ?? $section['description'] ){
					$title_field['desc'] = $desc;
				}
				if ( ! empty($title_field)){
					$title_field['type'] = 'title';
					$this->fields[] = $title_field;
				}

				foreach ($section['fields'] as $field){
					$field = $this->convert_field_to_cmb2_field($field);
					$this->fields[] = $field;
				}
			}
			unset( $settings['sections'] );
		}


		/**
		 * Special provision for cmb2-switch
		 */
		if ( ! class_exists( 'CMB2_Switch_Button' ) ){
			array_walk(
				$this->fields,
				function( &$field ){
					if ( $field['type'] == 'switch'){
						$field['type'] = 'checkbox';
					}
				}
			);
		}

		

		$this->cmb2_options = $settings;

		// register parent pages before sub-menu pages
		$priority = empty( $settings['parent_slug'] ) ? 9 : 10;

		add_action( 'cmb2_admin_init', [ $this, 'register_metabox' ], $priority );
	}

	public function register_metabox(){

		$this->cmb = new CMB2( $this->cmb2_options );

		foreach( $this->fields as $field ){
			$this->cmb->add_field( $field );
		}
	}

	/**
	 * Display options-page output. To override, set 'display_cb' box property.
	 * 
	 * @param CMB2_Options_Hook $hookup - instance of Options Page Hookup class (caller of this function)
	 * 
	 * @see CMB2_Options_Hook
	 */
	public function options_page_output( $hookup ) {
		include $this->admin_page->get_render_tpl();
	}


	private function convert_field_to_cmb2_field( $field ){
		$field['id'] =   $field['id'] ?? $field['slug'];
		$field['name'] = $field['name'] ?? $field['title'];
		$field['desc'] = $field['desc'] ?? $field['description'];
		
		unset( $field['slug'] );
		unset( $field['title'] );
		unset( $field['description'] );


		return $field;
	}
}
endif;