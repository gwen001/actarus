<?php

namespace ChangelogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ChangelogBundle:Default:index.html.twig');
    }
}
