<div class="wrap">
	<h1><?= get_admin_page_title() ?></h1>

	<div class="card">
		<h3>WPHelper\AdminPage</h3>
		<p>Please provide a template file or callback function to render this page
		<br />Like so:
		</p>
		<pre><code style="display: block;">new WPHelper\AdminPage(
	[
		'slug'     => '<?=$this->slug?>',
		'title'    => '<?=$this->title?>',
		<strong><em>'render' => 'callback_or_tpl_file',</em></strong>
	]
);</code></pre>
	</div>
</div>