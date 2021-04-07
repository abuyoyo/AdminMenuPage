<?php
/**
 * Template for CMB2 Options Page
 * 
 * @var WPHelper\CMB2_Options_Page $this
 * @var CMB2_Options_Hook $hookup
 * @global array $tabs
 */

$tabs = $hookup->get_tab_group_tabs();

?>
 <div class="wrap cmb2-options-page option-<?php echo esc_attr( sanitize_html_class( $hookup->option_key ) ); ?>">
	<?php if ( $this->cmb->prop( 'title' ) ) : ?>
		<h2><?php echo wp_kses_post( $this->cmb->prop( 'title' ) ); ?></h2>
	<?php endif; ?>
	<?php if ( count( $tabs ) > 1 ) : ?>
		<?php $hookup->options_page_tab_nav_output(); ?>
	<?php endif; ?>
	<form class="cmb-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" id="<?php echo $this->cmb->cmb_id; ?>" enctype="multipart/form-data" encoding="multipart/form-data">
		<input type="hidden" name="action" value="<?php echo esc_attr( $hookup->option_key ); ?>">
		<?php $hookup->options_page_metabox(); ?>
		<?php submit_button( esc_attr( $this->cmb->prop( 'save_button' ) ), 'primary', 'submit-cmb' ); ?>
	</form>
</div>