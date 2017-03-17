<?php

namespace ArusEntityAlertBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use ArusEntityAlertBundle\Entity\ArusEntityAlert;
use ArusEntityAlertBundle\Entity\Search;
use ArusEntityAlertBundle\Form\ArusEntityAlertAddType;
use ArusEntityAlertBundle\Form\ArusEntityAlertEditLimitedType;


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


	public function create( $entity, $text, $alert_level, $alert_status=0 )
	{
		$container = $this->container;

		if( !is_object($entity) ) {
			$entity = $container->get('app')->getEntityById( $entity );
		}

		$alert = new ArusEntityAlert();
		$alert->setProject( $container->get('app')->getEntityProject($entity) );
		$alert->setEntityId( $entity->getEntityId() );
		$alert->setDescr( $text );
		$alert->setLevel( $alert_level );
		$alert->setStatus( $alert_status );

		$em = $this->em;
		$em->persist( $alert );
		$em->flush( $alert );

		return true;
	}


	public function search( $data=[], $page=1, $limit=-1 )
	{
		if( $limit < 0 ) {
			$limit = $this->getParameter('results_per_page');
		}
		$offset = $limit * ($page-1);

		$result = $this->em->getRepository('ArusEntityAlertBundle:ArusEntityAlert')->search( $data, $offset, $limit );

		if( $offset >= 0 && is_array($result) && count($result) ) {
			$em = $this->em;
			$t_entity_type = array_flip( $this->container->getParameter('entity')['type'] );
			foreach ($result as $alert) {
				$e = $em->getRepository('ArusEntityAlertBundle:ArusEntityAlert')->getRelatedEntity($alert, $t_entity_type);
				$alert->setEntity($e);
			}
		}

		return $result;
	}


	public function getModAction( $entity )
	{
		$t_level = array_flip( $this->container->getParameter('alert')['level'] );

		$alert_list = $this->get('entity_alert')->getListAction( $entity->getEntityId() );

		$alert = new ArusEntityAlert();
		$alert->setEntityId( $entity->getEntityId() );
		$alertAddForm = $this->createForm( new ArusEntityAlertAddType(array('t_level'=>$t_level)), $alert, ['action'=>$this->router->generate('alert_new')] );

		$alertEditForm = $this->createForm( new ArusEntityAlertEditLimitedType(array('t_level'=>$t_level)) );

		return $this->templating->render(
			'ArusEntityAlertBundle:Default:mod.html.twig', array(
				'entity' => $entity,
				'alert_list' => $alert_list,
				'alert_add_form' => $alertAddForm->createView(),
				'alert_edit_form' => $alertEditForm->createView(),
			)
		);
	}


	public function getListAction($entity_id)
	{
		$em = $this->em;

		$search = new Search();
		$search->setEntityId( $entity_id );
		$t_alert = $em->getRepository('ArusEntityAlertBundle:ArusEntityAlert')->search( $search );

		return $this->templating->render(
			'ArusEntityAlertBundle:Default:list.html.twig', array(
				'entity_id' => $entity_id,
				't_alert' => $t_alert,
			)
		);
	}
}
