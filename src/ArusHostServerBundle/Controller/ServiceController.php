<?php

namespace ArusHostServerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use ArusHostBundle\Entity\ArusHost;
use ArusServerBundle\Entity\ArusServer;
use ArusHostServerBundle\Entity\ArusHostServer;

use Actarus\Utils;


class ServiceController extends Controller
{
	protected $em;

	protected $container;

	protected $router;

	protected $formFactory;

	protected $templating;


	public function __construct( $entityManager, $container, $router, $formFactory, $templating ) {
		$this->em          = $entityManager;
		$this->container   = $container;
		$this->router      = $router;
		$this->formFactory = $formFactory;
		$this->templating  = $templating;
	}


	public function exist( $host, $server )
	{
		$t_host_server = $this->em->getRepository('ArusHostServerBundle:ArusHostServer')->findOneBy( ['host'=>$host,'server'=>$server] );
		return $t_host_server;
	}


	public function create( $host, $server )
	{
		$hs = new ArusHostServer();
		$hs->setHost( $host );
		$hs->setServer( $server );

		$em = $this->em;
		$em->persist( $hs );
		$em->flush( $hs );

		return $hs;
	}


	public function import( $t_host_server )
	{
		set_time_limit( 0 );

		$cnt = 0;

		foreach( $t_host_server as $hs )
		{
			$t_hs = $this->getHostServer( $hs['host'], $hs['server'] );
			if( !$t_hs ) {
				continue;
			}
			$host = $t_hs['host'];
			$server = $t_hs['server'];

			$exist = $this->exist( $host, $server );
			if( !$exist ) {
				$cnt++;
				$this->create( $host, $server );
			}
		}

		return $cnt;
	}


	public function getHostServer( $host, $server )
	{
		if( !is_object($host) && !is_object($server) )
		{
			if( Utils::isInt($host) && Utils::isInt($server) ) {
				$host = $this->em->getRepository('ArusHostBundle:ArusHost')->findOneById( $host );
				$server = $this->em->getRepository('ArusServerBundle:ArusServer')->findOneById( $server );
			} else {
				$host = $this->em->getRepository('ArusHostBundle:ArusHost')->findOneByName( $host );
				$server = $this->em->getRepository('ArusServerBundle:ArusServer')->findOneByName( $server );
			}
		}
		
		if( $host && $server ) {
			return ['host'=>$host, 'server'=>$server];
		}
		else {
			return false;
		}
	}
}
