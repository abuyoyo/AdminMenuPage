<?php
namespace WPHelper;

defined( 'ABSPATH' ) || die( 'No soup for you!' );

if ( ! class_exists( AdminMenuPage::class ) ):
/**
 * AdminMenuPage
 * 
 * Deprecated class. Use AdminPage instead.
 * 
 * @author  abuyoyo
 * 
 * @since 0.2  Class AdminMenuPage
 * @since 0.12 Class AdminMenuPage extends/aliases class AdminPage
 * @since 0.39 Deprecated
 * 
 */
class AdminMenuPage extends AdminPage{

	/**
	 * Constructor.
	 * 
	 * @since 0.39 deprecated
	 *
	 * @param array $options
	 * 
	 * @deprecated
	 */
	public function __construct($options)
	{

		_doing_it_wrong( __METHOD__, 'Deprecated. Use class ' . AdminPage::class . ' instead.', "0.39");

		parent::__construct($options);

	}

}
endif;