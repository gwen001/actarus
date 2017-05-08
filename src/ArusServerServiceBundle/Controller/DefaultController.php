<?php

namespace ArusServerServiceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use ArusServerServiceBundle\Entity\ArusServerService;
use ArusServerServiceBundle\Form\ArusServerServiceType;
use ArusServerServiceBundle\Entity\Search;
use ArusServerServiceBundle\Form\SearchType;


class DefaultController extends Controller
{
	public function indexAction(Request $request)
	{
		
	}
}
