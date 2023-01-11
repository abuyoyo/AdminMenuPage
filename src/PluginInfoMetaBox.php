<?php
namespace WPHelper;

use DateTime;

if ( ! class_exists( 'WPHelper\PluginInfoMetaBox' ) ):
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

	/**
	 * @var PluginCore
	 */
	private $plugin_core;

	function __construct( PluginCore $plugin_core )
	{
		$this->plugin_core = $plugin_core;
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

		include __DIR__ . $this->tpl;
	}
}
endif;