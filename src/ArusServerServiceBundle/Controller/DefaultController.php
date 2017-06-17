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
		$search = new Search();
		$form = $this->createForm( new SearchType(), $search );
		$form->handleRequest($request);

		$data = null;
		if( $form->isSubmitted() && $form->isValid() )  {
			$data = $form->getData();
		}

		$page = 1;
		$limit = $this->getParameter('results_per_page');
		$total_service = $this->get('server_service')->search( $data, -1 );
		$n_page = ceil( $total_service/$limit );

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

		$t_service = $this->get('server_service')->search( $data, $page );
		$pagination = $this->get('app')->paginate( $total_service, count($t_service), $page );

		return $this->render('ArusServerServiceBundle:Default:index.html.twig', array(
			'form' => $form->createView(),
			't_service' => $t_service,
			'pagination' => $pagination,
		));
	}
}
