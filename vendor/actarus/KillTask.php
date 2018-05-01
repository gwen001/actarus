<?php

class KillTask extends Daemon
{
	/**
	 * instance var
	 *
	 * @var KillTask
	 */
	private static $instance;

	/**
	 * config
	 *
	 * @var Config
	 */
	private $config = null;


	/**
	 * logger
	 *
	 * @var Logger
	 */
	private $logger = null;


	/**
	 * constructor
	 *
	 */
	protected function __construct() {
		parent::__construct();
	}


	/**
	 * this object cannot be cloned
	 *
	 */
	private function __clone() {
	}


	/**
	 * singleton
	 *
	 * @return KillTask
	 */
	public static function getInstance()
	{
		if( !isset(self::$instance) ) {
			$class = __CLASS__;
			$c = new $class;
			$c->logger = Logger::getInstance();
			$c->config = Config::getInstance();
			self::$instance = $c;
		}

		return self::$instance;
	}


	/**
	 * running the daemon!
	 *
	 */
	public function run()
	{
		$this->addTic( 50, array($this,'renew_logfile') );
		$this->addTic( 50, array($this,'open_db_connection') );

		$this->open_db_connection();

		try {
			parent::run();
		} catch( Exception $e) {
			$this->logger->write( 'error: %s', $e->getMessage() );
			$this->quit();
		}

		while( 1 )
		{
			try {
				parent::preLoop();
			} catch( Exception $e) {
				$this->logger->write( 'pre looping error: %s', $e->getMessage() );
			}

			if( !$this->run ) {
				$this->logger->write( 'stop file found, breaking...' );
				break;
			}

			if( $this->getFreePlace() ) {
				$this->loop();
			}

			try {
				parent::postLoop();
			} catch( Exception $e) {
				$this->logger->write( 'post looping error: %s', $e->getMessage() );
			}

			$this->logger->write( 'looping...' );
		}
	}


	/**
	 * main function called at each loop
	 *
	 */
	private function loop()
	{
		//$q = "SELECT id FROM arus_entity_task WHERE cluster_id='".$this->config->parameters['daemon_cluster_id']."' AND status='".$this->config->parameters['task']['status']['running']."' AND timestampdiff(MINUTE,started_at,now())>'".$this->config->parameters['task']['max_duration']."' LIMIT 0,".$this->getFreePlace();
		$q = "SELECT id FROM arus_entity_task WHERE cluster_id='".$this->config->parameters['daemon_cluster_id']."' AND status='".$this->config->parameters['task']['status']['running']."' AND NOW()>kill_at LIMIT 0,".$this->config->parameters['daemon_kill_max_child'];
		$result = $this->config->db->query( $q );
		if( !$result ) {
			$this->logger->write( $this->config->db->error().' ('.$this->config->db->errno().') '.$q );
			return;
		}

		while( ($cmd=$result->fetch_object()) )
		{
			try {
				$this->fork( $this->config->consolePath, array('arus:task:stop','-t',$cmd->id) );
				continue;
			} catch( Exception $e) {
				$this->logger->write( 'error: %s', $e->getMessage() );
			}
		}
		/*
		$q = "SELECT id FROM arus_entity_task WHERE cluster_id='".$this->config->parameters['daemon_cluster_id']."' AND status='".$this->config->parameters['task']['status']['waiting']."' AND command LIKE 'task_killer%' LIMIT 0,".$this->config->parameters['daemon_kill_max_child'];
		$result = $this->config->db->query( $q );
		if( !$result ) {
			$this->logger->write( $this->config->db->error().' ('.$this->config->db->errno().') '.$q );
			return;
		}

		while( ($cmd=$result->fetch_object()) )
		{
			try {
				$this->fork( $this->config->consolePath, array('arus:task:run','-f','-t',$cmd->id) );
				continue;
			} catch( Exception $e) {
				$this->logger->write( 'error: %s', $e->getMessage() );
			}
		}
		*/
		//$this->logger->write( 'memory: %s | peak: %s', memory_get_usage(true), memory_get_peak_usage(true) );
	}


	/**
	 * leave the daemon
	 *
	 */
	public function quit()
	{
		parent::quit();
	}


	/**
	 * exit callback
	 *
	 * @param integer $signal
	 */
	public function _exit( $signal=0 )
	{
		$logger = Logger::getInstance();
		$logger->write( '----- EXIT (signal=%d) -----', $signal );

		exit( $signal );
	}


	/**
	 * one log file per day
	 *
	 */
	public function renew_logfile()
	{
		$this->logger->enableFile( $this->config->logPath );
		ini_set( 'error_log', $this->logger->getLogFile() );
	}


	/**
	 * reopen database connection
	 *
	 */
	public function open_db_connection()
	{
		if( isset($this->config->db) && $this->config->db ) {
			@$this->config->db->close();
		}

		$this->config->db = mysqli_connect( $this->config->parameters['database_host'], $this->config->parameters['database_user'], $this->config->parameters['database_password'], $this->config->parameters['database_name'] );
		if( !$this->config->db ) {
			$this->logger->write( 'Cannot connect to database' );
		}
	}
}
