<?php

class InterpretTask extends Daemon
{
	/**
	 * instance var
	 *
	 * @var InterpretTask
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
	 * @return InterpretTask
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
		$q = "SELECT id FROM arus_entity_task WHERE status='".$this->config->parameters['task']['status']['finished']."' LIMIT 0,".$this->getFreePlace();
		$result = $this->config->db->query( $q );
		if( !$result ) {
			$this->logger->write( $this->config->db->error().' ('.$this->config->db->errno().') '.$q );
			return;
		}

		while( ($cmd=$result->fetch_object()) )
		{
			if( !$this->reserveTask($cmd->id) ) {
				continue;
			}

			try {
				$this->fork( $this->config->consolePath, array('arus:task:interpret','-t',$cmd->id) );
				continue;
			} catch( Exception $e) {
				$this->logger->write( 'error: %s', $e->getMessage() );
			}

			if( !$this->finishTask($cmd->id) ) {
				continue;
			}
		}

		//$this->logger->write( 'memory: %s | peak: %s', memory_get_usage(true), memory_get_peak_usage(true) );
	}


	/**
	 * reserve a task
	 *
	 * @param integer $task_id
	 * @return boolean
	 */
	private function reserveTask( $task_id )
	{
		$q = "UPDATE arus_entity_task SET status='".$this->config->parameters['task']['status']['postreserved']."' WHERE id='".$task_id."'";
		$r = $this->config->db->query( $q );
		if( !$r ) {
			$this->logger->write( $this->config->db->error().' ('.$this->config->db->errno().') '.$q );
		}
		return $r;
	}


	/**
	 * release a task
	 *
	 * @param integer $task_id
	 * @return boolean
	 */
	private function finishTask( $task_id )
	{
		$q = "UPDATE arus_entity_task SET status='".$this->config->parameters['task']['status']['finished']."' WHERE id='".$task_id."'";
		$r = $this->config->db->query( $q );
		if( !$r ) {
			$this->logger->write( $this->config->db->error().' ('.$this->config->db->errno().') '.$q );
		}
		return $r;
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
