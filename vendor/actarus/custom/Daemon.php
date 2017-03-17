<?php

abstract class Daemon
{
	/**
	 * exit sortie
	 *
	 * @var array
	 */
	private $callback = null;

	/**
	 * delay between 2 loops
	 *
	 * @var integer
	 */
	private $delay = 500000; // half a second

	/**
	 * child counter
	 *
	 * @var integer
	 */
	private $nb_child = 0;

	/**
	 * max child number
	 *
	 * @var integer
	 */
	private $max_child = 1;

	/**
	 * lock file
	 *
	 * @var Locker
	 */
	private $locker = null;

	/**
	 * stop file path
	 *
	 * @var string
	 */
	private $stop_file = '';

	/**
	 * true if the deamon is running
	 *
	 * @var boolean
	 */
	protected $run = false;

	/**
	 * pid of this process
	 *
	 * @var integer
	 */
	private $pid = null;

	/**
	 * array of functions that will be called periodically
	 *
	 * @var array
	 */
	private $tic = array();


	/**
	 * constructor
	 *
	 */
	protected function __construct()
	{
		$this->locker = Locker::getInstance();
		$this->stop_file = basename($_SERVER['PHP_SELF']).'.stop';
		//}


		//public function init( $max_child=1, $callback=null )
		//{
		$pid = pcntl_fork();

		if( $pid == -1 ) {
			// fork error
			throw new Exception( 'cannot fork the currently running process, exiting...' );
			//return false;
		} elseif( $pid ) {
			// parent process, leaving
			throw new Exception( 'exiting parent process...' );
			//return false;
		}

		$this->pid = getmygid();

		// unlink process from terminal
		posix_setsid();

		declare( ticks=1 );
		pcntl_signal( SIGHUP,  array($this,'signal_handler') );
		pcntl_signal( SIGINT,  array($this,'signal_handler') );
		pcntl_signal( SIGQUIT, array($this,'signal_handler') );
		pcntl_signal( SIGABRT, array($this,'signal_handler') );
		//pcntl_signal( SIGKILL, array($this,'signal_handler') );
		pcntl_signal( SIGIOT,  array($this,'signal_handler') );
		pcntl_signal( SIGCHLD, array($this,'signal_handler') );
		pcntl_signal( SIGTERM, array($this,'signal_handler') );
		pcntl_signal( SIGTSTP, array($this,'signal_handler') );
	}


	/**
	 * signal handler
	 *
	 * @param integer $signal
	 */
	public function signal_handler( $signal )
	{
		switch( $signal )
		{
			case SIGCHLD:
				$this->nb_child--;
				// permet d'éliminer les zombies
				pcntl_waitpid( -1, $status, WNOHANG );
				break;
			default:
				call_user_func( $this->quit );
				break;
		}
	}


	/**
	 * create a new process and a new script
	 *
	 * @param string $path path of the script to execute
	 * @param array $args array to pass to the new script
	 */
	protected function fork( $path, $args=array() )
	{
		if( $this->nb_child >= $this->max_child )
		{
			// max_child reached
			throw new Exception( 'maximum child reached' );
		}
		else
		{
			$pid = pcntl_fork();

			if( $pid == -1 ) {
				// fork error
				throw new Exception( 'cannot fork the currently running process' );
			} elseif( $pid ) {
				$this->nb_child++;
			} else {
				// child process, everything ok
				pcntl_exec( $path, $args );
			}
		}
	}


	/**
	 * function executed when launching the daemon
	 *
	 */
	protected function run()
	{
		$this->run = true;
		if( !$this->locker->start() ) {
			$this->run = false;
			throw new Exception( 'script already running' );
		}
		$this->testStop();
	}


	/**
	 * function executed at each loop BEFORE the main function
	 *
	 */
	protected function preLoop()
	{
		$this->testStop();

		foreach( $this->tic as &$t ) {
			if( $t['current_loop'] >= $t['period'] ) {
				call_user_func( $t['func'] );
				$t['current_loop'] = 0;
			}
			$t['current_loop']++;
		}
	}


	/**
	 * function executed at each loop AFTER the main function
	 *
	 */
	protected function postLoop()
	{
		usleep( $this->delay );
	}


	/**
	 * check if the stop file exist
	 *
	 * @param boolean $force true if we want to force the stop
	 */
	protected function testStop( $force=false )
	{
		if( file_exists($this->stop_file) ) {
			$this->run = false;
			unlink( $this->stop_file );
		}
		if( $force ) {
			$this->run = false;
		}
	}


	/**
	 * leave the daemon
	 *
	 */
	protected function quit()
	{
		if( $this->locker->isLocked() ) {
			$this->locker->stop();
		}

		if( !is_null($this->callback) && is_callable($this->callback) ) {
			call_user_func( $this->callback );
		} else {
			exit( 0 );
			//posix_kill( $this->pid, SIGQUIT );
		}
	}


	/**
	 * setter delay
	 *
	 * @param integer $delay
	 * @return boolean
	 */
	public function setDelay( $delay )
	{
		$delay = (int)$delay;
		if( $delay <= 0 ) {
			return false;
		}

		$this->delay = $delay;
		return true;
	}
	public function getDelay() {
		return $this->delay;
	}


	/**
	 * setter max_child
	 *
	 * @param integer $max_child
	 * @return boolean
	 */
	public function setMaxChild( $max_child )
	{
		$max_child = (int)$max_child;
		if( $max_child <= 0 ) {
			return false;
		}

		$this->max_child = $max_child;
		return true;
	}
	public function getMaxChild() {
		return $this->max_child;
	}


	/**
	 * setter callback
	 *
	 * @param array $callback
	 * @return boolean
	 */
	public function setCallback( $callback )
	{
		if( is_callable($callback) ) {
			return false;
		}

		$this->callback = $callback;
		return true;
	}


	/**
	 * add a "tic", a function that will be executed periodically, every n loop
	 *
	 * @param integer $n execute the function every $n loop
	 * @param array $func function to execute
	 */
	public function addTic( $n, $func )
	{
		$this->tic[] = array(
			'current_loop' => 0,
			'period' => $n,
			'func' => $func
		);
	}


	/**
	 * return how many place are available to create new children
	 *
	 */
	protected function getFreePlace()
	{
		return ($this->max_child-$this->nb_child);
	}
}
