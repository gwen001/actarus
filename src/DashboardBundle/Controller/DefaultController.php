<?php

namespace DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
		$em = $this->getDoctrine()->getManager();
		$actarus = $this->get('app')->getActarus();
		$t_entity_type = array_flip( $this->getParameter('entity')['type'] );
		$t_task_status = $this->getParameter('task')['status'];
		$t_alert_status = $this->getParameter('alert')['status'];
		$t_alert_level = $this->getParameter('alert')['level'];
		//$t_alert_class = $this->getParameter('alert')['class'];

		$n_project = $this->get('project')->search( [], -1 );
		$n_server  = $this->get('server')->search( [], -1 );
		$n_domain  = $this->get('domain')->search( [], -1 );
		$n_host    = $this->get('host')->search( [], -1 );
		$n_task    = $this->get('entity_task')->search( [], -1 );
		$n_alert   = $this->get('entity_alert')->search( [], -1 );

		$t_last_task = $this->get('entity_task')->search( ['status'=>$t_task_status['interpreted']], 1, 10 );
		$t_last_alert = $this->get('entity_alert')->search( ['status'=>$t_alert_status['new'],'level'=>$t_alert_level['low']], 1, 10 );

		$a_last_alert = $this->get('entity_alert')->search( ['entity_id'=>$actarus->getEntityId(),'status'=>$t_alert_status['new']], 1, 10 );

        return $this->render('DashboardBundle:Default:index.html.twig',[
			'n_project' => $n_project,
			'n_server'  => $n_server,
			'n_domain'  => $n_domain,
			'n_host'    => $n_host,
			'n_task'    => $n_task,
			'n_alert'   => $n_alert,
			't_entity_type' => $t_entity_type,
			't_last_task'   => $t_last_task,
			't_last_alert'   => $t_last_alert,
			'a_last_alert'   => $a_last_alert,
			//'t_alert_class' => $t_alert_class,
		]);
    }
}
