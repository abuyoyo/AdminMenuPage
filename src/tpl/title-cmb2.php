<?php
/**
 * Template for CMB2 page title
 * 
 * @var CMB2 $cmb
 */
if ( $cmb->prop( 'title' ) ):
?>
	<h1><?php echo wp_kses_post( $cmb->prop( 'title' ) ); ?></h1>
	<hr class="wp-header-end">
<?php
endif;
