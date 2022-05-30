<?php
/**
 * Template for CMB2 Options Page
 * 
 * @var WPHelper\CMB2_Options_Page $this
 * @var CMB2_Options_Hook $hookup
 */
?>
<div class="wrap cmb2-options-page option-<?php echo esc_attr( sanitize_html_class( $hookup->option_key ) ); ?>">
	<?php include 'cmb-title.php' ?>
	<?php include 'tab-nav.php' ?>
	<?php include 'cmb-form.php' ?>
</div>