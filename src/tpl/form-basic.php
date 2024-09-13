<?php
/**
 * Settings-Page settings form
 * 
 * Print WordPress settings form and submit button.
 * 
 * @var array $args Additional arguments passed to template
 * @var WPHelper\AdminPage $admin_page
 * @var WPHelper\SettingsPage $settings_page
 */
extract($args);
?>
<form method="post" action="options.php">
	<style>
		/** 
		 * Restore common styling
		 * We hijack #poststuff from Edit Post for our sidebar wrap.
		 * Restore h2 formatting for non-#poststuff forms.
		 */
		#poststuff form h2 {
			font-size: 1.3em;
			padding: 0;
			margin: 0;
		}
	</style>
	<?php
	settings_fields( $settings_page->option_group );// Print hidden setting fields
	do_settings_sections( $settings_page->page );// Print title, info callback and form-table
	submit_button();
	?>
</form>