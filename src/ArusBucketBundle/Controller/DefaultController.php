<?php

namespace ArusBucketBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;

use ArusBucketBundle\Entity\ArusBucket;
use ArusBucketBundle\Form\ArusBucketAddType;
use ArusBucketBundle\Form\ArusBucketEditType;
use ArusBucketBundle\Form\ArusBucketQuickEditType;

use ArusBucketBundle\Entity\Search;
use ArusBucketBundle\Form\SearchType;


class DefaultController extends Controller
{
	public function indexAction(Request $request)
    {
        $t_status = $this->getParameter('bucket')['status'];

		$search = new Search();
		$form = $this->createForm( new SearchType(['t_status'=>$t_status]), $search );
		$form->handleRequest($request);

		$data = null;
		if( $form->isSubmitted() && $form->isValid() )  {
			$data = $form->getData();
		}

		$page = 1;
		$limit = $this->getParameter('results_per_page');
		$total_bucket = $this->get('bucket')->search( $data, -1 );
		$n_page = ceil( $total_bucket/$limit );

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

		$t_bucket = $this->get('bucket')->search( $data, $page );
		$pagination = $this->get('app')->paginate( $total_bucket, count($t_bucket), $page );

		return $this->render('ArusBucketBundle:Default:index.html.twig', array(
			'form' => $form->createView(),
			't_bucket' => $t_bucket,
            't_status' => $t_status,
			'pagination' => $pagination,
		));
    }
}
