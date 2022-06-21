<?php
/**
 * Template - Tabs navigation row
 * 
 * Print tabs navigation row.
 * 
 * @var CMB2_Options_Hook $hookup
 */
if ( ! isset( $hookup ) ){
	return;
}

$tabs = $hookup->get_tab_group_tabs();

if ( count( $tabs ) > 1 ){
	$hookup->options_page_tab_nav_output();
}