<?php

namespace ArusTaskCallbackBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use ArusTaskCallbackBundle\Entity\ArusTaskCallback;
use ArusTaskCallbackBundle\Entity\Search;
use ArusTaskCallbackBundle\Form\ArusTaskCallbackType;


class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ArusTaskCallbackBundle:Default:index.html.twig');
    }


	/**
	 * Create a new ArusDomain entity.
	 *
	 */
	public function newAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();

		$task_callback = $this->getParameter('task')['callback'];
		$alert_level = array_flip( $this->getParameter('alert')['level'] );
		$t_task = $em->getRepository('ArusTaskBundle:ArusTask')->findArray();
		$t_technology = $em->getRepository('ArusTechnologyBundle:ArusTechnology')->findArray();

		$callback = new ArusTaskCallback();
		$form = $this->createForm( new ArusTaskCallbackType(['alert_level'=>$alert_level,'t_task'=>$t_task,'t_technology'=>$t_technology,'task_callback'=>$task_callback]), $callback, ['action'=>$this->generateUrl('settings_task_callback_new')] );
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$cnt = $this->get('task_callback')->search( ['task_id'=>$callback->getTask()->getId()], -1 );
			$callback->setPriority( $cnt+1 );
			$this->removeExtraParams( $callback );
			$em->persist($callback);
			$em->flush();
			$this->addFlash( 'success', 'New callback added!' );
		}

		return $this->redirectToRoute('settings_task_show',array('id'=>$callback->getTask()->getId()));
	}


	private function removeExtraParams( $callback )
	{
		$allowed = $this->getParameter('task')['callback'][$callback->getAction()];

		foreach( $callback->getParams() as $k=>$p ) {
			if( !in_array($k,$allowed) ) {
				$callback->removeParam( $k );
			}
		}
	}


	/**
	 * Edit an existing ArusTaskCallback entity.
	 *
	 */
	public function editAction(Request $request, ArusTaskCallback $callback)
	{
		$em = $this->getDoctrine()->getManager();

		$task_callback = $this->getParameter('task')['callback'];
		$alert_level = array_flip( $this->getParameter('alert')['level'] );
		$t_task = $em->getRepository('ArusTaskBundle:ArusTask')->findArray();
		$t_technology = $em->getRepository('ArusTechnologyBundle:ArusTechnology')->findArray();

		$form = $this->createForm( new ArusTaskCallbackType(['alert_level'=>$alert_level,'t_task'=>$t_task,'t_technology'=>$t_technology,'task_callback'=>$task_callback]), $callback );
		$form->handleRequest( $request );

		if ($form->isSubmitted() && $form->isValid()) {
			$this->removeExtraParams( $callback );
			$em->persist($callback);
			$em->flush();

			//$this->addFlash( 'success', 'Your changes were saved!' );

			$response = new Response( json_encode(array('error'=>0)) );
			return $response;
		}
	}


	public function deleteAction(Request $request, ArusTaskCallback $callback)
	{
		$em = $this->getDoctrine()->getManager();
		$em->remove($callback);
		$em->flush();

		$response = new Response( json_encode(array('error'=>0)) );
		return $response;
	}


	public function getListAction(Request $request, $task_id)
	{
		echo $this->get('task_callback')->getListAction( $task_id );
		exit();
	}


	public function priorizeAction(Request $request, $task_id)
	{
		$em = $this->getDoctrine()->getManager();

		$task = $em->getRepository('ArusTaskBundle:ArusTask')->findOneById( $task_id );

		if( $task )
		{
			$priority = 1;
			$p = $this->get('request')->request->get('p');

			if( $p ) {
				$t_priority = explode( ',', $p );

				foreach( $t_priority as $callback_id ) {
					$callback = $em->getRepository('ArusTaskCallbackBundle:ArusTaskCallback')->findOneBy( ['id'=>$callback_id,'task'=>$task] );
					if( $callback ) {
						$callback->setPriority( $priority );
						$em->persist( $callback );
						$priority++;
					}
				}
			}
		}

		$em->flush();

		$response = new Response( json_encode(array('error'=>0)) );
		return $response;
	}
}
