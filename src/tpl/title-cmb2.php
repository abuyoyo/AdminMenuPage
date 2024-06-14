<?php
/**
 * Template for CMB2 page title
 * 
 * @var WPHelper\CMB2_OptionsPage $this
 */
if ( $this->cmb->prop( 'title' ) ):
?>
	<h1><?php echo wp_kses_post( $this->cmb->prop( 'title' ) ); ?></h1>
	<hr class="wp-header-end">
<?php
endif;
