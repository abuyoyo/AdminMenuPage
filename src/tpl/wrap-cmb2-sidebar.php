<?php
/**
 * Template for CMB2 Options Page
 * 
 * @var WPHelper\CMB2_OptionsPage $this
 * @var CMB2_Options_Hookup $hookup
 */
?>
<style>
	/* 
	 * fix top container alignment issues when using 2 columns
	 * This assumes first item is a title item
	 */
	.cmb2-options-page .cmb2-wrap .cmb-type-title:first-of-type {
		margin-top: 0;
	}
</style>
<div class="wrap cmb2-options-page option-<?php echo esc_attr( sanitize_html_class( $hookup->option_key ) ); ?>">

	<?php include 'title-cmb2.php' ?>
	<?php include 'tab-nav-cmb2.php' ?>

	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">

			<!-- main content -->
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable tabs-content">
					<?php include 'form-cmb2.php' ?>
				</div><!-- .meta-box-sortables -->
			</div><!-- #post-body-content -->

			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">
				<div class="meta-box-sortables">
					<?php do_action("wphelper/adminpage/plugin_info_box/{$this->admin_page->get_slug()}"); ?>
				</div><!-- .meta-box-sortables -->
			</div><!-- #postbox-container-1 .postbox-container -->
		</div><!-- #post-body -->
		<div class="clear"></div>
	</div><!-- #poststuff -->
</div><!-- .wrap -->