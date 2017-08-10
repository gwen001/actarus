<?php

namespace MaintenanceActionsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class DefaultController extends Controller
{
    /**
     * @Route("/maintenanceActions", name="maintenance_actions_homepage")
     */
    public function indexAction( Request $request )
    {
		return $this->render('MaintenanceActionsBundle:Default:index.html.twig');
    }
    
    
    /**
     * @Route("/maintenanceActions/cancelledTask", name="maintenance_actions_cancelled_task")
     */
    public function maintenanceActionsCancelledTaskAction( Request $request )
    {
    	$n_update = $this->get('entity_task')->interpretCancelledTask();
		$this->addFlash( 'success', $n_update.' tasks updated!' );
    	
		return $this->redirectToRoute('maintenance_actions_homepage');
    }
}
