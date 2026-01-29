<?php
namespace WPHelper;

use CMB2;
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
 * @todo Revisit/document 'render' + 'render_cb|render_tpl' usage.
 * @todo Review multiple pages registering to the same slug with multiple render callbacks.
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
	 * Title displayed in submenu.
	 *
	 * @var string
	 */
	protected $submenu_title;

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
	 * Render Type
	 *
	 * @var string custom-callback | custom-template | settings-page | cmb2-page | etc.
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
	 * @var array[] arrays of arguments passed to wp_enqueue_script()
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
	 * @var array[] arrays of arguments passed to wp_enqueue_style()
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

		if ( isset( $options->plugin_core ) )
			$this->plugin_core( $options->plugin_core );

		$this->slug( $options->slug ?? null );

		$this->title( $options->title ?? null );

		$this->menu_title( $options->menu_title ?? null );

		if ( isset( $options->submenu_title ) )
			$this->submenu_title( $options->submenu_title );

		if ( isset( $options->tab_title ) )
			$this->tab_title( $options->tab_title );

		if ( isset( $options->capability ) )
			$this->capability( $options->capability );

		$this->plugin_info( $options->plugin_info ?? null );

		if ( isset( $options->wrap ) ){ // before render()
			$this->wrap( $options->wrap );
		}

		if ( isset( $options->render_cb ) ) {
			$this->render_cb( $options->render_cb );
		}

		if ( isset( $options->display_cb ) ) {
			$this->render_cb( $options->display_cb );
		}

		if ( isset( $options->render_tpl ) ) { // before render()
			$this->render_tpl( $options->render_tpl );
		}

		// Run render after render_cb + render_tpl so we can have 'settings-page' with custom render_tpl
		$this->render( $options->render ?? null ); // Will use default tpl if empty

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
			$this->tab_title( $options->tab_title ?? $options->submenu_title ?? $options->menu_title ?? $options->title ?? $this->title ); // tab_title redundant - fallbacks are not
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
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Setter - title
	 * WordPress admin menu param
	 * 
	 * @access private
	 */
	private function title( $title=null ) {
		$this->title = $title
			?? $this->plugin_core?->title()
			?? ucfirst( $this->slug );
	}

	/**
	 * Setter - menu_title
	 * WordPress admin menu param
	 * 
	 * @access private
	 */
	private function menu_title( $menu_title ) {
		$this->menu_title = $menu_title ?? $this->title;
	}

	/**
	 * Setter - submenu_title
	 * WordPress admin menu param
	 * 
	 * @access private
	 */
	private function submenu_title( $submenu_title ) {
		$this->submenu_title = $submenu_title;
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
		$this->slug ??= $slug // if not empty
			?: $this->settings['option_key'] // if isset option_key
			?? $this->plugin_core?->slug()
			?? 'slug' . time(); // "unique" slug - USELESS - AdminPage MUST have id/slug
	}

	/**
	 * Setter - parent
	 * WordPress admin menu param
	 * 
	 * @access private
	 */
	private function parent( $parent ) {
		$this->parent = match( $parent ) {
			'dashboard'           => 'index.php',
			'posts'               => 'edit.php',
			'media'               => 'upload.php',
			'pages'               => 'edit.php?post_type=page',
			'comments'            => 'edit-comments.php',
			'appearance', // Official WordPress designation 
			'themes'              => 'themes.php',
			'plugins'             => 'plugins.php',
			'users'               => 'users.php',
			'settings', // Official WordPress designation
			'options'             => 'options-general.php',
			'tools'               => 'tools.php',
			'network',
			'network_settings'    => 'settings.php',
			default               => post_type_exists( $parent ?? '' ) ? "edit.php?post_type={$parent}" : $parent,
		};
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
			 * Only allow adding single hookup for tab_group action (fixes multiple nav-tab elements on non-CMB2 pages)
			 * Validate CMB2 meta-box exists (@see Fatal error above).
			 * 
			 * @var CMB2|bool $cmb
			 */
			if (
				! has_action( "wphelper/adminpage/tab_nav/{$this->tab_group}" )
				&&
				( $cmb = cmb2_get_metabox( $this->parent ) )
			) {
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
	 * Sets $this->render string
	 * Sets $this->render_cb or $this->render_tpl
	 * 
	 * @access private
	 * 
	 * @param string|Callable|Readable|null $render
	 * 										- Valid preset string ( `settings-page | cmb2 | cmb2-tabs` )
	 * 										- Render callback function
	 * 										- PHP template file
	 * 										- null
	 * 
	 * @return void Sets `$this->render` to ( `custom-callback | custom-template | default-template | settings-page | cmb2 | cmb2-tabs | cmb2-unavailable` )
	 */
	private function render( $render=null ) {
		if ( 'settings-page' == $render ) {
			$this->render_tpl( __DIR__ . '/tpl/form-basic.php' );
			$this->render ??= $render; // 'settings-page'
		} else if ( 'cmb2' == $render || 'cmb2-tabs' == $render ) {

			// validate
			if ( ! defined( 'CMB2_LOADED' ) ){
				$this->render_tpl( __DIR__ . '/tpl/wrap-cmb2-unavailable.php' );
				$this->render ??= 'cmb2-unavailable';
			} else {
				/**
				 * Render templates managed and included by CMB2_OptionsPage
				 * @see CMB2_OptionsPage::options_page_output()
				 */
				$this->render ??= $render; // 'cmb2' || 'cmb2-tabs'
			}

		} else if( is_callable( $render ) ) {
			$this->render_cb( $render );
			$this->render ??= 'custom-callback';
		} else if ( ! is_array( $render ) && is_readable( $render ?? '' ) ) {
			$this->render_tpl( $render );
			$this->render ??= 'custom-template';
		} else {
			$this->render_tpl( __DIR__ . '/tpl/wrap-default.php' );
			$this->render ??= 'default-template';
		}
	}

	/**
	 * Setter - render_cb
	 * 
	 * Set callback function in $this->render_cb
	 * 
	 * @access private
	 */
	private function render_cb($render_cb){

		if( is_callable( $render_cb ) )
			$this->render_cb ??= $render_cb;

	}

	/**
	 * Setter - render_tpl
	 * 
	 * Set template filename in $this->render_tpl
	 * 
	 * @access private
	 */
	private function render_tpl($render_tpl){

		if( is_readable( $render_tpl ) )
			$this->render_tpl ??= $render_tpl;

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

		if ( 'default-template' == $this->render ){
			/**
			 * default template has its own .wrap element.
			 * This is to reset 'sidebar' if plugin_info=true.
			 * When 'sidebar' is set Plugin Info box will appear but 2 nested .wrap elements.
			 * 
			 * @todo separate default card from .wrap element
			 */
			$this->wrap = 'none';
		}
	}

	/**
	 * Setter - plugin_info
	 * 
	 * Variable $plugin_info will only be set to true if PluginCore instance and MetaBox::add() method are available.
	 * 
	 * @access private
	 * 
	 * @param callable|boolean|mixed $plugin_info - Callable that renders the plugin info box | Boolean/truthy value to generate from PluginCore data. 
	 */
	private function plugin_info( $plugin_info ){

		if( is_callable( $plugin_info ) )
			$this->plugin_info ??= $plugin_info;

		// if true-y value passed and plugin_core isset and MetaBox::add() method exists
		else if ( ! empty( $plugin_info ) && ! empty( $this->plugin_core ) && is_callable( MetaBox::class, 'add' ) )
			$this->plugin_info ??= true;
		else 
			$this->plugin_info ??= false;
	}

	/**
	 * Add to tab group
	 * 
	 * @hook cmb2_tab_group_tabs
	 */
	public function add_to_tab_group( $tabs, $tab_group ){
		if ( $tab_group == $this->tab_group ){
			if ( empty( $this->parent ) ) {
				//	if parent page - set as first tab
				$tabs = array_merge( [ $this->slug => $this->tab_title ], $tabs );
			} else {
				$tabs[ $this->slug ] = $this->tab_title;
			}
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
			'submenu_title' => $this->submenu_title,
			'capability' => $this->capability,
			'slug' => $this->slug,
			'parent' => $this->parent,
			'hook_suffix' => $this->hook_suffix,
			'icon_url' => $this->icon_url,
			'position' => $this->position,
			'render' => $this->render, // string - custom-callback | custom-template | default-template | settings-page | cmb2 | cmb2-tabs | cmb2-unavailable
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
	 * All setup operations are now made in the constructor. This function is empty and will be deprecated.
	 * 
	 * @deprecated
	 */
	function setup(){
		_doing_it_wrong( __METHOD__, 'Deprecated. Noop/no-op. This function will be removed in v1.0', '0.14' );
	}

	/**
	 * Set default user capability if none provided
	 * 
	 * Finish constructing object after all info is available
	 * 
	 * @since 0.3  bootstrap()
	 * @since 0.32 Rename method init()
	 * 
	 * @hook 'init'
	 * @access private
	 * 
	 * @return void
	 */
	public function init(){

		if ( ! $this->capability )
			$this->capability = 'manage_options';

		add_action ( 'admin_init' , [ $this , 'admin_init' ] );
		add_action( "wphelper/adminpage/plugin_info_box/{$this->slug}" , [ $this , 'render_plugin_info_meta_box' ] );

		if ( ! empty( $this->plugin_info ) ) {
			add_action ( 'current_screen' , [ $this , 'add_plugin_info_meta_box' ], 11 );
		}

		if ( defined('WPH_DEBUG') && WPH_DEBUG ) {
			add_action ( 'current_screen' , [ $this , 'add_wph_debug_metabox' ], 20 );
		}

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

		$priority = empty( $this->parent ) ? 9 : 11;
		add_action ( 'admin_menu' , [ $this , 'add_menu_page' ], $priority );

	}

	/**
	 * Add WordPress toplevel or submenu page
	 * 
	 * @since 0.2
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

				// If parent && has subtitle - remove first submenu and replace menu_title parameter.
				if (
					! empty( $this->submenu_title )
					&&
					$this->menu_title != $this->submenu_title
				){
					$this->replace_submenu_title();
				}

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
	 * Add plugin-info meta-box to this screen
	 * 
	 * @hook current_screen, 11
	 * 
	 * @since 0.25
	 */
	public function add_plugin_info_meta_box() {

		/**
		 * bail early if method unavailable. This is a redundancy @see self::plugin_info()
		 */
		if ( ! is_callable( MetaBox::class, 'add' ) )
			return;

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
	 * Add plugin-info meta-box to this screen
	 * 
	 * @hook current_screen, 11
	 * 
	 * @since 0.38
	 */
	public function add_wph_debug_metabox() {

		/**
		 * bail early if method unavailable. This is a redundancy @see self::plugin_info()
		 */
		if ( ! is_callable( MetaBox::class, 'add' ) )
			return;

		$metabox_args = [
			'id' => $this->slug . '_wph_debug', // id is unique (each page adds its own identical metabox)
			'title' => 'WPHelper Debug',
			'context' => 'side',
			'priority' => 'low',
			'screens' => [ $this->get_hook_suffix() ],
			'template' => __DIR__ . '/tpl/meta-box-wphelper-debug.php',
		];
		( new MetaBox($metabox_args) )->add();

	}


	/**
	 * CMB2 options-page does not return page_hook/hook_suffix
	 * Generate hook_suffix ourselves.
	 * 
	 * @since 0.14
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
	 * @since 0.2  _bootstrap_admin_page()
	 * @since 0.32 Rename method admin_init()
	 * 
	 * @hook admin_init
	 * @access private
	 */
	public function admin_init(){

		// CMB2 options-page does not return page_hook/hook_suffix - MUST validate
		$this->validate_page_hook();

		add_action ( 'load-' . $this->hook_suffix , [ $this , 'load_page' ] );

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
	 * @since 0.2  _admin_page_setup()
	 * @since 0.32 Rename method load_page()
	 * 
	 * @hook load-{$hook_suffix}
	 * @access private
	 */
	public function load_page(){

		add_filter( 'admin_body_class', [ $this, 'admin_body_class' ] );

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
	 * 
	 * @since 0.3 _enqueue_scripts_actual()
	 * @since 0.4 admin_enqueue_scripts()
	 * @since 0.42 Remove $hook param
	 */
	public function admin_enqueue_scripts() {
		foreach ( $this->scripts as $script_args ){
			wp_enqueue_script( ...$script_args );
		}
	}

	/**
	 * admin_enqueue_styles
	 * Enqueue user-provided styles on admin page.
	 * 
	 * @since 0.10
	 * @since 0.42 Remove $hook param
	 * 
	 * @hook admin_enqueue_styles
	 * @access private
	 */
	public function admin_enqueue_styles() {
		foreach ( $this->styles as $style_args ){
			wp_enqueue_style( ...$style_args );
		}
	}

	/**
	 * Add .wphelper-admin-page classes to body
	 * 
	 * @since 0.32
	 */
	public function admin_body_class( $classes ) {
		$classes .= ' wphelper-admin-page';
		$classes .= " wphelper-admin-page-{$this->render}";
		return $classes;
	}

	/**
	 * Getter - capability
	 * Get the capability required to view the admin page.
	 * 
	 * @since 0.2
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
	 * @since 0.2
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
	 * @since 0.2 get_page_title()
	 * @since 0.3 get_title()
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
	 * @since 0.2
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
	 * @since 0.13
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
	 * @since 0.2
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
	 * @since 0.14
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
	 * 
	 * @since 0.2
	 */
	public function render_admin_page()
	{

		// if wrap - 1. We collect output buffer
		if ( 'none' != $this->wrap ){
			ob_start();
		}

		//---------------------------[The McGuffin]---------------------------------//

		$args = [ 'admin_page' => $this ];
		if ( ! empty( $this->settings_page ) ){
			$args['settings_page'] = $this->settings_page;
		}

		if ( isset( $this->render_cb ) && is_callable( $this->render_cb ) ) {
			call_user_func( $this->render_cb );
		} else if ( isset( $this->render_tpl ) && is_readable( $this->render_tpl ) ) {
			load_template( $this->render_tpl, false, $args );
		}
		//---------------------------[The McGuffin]---------------------------------//

		// if wrap - 2. include chosen wrap template
		if ( 'none' != $this->wrap ){

			$args['ob_content'] = ob_get_clean();

			switch ( $this->wrap ){
				case ( 'sidebar' ):
					$wrap_tpl = __DIR__ . '/tpl/wrap-sidebar.php';
					break;
				case ( 'simple' ):
				default:
					$wrap_tpl = __DIR__ . '/tpl/wrap-simple.php';
					break;
			}

			load_template( $wrap_tpl, false, $args );

		}
	}

	/**
	 * 
	 * @see render_plugin_info_meta_box()
	 * @deprecated
	 * 
	 * @since 0.17
	 * @since 0.25 deprecated
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
	 * @since 0.17 render_plugin_info_box()
	 * @since 0.25 render_plugin_info_meta_box() replaces/deprecates render_plugin_info_box()
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
	 * Callback passed to add_meta_box() \
	 * Render only "inside" div of plugin_info_meta_box.
	 * 
	 * @since 0.25
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
	 * 
	 * @since 0.25
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

	/**
	 * Replace submenu title of parent item
	 *
	 * @since 0.42
	 */
	private function replace_submenu_title(){
		remove_submenu_page( $this->slug, $this->slug );
		add_submenu_page(
			$this->slug,
			$this->title,
			$this->submenu_title,
			$this->capability,
			$this->slug,
			[ $this , 'render_admin_page' ],
			0
		);
	}
}
endif;