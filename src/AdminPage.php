<?php
namespace WPHelper;

use CMB2_Options_Hookup;

defined( 'ABSPATH' ) || die( 'No soup for you!' );

use function add_menu_page;
use function add_options_page;
use function add_submenu_page;

if ( ! class_exists( AdminPage::class ) ):
/**
 * AdminPage
 * 
 * Helper class
 * Create WordPress admin pages easily
 * 
 * @todo add 'menu_location' - settings. tools, toplevel etc. (extend 'parent' option)
 * @todo add add_screen_option( 'per_page', $args )
 * @todo fix is_readable() PHP error when sending callback array
 * @todo is_readable() + is_callable() called twice - on register and on render
 * @todo Merge methods validate_page_hook() + get_hook_suffix()
 */
class AdminPage
{
	/**
	 * Title displayed on page.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Title displayed in menu.
	 *
	 * @var string
	 */
	protected $menu_title;

	/**
	 * User capability required to view page.
	 *
	 * @var string
	 */
	protected $capability;

	/**
	 * Menu slug.
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * Path to the admin page templates.
	 *
	 * @var string
	 */
	protected $template;

	/**
	 * Parent slug if submenu.
	 *
	 * @var string
	 */
	protected $parent;

	/**
	 * Icon to use in menu.
	 *
	 * @var string
	 */
	protected $icon_url;

	/**
	 * Position in menu.
	 *
	 * @var int
	 */
	protected $position;

	/**
	 * Render Type (callback, template, settings-page, cmb2-page etc.)
	 *
	 * @var string
	 */
	protected $render;

	/**
	 * Render callback function.
	 *
	 * @var callable
	 */
	protected $render_cb;

	/**
	 * Render template file
	 *
	 * @var string filename
	 */
	protected $render_tpl;

	/**
	 * Should library print WordPress 'wrap'
	 *
	 * @var string wrap type
	 */
	protected $wrap;

	/**
	 * Tab Group
	 *
	 * @var string CMB2 tab group
	 */
	protected $tab_group;

	/**
	 * Tab Title
	 *
	 * @var string CMB2 tab title
	 */
	protected $tab_title;

	/**
	 * Render callback function.
	 *
	 * @var callable | boolean
	 */
	protected $plugin_info;

	/**
	 * Scripts
	 *
	 * @var array[] arrays of script arguments passed to wp_enqueue_script()
	 */
	protected $scripts;

	/**
	 * Settings
	 *
	 * @var array[] arrays of settings sections and fields
	 */
	protected $settings;

	/**
	 * Styles
	 *
	 * @var array[] arrays of script arguments passed to wp_enqueue_style()
	 */
	protected $styles;

    /**
     * Methods
     *
     * @var Callable[] arrays of Callable methods to hook on `load-{$hook_suffix}` 
     */
	protected $methods = [];

	/**
	* Hook suffix provided by WordPress when registering menu page.
	*
	* @var string
	*/
	protected $hook_suffix;

	/**
	 * PluginCore instance
	 *
	 * @var PluginCore
	 */
	protected $plugin_core;

	/**
	 * Settings Page
	 *
	 * @var SettingsPage
	 */
	protected $settings_page;

	/**
	 * CMB2 custom settings page
	 *
	 * @var CMB2_OptionsPage|CMB2_OptionsPage_Multi
	 */
	protected $cmb2_page;

	/**
	 * Plugin Info Meta Box render object
	 *
	 * @var PluginInfoMetaBox
	 */
	protected $plugin_info_meta_box;

