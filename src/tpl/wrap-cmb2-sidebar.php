<?php
/**
 * Template for CMB2 Options Page
 * 
 * @var array $args Additional arguments passed to template.
 * @var CMB2_Options_Hookup $hookup
 */
extract($args);
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
<div class="wrap cmb2-options-page option-<?php echo esc_attr( sanitize_html_class( $hookup->option_key ) ); ?> wph-wrap wph-wrap-cmb2-sidebar">

	<?php include 'title-cmb2.php' ?>
	<?php include 'tab-nav-cmb2.php' ?>

	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">

			<!-- main content -->
			<div id="post-body-content">
				<?php include 'form-cmb2.php' ?>
			</div><!-- #post-body-content -->

			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">
				<?php load_template( __DIR__ . '/do-meta-boxes.php', false, $args ); ?>
			</div><!-- #postbox-container-1 .postbox-container -->
		</div><!-- #post-body -->
		<div class="clear"></div>
	</div><!-- #poststuff -->
</div><!-- .wrap -->