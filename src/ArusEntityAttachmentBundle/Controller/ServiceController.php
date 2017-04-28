<?php

namespace ArusEntityAttachmentBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use ArusEntityAttachmentBundle\Entity\ArusEntityAttachment;
use ArusEntityAttachmentBundle\Entity\Search;
use ArusEntityAttachmentBundle\Form\ArusEntityAttachmentAddType;
use ArusEntityAttachmentBundle\Form\ArusEntityAttachmentEditLimitedType;


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


	public function create( $entity, $text )
	{
		$container = $this->container;

		if( !is_object($entity) ) {
			$entity = $container->get('app')->getEntityById( $entity );
		}

		$attachment = new ArusEntityAttachment();
		$attachment->setProject( $container->get('app')->getEntityProject($entity) );
		$attachment->setEntityId( $entity->getEntityId() );
		$attachment->setDescr( $text );

		$em = $this->em;
		$em->persist( $attachment );
		$em->flush( $attachment );

		return true;
	}


	public function search( $data=[], $page=1, $limit=-1 )
	{
		if( $limit < 0 ) {
			$limit = $this->getParameter('results_per_page');
		}
		$offset = $limit * ($page-1);

		$result = $this->em->getRepository('ArusEntityAttachmentBundle:ArusEntityAttachment')->search( $data, $offset, $limit );

		if( $offset >= 0 && is_array($result) && count($result) ) {
			$em = $this->em;
			$t_entity_type = array_flip( $this->container->getParameter('entity')['type'] );
			foreach ($result as $attachment) {
				$e = $em->getRepository('ArusEntityAttachmentBundle:ArusEntityAttachment')->getRelatedEntity($attachment, $t_entity_type);
				$attachment->setEntity($e);
			}
		}

		return $result;
	}


	public function getModAction( $entity )
	{
		$attachment_list = $this->get('entity_attachment')->getListAction( $entity->getEntityId() );

		$attachment = new ArusEntityAttachment();
		$attachment->setEntityId( $entity->getEntityId() );
		$attachmentAddForm = $this->createForm( new ArusEntityAttachmentAddType(), $attachment, ['action'=>$this->router->generate('attachment_new')] );

		$attachmentEditForm = $this->createForm( new ArusEntityAttachmentEditLimitedType() );

		return $this->templating->render(
			'ArusEntityAttachmentBundle:Default:mod.html.twig', array(
				'entity' => $entity,
				'attachment_list' => $attachment_list,
				'attachment_add_form' => $attachmentAddForm->createView(),
				'attachment_edit_form' => $attachmentEditForm->createView(),
			)
		);
	}


	public function getListAction($entity_id)
	{
		$em = $this->em;

		$search = new Search();
		$search->setEntityId( $entity_id );
		$t_attachment = $em->getRepository('ArusEntityAttachmentBundle:ArusEntityAttachment')->search( $search );

		return $this->templating->render(
			'ArusEntityAttachmentBundle:Default:list.html.twig', array(
				'entity_id' => $entity_id,
				't_attachment' => $t_attachment,
			)
		);
	}
}
