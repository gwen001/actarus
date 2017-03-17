<?php

namespace ArusHostServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ArusHostServerBundle:Default:index.html.twig');
    }
}
