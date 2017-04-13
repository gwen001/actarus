<?php

require_once( dirname(__FILE__).'/vendor/actarus/custom/Config.php' );


// config
$config = Config::getInstance();
$config->actarusPath = dirname(__FILE__);
$config->appPath     = $config->actarusPath.'/app';
$config->consolePath = $config->actarusPath.'/app/console';
$config->configPath  = $config->appPath.'/config';
$config->logPath     = $config->appPath.'/logs';
$config->loadParameters( $config->configPath.'/parameters.yml', 'parameters' );
$config->loadParameters( $config->configPath.'/myparameters.yml', 'parameters' );
//var_dump( $config );
//exit();

$db = $config->db = mysqli_connect( $config->parameters['database_host'], $config->parameters['database_user'], $config->parameters['database_password'], $config->parameters['database_name'] );


/*
$q = "SELECT * FROM arus_server AS s WHERE project_id='3' AND created_at>='2017-03-23 00:00:00'";
$r = $db->query( $q );

while( ($s=$r->fetch_object()) )
{
	$cmd = $config->consolePath.' arus:task:create -t 23 -i '.$s->entity_id;
	echo $cmd."\n";
	system( $cmd );
}
*/

/*
$q = "SELECT h.* FROM arus_host as h left join arus_entity_task as t on h.entity_id=t.entity_id where t.command like 'testhttp%' and t.output like '%443:OK%'";
$r = $db->query( $q );

while( ($s=$r->fetch_object()) )
{
	$cmd = $config->consolePath.' arus:task:create -t 42 -i '.$s->entity_id;
	echo $cmd."\n";
	system( $cmd );
}
*/

/*
$q = "SELECT h.* FROM arus_host as h WHERE h.project_id='331'";
$r = $db->query( $q );

while( ($s=$r->fetch_object()) )
{
	$cmd = $config->consolePath.' arus:task:create -t 42 -i '.$s->entity_id;
	echo $cmd."\n";
	system( $cmd );
	$cmd = $config->consolePath.' arus:task:create -t 40 -i '.$s->entity_id;
	echo $cmd."\n";
	system( $cmd );
}
*/

/*
$t_buckets = file( '/home/gwen/Sécurité/bug-bounty/h1-s3-buckets.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
var_dump( count($t_buckets) );

foreach( $t_buckets as $b )
{
	$tmp = explode( '-', $b );
	
	$q_select = "SELECT * FROM arus_project WHERE name LIKE '".$tmp[0]."'";
	$r = $db->query( $q_select );
	if( !$r || !$r->num_rows ) {
		continue;
	}
	
	$project = $r->fetch_object();
	
	$q_insert = "INSERT INTO arus_bucket (project_id,name,status,created_at,updated_at) VALUES ('".$project->id."','".$b."','0',NOW(),NOW())";
	echo $q_insert."\n";
	$db->query( $q_insert );
}
*/


$db->close();

exit( 0 );

?>