	/**
	 * Constructor.
	 *
	 * @param array $options
	 */
	public function __construct($options)
	{

		$options = (object) $options;

		/**
		 * @todo - fallback to plugin_core on missing options (title, etc.) in bootstrap()
		 */
		if ( isset( $options->plugin_core ) )
			$this->plugin_core( $options->plugin_core );

		$this->title( $options->title ?? null );

		/**
		 * @todo move this to bootstrap()
		 */
		if ( ! isset( $options->menu_title ) )
			$options->menu_title = $this->title;

		if ( isset( $options->menu_title ) )
			$this->menu_title( $options->menu_title );

		if ( isset( $options->capability ) )
			$this->capability( $options->capability );

		$this->slug( $options->slug ?? null );

		if ( isset( $options->plugin_info ) ){ // before render()
			$this->plugin_info( $options->plugin_info );
		}

		if ( isset( $options->wrap ) ){ // before render()
			$this->wrap( $options->wrap );
		}

		if ( isset( $options->render_cb ) ) {
			_deprecated_argument( __METHOD__, '0.30', "Option 'render_cb' will be removed in version 1.0. Use 'render' instead." );
			$this->render_cb( $options->render_cb );
		}

		if ( isset( $options->render_tpl ) ) { // before render()
			_deprecated_argument( __METHOD__, '0.30', "Option 'render_tpl' will be removed in version 1.0. Use 'render' instead." );
			$this->render_tpl( $options->render_tpl );
		}

		// This runs last so we can have 'settings-page' with custom render_tpl
		if ( isset( $options->render ) )
			$this->render( $options->render );


		if (true)
			$this->render(); // render anyway - will use default tpl if render is empty

		if (true)
			$this->wrap(); // set wrap anyway - will set to 'none' if empty


		if ( isset( $options->parent ) )
			$this->parent( $options->parent );

		if ( isset( $options->icon_url ) )
			$this->icon_url( $options->icon_url );

		if ( isset( $options->position ) )
			$this->position( $options->position );

		if ( isset( $options->tab_group ) ){
			$this->tab_group( $options->tab_group );
			$this->tab_title( $options->tab_title ?? $options->submenu_title ?? $options->menu_title );
		}

		if ( isset( $options->scripts ) )
			$this->scripts( $options->scripts );

		if ( isset( $options->styles ) )
			$this->styles( $options->styles );

		if ( isset( $options->methods ) )
			$this->methods( $options->methods );

		if ( isset( $options->settings ) )
			$this->settings( $options->settings );

		/**
		 * Bootstrap on init. Do not call directly from constructor.
		 * That way setter functions can be called after instance is created.
		 */
		add_action( 'init', [ $this, 'bootstrap' ] );
	}

	/**
	 * Setter - title
	 * WordPress admin menu param
	 * 
	 * @access private
	 */
	private function title( $title=null ) {
		$this->title = $title ?? ( isset( $this->plugin_core ) ? $this->plugin_core->title() : __METHOD__ );
	}

	/**
	 * Setter - menu_title
	 * WordPress admin menu param
	 * 
	 * @access private
	 */
	private function menu_title( $menu_title ) {
		$this->menu_title = $menu_title;
	}

	/**
	 * Setter - capability
	 * WordPress admin menu param
	 * 
	 * @access private
	 */
	private function capability( $capability ) {
		$this->capability = $capability;
	}

	/**
	 * Setter - slug
	 * WordPress admin menu param
	 * 
	 * @access private
	 */
	private function slug( $slug ) {

		$this->slug = $slug // if not empty
			?: $this->settings['option_key'] // if isset option_key
			?? (
				isset( $this->plugin_core )
					? (
						method_exists( PluginCore::class, 'token' )
							? $this->plugin_core->token() // PluginCore ~0.25
							: str_replace('-','_', strtolower( $this->plugin_core->slug() ) ) // PluginCore <= 0.24
					)
					: 'slug' . time() // unique slug
				);

	}

	/**
	 * Setter - parent
	 * WordPress admin menu param
	 * 
	 * @access private
	 */
	private function parent( $parent ) {
		switch( $parent ) {
			case 'options':
			case 'settings':
			case 'options-general.php':
				$this->parent = 'options-general.php';
			break;
			case 'tools':
			case 'tools.php':
				$this->parent = 'tools.php';
			break;
			default:
				$this->parent = $parent;
			break;
		}
	}

	/**
	 * Setter - icon_url
	 * WordPress admin menu param
	 * 
	 * @access private
	 */
	private function icon_url( $icon_url ) {
		$this->icon_url = $icon_url;
	}

	/**
	 * Setter - position
	 * WordPress admin menu param
	 * 
	 * @access private
	 */
	private function position( $position ) {
		$this->position = $position;
	}

