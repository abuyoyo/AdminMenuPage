<?php
/**
 * AdminMenuPage
 * 
 * Helper class
 * Create WordPress admin pages easily
 * 
 * @author  abuyoyo
 * @version 0.6
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
		$this->template = rtrim( $template, '/' );
		
		$this->parent = $parent;

		$this->icon_url = $icon_url;
		$this->position = $position;

		$this->scripts = $scripts;
    }
	
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
	
	function _bootstrap_admin_page(){
		add_action ( 'load-'.$this->hook_suffix , [ $this , '_admin_page_setup' ] );
	}
	
	public function _admin_page_setup(){
		
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
	}

	public function admin_enqueue_scripts($hook) {

		// redundant
		// this only gets called on load-{$this->hook_suffix} anyway
		if( $hook != $this->hook_suffix ) {
			return;
		}

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
		
        $this->render_template($this->template);
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
 
        include $this->template;
    }
}
endif;