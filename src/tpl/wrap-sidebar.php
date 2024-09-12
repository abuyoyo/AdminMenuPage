<?php
/**
 * Template for Settings Page with Sidebar.
 * 
 * @var array $args Arguments passed to load_template()
 * @var string $ob_content Render template or callback
 * @var WPHelper\AdminPage $admin_page
 */
extract($args);
?>
<div class="wrap wph-wrap wph-wrap-sidebar">
	<h1><?= get_admin_page_title() ?></h1>
	<hr class="wp-header-end">
	<?php include 'tab-nav-simple.php' ?>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">

			<!-- main content -->
			<div id="post-body-content">
				<?php echo $ob_content ?>
			</div><!-- #post-body-content -->

			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">
				<?php load_template( __DIR__ . '/do-meta-boxes.php', false, $args ); ?>
			</div><!-- #postbox-container-1 .postbox-container -->
		</div><!-- #post-body -->
		<div class="clear"></div>
	</div><!-- #poststuff -->
</div><!-- .wrap -->