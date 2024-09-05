<?php
/**
 * Bootstrap WordPress core meta-boxes to generate metaboxes
 * 
 * @var WPHelper\AdminPage $admin_page
 */
extract($args);
?>
<style>
	/*
	we don't actually have draggable/movable metaboxes
	.hide-if-no-js / hidden classes would help
		*/
	.handle-actions {
		display: none;
	}
	.postbox .postbox-header .hndle {
		cursor: unset;
	}
</style>
<?php
/**
 * Allow meta-boxes to hook to this page ('side' context).
 * 
 * 
 */
do_action('add_meta_boxes', $admin_page->get_hook_suffix(), 'side' );

/**
 * Remove 'Featured Image' meta-box added by core.
 * 
 * @see register_and_do_post_meta_boxes() (wp-admin/includes/meta-boxes.php)
 * @todo Investigate why $thumbnail_support returns true for our pages.
 */ 
remove_meta_box( 'postimagediv', $admin_page->get_hook_suffix(), 'side' );

/**
 * Render meta-boxes
 * 
 * Renders div.meta-box-sortables
 */
do_meta_boxes( $admin_page->get_hook_suffix(), 'side', null );
