<?php

namespace ArusDomainBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;

use ArusDomainBundle\Entity\ArusDomain;
use ArusDomainBundle\Form\ArusDomainAddType;
use ArusDomainBundle\Form\ArusDomainEditType;
use ArusDomainBundle\Form\ArusDomainQuickEditType;

use ArusDomainBundle\Entity\Search;
use ArusDomainBundle\Form\SearchType;

use ArusEntityTaskBundle\Entity\ArusEntityTask;
use ArusEntityTaskBundle\Entity\Search as EntityTaskSearch;

use ArusCommentBundle\Entity\ArusComment;
use ArusCommentBundle\Form\ArusCommentType;


class DefaultController extends Controller
{
	public function indexAction(Request $request)
    {
        $t_status = $this->getParameter('domain')['status'];

		$search = new Search();
		$form = $this->createForm( new SearchType(['t_status'=>$t_status]), $search );
		$form->handleRequest($request);

		$data = null;
		if( $form->isSubmitted() && $form->isValid() )  {
			$data = $form->getData();
		}

		$page = 1;
		$limit = $this->getParameter('results_per_page');
		$total_domain = $this->get('domain')->search( $data, -1 );
		$n_page = ceil( $total_domain/$limit );

		if( is_array($data) || is_object($data) ) {
			if (is_array($data) && isset($data['page'])) {
				$page = $data['page'];
			} else {
				$page = $data->getPage();
			}
			if ($page <= 0 || $page > $n_page) {
				$page = 1;
			}
		}

		$t_domain = $this->get('domain')->search( $data, $page );
		foreach( $t_domain as $d ) {
			$d->setEntityAlerts( $this->get('entity_alert')->search(['entity_id'=>$d->getEntityId()]) );
		}

		$pagination = $this->get('app')->paginate( $total_domain, count($t_domain), $page );

		return $this->render('ArusDomainBundle:Default:index.html.twig', array(
			'form' => $form->createView(),
			't_domain' => $t_domain,
            't_status' => $t_status,
			'pagination' => $pagination,
		));
    }


	/**
	 * Finds and displays a ArusDomain entity.
	 *
	 */
	public function showAction(Request $request, ArusDomain $domain)
	{
        $t_status = $this->getParameter('domain')['status'];
		$quick_edit = $this->createForm(new ArusDomainQuickEditType(['t_status'=>$t_status]), $domain, ['action'=>$this->generateUrl('domain_quickedit',['id'=>$domain->getId()])] );

        $deleteForm = $this->createDeleteForm($domain);

		$alert_mod = $this->get('entity_alert')->getModAction( $domain );
		$task_mod = $this->get('entity_task')->getModAction( $domain );

        $t_host = $this->get('host')->search( ['domain'=>$domain] );
        foreach( $t_host as $h ) {
            $h->setEntityAlerts( $this->get('entity_alert')->search(['entity_id'=>$h->getEntityId()]) );
        }

        return $this->render('ArusDomainBundle:Default:show.html.twig', array(
			'domain' => $domain,
			'delete_form' => $deleteForm->createView(),
			'alert_mod' => $alert_mod,
			'task_mod' => $task_mod,
            't_host' => $t_host,
            't_status' => $t_status,
            'quick_edit' => $quick_edit->createView(),
		));
	}


	/**
	 * Create a new ArusDomain entity.
	 *
	 */
	public function newAction(Request $request)
	{
		$domain = new ArusDomain();
		$form = $this->createForm( new ArusDomainAddType(), $domain );
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$exist = $this->get('domain')->exist( $domain->getProject(), $domain->getName() );
			if( !$exist ) {
				$domain = $this->get('domain')->create( $domain->getProject(), $domain->getName(), $domain->getRecon() );
				$this->addFlash( 'success', 'New domain added!' );
				return $this->redirectToRoute('domain_show',array('id'=>$domain->getId()));
			} else {
				$form->get('name')->addError( new FormError('This domain already exist') );
			}
		}

