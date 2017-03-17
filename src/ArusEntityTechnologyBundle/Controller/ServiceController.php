<?php

namespace ArusEntityTechnologyBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use ArusEntityTechnologyBundle\Entity\ArusEntityTechnology;
use ArusEntityTechnologyBundle\Entity\Search;
use ArusEntityTechnologyBundle\Form\ArusEntityTechnologyType;


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


	public function create( $techno, $entity, $version='' )
	{
		if( !is_object($entity) ) {
			$entity = $this->get('app')->getEntityById( $entity );
		}

		$this->doCreate( $techno, $entity );

		$t_implies = $techno->getImplies();
		foreach( $t_implies as $imp ) {
			$t = $this->em->getRepository('ArusTechnologyBundle:ArusTechnology')->findOneById( $imp );
			$this->doCreate( $t, $entity, $version );
		}

		return true;
	}


	public function doCreate( $techno, $entity, $version='' )
	{
		$exist = $this->search( ['technology_id'=>$techno,'entity_id'=>$entity->getEntityId()], -1 );

		if( $exist ) {
			return false;
		} else {
			$new = new ArusEntityTechnology();
			$new->setTechnology( $techno );
			$new->setEntityId( $entity->getEntityId() );
			$new->setVersion( $version );

			$em = $this->em;
			$em->persist( $new );
			$em->flush( $new );

			return true;
		}
	}


	public function search( $data, $page=1, $limit=-1 )
	{
		if( $limit < 0 ) {
			$limit = $this->getParameter('results_per_page');
		}
		$offset = $limit * ($page-1);

		return $this->em->getRepository('ArusEntityTechnologyBundle:ArusEntityTechnology')->search( $data, $offset, $limit );
	}


	public function getModAction( $entity )
	{
		$techno_list = $this->getListAction( $entity->getEntityId() );

		$techno = new ArusEntityTechnology();
		$techno->setEntityId( $entity->getEntityId() );
		$technoAddForm = $this->formFactory->create( new ArusEntityTechnologyType(), $techno, ['action'=>$this->router->generate('technology_new')] );

		$technoEditForm = $this->formFactory->create(new ArusEntityTechnologyType());

		return $this->templating->render(
			'ArusEntityTechnologyBundle:Default:mod.html.twig', array(
				'entity' => $entity,
				'techno_list' => $techno_list,
				'techno_add_form' => $technoAddForm->createView(),
				'techno_edit_form' => $technoEditForm->createView(),
			)
		);
	}


	public function getListAction($entity_id)
	{
		$em = $this->em;

		$search = new Search();
		$search->setEntityId( $entity_id );
		$t_techno = $em->getRepository('ArusEntityTechnologyBundle:ArusEntityTechnology')->search( $search );

		return $this->templating->render(
			'ArusEntityTechnologyBundle:Default:list.html.twig', array(
				'entity_id' => $entity_id,
				't_techno' => $t_techno,
			)
		);
	}
}
