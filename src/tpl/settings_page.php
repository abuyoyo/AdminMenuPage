<?php
/**
 * Template for Settings page
 * 
 * included from WPHelper\AdminPage::render_admin_page()
 * so $this is available
 */
?>
<div class="wrap">
	<h1><?= get_admin_page_title() ?></h1>
	<form method="post" action="options.php">
	<?php
		settings_fields( $this->settings_page->option_group );// Print hidden setting fields
		do_settings_sections( $this->get_slug() );// Print title, info callback and form-table
		submit_button();
	?>					
	</form>
</div>