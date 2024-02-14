<?php
/**
 * Settings-Page settings form
 * 
 * Print WordPress settings form and submit button.
 */
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
	/** @var WPHelper\AdminPage $this */
	settings_fields( $this->settings_page->option_group );// Print hidden setting fields
	do_settings_sections( $this->settings_page->page );// Print title, info callback and form-table
	submit_button();
	?>
</form>