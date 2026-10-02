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
 * @see CMB2_Options_Hookup::options_page_output and 'display_cb' - to manipulate tabs
 * 
 * @since 0.14
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
	 * Constructor
	 * 
	 * @since 0.14
	 */
	function __construct( AdminPage $admin_page ){

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

		/**
		 * Setting 'options_type' => 'multi' is not well documented.
		 * Default CMB2 Options page saves form into a single database option.
		 * With 'options_type' => 'multi' each field is saved as separate option (ie. multi-option).
		 * 
		 * @todo Rename 'options_type' => 'multi' setting.
		 */
		if ( $settings['options_type'] ?? '' === 'multi' ) {
			foreach( $this->fields as $field ){
				add_filter( "cmb2_override_{$field['id']}_meta_value",  [ $this, 'cmb2_override_get' ],    10, 4 );
				add_filter( "cmb2_override_{$field['id']}_meta_save",   [ $this, 'cmb2_override_save' ],   10, 4 );
				add_filter( "cmb2_override_{$field['id']}_meta_remove", [ $this, 'cmb2_override_delete' ], 10, 4 );
			}
		}

	}

	/**
	 * Register CMB2 metabox
	 * 
	 * @since 0.14
	 */
	public function register_metabox(){
		$this->cmb = new CMB2( $this->cmb2_options );
	}

	/**
	 * Display options-page output. To override, set 'display_cb' box property.
	 * 
	 * @since 0.14
	 */
	public function options_page_output( CMB2_Options_Hookup $hookup ) {
		
		$options = $this->admin_page->options();

		$args = [
			'admin_page' => $this->admin_page,
			'hookup' => $hookup,
			'cmb' => $this->cmb,
		];

		$tpl = ( ! empty( $options['plugin_info'] ) )
			? __DIR__ . '/tpl/wrap-cmb2-sidebar.php'
			: __DIR__ . '/tpl/wrap-cmb2-simple.php';

		load_template( $tpl, false, $args );

	}

	/**
	 * @since 0.14
	 */
	private function convert_field_to_cmb2_field( array $field ){

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
	 * 
	 * @since 0.17
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

	/**
	 * @hook cmb2_override_{$field_id}_meta_value
	 * 
	 * @since 0.14 CMB2_Override_Meta::cmb2_override_get()
	 * @since 0.44 Method moved to class CMB2_OptionsPage::cmb2_override_get()
	 * 
	 * @link https://github.com/CMB2/CMB2-Snippet-Library/blob/master/filters-and-actions/override-cmb2-data-source.php
	 */
	function cmb2_override_get( $override, $args, $field_args, $field ) {
		return get_option( $field_args['field_id'], '' );
	}

	/**
	 * @hook cmb2_override_{$field_id}_meta_save
	 * 
	 * @since 0.14 CMB2_Override_Meta::cmb2_override_save()
	 * @since 0.44 Method moved to class CMB2_OptionsPage::cmb2_override_save()
	 * 
	 * @link https://github.com/CMB2/CMB2-Snippet-Library/blob/master/filters-and-actions/override-cmb2-data-source.php
	 */
	function cmb2_override_save( $override, $args, $field_args, $field ) {
		// Here, we're storing the data to the options table, but you can store to any data source here.
		// If to a custom table, you can use the $args['id'] as the reference id.
		$updated = update_option( $field_args['id'], $args['value'], false );
		return !! $updated;
	}

	/**
	 * @hook cmb2_override_{$field_id}_meta_remove
	 * 
	 * @since 0.14 CMB2_Override_Meta::cmb2_override_delete()
	 * @since 0.44 Method moved to class CMB2_OptionsPage::cmb2_override_delete()
	 * 
	 * @link https://github.com/CMB2/CMB2-Snippet-Library/blob/master/filters-and-actions/override-cmb2-data-source.php
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