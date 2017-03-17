<?php

namespace SqlmapBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use SqlmapBundle\Entity\Sqlmap;
//use SqlmapBundle\Form\SqlmapType;


class DefaultController extends Controller
{
    public function goSettings()
    {
		//$sqlmap = new Sqlmap();
		//$form = $this->createForm( new SqlmapType(), $sqlmap );
		//$form = new SqlmapType();
		
		//$ff = $form->buildForm( new SqlmapType(), $sqlmap );
		
		
		//$form->handleRequest($request);
	
		$sqlmap = new Sqlmap();

		$form = $this->get( 'tools.form.type.sqlmap' );
		//$form->setData( $sqlmap );
	
    	//$form = $this->createForm( 'sqlmap', $sqlmap );
		
		
	
		return 'aaaa';
    }
}
