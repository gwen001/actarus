<?php

namespace ArusEntityCommentBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use ArusEntityCommentBundle\Entity\ArusEntityComment;
use ArusEntityCommentBundle\Entity\Search;
use ArusEntityCommentBundle\Form\ArusEntityCommentType;


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
		$comment = new ArusEntityComment();
		$comment->setEntityId( $entity->getEntityId() );
		$comment->setComment( $text );

		$em = $this->em;
		$em->persist( $comment );
		$em->flush( $comment );

		return true;
	}


	public function search( $data=[], $page=1, $limit=-1 )
	{
		if( $limit < 0 ) {
			$limit = $this->getParameter('results_per_page');
		}
		$offset = $limit * ($page-1);

		$result = $this->em->getRepository('ArusEntityCommentBundle:ArusEntityComment')->search( $data, $offset, $limit );

		if( $offset >= 0 && is_array($result) && count($result) ) {
			$em = $this->em;
			$t_entity_type = array_flip( $this->container->getParameter('entity')['type'] );
			foreach ($result as $com) {
				$e = $em->getRepository('ArusEntityCommentBundle:ArusEntityComment')->getRelatedEntity($com, $t_entity_type);
				$com->setEntity($e);
			}
		}

		return $result;
	}


	public function getModAction( $entity )
	{
		$comment_list = $this->getListAction( $entity->getEntityId() );

		$comment = new ArusEntityComment();
		$comment->setEntityId( $entity->getEntityId() );
		$commentAddForm = $this->formFactory->create( new ArusEntityCommentType(), $comment, ['action'=>$this->router->generate('comment_new')] );

		$commentEditForm = $this->formFactory->create(new ArusEntityCommentType());

		return $this->templating->render(
			'ArusEntityCommentBundle:Default:mod.html.twig', array(
				'entity' => $entity,
				'comment_list' => $comment_list,
				'comment_add_form' => $commentAddForm->createView(),
				'comment_edit_form' => $commentEditForm->createView(),
			)
		);
	}


	public function getListAction($entity_id)
	{
		$em = $this->em;

		$search = new Search();
		$search->setEntityId( $entity_id );
		$t_comment = $em->getRepository('ArusEntityCommentBundle:ArusEntityComment')->search( $search );

		return $this->templating->render(
			'ArusEntityCommentBundle:Default:list.html.twig', array(
				'entity_id' => $entity_id,
				't_comment' => $t_comment,
			)
		);
	}
}
