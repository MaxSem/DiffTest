<?php

if ( function_exists( 'wikidiff2_inline_diff' ) ) {
	//die( "wikidiff2 not found, nothing to test\n" );
}
ini_set( 'user_agent', 'Hi, Domas!' );
// Bail out early in case of any problems
error_reporting( E_ALL | E_STRICT );
set_error_handler( function( $errno , $errstr ) {
	echo $errstr;
	die ( 1 );
} );

require 'Api.php';
require 'Change.php';

if ( !is_dir( 'diffs' ) ) {
	mkdir( 'diffs' );
}

$numDiffs = 500;
$site = "http://en.wikipedia.org/w";
$apiUrl = "$site/api.php";
$indexUrl = "$site/index.php";

$recentChanges = Api::request( array(
	'action' => 'query',
	'list' => 'recentchanges',
	'rctype' => 'edit',
	'rclimit' => 'max',
) );

$changes = array();
foreach ( $recentChanges['query']['recentchanges'] as $rc ) {
	$changes[] = new Change( $rc['title'], $rc['old_revid'], $rc['revid'] );
}

$count = count( $changes );
echo "Found $count changes\n";

$count = 0;
foreach ( $changes as $change ) {
	$id = sprintf( "%04d", $count );
	echo "\n---------------------------------------\n[$id] {$change->page}\n";
	if ( !$change->load() ) {
		echo "Not all content loaded, skipping\n";
	}
	$time = microtime( true );
	$diff = wikidiff2_inline_diff( $change->prev, $change->next, 2 );
	$time = microtime( true ) - $time;
	echo "Diffed in {$time}s\n";

	file_put_contents( "diffs/$id",
		"{$change->page}
$indexUrl?diff={$change->nextId}&oldid={$change->prevId}
{$time}s


$diff"
	);
	$count++;
}
