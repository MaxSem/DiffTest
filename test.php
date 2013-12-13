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
echo "<h1>Found $count changes</h1>\n";

$count = 0;
foreach ( $changes as $change ) {
	$id = sprintf( "%04d", $count );
	$page = htmlspecialchars( $change->page );
	echo "\n<h2>[$id] {$page}</h2>\n";
	if ( !$change->load() ) {
		echo "<b>Not all content loaded, skipping</b><br>\n";
	}
	$time = microtime( true );
	$diff = wikidiff2_inline_diff( $change->prev, $change->next, 2 );
	$time = microtime( true ) - $time;
	echo "Diffed in {$time}s<br>\n";
	$url = htmlspecialchars( "$indexUrl?diff={$change->nextId}&oldid={$change->prevId}" );
	echo "<a href='$url'>$url</a>";

	echo $diff;

	$count++;
}
