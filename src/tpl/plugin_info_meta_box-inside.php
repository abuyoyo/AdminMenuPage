<?php
/**
 * Plugin Info Metabox - .inside
 */
?>
<h3 style="font-weight: 100; font-size: 1.5em;"><?php echo $plugin_data['Name']; ?></h3>
<p>
<?php if ( ! empty( $plugin_data['Version'] ) ): ?>
	Version: <?php echo $plugin_data['Version']; ?><br/>
<?php endif; ?>
<?php if ( ! empty( $plugin_data['Author'] ) ): ?>
	Author: 
	<?php if ( ! empty( $plugin_data['AuthorURI'] ) ): ?>
		<a href="<?php echo $plugin_data['AuthorURI'] ?>"><?php echo $plugin_data['Author']; ?></a><br/>
	<?php else: ?>
		<?php echo $plugin_data['Author']; ?><br/>
	<?php endif; ?>
<?php endif; ?>
<?php if ( ! empty( $plugin_data['UpdateURI'] ) || ! empty( $plugin_data['PluginURI'] ) ): ?>
	Repo: <a href="<?php echo $plugin_data['UpdateURI'] ?: $plugin_data['PluginURI']; ?>">
		<?php echo $plugin_data['TextDomain'] ?? $this->$plugin_core->slug(); ?>
	</a><br/>
<?php endif; ?>
<?php if ( ! empty( $update_message ) ): ?>
	Last Updated: <?php echo $update_message; ?>
<?php endif; ?>
</p>
<?php
/**
 * Print WPHelper debug info in plugin info meta box
 * 
 * @since 0.26
 * 
 * @todo Render wph_debug in its own meta-box.
 */ 
if ( defined('WPH_DEBUG') && WPH_DEBUG ) {
	/** @var WPHelper\PluginInfoMetaBox $this */
	$this->wph_debug();
}