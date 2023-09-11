<?php
/**
 * Settings-Page settings form
 * 
 * Print WordPress settings form and submit button.
 */
?>
<form method="post" action="options.php">
	<?php
	/** @var WPHelper\AdminPage $this */
	settings_fields( $this->settings_page->option_group );// Print hidden setting fields
	do_settings_sections( $this->get_slug() );// Print title, info callback and form-table
	submit_button();
	?>
</form>