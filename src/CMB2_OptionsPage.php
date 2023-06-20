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
 * 
 * @todo add 'submenu' field and functionality (rename first submenu item) to WPHelper\AdminPage
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
		$settings['display_cb'] ??= [ $this, 'options_page_output' ];

		$settings['option_key']  ??= ( $settings['option_name'] ?? ( $settings['id'] ?? $admin_options['slug'] ) );
		$settings['title']       ??= $admin_options['title'];
		$settings['menu_title']  ??= $admin_options['menu_title'];
		// @todo Only if cmb2-tabs
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

		/**
		 * CMB2 only accepts url slug
		 * 
		 * @todo export parent_slug conversion to dedicated method
		 * @todo perhaps move this to AdminPage::parent() method
		 */
		switch ( $settings['parent_slug'] ) {
			case 'dashboard':
				$settings['parent_slug'] = 'index.php';
				break;
			case 'posts':
				$settings['parent_slug'] = 'edit.php';
				break;
			case 'media':
				$settings['parent_slug'] = 'upload.php';
				break;
			case 'pages':
				$settings['parent_slug'] = 'edit.php?post_type=page';
				break;
			case 'comments':
				$settings['parent_slug'] = 'edit-comments.php';
				break;
			case 'themes':
			case 'appearance': // Official WordPress designation 
				$settings['parent_slug'] = 'themes.php';
				break;
			case 'plugins':
				$settings['parent_slug'] = 'plugins.php';
				break;
			case 'users':
				$settings['parent_slug'] = 'users.php';
				break;
			case 'options':
			case 'settings': // Official WordPress designation
				$settings['parent_slug'] = 'options-general.php';
				break;
			case 'tools':
				$settings['parent_slug'] = 'tools.php';
				break;
			case 'network':
			case 'network_settings':
				$settings['parent_slug'] = 'settings.php';
				break;
			case null:
				break;
			default:
				if ( post_type_exists( $settings['parent_slug'] ) ){
					$settings['parent_slug'] = "edit.php?post_type={$settings['parent_slug']}";
				}
				break;
		}
		
		if ( $admin_options['render'] == 'cmb2-tabs' ){
			$settings['tab_group'] ??= $settings['parent_slug'] ?? $settings['id'];
			$settings['tab_title'] ??= $settings['menu_title'];
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

		// re-insert fields back into settings
		$settings['fields'] = $this->fields;

		$this->cmb2_options = $settings;

		// register parent pages before sub-menu pages
		$priority = empty( $settings['parent_slug'] ) ? 9 : 10;

		if ( $settings['allow_on_front'] ?? false ){
			add_action( 'cmb2_init', [ $this, 'register_metabox' ], $priority );
		} else {
			add_action( 'cmb2_admin_init', [ $this, 'register_metabox' ], $priority );
		}

		/**
		 * @todo add 'submenu' field and functionality to WPHelper\AdminPage
		 * @todo reverse control/flow - so 'tab title' inherits/defaults to AdminPage 'submenu' field if exists.
		 */
		if ( empty( $settings['parent_slug'] ) && $settings['menu_title'] != $settings['tab_title'] ){
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

		if ( ! empty( $options['plugin_core'] ) || ! empty( $options['plugin_info'] ) ){
			include  __DIR__ . '/tpl/wrap-cmb2-sidebar.php';
		} else {
			include __DIR__ . '/tpl/wrap-cmb2-simple.php';
		}

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
	 * Replace submenu title of parent item with tab title
	 * 
	 * @todo add 'submenu' field and functionality to WPHelper\PluginCore
	 */
	public function replace_submenu_title(){

		remove_submenu_page( $this->cmb2_options['id'], $this->cmb2_options['id'] );// Remove the default submenu so we can add our customized version.
		add_submenu_page(
			$this->cmb2_options['id'],
			$this->cmb2_options['title'],
			$this->cmb2_options['tab_title'],
			$this->cmb2_options['capability'],
			$this->cmb2_options['id'],
			'',
			0,
		);
	}
}
endif;