<?php

class Logger
{
	const FIELD_SEP = ' || ';

	/**
	 * tableau d'instances
	 *
	 * @var array
	 */
	private static $instance = array();

	/**
	 * true si on peut logguer les messages dans un fichier, sinon false
	 *
	 * @var boolean
	 */
	private $enable_file = false;

	/**
	 * identifiant du logger
	 *
	 * @var string
	 */
	private $id = '';

	/**
	 * chemin des fichiers de log
	 *
	 * @var string
	 */
	private $path = './log';

	/**
	 * nom du fichier de log
	 *
	 * @var string
	 */
	private $logfile = '';

	/**
	 * prefix des fichiers de log
	 *
	 * @var string
	 */
	private $prefix = 'log_';

	/**
	 * extension des fichiers de log
	 *
	 * @var string
	 */
	private $extension = 'log';

	/**
	 * champ special, information persistante
	 *
	 * @var array
	 */
	private $special_field = array();


	/**
	 * constructeur
	 *
	 */
	private function __construct() {
	}


	/**
	 * singleton
	 *
	 * @return Logger
	 */
	public static function getInstance( $name='' )
	{
		$class = __CLASS__;

		if( !isset(self::$instance[$name]) || !(self::$instance[$name] instanceof $class) ) {
			self::$instance[ $name ] = new $class;
			self::$instance[ $name ]->init();
		}

		return self::$instance[ $name ];
	}


	/**
	 * initialisation
	 *
	 */
	protected function init()
	{
		$this->id = uniqid( '', true );
		$this->id = str_replace( '.', '', $this->id );
	}


	public function log( $message ) {
		$this->write( $message );
	}


	/**
	 * Choix du support de log utilise : bdd ou fichier
	 *
	 * @param string $message message a logguer
	 * @return boolean true ou false selon que le message a ete insere ou pas
	 */
	public function write( $message )
	{
		$args = func_get_args();
		$message = trim( array_shift($args) );
		if( $message == '' ) {
			return false;
		}

		if( count($args) && is_array($args) ) {
			$message = vsprintf( $message, $args );
		}
		$message = preg_replace( '/[[:space:]]+/', ' ', $message );

		$debug = debug_backtrace();
		//print_r( $debug );
		$function = '';
		$file = $debug[0]['file'];
		$line = $debug[0]['line'];

		if( count($debug) > 1 )
		{
			//$defined_functions = get_defined_functions();
			//print_r( $defined_functions );

			//if( in_array($debug[1]['function'], $defined_functions['user']) )
			{
				$function = $debug[1]['function'];
				if( isset($debug[1]['class']) ) {
					$function = $debug[1]['class'].$debug[1]['type'].$function;
				}
			}
		}

		$infos  = '[FILE:'.$file.']';
		$infos .= '[LINE:'.$line.']';
		$infos .= '[FUNC:'.$function.']';

		return $this->writeFile( $message, $infos );
	}


	/**
	 * Loggue un message dans le fichier de log
	 *
	 * @return boolean true ou false selon que le message a ete insere ou pas
	 */
	private function writeFile( $message, $infos='' )
	{
		if( !$this->enable_file ) {
			return false;
		}

		if( file_exists($this->logfile) ) {
			$fp = fopen( $this->logfile, 'a' );
		}
		else {
			$fp = fopen( $this->logfile, 'w' );
			chmod( $this->logfile, 0777 );
		}

		if( !$fp ) {
			return false;
		}

		//$msg  = $this->id;
		$msg  = getmypid();
		//$msg .= self::FIELD_SEP.php_uname('n');
		$msg .= self::FIELD_SEP.date( 'Y-m-d H:i:s' );
		$msg .= self::FIELD_SEP.basename($_SERVER['SCRIPT_FILENAME']);
		$msg .= self::FIELD_SEP.$message;
		$msg .= self::FIELD_SEP.$infos;

		foreach( $this->special_field as $v ) {
			$msg .= self::FIELD_SEP.$v;
		}

		$msg .= "\n";

		flock( $fp, LOCK_EX );
		fputs( $fp, $msg );
		fclose( $fp );

		return true;
	}


	public function enableFile( $path='' )
	{
		$path = trim( $path );
		if( $path != '' ) {
			$this->path = $path;
		}

		if( !is_dir($this->path) ) {
			umask( 0 );
			if( !mkdir($this->path, 0777, true) ) {
				$this->enable_file = false;
				return false;
			}
			chmod( $this->path, 0777 );
		}
		if( !is_writable($this->path) ) {
			$this->enable_file = false;
			return false;
		}

		$this->enable_file = true;
		$this->logfile = $this->path.'/'.$this->prefix.date( 'Ymd' ).'.'.$this->extension;

		return true;
	}

	public function disableFile()
	{
		$this->enable_file = false;
		return true;
	}

	public function getLogFile()
	{
		return $this->logfile;
	}

	public function setPath( $path )
	{
		$this->path = $path;
		return true;
	}

	public function getPath()
	{
		return $this->path;
	}

	public function setPrefix( $prefix )
	{
		$this->prefix = $prefix;
		return true;
	}

	public function getPrefix()
	{
		return $this->prefix;
	}

	public function setExtension( $extension )
	{
		$this->extension = $extension;
		return true;
	}

	public function getExtension()
	{
		return $this->extension;
	}

	public function addSpecialField( $str )
	{
		$this->special_field[] = $str;
		return true;
	}

	public function getSpecialField()
	{
		return $this->special_field;
	}
}
