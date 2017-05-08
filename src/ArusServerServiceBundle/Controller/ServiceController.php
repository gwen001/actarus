<?php

namespace ArusServerServiceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use ArusServerBundle\Entity\ArusServer;
use ArusServerServiceBundle\Entity\ArusServerService;


class ServiceController extends Controller
{
	protected $em;

	protected $container;

	protected $router;

	protected $formFactory;

	protected $templating;


	public function __construct( $entityManager, $container, $router, $formFactory, $templating )
	{
		$this->em = $entityManager;
		$this->container = $container;
		$this->router = $router;
		$this->formFactory = $formFactory;
		$this->templating = $templating;
	}


	public function create( $server, $port, $type, $service='', $version='' )
	{
		$ss = new ArusServerService();
		$ss->setServer( $server );
		$ss->setPort( (int)$port );
		$ss->setType( $type );
		$ss->setService( $service );
		$ss->setVersion( $version );

		$em = $this->em;
		$em->persist( $ss );
		$em->flush( $ss );

		return $ss;
	}
}
