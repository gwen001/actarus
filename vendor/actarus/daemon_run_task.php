#!/usr/bin/php
<?php

require_once( dirname(__FILE__).'/Config.php' );
require_once( dirname(__FILE__).'/Daemon.php' );
require_once( dirname(__FILE__).'/Logger.php' );
require_once( dirname(__FILE__).'/Locker.php' );
require_once( dirname(__FILE__).'/RunTask.php' );


// config
$config = Config::getInstance();
$config->actarusPath = dirname(dirname(dirname(__FILE__)));
$config->appPath     = $config->actarusPath.'/app';
$config->consolePath = $config->actarusPath.'/app/console';
$config->configPath  = $config->appPath.'/config';
$config->logPath     = $config->appPath.'/logs';
$config->loadParameters( $config->configPath.'/parameters.yml', 'parameters' );
$config->loadParameters( $config->configPath.'/myparameters.yml', 'parameters' );
$config->daemonDelay = 3000000; // 3 seconds
$config->daemonChild = $config->parameters['daemon_run_max_child'];
if( is_array($config->parameters['daemon_run_task_priority']) && count($config->parameters['daemon_run_task_priority']) ) {
	$config->taskPriority = "'".implode("','",$config->parameters['daemon_run_task_priority'])."'";
} else {
	$config->taskPriority = null;
}
if( is_array($config->parameters['daemon_run_task_ignore']) && count($config->parameters['daemon_run_task_ignore']) ) {
	$config->taskIgnore = "'".implode("','",$config->parameters['daemon_run_task_ignore'])."'";
} else {
	$config->taskIgnore = 0;
}

// logger
$logger = Logger::getInstance();
$logger->setPrefix( 'cmd_' );
$logger->enableFile( $config->logPath );
$logger->write( '------------ START ------------' );
// we want php errors in the same log file
ini_set( 'error_log', $logger->getLogFile() );


// daemon creation
try {
	$daemon = RunTask::getInstance();
} catch( Exception $e) {
	echo $e->getMessage()."\n";
	exit( -1 );
}


$daemon->setDelay( $config->daemonDelay );
$daemon->setMaxChild( $config->daemonChild );
$daemon->setCallback( array('RunTask','_exit') );


// let's go!
$daemon->run();


// the end
$daemon->quit();