		return $this->render('ArusDomainBundle:Default:new.html.twig', array(
			'domain' => $domain,
			'form' => $form->createView(),
		));
	}


	/**
	 * Finds and displays a ArusDomain entity.
	 *
	 */
	public function viewAction(Request $request, ArusDomain $domain)
	{
        $t_status = $this->getParameter('domain')['status'];
		$quick_edit = $this->createForm(new ArusProjectQuickEditType(['t_status'=>$t_status]), $domain, ['action'=>$this->generateUrl('domain_quickedit',['id'=>$domain->getId()])] );

		return $this->render('ArusDomainBundle:Default:view.html.twig', array(
			'domain' => $domain,
            't_status' => $t_status,
            'quick_edit' => $quick_edit->createView(),
		));
	}


	/**
	 * Displays a form to edit an existing Domain entity.
	 *
	 */
	public function quickeditAction(Request $request, ArusDomain $domain)
	{
		$r = ['error'=>0];
        $t_status = $this->getParameter('domain')['status'];

		$form = $this->createForm( new ArusDomainQuickEditType(['t_status'=>$t_status]), $domain, ['action'=>$this->generateUrl('domain_quickedit',['id'=>$domain->getId()])] );
		$form->handleRequest($request);

		if( $form->isSubmitted() && $form->isValid() ) {
			$em = $this->getDoctrine()->getManager();
			$em->persist( $domain );
			$em->flush();
		}

		$response = new Response( json_encode($r) );
		return $response;
	}


	/**
	 * Displays a form to edit an existing ArusDomain entity.
	 *
	 */
	public function editAction(Request $request, ArusDomain $domain)
	{
        $t_status = $this->getParameter('domain')['status'];

		$form = $this->createForm(new ArusDomainEditType(['t_status'=>$t_status]), $domain, ['action'=>$this->generateUrl('domain_edit',['id'=>$domain->getId()])] );
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$exist = $this->get('domain')->exist( $domain->getProject(), $domain->getName(), $domain->getId() );
			if( !$exist ) {
				$em = $this->getDoctrine()->getManager();
				$em->persist( $domain );
				$em->flush();
				$this->addFlash( 'success', 'Your changes were saved!' );
			} else {
				$this->addFlash( 'danger', 'Error!' );
			}
			return $this->redirectToRoute('domain_show',array('id'=>$domain->getId()));
		}

		return $this->render('ArusDomainBundle:Default:edit.html.twig', array(
			'domain' => $domain,
			'form' => $form->createView(),
		));
	}


	/**
	 * Deletes a ArusDomain entity.
	 *
	 */
	public function deleteAction(Request $request, ArusDomain $domain)
	{
		$form = $this->createDeleteForm($domain);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->get('app')->entityDelete( $domain );
			$em = $this->getDoctrine()->getManager();
			$em->remove( $domain );
			$em->flush();

			$this->addFlash( 'success', 'Domain deleted!' );
		}

		return $this->redirectToRoute('domain_homepage');
	}


	/**
	 * Creates a form to delete a ArusDomain entity.
	 *
	 * @param ArusDomain $domain The ArusDomain entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createDeleteForm(ArusDomain $domain)
	{
		return $this->createFormBuilder()
		->setAction($this->generateUrl('domain_delete', array('id' => $domain->getId())))
		->setMethod('DELETE')
		->getForm()
			;
	}


	public function getCommentsAction(Request $request, ArusDomain $domain)
	{
		$em = $this->getDoctrine()->getManager();

		$search = new CommentSearch();
		$search->setEntityId( $domain->getEntityId() );
		$t_comment = $em->getRepository('ArusCommentBundle:ArusComment')->search( $search );
		$domain->setComments( $t_comment );

		return $this->render('ArusDomainBundle:Default:comments.html.twig', array(
			'domain' => $domain,
		));
	}
}
