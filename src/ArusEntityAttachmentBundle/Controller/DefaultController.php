<?php

namespace ArusEntityAttachmentBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use ArusEntityAttachmentBundle\Entity\ArusEntityAttachment;
use ArusEntityAttachmentBundle\Form\ArusEntityAttachmentAddType;
use ArusEntityAttachmentBundle\Form\ArusEntityAttachmentEditType;
use ArusEntityAttachmentBundle\Form\ArusEntityAttachmentEditLimitedType;

use ArusEntityAttachmentBundle\Entity\Search;
use ArusEntityAttachmentBundle\Form\SearchType;

use ArusProjectBundle\Entity\ArusProject;

use Actarus\Utils;


class DefaultController extends Controller
{
	public function indexAction(Request $request)
	{
		$t_entity_type = array_flip( $this->getParameter('entity')['type'] );

		$search = new Search();
		$form = $this->createForm( new SearchType(array('t_entity_type'=>$t_entity_type)), $search );
		$form->handleRequest($request);

		$data = null;
		if( $form->isSubmitted() && $form->isValid() )  {
			$data = $form->getData();
		} else {
			$data = $search;
		}

		$page = 1;
		$limit = $this->getParameter('results_per_page');
		$total_attachment = $this->get('entity_attachment')->search( $data, -1 );
		$n_page = ceil( $total_attachment/$limit );

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

		$t_attachment = $this->get('entity_attachment')->search( $data, $page );
		$pagination = $this->get('app')->paginate( $total_attachment, count($t_attachment), $page );

		return $this->render('ArusEntityAttachmentBundle:Default:index.html.twig', array(
			'form' => $form->createView(),
			't_attachment' => $t_attachment,
			't_entity_type' => $t_entity_type,
			'pagination' => $pagination,
		));
	}


    /**
     * Finds and displays a ArusEntityAttachment entity.
     *
     */
    public function showAction(Request $request, ArusEntityAttachment $attachment)
    {
        $t_entity_type = array_flip( $this->getParameter('entity')['type'] );

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ArusEntityAttachmentBundle:ArusEntityAttachment')->getRelatedEntity( $attachment, $t_entity_type );
        $attachment->setEntity( $entity );
        $attachment->setProject( $this->get('app')->getEntityProject($entity) );

        $deleteForm = $this->createDeleteForm($attachment);

        return $this->render('ArusEntityAttachmentBundle:Default:show.html.twig', array(
            'attachment' => $attachment,
            'entity' => $entity,
            't_entity_type' => $t_entity_type,
            'delete_form' => $deleteForm->createView(),
        ));
    }


    /**
	 * Create a new ArusEntityAttachment entity.
	 *
	 */
	public function newAction(Request $request)
	{
		$attachment = new ArusEntityAttachment();
		$form = $this->createForm( new ArusEntityAttachmentAddType(), $attachment );
		$form->handleRequest( $request );

		if ($form->isSubmitted() && $form->isValid()) {
			$this->get('entity_attachment')->create( $attachment->getEntityId(), $attachment->getDescr() );
			//$this->addFlash( 'success', 'New attachment added!' );
			$response = new Response( json_encode(array('error'=>0)) );
			return $response;
		}
	}


    /**
     * Displays a form to edit an existing ArusEntityAttachment entity.
     *
     */
    public function editAction(Request $request, ArusEntityAttachment $attachment)
    {
        $em = $this->getDoctrine()->getManager();

        if( Utils::isAjax() ) {
            $form = $this->createForm( new ArusEntityAttachmentEditLimitedType(), $attachment );
            $form->handleRequest( $request );

            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($attachment);
                $em->flush();

                //$this->addFlash( 'success', 'Your changes were saved!' );

                $response = new Response( json_encode(array('error'=>0)) );
                return $response;
            }
        }
        else
        {
            $deleteForm = $this->createDeleteForm($attachment);

            $t_entity_type = array_flip( $this->getParameter('entity')['type'] );
            $entity = $em->getRepository('ArusEntityAttachmentBundle:ArusEntityAttachment')->getRelatedEntity( $attachment, $t_entity_type );
	        $attachment->setEntity( $entity );
	        $attachment->setProject( $this->get('app')->getEntityProject($entity) );

            $form = $this->createForm(new ArusEntityAttachmentEditType(), $attachment );
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em->persist($attachment);
                $em->flush();
                $this->addFlash( 'success', 'Your changes were saved!' );
                return $this->redirectToRoute('attachment_show',array('id'=>$attachment->getId()));
            }

