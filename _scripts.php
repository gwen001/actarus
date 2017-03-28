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



$db->close();

exit( 0 );

?>