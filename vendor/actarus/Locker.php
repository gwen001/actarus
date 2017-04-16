<?php

class Locker
{
	/**
	 * variable d'instance
	 *
	 * @var Locker
	 */
	private static $instance;

	/**
	 * identifiant de la sémaphore
	 *
	 * @var resource
	 */
	private $sem;

	/**
	 * clé
	 *
	 * @var integer
	 */
	private $lock_key = '0123456789';

	/**
	 * répertoire où sont stockés les verrous
	 *
	 * @var string
	 */
	private $lock_path = './';

	/**
	 * nom du fichier du verrou
	 *
	 * @var string
	 */
	private $file = '';

	/**
	 * extension du fichier du verrou
	 *
	 * @var string
	 */
	private $extension = 'lock';

	/**
	 * true si le verrou est actif
	 *
	 * @var boolean
	 */
	private $is_locked = false;


	private function __construct() {
	}


	private function __clone() {
	}


	/**
	 * masque singleton
	 *
	 * @return cTravelLocker
	 */
	public static function getInstance()
	{
		if( !isset(self::$instance) ) {
			$class = __CLASS__;
			$c = new $class;
			$c->lock_key = sprintf( "%u", crc32($_SERVER['PHP_SELF']) );
			$c->file = $c->lock_path . '/' . basename($_SERVER['PHP_SELF']) . '.' . $c->extension;
			self::$instance = $c;
		}

		return self::$instance;
	}


	public function start()
	{
		$this->sem = sem_get( $this->lock_key );
		if( !sem_acquire($this->sem) ) {
			return false;
		}

		if( file_exists($this->file) ) {
			sem_release( $this->sem );
			sem_remove( $this->sem );
			return false;
		}

		$fp = fopen( $this->file, 'wb' );
		if( !$fp ) {
			sem_release( $this->sem );
			sem_remove( $this->sem );
			return false;
		}
		fclose( $fp );

		$this->is_locked = true;
		sem_release( $this->sem );
		sem_remove( $this->sem );

		return true;
	}


	public function stop()
	{
		$this->sem = sem_get( $this->lock_key );
		if( !sem_acquire($this->sem) ) {
			return false;
		}

		unlink( $this->file );
		$this->is_locked = false;
		sem_release( $this->sem );
		sem_remove( $this->sem );

		return true;
	}


	public function isLocked()
	{
		return $this->is_locked;
	}
}
