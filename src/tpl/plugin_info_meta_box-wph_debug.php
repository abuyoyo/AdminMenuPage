<?php
/**
 * Plugin Info Metabox - .wph-debug
 * 
 * @since 0.26
 */

use WPHelper\AdminPage;
use WPHelper\MetaBox;
use WPHelper\PluginCore;
use WPHelper\Utility\Singleton;
use WPHelper\DatabaseTable;

if (class_exists(AdminPage::class)){
	$wph_admin_ref = new ReflectionClass(AdminPage::class);
	$wph_admin_file = $wph_admin_ref->getFileName();
	$wph_admin_composer =  json_decode(file_get_contents( dirname( dirname( $wph_admin_file ) ) . '/composer.json' )) ;
}

if (class_exists(PluginCore::class)){
	$wph_pc_ref = new ReflectionClass(PluginCore::class);
	$wph_pc_file = $wph_pc_ref->getFileName();
	$wph_pc_composer =  json_decode(file_get_contents( dirname( $wph_pc_file ) . '/composer.json' )) ;
}
	
if (class_exists(MetaBox::class)){
	$wph_mb_ref = new ReflectionClass(MetaBox::class);
	$wph_mb_file = $wph_mb_ref->getFileName();
	$wph_mb_composer =  json_decode(file_get_contents( dirname( $wph_mb_file ) . '/composer.json' )) ;
}

if (trait_exists(Singleton::class)){
	$wph_util_ref = new ReflectionClass(Singleton::class);
	$wph_util_file = $wph_util_ref->getFileName();
	$wph_util_composer =  json_decode(file_get_contents( dirname( dirname( $wph_util_file ) ) . '/composer.json' )) ;
}

if (class_exists(DatabaseTable::class)){
	$wph_db_ref = new ReflectionClass(DatabaseTable::class);
	$wph_db_file = $wph_db_ref->getFileName();
	$wph_db_composer =  json_decode(file_get_contents( dirname( $wph_db_file ) . '/composer.json' )) ;
}
?>
<style>
	.inside {
		word-wrap: break-word;
	}
</style>
<?php if ( ! empty( $wph_admin_composer ) ): ?>
<hr>
<p>
	AdminPage: <?php echo $wph_admin_composer->version; ?><br/>
	Location: <?php echo $wph_admin_file; ?><br/>
<?php endif; ?>

<?php if ( ! empty( $wph_pc_composer ) ): ?>
<hr>
<p>
	PluginCore: <?php echo $wph_pc_composer->version; ?><br/>
	Location: <?php echo $wph_pc_file; ?><br/>
<?php endif; ?>

<?php if ( ! empty( $wph_mb_composer ) ): ?>
<hr>
<p>
	MetaBox: <?php echo $wph_mb_composer->version; ?><br/>
	Location: <?php echo $wph_mb_file; ?><br/>
<?php endif; ?>

<?php if ( ! empty( $wph_util_composer ) ): ?>
<hr>
<p>
	Utility: <?php echo $wph_util_composer->version; ?><br/>
	Location: <?php echo $wph_util_file; ?><br/>
<?php endif; ?>

<?php if ( ! empty( $wph_db_composer ) ): ?>
<hr>
<p>
	DatabaseTable: <?php echo $wph_db_composer->version; ?><br/>
	Location: <?php echo $wph_db_file; ?><br/>
<?php endif; ?>

</p>