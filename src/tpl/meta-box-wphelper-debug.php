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
use WPHelper\PucFactory;

$wph_libraries = [];

if ( class_exists( AdminPage::class ) ) {
	$wph_ref = new ReflectionClass( AdminPage::class );
	$wph_file = $wph_ref->getFileName();
	$wph_composer =  json_decode( file_get_contents( dirname( $wph_file, 2 ) . '/composer.json' ) );

	$wph_libraries[] = [
		'name' => 'AdminPage',
		'ver' => $wph_composer->version,
		'loc' =>  wph_reduce_path( dirname( $wph_file, 2 ) ),
	];
}

if ( class_exists( PluginCore::class ) ) {
	$wph_ref = new ReflectionClass( PluginCore::class );
	$wph_file = $wph_ref->getFileName();
	$wph_composer =  json_decode( file_get_contents( dirname( $wph_file ) . '/composer.json' ) );

	$wph_libraries[] = [
		'name' => 'PluginCore',
		'ver' => $wph_composer->version,
		'loc' =>  wph_reduce_path( dirname( $wph_file ) ),
	];
}
	
if ( class_exists( MetaBox::class ) ) {
	$wph_ref = new ReflectionClass( MetaBox::class );
	$wph_file = $wph_ref->getFileName();
	$wph_composer =  json_decode( file_get_contents( dirname( $wph_file ) . '/composer.json' ) );

	$wph_libraries[] = [
		'name' => 'MetaBox',
		'ver' => $wph_composer->version,
		'loc' =>  wph_reduce_path( dirname( $wph_file ) ),
	];
}

if ( trait_exists( Singleton::class ) ) {
	$wph_ref = new ReflectionClass( Singleton::class );
	$wph_file = $wph_ref->getFileName();
	$wph_composer =  json_decode( file_get_contents( dirname( $wph_file, 2 ) . '/composer.json' ) );

	$wph_libraries[] = [
		'name' => 'Utility',
		'ver' => $wph_composer->version,
		'loc' =>  wph_reduce_path( dirname( $wph_file, 2 ) ),
	];
}

if ( function_exists( 'wph_die' ) ) {
	$wph_util_func = new ReflectionFunction( 'wph_die' );
	$wph_file = $wph_util_func->getFileName();
	$wph_composer =  json_decode( file_get_contents( dirname( $wph_file, 3 ) . '/composer.json' ) );

	$wph_libraries[] = [
		'name' => 'Utility functions',
		'ver' => $wph_composer->version,
		'loc' =>  wph_reduce_path( dirname( $wph_file, 3 ) ),
	];
}

if ( class_exists( DatabaseTable::class ) ) {
	$wph_ref = new ReflectionClass( DatabaseTable::class );
	$wph_file = $wph_ref->getFileName();
	$wph_composer =  json_decode( file_get_contents( dirname( $wph_file ) . '/composer.json' ) );

	$wph_libraries[] = [
		'name' => 'DatabaseTable',
		'ver' => $wph_composer->version,
		'loc' =>  wph_reduce_path( dirname( $wph_file ) ),
	];
}

if ( class_exists( Screen_Meta_Links::class ) ) {
	$wph_ref = new ReflectionClass( Screen_Meta_Links::class );
	$wph_file = $wph_ref->getFileName();

	$wph_libraries[] = [
		'name' => 'Screen_Meta_Links',
		'ver' => get_plugin_data( $wph_file )[ 'Version' ],
		'loc' =>  wph_reduce_path( dirname( $wph_file ) ),
	];
}

if ( class_exists( PucFactory::class ) ){
	$wph_ref = new ReflectionClass( PucFactory::class );
	$wph_file = $wph_ref->getFileName();

	$wph_libraries[] = [
		'name' => 'PluginUpdateChecker',
		'ver' => $wph_ref->getStaticPropertyValue( 'latestCompatibleVersion' ),
		'loc' =>  wph_reduce_path( dirname( $wph_file, 3 ) ),
	];
}

unset( $wph_ref );
unset( $wph_file );
unset( $wph_composer );

?>
<style>
	.inside {
		word-wrap: break-word;
	}
</style>

<?php foreach ( $wph_libraries as $k => $wph_lib ): ?>
<?php if ( $k > 0 ): ?>
	<hr>
<?php endif; ?>
	<p>
		<?php echo $wph_lib[ 'name' ] ?>: <?php echo $wph_lib[ 'ver' ]; ?><br/>
		Location: <?php echo $wph_lib[ 'loc' ]; ?>
	</p>
<?php endforeach;

unset( $wph_libraries );