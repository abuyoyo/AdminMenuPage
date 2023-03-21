<?php
/**
 * Template for CMB2 Options Page
 * 
 * @var string $ob_content - Render template or callback
 * @var WPHelper\AdminPage $this
 */
?>
<div class="wrap">
	<h1><?= get_admin_page_title() ?></h1>
	<?php include 'tab-nav-simple.php' ?>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">

			<!-- main content -->
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable tabs-content">
					<?php echo $ob_content ?>
				</div><!-- .meta-box-sortables -->
			</div><!-- #post-body-content -->

			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">
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
				do_action('add_meta_boxes', $this->get_hook_suffix(), 'side' );
				
				/**
				 * Remove 'Featured Image' meta-box added by core.
				 * 
				 * @see register_and_do_post_meta_boxes() (wp-admin/includes/meta-boxes.php)
				 * @todo Investigate why $thumbnail_support returns true for our pages.
				 */ 
				remove_meta_box( 'postimagediv', $this->get_hook_suffix(), 'side' );

				/**
				 * Render meta-boxes
				 */
				do_meta_boxes( $this->get_hook_suffix(), 'side', null );
				?>
			</div><!-- #postbox-container-1 .postbox-container -->
		</div><!-- #post-body -->
		<div class="clear"></div>
	</div><!-- #poststuff -->
</div><!-- .wrap -->