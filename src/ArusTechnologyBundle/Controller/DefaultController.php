<?php

namespace ArusTechnologyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    public function getListAction()
    {
		$em = $this->getDoctrine()->getManager();
		$t_techno = []; 
		$result = $em->getRepository('ArusTechnologyBundle:ArusTechnology')->findBy( [], ['name'=>'ASC'] );
		
		foreach( $result as $t ) {
			$tmp = ['name'=>$t->getName(), 'id'=>$t->getId(), 'icon'=>'/img/technology/'.$t->getIcon()];
			$t_techno[] = $tmp;
		}
		
		$response = new Response( json_encode($t_techno) );
		return $response;
    }
}
