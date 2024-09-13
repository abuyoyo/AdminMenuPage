<?php
/**
 * Simple wrap
 * 
 * @var array $args Additional arguments passed to template
 * @var string $ob_content Rendered output
 * @var WPHelper\AdminPage $admin_page
 */
extract($args);
?>
<div class="wrap wph-wrap wph-wrap-simple">
	<h1><?= get_admin_page_title() ?></h1>
	<hr class="wp-header-end">
	<?php include 'tab-nav-simple.php' ?>
	<?php echo $ob_content; ?>
</div>