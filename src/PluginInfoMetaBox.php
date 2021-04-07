<?php
namespace WPHelper;

use DateTime;
use function get_plugin_data;
/**
 * Plugin Info Metabox
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
	 */
	function plugin_info_box(){

		$plugin_data = get_plugin_data( $this->plugin_core->file() , false ); // false = no markup (i think)


		$last_update = $plugin_data['Last Update'];
		$last_update = DateTime::createFromFormat('Y_m_d', $last_update);

		// $last_update = new DateTime('now');
		// $last_update->add(new DateInterval('P1D'));
		// $last_update->add(new DateInterval('P2D'));
		if ($last_update):
			$diff = (int) abs( time() - $last_update->format('U') );

			if ( $diff < (DAY_IN_SECONDS) ){
				$update_message = 'Today';
			}elseif ($diff < (2 * DAY_IN_SECONDS)){
				$update_message = 'Yesterday';
			}else{
				$update_message = human_time_diff($last_update->format('U')) . ' ago';
			}
		else:
			$update_message = "A long, long time ago";
		endif;

		include __DIR__ . $this->tpl;
	}
}