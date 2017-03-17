<?php

namespace ArusHostBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use ArusServerBundle\Entity\ArusServer;
use ArusDomainBundle\Entity\ArusDomain;
use ArusHostBundle\Entity\ArusHost;
use ArusHostServerBundle\Entity\ArusHostServer;
use ArusEntityTaskBundle\Entity\ArusEntityTask;

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


	public function search( $data=[], $page=1, $limit=-1 )
	{
		if( $limit < 0 ) {
			$limit = $this->getParameter('results_per_page');
		}
		$offset = $limit * ($page-1);

		$t_host = $this->em->getRepository('ArusHostBundle:ArusHost')->search( $data, $offset, $limit );
		if( is_array($t_host) ) {
			foreach ($t_host as $h) {
				$this->get('app')->getRelatedEntityObject( $h, true );
			}
		}

		return $t_host;
	}


	public function getInfo($host,$return=false)
	{
		$host = base64_decode( $host );
		$tmp = explode( '.', $host );
		$cnt = count( $tmp );

		$t_info = array( 'projectId'=>0, 'projectName'=>'', 'domain'=>'', 'domainExist'=>0, 'server'=>'', 'serverExist'=>0);

		if( $cnt >= 2 ) {
			$t_info['domain'] = Utils::extractDomain( $host );
			$domain = $this->get('domain')->search( ['name'=>$t_info['domain']] );
			if( $domain ) {
				$domain = $domain[0];
				$t_info['domainExist'] = 1;
				$t_info['projectId'] = $domain->getProject()->getId();
				$t_info['projectName'] = $domain->getProject()->getName();
			}
			$ip = gethostbyname( $host );
			if( Utils::isIP($ip) ) {
				$t_info['server'] = $ip;
				$server = $this->get('server')->search( ['name'=>$t_info['server']] );
				if( $server ) {
					$server = $server[0];
					$t_info['serverExist'] = 1;
					if( !$t_info['projectId'] ) {
						$t_info['projectId'] = $server->getProject()->getId();
						$t_info['projectName'] = $server->getProject()->getName();
					}
				}
			}
		}

		if( $return ) {
			return $t_info;
		} else {
			$response = new Response( json_encode($t_info) );
			$response->headers->set( 'Content-Type', 'application/json' );
			$response->send();
		}
	}


	public function exist( $project, $name, $id=null, $return_object=false )
	{
		$t_params = ['project'=>$project, 'name'=>[$name,'=']];

		if( $id ) {
			$t_params['id'] = [$id,'!='];
		}

		if( $return_object ) {
			$offset = 1;
		} else {
			$offset = -1;
		}

		$exist = $this->search( $t_params, $offset );

		if( !$return_object ) {
			return $exist;
		} elseif( is_array($exist) && count($exist) ) {
			return $exist[0];
		} else {
			return false;
		}
	}


	public function create( $project, $domain, $name, $recon=true )
	{
		if( !Utils::isSubdomain($name) ) {
			return false;
		}

		$host = new ArusHost();
		$host->setProject( $project );
		$host->setDomain( $domain );
		$host->setName( $name );

		$em = $this->em;
		$em->persist( $host );
		$em->flush( $host );

		if( $recon ) {
			$this->get('app')->recon( $host, 'host' );
		}

		return $host;
	}


	public function import( $project, $t_host, $recon=true )
	{
		set_time_limit( 0 );

		$em = $this->em;
		$container = $this->container;
		$cnt = 0;
		$t_host = array_map( 'trim', $t_host );

		foreach( $t_host as $h )
		{
			if( !Utils::isSubdomain($h) ) {
				continue;
			}

			$host = $this->exist( $project, $h );
			if( $host ) {
				continue;
			}

			$t_info = $this->getInfo( base64_encode($h), true );

			$domain = $this->get('domain')->exist( $project, $t_info['domain'], null, true );
			if( !$domain ) {
				$domain = $this->get('domain')->create( $project, $t_info['domain'], $recon );
			}

			if( $project && $domain ) {
				$this->create( $project, $domain, $h, $recon );
				$cnt++;
			}
		}

		return $cnt;
	}
}
