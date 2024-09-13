<?php
/**
 * Plugin Info Metabox - .inside
 * 
 * @var array $args Arguments passed to template.
 * @var array $plugin_data
 * @var string $repo_href
 * @var string $repo_text
 * @var string $update_message
 */
extract($args);
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
<?php if ( ! empty( $repo_href ) ): ?>
	Repo: <a href="<?php echo $repo_href; ?>"><?php echo $repo_text; ?></a><br/>
<?php endif; ?>
<?php if ( ! empty( $update_message ) ): ?>
	Last Updated: <?php echo $update_message; ?>
<?php endif; ?>
</p>