	/**
	 * Setter - tab_group
	 * CMB2 Tab Group - used by regular 'wrap' pages as well.
	 * 
	 * @access private
	 */
	private function tab_group( $tab_group ) {
		$this->tab_group = $tab_group;

		add_filter( 'cmb2_tab_group_tabs', [ $this, 'add_to_tab_group' ], 10, 2 );
		add_action( 'cmb2_admin_init', function(){
			/**
			 * When deactivating CMB2 and reactivating - got this fatal error:
			 * 
			 * Fatal error: Uncaught Error: Argument 1 passed to CMB2_Options_Hookup::__construct()
			 * must be an instance of CMB2, bool given,
			 * called in \wp-content\plugins\cgv\inc\CGV.php on line 56
			 * in \wp-content\plugins\cmb2\includes\CMB2_Options_Hookup.php on line 39
			 * 
			 * Validate cmb2_get_metabox did not return false.
			 */
			if ( $cmb = cmb2_get_metabox( $this->parent ) ){
				$hookup = new CMB2_Options_Hookup( $cmb, $this->slug );
				add_action ( "wphelper/adminpage/tab_nav/{$this->tab_group}", [ $hookup, 'options_page_tab_nav_output' ] );
			}
		});
	}

	/**
	 * Setter - tab_title
	 * CMB2 Tab Title - only set if tab_group.
	 * 
	 * @access private
	 */
	private function tab_title( $tab_title ) {
		$this->tab_title = $tab_title;
	}

	/**
	 * Setter - render
	 * Sets render cb or tpl
	 * 
	 * accepts:
	 *     presets: 'settings-page', 'cmb2', 'cmb2-tabs', 'render_cb', 'render_tpl'
	 *     callback: A callable function that prints page.
	 *     readable: A template file
	 * 
	 * @access private
	 */
	private function render( $render=null ) {
		if ( 'settings-page' == $render ) {
			$this->render_tpl( __DIR__ . '/tpl/form-basic.php' );
			$this->render = $this->render ?? $render; // 'settings-page'
		} else if ( 'cmb2' == $render || 'cmb2-tabs' == $render ) {

			// validate
			if ( ! defined( 'CMB2_LOADED' ) ){
				$this->render_tpl( __DIR__ . '/tpl/wrap-cmb2-unavailable.php' );
				$this->render = $this->render ?? 'render_tpl';
			} else {
				/**
				 * Render templates managed and included by CMB2_OptionsPage
				 * @see CMB2_OptionsPage::options_page_output()
				 */
				$this->render = $this->render ?? $render; // 'cmb2' || 'cmb2-tabs'
			}

		} else if( is_callable( $render ) ) {
			$this->render_cb( $render );
			$this->render = $this->render ?? 'render_cb';
		} else if ( is_readable( $render ?? '' ) ) {
			$this->render_tpl( $render );
			$this->render = $this->render ?? 'render_tpl';
		} else {
			$this->render_tpl( __DIR__ . '/tpl/wrap-default.php' );
			$this->render = $this->render ?? 'render_tpl';
		}
	}

	/**
	 * Setter - render_cb
	 * 
	 * if $this->render == 'render_cb'
	 * set callback function in $this->render_cb
	 * 
	 * @access private
	 */
	private function render_cb($render_cb){

		// we already have it
		if ( $this->render_cb )
			return;

		if( is_callable( $render_cb ) )
			$this->render_cb = $render_cb;

	}

	/**
	 * Setter - render_tpl
	 * 
	 * if $this->render == 'render_tpl'
	 * save template filename to $this->render_tpl
	 * 
	 * @access private
	 */
	private function render_tpl($render_tpl){

		// we already have it
		if ($this->render_tpl)
			return;

		if( is_readable( $render_tpl ) )
			$this->render_tpl = $render_tpl;

	}

	/**
	 * Setter - wrap
	 * 
	 * Set wrap type.
	 * Default: none
	 * 
	 * @access private
	 */
	private function wrap($wrap=null){

		// we already have it
		if ($this->wrap)
			return;

		if ( ! empty($wrap) ){
			$this->wrap = 'simple';
		} else {
			$this->wrap = 'none';
		}

		if ( 'sidebar' == $wrap ){
			$this->wrap = 'sidebar';
		}

		// if plugin_info == true we set to sidebar regardless of passed $wrap parameter
		if ( ! empty($this->plugin_info) ){
			$this->wrap = 'sidebar';
		}

		if ( 'settings-page' == $this->render ){
			if ( empty($this->plugin_info) ){
				$this->wrap = 'simple';
			}
		}

	}

	/**
	 * Setter - plugin_info
	 * 
	 * accepts:
	 *     callable: Function that prints plugin info box
	 *     boolean true (or non-empty value): print default 
	 * 
	 * @access private
	 */
	private function plugin_info( $plugin_info ){

		// we already have it
		if ( $this->plugin_info )
			return;

		if( is_callable( $plugin_info ) )
			$this->plugin_info = $plugin_info;

		// if true-y value passed and plugin_core isset
		else if ( ! empty( $plugin_info ) && ! empty( $this->plugin_core ) )
			$this->plugin_info = true;
	}

