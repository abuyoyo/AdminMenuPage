<style>
	pre code{
		display: block;
	}
</style>

<div class="card">
	<h1>WPHelper\AdminMenuPage</h1>
	<h2><?php esc_html( get_admin_page_title() ) ?></h2>
	<p>Please provide a template file or callback function to render this page
	<br />Like so:
	</p>
	<pre><code>
new AdminMenuPage(
  [
    'render' => 'callback_or_tpl_file',
  ]
);

</code></pre>
</div>