<?php
/**
 * WordPress admin 'wrap' div
 * 
 * @var array $args Additional arguments passed to template
 * @var WPHelper\AdminPage $admin_page
 * 
 * @todo separate default card from .wrap element.
 */
extract($args);
?>
<div class="wrap wph-wrap wph-wrap-default">
	<h1><?= get_admin_page_title() ?></h1>
	<hr class="wp-header-end">

	<div class="card">
		<h3>WPHelper\AdminPage</h3>
		<p>Please provide a template file or callback function to render this page
		<br />Like so:
		</p>
		<pre><code style="display: block;">new WPHelper\AdminPage(
	[
		'slug'     => '<?= $admin_page->get_slug() ?>',
		'title'    => '<?= $admin_page->get_title() ?>',
		<strong><em>'render' => 'callback_or_tpl_file',</em></strong>
	]
);</code></pre>
	</div>
</div>