	/**
	 * Add to tab group
	 * 
	 * @hook cmb2_tab_group_tabs
	 */
	public function add_to_tab_group( $tabs, $tab_group ){
		if ( $tab_group == $this->tab_group ){
			$tabs[ $this->slug ] = $this->tab_title;
		}
		return $tabs;
	}

	/**
	 * Setter - scripts
	 * Scripts to enqueue on admin page
	 * 
	 * @access private
	 */
	private function scripts($scripts){
		$this->scripts = $scripts;
	}

	/**
	 * Setter - styles
	 * Styles to enqueue on admin page
	 * 
	 * @access private
	 */
	private function styles($styles){
		$this->styles = $styles;
	}

	/**
	 * Setter - methods
	 * Callables to run on 'load-{$hook_suffix}'
	 * 
	 * @access private
	 */
	function methods($methods){
		$this->methods = $methods;
	}

	function plugin_core($plugin_core){
		if ( $plugin_core instanceof PluginCore ){
			$this->plugin_core = $plugin_core;
		}
	}

	function settings($settings){
		$this->settings = $settings;
	}

	/**
	 * Getter - Options
	 * Array representation of $this object.
	 * 
	 * @return array options
	 */
	public function options(){
		$options = [
			'title' => $this->title,
			'menu_title' => $this->menu_title,
			'capability' => $this->capability,
			'slug' => $this->slug,
			'parent' => $this->parent,
			'hook_suffix' => $this->hook_suffix,
			'icon_url' => $this->icon_url,
			'position' => $this->position,
			'render' => $this->render, // render_cb | render_tpl | settings-page | cmb2 | cmb2-tabs
			'render_cb' => $this->render_cb,
			'render_tpl' => $this->render_tpl,
			'settings' => $this->settings,
			'wrap' => $this->wrap,
			'tab_group' => $this->tab_group,
			'tab_title' => $this->tab_title,
			'plugin_core' => $this->plugin_core,
			'plugin_info' => $this->plugin_info,
		];

		return $options;
	}

	/**
	 * REGISTER MENU - NOOP/DEPRECATE NOTICE
	 * 
	 * Empty function. Kept here for backward-compatibility purposes.
	 * 
	 * All setup operations are now made in the constructor. This function is empty and will be deprecated.
	 * 
	 * This runs for all registers
	 * hook_suffix not defined yet
	 * 
	 * inside WPHelper namespace
	 * \get_current_screen() function not defined
	 * \current_action() also????
	 */
	function setup(){
		_doing_it_wrong( __METHOD__, 'Deprecated. Noop/no-op. This function will be removed in v1.0', '0.14' );
	}

	/**
	 * Set default user capability if none provided
	 * 
	 * Finish constructing object after all info is available
	 * 
	 * @hook 'init'
	 * @access private
	 * 
	 * @return void
	 */
	public function bootstrap(){

		if ( ! $this->capability )
			$this->capability = 'manage_options';

		add_action ( 'admin_init' , [ $this , '_bootstrap_admin_page' ] );
		add_action( "wphelper/adminpage/plugin_info_box/{$this->slug}" , [ $this , 'render_plugin_info_meta_box' ] );

		if ( in_array( $this->render, [ 'cmb2', 'cmb2-tabs' ] ) ){

			/**
			 * Option 'multi' is not well documented.
			 * Default CMB2 Options pages save forms into single database option.
			 * "Multi" pages allow creating CMB2 Option pages where every field is saved as separate option (ie. multi-option).
			 * 
			 * @todo Rename 'multi' option + class CMB2_OptionsPage_Multi.
			 */
			$this->cmb2_page = $this->settings['options_type'] ?? '' == 'multi'
				? new CMB2_OptionsPage_Multi( $this )
				: new CMB2_OptionsPage( $this );

			// skip add_menu_page
			return;
		}

		if ( $this->render == 'settings-page' ){
			$this->settings_page = new SettingsPage($this);
		}

		add_action ( 'admin_menu' , [ $this , 'add_menu_page' ], 11 );

		if ( ! empty( $this->plugin_info ) ) {
			add_action ( 'admin_menu' , [ $this , 'add_plugin_info_meta_box' ], 11 );
		}

	}

