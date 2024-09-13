<?php
/**
 * Template - Tabs navigation row
 * 
 * Print tabs navigation row.
 * 
 * @var WPHelper\AdminPage $admin_page
 */
do_action( "wphelper/adminpage/tab_nav/{$admin_page->options()['tab_group']}" );