            return $this->render('ArusEntityAttachmentBundle:Default:edit.html.twig', array(
                'attachment' => $attachment,
                'entity' => $entity,
                'form' => $form->createView(),
                'delete_form' => $deleteForm->createView(),
	            't_entity_type' => $t_entity_type,
            ));
        }
    }


    /**
     * Edit an existing ArusEntityAttachment entity.
     *
     */
    public function editLimitedAction(Request $request, ArusEntityAttachment $attachment)
    {
        $form = $this->createForm( new ArusEntityAttachmentEditLimitedType(), $attachment );
        $form->handleRequest( $request );

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($attachment);
            $em->flush();

            //$this->addFlash( 'success', 'Your changes were saved!' );

            $response = new Response( json_encode(array('error'=>0)) );
            return $response;
        }
    }


    /**
	 * Deletes a ArusEntityAttachment entity.
	 *
	 */
	public function deleteAction(Request $request, ArusEntityAttachment $attachment)
	{
        if( Utils::isAjax() ) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($attachment);
            $em->flush();
            $response = new Response( json_encode(array('error'=>0)) );
            return $response;
        } else {
            $form = $this->createDeleteForm($attachment);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($attachment);
                $em->flush();
                $this->addFlash( 'success', 'Attachment deleted!' );
            }

            return $this->redirectToRoute('attachment_homepage');
        }
	}


    /**
     * Creates a form to delete a ArusEntityAttachment entity.
     *
     * @param ArusEntityAttachment $attachment The ArusEntityAttachment entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ArusEntityAttachment $attachment)
    {
        return $this->createFormBuilder()
        ->setAction($this->generateUrl('attachment_delete', array('id' => $attachment->getId())))
        ->setMethod('DELETE')
        ->getForm()
            ;
    }


    /**
     * Confirm a ArusEntityAttachment entity.
     *
     */
    public function confirmAction(Request $request, ArusEntityAttachment $attachment)
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($attachment);
        $em->flush();

        $response = new Response( json_encode(array('error'=>0)) );
        return $response;
    }


	/**
	 * Cancel a ArusEntityAttachment entity.
	 *
	 */
	public function cancelAction(Request $request, ArusEntityAttachment $attachment)
	{
		$em = $this->getDoctrine()->getManager();
		$em->persist($attachment);
		$em->flush();

		$response = new Response( json_encode(array('error'=>0)) );
		return $response;
	}


	public function getListAction(Request $request, $entity_id)
	{
		echo $this->get('entity_attachment')->getListAction( $entity_id );
		exit();
	}


	public function browseAction(Request $request)
	{
		$absolute_path = dirname($this->get('kernel')->getRootDir()).'/web/'.$this->getParameter('attachments_path');
		//var_dump($absolute_path);
		
		$d = opendir( $absolute_path );
		$t_dir = [];
		
		while( ($o=readdir($d)) ) {
			$p = $absolute_path.$o;
			if( (int)$o && is_dir($p)) {
				$t_glob = glob($p.'/*');
				$cnt = count( $t_glob );
				if( $cnt ) {
					$t_dir[$o] = $cnt;
				}
			}
		}
		
		//var_dump( $t_dir );
		closedir( $d );
		
		$em = $this->getDoctrine()->getManager();
        $t_project = $em->getRepository('ArusProjectBundle:ArusProject')->findArray();
		//var_dump( $t_project );
		
		$t_final = [];
		foreach( $t_project as $id=>$name ) {
			if( isset($t_dir[$id]) ) {
				$t_final[$id] = [ $name, $t_dir[$id] ];
			}
		}
		//var_dump( $t_final );
		
        return $this->render('ArusEntityAttachmentBundle:Default:browse.html.twig', array(
            't_dir' => $t_final,
            't_project' => $t_project,
        ));
	}
	
	
	public function listAction(Request $request, ArusProject $project)
	{
		$t_entity_type = array_flip( $this->getParameter('entity')['type'] );
		$t_attachment = $this->get('entity_attachment')->search( ['project'=>$project], 1, null );
		$absolute_path = dirname($this->get('kernel')->getRootDir()).'/web/'.$this->getParameter('attachments_path').$project->getId();
		//var_dump( count($t_attachment) );
		
		$t_size = [];
		foreach( $t_attachment as $a ) {
			$f = $absolute_path.'/'.$a->getFilename();
			$t_size[ $a->getId() ] = filesize( $f );
		}
		
        return $this->render('ArusEntityAttachmentBundle:Default:list.html.twig', array(
            'project' => $project,
            't_size' => $t_size,
            't_attachment' => $t_attachment,
			't_entity_type' => $t_entity_type,
        ));
	}
}
