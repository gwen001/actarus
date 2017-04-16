#!/usr/bin/php
<?php

function usage( $err=null ) {
	echo 'Usage: '.$_SERVER['argv'][0]." <task_id> <task_pid>\n";
	if( $err ) {
		echo 'Error: '.$err."!\n";
	}
	exit();
}

if( $_SERVER['argc'] != 3 ) {
	usage( 'Invalid argument' );
}


$task_id = (int)$_SERVER['argv'][1];
$task_pid = (int)$_SERVER['argv'][2];
//var_dump( $task_id );
//var_dump( $task_pid );

if( !$task_id ) {
	usage( 'Invalid task ID' );
}
if( !$task_pid ) {
	usage( 'Invalid task PID' );
}

$ps = 'pstree -ap -n '.$task_pid;
exec( $ps, $output );
//var_dump( $output );

if( !count($output) ) {
	usage( 'Processus not found' );
}

$to_kill = [];

foreach( $output as $k=>$line ) {
	$tmp = explode( ',', $line );
	$tmp2 = explode( ' ', $tmp[1] );
	$to_kill[] = preg_replace( '#[^0-9]#', '', $tmp2[0]);
}

$cmd = 'kill -9 '.implode( ' ', $to_kill ).' 2>/dev/null';
//echo $cmd."\n";
//exit();
exec( $cmd );

echo 'Task killed.';

exit();
