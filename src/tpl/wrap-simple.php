<?php
/**
 * Simple wrap
 */
?>
<div class="wrap">
	<h1><?= get_admin_page_title() ?></h1>
	<?php include 'tab-nav.php' ?>
	<?php echo $ob_content; ?>
</div>