	/**
	 * Add WordPress toplevel or submenu page
	 */
	public function add_menu_page(){

		switch ( $this->parent ){
			case null:
			case '':
				$this->hook_suffix = add_menu_page(
					$this->title,
					$this->menu_title,
					$this->capability,
					$this->slug,
					[ $this , 'render_admin_page' ],
					$this->icon_url,
					$this->position
				);
				break;
			case 'options':
			case 'settings':
			case 'options-general.php':
				$this->hook_suffix = add_options_page(
					$this->title,
					$this->menu_title,
					$this->capability,
					$this->slug,
					[ $this , 'render_admin_page' ]
				);
				break;
			default:
				$this->hook_suffix = add_submenu_page(
					$this->parent,
					$this->title,
					$this->menu_title,
					$this->capability,
					$this->slug,
					[ $this , 'render_admin_page' ]
				);
				break;
		}
	}

	/**
	 * 
	 */
	public function add_plugin_info_meta_box() {
		$metabox_args = [
			'id' => $this->slug . '_plugin_info_meta_box', // id is unique (in case a plugin uses $this->slug)
			'title' => 'Plugin Info',
			'context' => 'side',
			'screens' => [ $this->get_hook_suffix() ],
			// 'template',
			'render' => [ $this , 'render_plugin_info_meta_box_inside' ],
		];
		( new MetaBox($metabox_args) )->add();

	}


	/**
	 * CMB2 options-page does not return page_hook/hook_suffix
	 * Generate hook_suffix ourselves.
	 * 
	 * @todo This method should probably be private.
	 * @todo Merge methods validate_page_hook() + get_hook_suffix()
	 */
	public function validate_page_hook(){

		if ( empty( $this->hook_suffix ) ){
			$this->hook_suffix = get_plugin_page_hookname( $this->slug, $this->parent );
		}

	}

	/**
	 * REGISTER ADMIN PAGE
	 * 
	 * hook_suffix is KNOWN
	 * get_current_screen() is NOT
	 * 
	 * Runs for EVERY AdminPage instance
	 * AdminNotice->onPage() works
	 * 
	 * @hook admin_init
	 * @access private
	 */
	public function _bootstrap_admin_page(){

		// CMB2 options-page does not return page_hook/hook_suffix - MUST validate
		$this->validate_page_hook();

		add_action ( 'load-' . $this->hook_suffix , [ $this , '_admin_page_setup' ] );

		foreach ( $this->methods as $method ){
			if( is_callable( $method ) ){
				add_action ( 'load-' . $this->hook_suffix , $method );
			}
		}
	}

	/**
	 * SHOW ADMIN PAGE
	 * 
	 * current_screen IS AVAILABLE
	 * 
	 * Only runs on actual screen showing
	 * AdminNotice->onPage() redundant
	 * 
	 * @hook load-{$hook_suffix}
	 * @access private
	 */
	public function _admin_page_setup(){

		if ( $this->scripts )
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		if ( $this->styles )
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_styles' ] );
	}

	/**
	 * admin_enqueue_scripts
	 * Enqueue user-provided scripts on admin page.
	 * 
	 * @hook admin_enqueue_scripts
	 * @access private
	 */
	public function admin_enqueue_scripts( $hook ) {

		// redundant
		// this only gets called on load-{$this->hook_suffix} anyway
		if( $hook != $this->hook_suffix ) {
			return;
		}

		if ( ! $this->scripts)
			return;

		foreach ( $this->scripts as $script_args ){
			wp_enqueue_script( ...$script_args );
		}

	}

	/**
	 * admin_enqueue_styles
	 * Enqueue user-provided styles on admin page.
	 * 
	 * @hook admin_enqueue_styles
	 * @access private
	 */
	public function admin_enqueue_styles( $hook ) {

		// redundant
		// this only gets called on load-{$this->hook_suffix} anyway
		if( $hook != $this->hook_suffix ) {
			return;
		}

		if ( ! $this->styles)
			return;

		foreach ( $this->styles as $style_args ){
			wp_enqueue_style( ...$style_args );
		}

	}

	/**
	 * Getter - capability
	 * Get the capability required to view the admin page.
	 *
	 * @return string
	 */
	public function get_capability()
	{
		return $this->capability;
	}

	/**
	 * Getter - menu_title
	 * Get the title of the admin page in the WordPress admin menu.
	 *
	 * @return string
	 */
	public function get_menu_title()
	{
		return $this->menu_title;
	}

