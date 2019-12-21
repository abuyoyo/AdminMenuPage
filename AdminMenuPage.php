<?php
/**
 * AdminMenuPage
 * 
 * Helper class
 * Create WordPress admin pages easily
 * 
 * @author  abuyoyo
 * @version 0.8
 * 
 * @todo add styles to WPHelper\AdminMenuPage
 * @todo add 'menu_location' - settings. tools, toplevel etc.
 * @todo add add_screen_option( 'per_page', $args );
 * @todo accept WPHelper\PluginCore instance (get title slug etc. from there)
 */
namespace WPHelper;

if ( ! class_exists( 'WPHelper\AdminMenuPage' ) ):
class AdminMenuPage
{
    /**
     * Title displayed on page.
     *
     * @var string
     */
    private $title;
 
    /**
     * Title displayed in menu.
     *
     * @var string
     */
    private $menu_title;
 
    /**
     * User capabailty required to view page.
     *
     * @var string
     */
    private $capability;
 
    /**
     * Menu slug.
     *
     * @var string
     */
    private $slug;

    /**
     * Path to the admin page templates.
     *
     * @var string
     */
    private $template;
 
    /**
     * Parent slug if submenu.
     *
     * @var string
     */
    private $parent;
 
    /**
     * Icon to use in menu.
     *
     * @var string
     */
    private $icon_url;
 
    /**
     * Position in menu.
     *
     * @var int
     */
    private $position;
 
    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct($options)
    {
		extract($options);
		
        $this->title = $title;
		if (! $menu_title)
			$menu_title = $title;
		$this->menu_title = $menu_title;
		$this->capability = $capability;
		$this->slug = $slug;

		if ( $render ) // dev
			$this->render($render);

		if ( $render_cb ) // dev
			$this->render_cb($render_cb);

		if ( $render_tpl ) // dev
			$this->render_tpl($render_tpl);

		if (true)
			$this->render(); // render anyway

		$this->template = rtrim( $template, '/' ); // original - deprecate

		if ( $parent )
			$this->parent($parent);

		if ( $icon_url )
			$this->icon_url($icon_url);

		if ( $position )
			$this->position($position);

		if ( $scripts )
			$this->scripts($scripts);
	}
	
	function parent($parent){
		$this->parent = $parent;
	}
	
	function icon_url($icon_url){
		$this->icon_url = $icon_url;
	}
	
	function position($position){
		$this->position = $position;
	}


	function render($render=null){

		if (! $render){

		}

		if( is_callable( $render ) )
			$this->render_cb($render);
		else if (is_readable($render) )
			$this->render_tpl($render);
		else
			$this->render_tpl(__DIR__ . '/tpl/default.php');
	}

	function render_cb($render_cb){
		
		// we already have it
		if ($this->render_cb)
			return;

		if( is_callable( $render_cb ) )
			$this->render_cb = $render_cb;

	}

	function render_tpl($render_tpl){
		
		// we already have it
		if ($this->render_tpl)
			return;

		if( is_readable( $render_tpl ) )
			$this->render_tpl = $render_tpl;

	}
	
	function scripts($scripts){
		$this->scripts = $scripts;
	}
	
	/**
	 * REGISTER MENU
	 * 
	 * This runs for all registers
	 * hook_suffix not defined yet
	 * 
	 * inside WPHelper namespace
	 * \get_current_screen() function not defined
	 * \current_action() also????
	 * 
	 */
	function setup(){
		$this->bootstrap(); // set opinionated defaults

		add_action ( 'admin_menu' , [ $this , 'add_menu_page' ], 11 );
		add_action ( 'admin_menu' , [ $this , '_bootstrap_admin_page' ], 12 );
	}

	/**
	 * Set default user capability if none provided
	 * 
	 * @return void
	 */
	function bootstrap(){
		if ( ! $this->capability )
			$this->capability = 'manage_options';
	}
	
	/**
	 * Add WordPress toplevel or submenu page
	 */
	public function add_menu_page(){

		if ( ! $this->parent ){
			$this->hook_suffix = \add_menu_page( 
				$this->title, 
				$this->menu_title, 
				$this->capability, 
				$this->slug, 
				[ $this , 'render_admin_page' ],
				$this->icon_url, 
				$this->position
			);
		}else{
			$this->hook_suffix = \add_submenu_page(
				$this->parent, 
				$this->title, 
				$this->menu_title, 
				$this->capability, 
				$this->slug, 
				[ $this , 'render_admin_page' ]
			);
			
		}
		
	}
	
	/**
	 * REGISTER ADMIN PAGE
	 * 
	 * hook_suffix is KNOWN
	 * get_current_screen() is NOT
	 * 
	 * Runs for EVERY AdminMenuPage instance
	 * AdminNotice->onPage() works
	 */
	function _bootstrap_admin_page(){
		add_action ( 'load-'.$this->hook_suffix , [ $this , '_admin_page_setup' ] );
	}
	
	/**
	 * SHOW ADMIN PAGE
	 * 
	 * current_screen IS AVAILABLE
	 * 
	 * Only runs on actual screen showing
	 * AdminNotice->onPage() redundant
	 */
	public function _admin_page_setup(){

		if ($this->scripts)
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
	}

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
     * Get the capability required to view the admin page.
     *
     * @return string
     */
    public function get_capability()
    {
        return $this->capabailty;
    }
 
    /**
     * Get the title of the admin page in the WordPress admin menu.
     *
     * @return string
     */
    public function get_menu_title()
    {
        return $this->menu_title;
    }
 
    /**
     * Get the title of the admin page.
     *
     * @return string
     */
    public function get_title()
    {
		return $this->title;
    }
 
    /**
     * Get the parent slug of the admin page.
     *
     * @return string
     */
    public function get_parent_slug()
    {
        return $this->parent;
    }
 
    /**
     * Get the slug used by the admin page.
     *
     * @return string
     */
    public function get_slug()
    {
        return $this->slug;
    }
 
 
    /**
     * Render the top section of the plugin's admin page.
     */
    public function render_admin_page()
    {
		// @todo if render callback supplied - add shortcircuit hook here
		// execute render callback and return early

		if ($this->render_cb && is_callable($this->render_cb)){
			call_user_func($this->render_cb);
			return;
		}else if($this->render_tpl && is_readable($this->render_tpl)){
			include $this->render_tpl;
			return;
		}
    }
 
    /**
     * Renders the given template if it's readable.
     *
     * @param string $template
     */
    private function render_template($template)
    {
         if (!is_readable($template)) {
            return;
        }
 
        include $template;
    }
}
endif;