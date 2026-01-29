<?php
namespace WPHelper;

defined( 'ABSPATH' ) || die( 'No soup for you!' );

use CMB2;
use CMB2_Options_Hookup;

if ( ! class_exists( CMB2_OptionsPage::class ) ):
/**
 * CMB2_OptionsPage
 * 
 * Helper class
 * Create WordPress Setting page using CMB2 Options Hookup.
 * 
 * @author  abuyoyo
 * 
 * @see CMB2_Options_Hookup::options_page_output and 'display_cb' - to manipulate tabs
 */
class CMB2_OptionsPage{

	/**
	 * @var AdminPage $admin_page
	 */
	public $admin_page;

	/**
	 * @var array $fields
	 */
	protected $fields;

	/**
	 * @var CMB2 $cmb
	 */
	private $cmb;

	/**
	 * @var array $cmb2_options
	 */
	protected $cmb2_options;

	/**
	 * @param AdminPage $admin_page
	 */
	function __construct( $admin_page ){

		$this->admin_page = $admin_page;

		$admin_options = $this->admin_page->options();

		$settings = $admin_options['settings'];

		$settings['object_types'] = [ 'options-page' ];
		$settings['display_cb']  ??= $admin_options['render_cb'] ?? [ $this, 'options_page_output' ];

		$settings['option_key']  ??= $settings['option_name'] ?? $settings['id'] ?? $admin_options['slug'];
		$settings['title']       ??= $admin_options['title'];
		$settings['menu_title']  ??= $admin_options['menu_title'];
		$settings['submenu_title'] ??= $admin_options['submenu_title'] ?? $admin_options['tab_title'] ?? $settings['tab_title'] ?? $admin_options['menu_title'];
		$settings['tab_group']   ??= $admin_options['tab_group'];
		$settings['tab_title']  ??= $admin_options['tab_title'] ?? $settings['submenu_title'] ?? $settings['menu_title'];
		$settings['parent_slug'] ??= $admin_options['parent'];
		$settings['position']    ??= $admin_options['position'];
		$settings['icon_url']    ??= $admin_options['icon_url'];
		$settings['capability']  ??= $admin_options['capability'];

		/**
		 * CMB2 must have admin menu page slug same as option key :(
		 */
		$settings['id'] = $settings['option_key'];
		
		unset( $settings['option_name'] );
		
		if ( $admin_options['render'] == 'cmb2-tabs' ){
			$settings['tab_group'] ??= $settings['parent_slug'] ?? $settings['id'];
		}

		$this->fields = $settings['fields'] ?? [];
		/**
		 * @todo revisit this - might not need to unset fields
		 */
		if ( isset( $settings['fields'] ) ){
			unset( $settings['fields'] );
		}

		/**
		 * If args are formatted for SettingsPage we convert to CMB2 options format
		 * Convert nested sections=>fields to straight title, fields, title, fields.
		 * 
		 * @todo export this to dedicated method
		 */
		if ( isset( $settings['sections'] ) ){

			// CMB2 expects "flat" fields array. With titles as separating fields.
			$this->fields = [];

			foreach ( $settings['sections'] as $section ){

				// skip if we already have a CMB2 title field
				if ( current( $section['fields'] )['type'] !== 'title' ) {
					/**
					 * Create CMB2 title field from section args.
					 * 
					 * We expect section to have regular slug/title/description fields
					 * But we also accept CMB2 fields id/name/desc
					 */
					$title_field = [];
					if ( $id = $section['id'] ?? $section['slug'] ) {
						$title_field['id'] = $id;
					}
					if ( $name = $section['name'] ?? $section['title'] ) {
						$title_field['name'] = $name;
					}
					if ( $desc = $section['desc'] ?? $section['description'] ) {
						$title_field['desc'] = $desc;
					}
					if ( ! empty( $title_field ) ) {
						$title_field['type'] = 'title';
						$this->fields[] = $title_field;
					}
				}

				// add section fields to "flat" array.
				foreach ( $section['fields'] as $field ) {
					$this->fields[] = $this->convert_field_to_cmb2_field($field);
				}

			}
			unset( $settings['sections'] );
		}

		/**
		 * Special provision for cmb2-switch
		 */
		if ( ! class_exists( 'CMB2_Switch_Button' ) ) {
			array_walk(
				$this->fields,
				function( &$field ){
					if ( $field['type'] == 'switch'){
						$field['type'] = 'checkbox';
					}
				}
			);
		}

		// re-insert fields back into settings
		$settings['fields'] = $this->fields;

		$this->cmb2_options = $settings;

		// register parent pages before sub-menu pages
		$priority = empty( $settings['parent_slug'] ) ? 9 : 10;
		$cmb2_init_hook = ( ! is_admin() && ( $settings['allow_on_front'] ?? false ) ) ? 'cmb2_init' : 'cmb2_admin_init';
		add_action( $cmb2_init_hook, [ $this, 'register_metabox' ], $priority );

		// If parent && has subtitle - remove first submenu and replace menu_title parameter.
		if (
			empty( $settings['parent_slug'] )
			&&
			! empty( $settings['submenu_title'] )
			&&
			$settings['menu_title'] != $settings['submenu_title']
		){
			add_action('admin_menu', [ $this, 'replace_submenu_title'], 11 );
		}

	}

	public function register_metabox(){
		$this->cmb = new CMB2( $this->cmb2_options );
	}

	/**
	 * Display options-page output. To override, set 'display_cb' box property.
	 * 
	 * @param CMB2_Options_Hookup $hookup - instance of Options Page Hookup class (caller of this function)
	 * 
	 * @see CMB2_Options_Hookup
	 */
	public function options_page_output( $hookup ) {
		
		$options = $this->admin_page->options();

		$args = [
			'admin_page' => $this->admin_page,
			'hookup' => $hookup,
			'cmb' => $this->cmb,
		];

		$tpl = ( ! empty( $options['plugin_info'] ) )
			? __DIR__ . '/tpl/wrap-cmb2-sidebar.php'
			: __DIR__ . '/tpl/wrap-cmb2-simple.php';

		load_template( $tpl, false, $args);

	}


	private function convert_field_to_cmb2_field( $field ){

		$field['id']   ??= $field['slug']        ?? null;
		$field['name'] ??= $field['title']       ?? null;
		$field['desc'] ??= $field['description'] ?? null;
		
		unset( $field['slug'] );
		unset( $field['title'] );
		unset( $field['description'] );

		return array_filter($field);
	}


	/**
	 * Replace submenu title of parent item
	 */
	public function replace_submenu_title(){

		remove_submenu_page( $this->cmb2_options['id'], $this->cmb2_options['id'] );// Remove the default submenu so we can add our customized version.
		add_submenu_page(
			$this->cmb2_options['id'],
			$this->cmb2_options['title'],
			$this->cmb2_options['submenu_title'],
			$this->cmb2_options['capability'],
			$this->cmb2_options['id'],
			'',
			0,
		);
	}
}
endif;