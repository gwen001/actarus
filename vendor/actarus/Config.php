<?php

class Config
{
	/**
	 * instance var
	 *
	 * @var Command
	 */
	private static $instance;


	/**
	 * constructor
	 *
	 */
	protected function __construct() {
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
	 * @return Command
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance)) {
			$class = __CLASS__;
			$c = new $class;
			self::$instance = $c;
		}

		return self::$instance;
	}


	/**
	 * load parameters
	 *
	 * @param $file file to load
	 * @param null $key parameters key to load
	 * @return bool true if success, else false
	 */
	public function loadParameters( $file, $key=null )
	{
		if( !is_file($file) ) {
			return false;
		}

		$p = yaml_parse_file( $file );

		if( !is_null($key) ) {
			if( isset($p[$key]) ) {
				$p = $p[$key];
			} else {
				return false;
			}
		}

		if( !isset($this->parameters) || !is_array($this->parameters) ) {
			$this->parameters = array();
		}

		$this->parameters = array_merge( $this->parameters, $p );
		return true;
	}
}
