<?php

namespace Actarus;

use Doctrine\Common\Util\Inflector as Inflector;


class Utils
{
	public static function isCli()
	{
		return (php_sapi_name() === 'cli');
	}


	public static function dateFR2Time( $date )
	{
		list( $day, $month, $year ) = explode( '/', $date );
		$timestamp = mktime( 0, 0, 0, $month, $day, $year );
		return $timestamp;
	}


	public static function stripAccents( $str, $charset='utf-8' )
	{
		$str = htmlentities( $str, ENT_NOQUOTES, $charset );

		$str = preg_replace( '#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str );
		$str = preg_replace( '#&([A-za-z]{2})(?:lig);#', '\1', $str ); // pour les ligatures e.g. '&oelig;'
		$str = preg_replace( '#&[^;]+;#', '', $str ); // supprime les autres caractères

		return $str;
	}


	public static function urlize( $str )
	{
		$str = str_replace( (array)array("'",'"'), ' ', $str );

		$clean = self::stripAccents( $str );
		$clean = iconv( 'UTF-8', 'ASCII//TRANSLIT', $clean );
		$clean = preg_replace( "/[^a-zA-Z0-9\/_|+ -\.]/", '', $clean );
		$clean = strtolower( trim($clean,'-') );
		$clean = preg_replace( "/[\/_|+ -\.]+/", '-', $clean );

		return $clean;
	}


	public static function isIp( $str )
	{
		return preg_match( '#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z#', trim($str) );
	}


	public static function isDomain( $str )
	{
		$str = strtolower( $str );

		if( preg_match('/[^0-9a-z_\-\.]/',$str) || preg_match('/[^0-9a-z]/',$str[0]) || preg_match('/[^a-z]/',$str[strlen($str)-1]) || substr_count($str,'.')>2 || substr_count($str,'.')<=0 ) {
			return false;
		} else {
			return true;
		}
	}


	public static function isSubdomain( $str )
	{
		$str = strtolower( $str );

		if( preg_match('/[^0-9a-z_\-\.]/',$str) || preg_match('/[^0-9a-z]/',$str[0]) || preg_match('/[^a-z]/',$str[strlen($str)-1]) || substr_count($str,'.')<2 ) {
			return false;
		} else {
			return true;
		}
	}


	public static function cleanOutput( $str )
	{
		$str = preg_replace( '#\[[0-9;]{1,4}m#', '', $str );

		return $str;
	}


	public static function isAjax()
	{
		if( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ) {
			return true;
		} else {
			return false;
		}
	}

	public static function array2object( $data, $class )
	{
		if( is_array($data) && !is_object($data) ) {
			$o = new $class;
			foreach( $data as $k=>$v ) {
				$f = Inflector::camelize('set_'.$k);
				if( is_callable([$o,$f]) ) {
					$o->$f( $v );
				}
			}
			$data = $o;
		}

		return $data;
	}


	public static function extractDomain( $host )
	{
		$tmp = explode( '.', $host );
		$cnt = count( $tmp );

		$domain = $tmp[$cnt-1];

		for( $i=$cnt-2 ; $i>=0 ; $i-- ) {
			$domain = $tmp[$i].'.'.$domain;
			if( strlen($tmp[$i]) > 3 ) {
				break;
			}
		}

		return $domain;
	}


	public function getScore( $t_alerts )
	{
		$score = [];

		for( $i=-1 ; $i<=3 ; $i++ ) {
			$score[$i] = 0;
		}

		foreach( $t_alerts as $a ) {
			if( $a->getStatus() == 0 || $a->getStatus() == 1 ) {
				$score[ $a->getLevel() ]++;
				$s = $a->getLevel() * 10;
				if( $a->getStatus() == 0 ) {
					$s = $s/2;
				}
				$score[-1] += $s;
			}
		}

		return $score;
	}


	public function getMaxAlertLevel( $t_alerts )
	{
		$max = [-1,-1];

		foreach( $t_alerts as $a ) {
			if( $a->getStatus() == 0 || $a->getStatus() == 1 ) {
				$s = $a->getLevel();
				if( $s >= $max[0] ) {
					$max[0] = $s;
					if( $a->getStatus() > $max[1] ) {
						$max[1] = $a->getStatus();
					}
				}
			}
		}

		return $max;
	}


	public function isInt( $str )
	{
		return !preg_match( '#[^0-9]#', $str );
	}
}
