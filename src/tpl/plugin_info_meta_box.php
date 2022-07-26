<?php
/**
 * Plugin Info Metabox
 */
?>
<style>
	#plugin_info h2 {
		border-bottom: 1px solid #eee;
	}
	#plugin_info h3{
		font-weight: 100;
		font-size: 1.5em;
	}
</style>
<div id="plugin_info" class="postbox">
	<h2><span>Plugin Info</span></h2>
	<div class="inside">
		<h3><?=$plugin_data['Name'] ?></h3>
		<p>
			Version: <?= $plugin_data['Version'] ?><br/>
			Author: <a href="<?= $plugin_data['AuthorURI'] ?>"><?= $plugin_data['Author'] ?></a><br/>
			GitHub: <a href="<?= $plugin_data['PluginURI'] ?>"><?= $plugin_data['TextDomain'] ?></a><br/>
			<?php if ( ! empty( $update_message ) ): ?>Last Updated: <?= $update_message ?><?php endif; ?>
		</p>
	</div><!-- .inside -->

</div><!-- .postbox -->