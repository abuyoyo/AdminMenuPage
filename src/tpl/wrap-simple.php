<?php
/**
 * Simple wrap
 * 
 * @var string $ob_content - Render template or callback
 */
?>
<div class="wrap">
	<h1><?= get_admin_page_title() ?></h1>
	<?php include 'tab-nav-cmb2.php' ?>
	<?php echo $ob_content; ?>
</div>