<?php

namespace ArusDomainBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\Collection;

use ArusDomainBundle\Entity\ArusDomain;
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

		$t_domain = $this->em->getRepository('ArusDomainBundle:ArusDomain')->search( $data, $offset, $limit );
		if( is_array($t_domain) ) {
			foreach($t_domain as $d) {
				$this->get('app')->getRelatedEntityObject( $d, true );
				if( !Utils::isCli() ) { // that's sucks ! (because of the subthreat cron)
					$n = $this->get('host')->search( ['domain'=>$d], -1 );
					$d->setHosts( array_fill(0,$n,null) );
				}
			}
		}

		return $t_domain;
	}


	public function exist( $project, $name, $id=null, $return_object=false )
	{
		$t_params = ['project'=>$project, 'name'=>[$name,'LIKE']];

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


	public function create( $project, $name, $recon=true )
	{
		if( !Utils::isDomain($name) ) {
			return false;
		}

		$domain = new ArusDomain();
		$domain->setProject( $project );
		$domain->setName( $name );

		$em = $this->em;
		$em->persist( $domain );
		$em->flush( $domain );

		if( $recon ) {
			$this->get('app')->recon( $domain, 'domain' );
		}

		return $domain;
	}


	public function sameProject( $domain, $new_host )
	{
		return $this->exist( $domain->getProject(), Utils::extractDomain($new_host) );
	}
	
	
	public function survey( $domain )
	{
		$domain->setSurvey( 1-$domain->getSurvey() );
		
		$em = $this->em;
		$em->persist( $domain );
		$em->flush( $domain );
		
		return $domain;
	}
	

	public function import( $project, $t_domain, $recon=true )
	{
		set_time_limit( 0 );

		$cnt = 0;
		$t_domain = array_map( 'trim', $t_domain );
		$t_domain = array_unique( $t_domain );

		foreach( $t_domain as $d )
		{
			if( !Utils::isDomain($d) ) {
				continue;
			}

			$domain = $this->exist( $project, $d );
			if( $domain ) {
				continue;
			}

			$this->create( $project, $d, $recon );
			$cnt++;
		}

		return $cnt;
	}
	
	
	public function isWhiteListed( $domain )
	{
		$domain = Utils::extractDomain( $domain );
		$t_whitelist = $this->getParameter('domain')['whitelist'];

		return in_array($domain,$t_whitelist);
	}
}
