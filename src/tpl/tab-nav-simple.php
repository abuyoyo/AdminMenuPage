<?php
/**
 * Template - Tabs navigation row
 * 
 * Print tabs navigation row.
 * 
 * @var WPHelper\AdminPage $admin_page
 */

$tabs = $admin_page->get_tab_group_tabs() ?? [];

if ( count( $tabs ) > 1 ) : ?>
<h2 class="nav-tab-wrapper">
	<?php foreach ( $tabs as $option_key => $tab_title ) : ?>
		<a class="nav-tab<?php if ( $option_key === $_GET['page'] ?? null ) : ?> nav-tab-active<?php endif; ?>" href="<?php menu_page_url( $option_key ); ?>"><?php echo wp_kses_post( $tab_title ); ?></a>
	<?php endforeach; ?>
</h2>
<?php endif;
