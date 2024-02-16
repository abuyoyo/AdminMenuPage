<?php
/**
 * Template for CMB2 Options Page
 * 
 * @var WPHelper\CMB2_OptionsPage $this
 * @var CMB2_Options_Hookup $hookup
 */
?>
<div class="wrap cmb2-options-page option-<?php echo esc_attr( sanitize_html_class( $hookup->option_key ) ); ?> wph-wrap wph-wrap-cmb2-simple">
	<?php include 'title-cmb2.php' ?>
	<?php include 'tab-nav-cmb2.php' ?>
	<?php include 'form-cmb2.php' ?>
</div>