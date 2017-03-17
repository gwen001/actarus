<?php

namespace SettingsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class DefaultController extends Controller
{
    /**
     * @Route("/settings", name="settings_homepage")
     */
    public function indexAction( Request $request )
    {
		//$sqlmap_form = $this->get('tools.sqlmap')->goSettings();
		//var_dump( $sqlmap_form );
		//$form = $this->get( 'tools.form.sqlmap' );
	
		return $this->render('SettingsBundle:Default:index.html.twig');
    }
}
