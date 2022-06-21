<?php
/**
 * Template form.cmb-form
 * 
 * Print form tag used by CMB2 options page.
 * 
 * @var WPHelper\CMB2_Options_Page $this
 * @var CMB2_Options_Hook $hookup
 */
?>
<form class="cmb-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" id="<?php echo $this->cmb->cmb_id; ?>" enctype="multipart/form-data" encoding="multipart/form-data">
	<input type="hidden" name="action" value="<?php echo esc_attr( $hookup->option_key ); ?>">
	<?php $hookup->options_page_metabox(); ?>
	<?php submit_button( esc_attr( $this->cmb->prop( 'save_button' ) ), 'primary', 'submit-cmb' ); ?>
</form>