	/**
	 * Getter - title
	 * Get the title of the admin page.
	 *
	 * @return string
	 */
	public function get_title()
	{
		return $this->title;
	}

	/**
	 * Getter - parent / parent_slug
	 * Get the parent slug of the admin page.
	 *
	 * @return string
	 */
	public function get_parent_slug()
	{
		return $this->parent;
	}

	/**
	 * Getter - hook_suffix
	 * Get the hook suffix provided by WordPress when registering menu page..
	 *
	 * @return string
	 * 
	 * @todo Throw Exception|WP_Error if called before 'current_screen' hook.
	 * @todo Merge methods validate_page_hook() + get_hook_suffix()
	 */
	public function get_hook_suffix()
	{
		return $this->hook_suffix;
	}

	/**
	 * Getter - slug
	 * Get the slug used by the admin page.
	 *
	 * @return string
	 */
	public function get_slug()
	{
		return $this->slug;
	}

	/**
	 * Getter - render_tpl
	 * Get the render template.
	 *
	 * @return string
	 */
	public function get_render_tpl()
	{
		return $this->render_tpl;
	}

	/**
	 * Render Admin Page
	 * Render the top section of the plugin's admin page.
	 * 
	 * This callback function used as $callback parameter in add_menu_page()
	 * 
	 * @access public
	 */
	public function render_admin_page()
	{

		// if wrap - 1. We collect output buffer
		if ( 'none' != $this->wrap ){
			ob_start();
		}

		//---------------------------[The McGuffin]---------------------------------//
		if ( isset( $this->render_cb ) && is_callable( $this->render_cb ) ) {
			call_user_func( $this->render_cb );
		} else if ( isset( $this->render_tpl ) && is_readable( $this->render_tpl ) ) {
			include $this->render_tpl;
		}
		//---------------------------[The McGuffin]---------------------------------//

		// if wrap - 2. include chosen wrap template
		if ( 'none' != $this->wrap ){
			$ob_content = ob_get_clean();

			switch ( $this->wrap ){
				case ( 'simple' ):
					include 'tpl/wrap-simple.php';
					break;
				case ( 'sidebar' ):
					include 'tpl/wrap-sidebar.php';
					break;
				default:
					break;
			}

		}
	}

	/**
	 * 
	 * @see render_plugin_info_meta_box()
	 * @deprecated
	 */
	public function render_plugin_info_box(){

		_doing_it_wrong( __METHOD__, 'Deprecated. Use render_plugin_info_meta_box() instead.', '0.26' );

		$this->render_plugin_info_meta_box();
	}



	/**
	 * Render plugin info meta-box
	 * 
	 * Call user-provided callable.
	 * Or else attempt to create PluginInfoMetaBox class from $this->plugin_core and call its render function.
	 * 
	 * @access private?
	 * 
	 * @todo See if this function should be public API or only run on action hook
	 * @todo deprecate public use - use wphelper/adminpage/plugin_info_box/{$this->slug} instead
	 */
	public function render_plugin_info_meta_box(){

		if ( isset( $this->plugin_info ) && is_callable( $this->plugin_info ) ) {
			call_user_func( $this->plugin_info );
		} else {

			if (!$this->bootstrap_plugin_info_meta_box()){
				return;
			}

			/**
			 * Allow plugins to modify plugin info meta box
			 * 
			 * @since 0.23
			 */
			do_action( "wphelper/plugin_info_meta_box/{$this->plugin_core->slug()}" );
		}
	}

	/**
	 * 
	 */
	public function render_plugin_info_meta_box_inside(){
		if ( isset( $this->plugin_info ) && is_callable( $this->plugin_info ) ) {
			call_user_func( $this->plugin_info );
		} else {

			if (!$this->bootstrap_plugin_info_meta_box()){
				return;
			}

			do_action( "wphelper/plugin_info_meta_box/inside/{$this->plugin_core->slug()}" );
		}
	}

	/**
	 * Bootstrap PluginInfoMetaBox
	 */
	private function bootstrap_plugin_info_meta_box() {
		if ( empty( $this->plugin_info_meta_box ) && ! empty( $this->plugin_core ) ){
			$this->plugin_info_meta_box = new PluginInfoMetaBox( $this->plugin_core );
		}

		// If no plugin_info_meta_box - return false
		if ( empty( $this->plugin_info_meta_box ) ) {
			return false;
		}

		return true;
	}
}
endif;