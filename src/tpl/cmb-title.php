<?php
/**
 * Template for CMB2 page title
 * 
 * @var WPHelper\CMB2_Options_Page $this
 */
if ( $this->cmb->prop( 'title' ) ) {
	echo '<h2>' . wp_kses_post( $this->cmb->prop( 'title' ) ) . '</h2>';
}