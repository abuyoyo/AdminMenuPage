<?php
/**
 * Simple wrap
 * 
 * @var string $ob_content - Render template or callback
 */
?>
<div class="wrap wph-wrap wph-wrap-simple">
	<h1><?= get_admin_page_title() ?></h1>
	<hr class="wp-header-end">
	<?php include 'tab-nav-simple.php' ?>
	<?php echo $ob_content; ?>
</div>