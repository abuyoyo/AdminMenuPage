<?php
/**
 * Plugin Info Metabox
 * 
 * @var array $args Arguments passed to template.
 */
?>
<div id="plugin_info" class="postbox">
	<h2 style="border-bottom: 1px solid #eee;"><span>Plugin Info</span></h2>
	<div class="inside">
		<?php load_template( __DIR__ . '/plugin_info_meta_box-inside.php', false, $args ); ?>
	</div><!-- .inside -->

</div><!-- .postbox -->