<?php
/**
 * Plugin Info Metabox - .wph-debug
 * 
 * @since 0.26
 * 
 * @todo Render wph_debug in its own meta-box.
 * @todo Move wph_debug functionality from template file to dedicated method/class.
 */

use WPHelper\AdminPage;
use WPHelper\MetaBox;
use WPHelper\PluginCore;
use WPHelper\Utility\Singleton;
use WPHelper\DatabaseTable;

if (class_exists(AdminPage::class)){
	$wph_admin_ref = new ReflectionClass(AdminPage::class);
	$wph_admin_file = $wph_admin_ref->getFileName();
	$wph_admin_composer =  json_decode(file_get_contents( dirname( $wph_admin_file, 2 ) . '/composer.json' )) ;
	$wph_admin_loc = wph_reduce_path( dirname( $wph_admin_file, 2 ) );
}

if (class_exists(PluginCore::class)){
	$wph_pc_ref = new ReflectionClass(PluginCore::class);
	$wph_pc_file = $wph_pc_ref->getFileName();
	$wph_pc_composer =  json_decode(file_get_contents( dirname( $wph_pc_file ) . '/composer.json' )) ;
	$wph_pc_loc = wph_reduce_path( dirname( $wph_pc_file ) );
}
	
if (class_exists(MetaBox::class)){
	$wph_mb_ref = new ReflectionClass(MetaBox::class);
	$wph_mb_file = $wph_mb_ref->getFileName();
	$wph_mb_composer =  json_decode(file_get_contents( dirname( $wph_mb_file ) . '/composer.json' )) ;
	$wph_mb_loc = wph_reduce_path( dirname( $wph_mb_file ) );
}

if (trait_exists(Singleton::class)){
	$wph_util_ref = new ReflectionClass(Singleton::class);
	$wph_util_file = $wph_util_ref->getFileName();
	$wph_util_composer =  json_decode(file_get_contents( dirname( $wph_util_file, 2 ) . '/composer.json' )) ;
	$wph_util_loc = wph_reduce_path( dirname( $wph_util_file, 2 ) );
}

if (function_exists('wph_die')){
	$wph_util_func = new ReflectionFunction('wph_die');
	$wph_util_func_file = $wph_util_func->getFileName();
	$wph_util_func_composer =  json_decode(file_get_contents( dirname( $wph_util_func_file, 3 ) . '/composer.json' )) ;
	$wph_util_func_loc = wph_reduce_path( dirname( $wph_util_func_file, 3 ) );
}

if (class_exists(DatabaseTable::class)){
	$wph_db_ref = new ReflectionClass(DatabaseTable::class);
	$wph_db_file = $wph_db_ref->getFileName();
	$wph_db_composer =  json_decode(file_get_contents( dirname( $wph_db_file ) . '/composer.json' )) ;
	$wph_db_loc = wph_reduce_path( dirname( $wph_db_file ) );
}

/**
 * Custom function for WPH_DEBUG plugin-info meta-box
 * 
 * Show relative path to known directories WP_CONTENT_DIR or ABSPATH
 * 
 * @since 0.32
 */
function wph_reduce_path($path) {
	return trailingslashit(
		str_replace(
			[
				wp_normalize_path( trailingslashit( WP_CONTENT_DIR ) ),
				wp_normalize_path( ABSPATH ),
			],
			'',
			wp_normalize_path($path)
		)
	);
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
	Location: <?php echo $wph_admin_loc; ?><br/>
<?php endif; ?>

<?php if ( ! empty( $wph_pc_composer ) ): ?>
<hr>
<p>
	PluginCore: <?php echo $wph_pc_composer->version; ?><br/>
	Location: <?php echo $wph_pc_loc; ?><br/>
<?php endif; ?>

<?php if ( ! empty( $wph_mb_composer ) ): ?>
<hr>
<p>
	MetaBox: <?php echo $wph_mb_composer->version; ?><br/>
	Location: <?php echo $wph_mb_loc; ?><br/>
<?php endif; ?>

<?php if ( ! empty( $wph_util_composer ) ): ?>
<hr>
<p>
	Utility: <?php echo $wph_util_composer->version; ?><br/>
	Location: <?php echo $wph_util_loc; ?><br/>
<?php endif; ?>

<?php if ( ! empty( $wph_util_func_composer ) ): ?>
	functions: <?php echo $wph_util_func_composer->version; ?><br/>
	Location: <?php echo $wph_util_func_loc; ?><br/>
<?php endif; ?>

<?php if ( ! empty( $wph_db_composer ) ): ?>
<hr>
<p>
	DatabaseTable: <?php echo $wph_db_composer->version; ?><br/>
	Location: <?php echo $wph_db_loc; ?><br/>
<?php endif; ?>

</p>