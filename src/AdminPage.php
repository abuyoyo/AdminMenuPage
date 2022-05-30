<?php
namespace WPHelper;

use function add_menu_page;
use function add_options_page;
use function add_submenu_page;

if ( ! class_exists( 'WPHelper\AdminPage' ) ):
/**
 * AdminPage
 * 
 * Helper class
 * Create WordPress admin pages easily
 * 
 * @author  abuyoyo
 * @version 0.17
 * 
 * @todo add 'menu_location' - settings. tools, toplevel etc. (extend 'parent' option)
 * @todo add add_screen_option( 'per_page', $args );
 * @todo accept WPHelper\PluginCore instance (get title slug etc. from there)
 * @todo fix is_readable() PHP error when sending callback array
 * @todo is_readable() + is_callable() called twice - on register and on render
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
	 * User capabailty required to view page.
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
	 * Styles
	 *
	 * @var array[] arrays of script arguments passed to wp_enqueue_style()
	 */
	protected $styles;

    /**
     * Methods
     *
     * @var Callable[] arrays of Callbale methods to hook on `load-{$hook_suffix}` 
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
	 * Delegate admin_menu hookup to CMB2 implementation
	 *
	 * @var boolean
	 */
	protected $delegate_hookup = false;

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

		if ( isset( $options->title ) )
			$this->title( $options->title );

		/**
		 * @todo move this to bootstrap()
		 */
		if ( ! isset( $options->menu_title ) )
			$options->menu_title = $options->title;

		if ( isset( $options->menu_title ) )
			$this->menu_title( $options->menu_title );

		if ( isset( $options->capability ) )
			$this->capability( $options->capability );

		if ( isset( $options->slug ) )
			$this->slug( $options->slug );

		if ( isset( $options->plugin_info ) ){ // before render()
			$this->plugin_info( $options->plugin_info );
		}

		if ( isset( $options->render ) ) // dev
			$this->render( $options->render );

		if ( isset( $options->render_cb ) ) // dev - deprecate?
			$this->render_cb( $options->render_cb );

		if ( isset( $options->render_tpl ) ) // dev - deprecate?
			$this->render_tpl( $options->render_tpl );

		if (true)
			$this->render(); // render anyway - will use default tpl if render is empty

		if ( isset( $options->parent ) )
			$this->parent( $options->parent );

		if ( isset( $options->icon_url ) )
			$this->icon_url( $options->icon_url );

		if ( isset( $options->position ) )
			$this->position( $options->position );

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
	private function title($title){
		$this->title = $title;
	}

	/**
	 * Setter - menu_title
	 * WordPress admin menu param
	 * 
	 * @access private
	 */
	private function menu_title($menu_title){
		$this->menu_title = $menu_title;
	}

	/**
	 * Setter - capability
	 * WordPress admin menu param
	 * 
	 * @access private
	 */
	private function capability($capability){
		$this->capability = $capability;
	}

	/**
	 * Setter - slug
	 * WordPress admin menu param
	 * 
	 * @access private
	 */
	private function slug($slug){
		$this->slug = $slug;
	}

	/**
	 * Setter - parent
	 * WordPress admin menu param
	 * 
	 * @access private
	 */
	private function parent($parent){
		switch( $parent ) {
			case 'options':
			case 'settings':
			case 'options-general.php':
				$this->parent = 'options-general.php';
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
	private function icon_url( $icon_url ){
		$this->icon_url = $icon_url;
	}

	
	/**
	 * Setter - position
	 * WordPress admin menu param
	 * 
	 * @access private
	 */
	private function position( $position ){
		$this->position = $position;
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
	private function render($render=null){
		if ( 'settings-page' == $render ) {
			$this->render_tpl(__DIR__ . '/tpl/settings_page.php');
			$this->render = $this->render ?? $render; // 'settings-page'
		} else if ( 'cmb2' == $render || 'cmb2-tabs' == $render ) {

			$this->delegate_hookup = true;

			if ( ! empty( $this->plugin_core ) || ! empty( $this->plugin_info ) ){
				$this->render_tpl( __DIR__ . '/tpl/cmb2_options_page-plugin_info.php' );
			} else {
				$this->render_tpl(__DIR__ . '/tpl/cmb2_options_page.php');
			}

			$this->render = $this->render ?? $render; // 'cmb2' || 'cmb2-tabs'

		} else if( is_callable( $render ) ) {
			$this->render_cb($render);
			$this->render = $this->render ?? 'render_cb';
		} else if ( is_readable($render) ) {
			$this->render_tpl($render);
			$this->render = $this->render ?? 'render_tpl';
		} else {
			$this->render_tpl(__DIR__ . '/tpl/default.php');
			$this->render = $this->render ?? 'render_tpl';
		}
	}

	/**
	 * Setter - render_cb
	 * 
	 * if $this->render == 'render_cb'
	 * set callback funtion in $this->render_cb
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
		$this->plugin_core = $plugin_core;
	}

	function settings($settings){
		$this->settings = $settings;
	}

	/**
	 * Getter - Options
	 * Array representation of $this object.
	 * 
	 * @return array options
	 * 
	 * @todo add new properties (like plugin_info)
	 */
	public function options(){
		$options = [
			'title' => $this->title,
			'menu_title' => $this->menu_title,
			'capability' => $this->capability,
			'slug' => $this->slug,
			'parent' => $this->parent,
			'icon_url' => $this->icon_url,
			'position' => $this->position,
			'render' => $this->render, // render_cb | render_tpl | settings-page | cmb2 | cmb2-tabs
			'render_cb' => $this->render_cb,
			'render_tpl' => $this->render_tpl,
			'settings' => $this->settings,
			'plugin_core' => $this->plugin_core,
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

		if ( $this->render == 'settings-page' ){

			$this->settings_page = new SettingsPage($this);
			$this->settings_page->setup();

		}

		// if ( $this->delegate_hookup ){
		if ( 'cmb2' == $this->render || 'cmb2-tabs' == $this->render ){

			if ( isset( $this->settings['options_type'] ) && $this->settings['options_type'] == 'multi' ) {
				$this->cmb2_page = new CMB2_OptionsPage_Multi( $this );
			} else {
				$this->cmb2_page = new CMB2_OptionsPage( $this );
			}
			
			/**
			 * @todo Perhpaps this can hook on admin_init - right after admin_menu has finished
			 * @todo CMB2 options-page does not return page_hook/hook_suffix - MUST validate
			 */
			add_action ( 'admin_menu' , [ $this , '_bootstrap_admin_page' ], 12 );

			// skip add_menu_page
			return;
		}

		// if ( ! $this->delegate_hookup ){
		add_action ( 'admin_menu' , [ $this , 'add_menu_page' ], 11 );
		add_action ( 'admin_menu' , [ $this , '_bootstrap_admin_page' ], 12 );

		add_action( "wphelper/adminpage/plugin_info_box/{$this->slug}" , [ $this , 'render_plugin_info_box' ] );
	}

	/**
	 * Add WordPress toplevel or submenu page
	 */
	public function add_menu_page(){

		if ( ! $this->parent ){
			$this->hook_suffix = add_menu_page( 
				$this->title, 
				$this->menu_title, 
				$this->capability, 
				$this->slug, 
				[ $this , 'render_admin_page' ],
				$this->icon_url, 
				$this->position
			);
		}else{
			switch ($this->parent){
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

	}


	/**
	 * 
	 */
	public function validate_page_hook(){

		/**
		 * hack!
		 * This is ad hoc validation - should do this earlier
		 */
		if ( empty( $this->slug ) ){
			$this->slug = $this->settings['option_key'];
		}

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
	 * @hook admin_menu priority 12
	 * @access private
	 * 
	 * @todo move this function to admin_init - after admin_menu has finished
	 */
	public function _bootstrap_admin_page(){

		/**
		 * @todo perhaps run this on 'admin_init'
		 */
		$this->validate_page_hook();

		add_action ( 'load-' . $this->hook_suffix , [ $this , '_admin_page_setup' ] );

		foreach ( $this->methods as $method ){
			if( is_callable( $method ) ){
				add_action ( 'load-'.$this->hook_suffix , $method );
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

		if ($this->scripts)
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		if ($this->styles)
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_styles' ] );
	}

	/**
	 * admin_enqueue_scripts
	 * Enqueue user-provided scripts on admin page.
	 * 
	 * @hook admin_enqueue_scripts
	 * @access private
	 */
	public function admin_enqueue_scripts($hook) {

		// redundant
		// this only gets called on load-{$this->hook_suffix} anyway
		if( $hook != $this->hook_suffix ) {
			return;
		}

		if ( ! $this->scripts)
			return;

		foreach ($this->scripts as $script_args){
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
	public function admin_enqueue_styles($hook) {

		// redundant
		// this only gets called on load-{$this->hook_suffix} anyway
		if( $hook != $this->hook_suffix ) {
			return;
		}

		if ( ! $this->styles)
			return;

		foreach ($this->styles as $style_args){
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
		return $this->capabailty;
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
		// @todo if render callback supplied - add shortcircuit hook here
		// execute render callback and return early

		if ( isset( $this->render_cb ) && is_callable($this->render_cb)) {
			call_user_func( $this->render_cb );
		}else if ( isset( $this->render_tpl ) && is_readable($this->render_tpl)) {
			include $this->render_tpl;
		}
	}

	/**
	 * Render plugin info metabox
	 * 
	 * Call user-provided callable.
	 * Or else attempt to create PluginInfoMetaBox class from $this->plugin_core and call its render function.
	 * 
	 * @access private?
	 * 
	 * @todo See if this function should be public API or only run on action hook
	 * @todo deprecate public use - use wphelper/adminpage/plugin_info_box/{$this->slug} instead
	 */
	public function render_plugin_info_box(){

		if ( isset( $this->plugin_info ) && is_callable( $this->plugin_info ) ) {
			call_user_func( $this->plugin_info );
		} else {
			if ( ! empty( $this->plugin_core ) && empty( $this->plugin_info_meta_box ) ){
				$this->plugin_info_meta_box = new PluginInfoMetaBox( $this->plugin_core );
			}
			$this->plugin_info_meta_box->plugin_info_box();
		}

	}
}
endif;