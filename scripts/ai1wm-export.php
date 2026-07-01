<?php
/**
 * Headless All-in-One WP Migration export.
 * Defines DOING_CRON so Ai1wm_Export_Controller::export() runs ALL stages in a
 * single pass (instead of returning JSON between AJAX calls). Produces a
 * .wpress package in wp-content/ai1wm-backups/.
 *
 * Usage: docker compose cp scripts/ai1wm-export.php wordpress:/tmp/e.php
 *        docker compose exec wordpress wp eval-file /tmp/e.php --allow-root
 */

$secret = get_option( 'ai1wm_secret_key' );
if ( ! $secret ) {
	echo "ERROR: ai1wm_secret_key option is empty\n";
	exit( 1 );
}

$params = array(
	'secret_key' => $secret,
	'storage'    => 'linden-export-' . substr( md5( uniqid( '', true ) ), 0, 8 ),
);

echo "Starting AI1WM export (storage = {$params['storage']})…\n";
Ai1wm_Export_Controller::export( $params );
echo "Export finished.\n";

$dir   = WP_CONTENT_DIR . '/ai1wm-backups';
$files = glob( $dir . '/*.wpress' );
if ( ! $files ) {
	echo "No .wpress file produced.\n";
	exit( 1 );
}
usort( $files, function ( $a, $b ) { return filemtime( $b ) - filemtime( $a ); } );
$f = $files[0];
echo "LATEST_FILE=" . basename( $f ) . "\n";
echo "PATH=" . $f . "\n";
echo "SIZE_MB=" . round( filesize( $f ) / 1048576, 2 ) . "\n";
