<?php
namespace WPHelper;

use DateTime;

if ( ! class_exists( PluginInfoMetaBox::class ) ):
/**
 * Plugin Info Metabox
 * 
 * Get instance of PluginCore
 * Render default plugin info box template.
 * 
 * @since 0.14
 */
class PluginInfoMetaBox{

	private $tpl = '/tpl/plugin_info_meta_box.php';

	private $tpl_inside = '/tpl/plugin_info_meta_box-inside.php';

	/**
	 * @var PluginCore
	 */
	public $plugin_core;

	function __construct( PluginCore $plugin_core )
	{
		$this->plugin_core = $plugin_core;

		/**
		 * Allow plugins to render or modify plugin info box
		 * 
		 * Call: do_action('wphelper/plugin_info_meta_box/{$slug}')
		 * action used in AdminPage::render_plugin_info_meta_box()
		 * 
		 * @since 0.23
		 */
		add_action( "wphelper/plugin_info_meta_box/{$this->plugin_core->slug()}", [ $this, 'plugin_info_box' ] );
		
		add_action( "wphelper/plugin_info_meta_box/inside/{$this->plugin_core->slug()}", [ $this, 'inside' ] );
	}

	/**
	 * Setup args used in template.
	 * 
	 * @todo move 'repo' setup to method from template
	 */
	function setup_template_args() {

		$plugin_data = $this->plugin_core->plugin_data();


		$last_update = $plugin_data['Last Update'] ?: $plugin_data['Release Date'];
		$last_update = DateTime::createFromFormat('Y_m_d', $last_update);

		if ($last_update) {
			$diff = (int) abs( time() - $last_update->format('U') );

			if ( $diff < (DAY_IN_SECONDS) ){
				$update_message = 'Today';
			}elseif ($diff < (2 * DAY_IN_SECONDS)){
				$update_message = 'Yesterday';
			}else{
				$update_message = human_time_diff($last_update->format('U')) . ' ago';
			}
		} else {
			$update_message = '';
		}
		return compact('plugin_data','update_message');

	}

	/**
	 * PLUGIN INFO BOX
	 * 
	 * Display plugin info meta-box on admin pages
	 * 
	 * @since iac_engine 1.1.0
	 * @since iac_engine 1.2.0 plugin_info_box now a function
	 * @since iac_engine 1.3.0 use 'Last Update' header
	 * @since 0.14             PluginInfoMetaBox::plugin_info_box()
	 * 
	 * @todo rename method render()
	 */
	function plugin_info_box(){
		$args = $this->setup_template_args();
		extract($args);
		include __DIR__ . $this->tpl;
	}


	/**
	 * Only print meta-box .inside
	 * No header.
	 */
	function inside(){
		$args = $this->setup_template_args();
		extract($args);
		include __DIR__ . $this->tpl_inside;
	}
}
endif;