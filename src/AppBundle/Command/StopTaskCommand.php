<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;


class StopTaskCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('arus:task:stop')
			->setDescription('Stop a task')
			->setDefinition(
				new InputDefinition([
					new InputOption(
						'task_id',
						't',
						InputOption::VALUE_REQUIRED,
						'What task do you want to stop?'
					)
				])
			);
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$container = $this->getContainer();
		$logger = $container->get('logger');
		$em = $container->get('doctrine')->getManager();
		$t_status = $container->getParameter('task')['status'];

		$task_id = (int)$input->getOption('task_id');
		$logger->info($task_id);

		$task = $em->getRepository('ArusEntityTaskBundle:ArusEntityTask')->findOneById( $task_id );
		if( !$task ) {
			$logger->info( 'error: task id not found (id='.$task_id.')' );
			exit( -1 );
		}

		/*if( $task->getClusterId() != $container->getParameter('daemon_cluster_id') ) {
			$logger->info('error: wrong cluster (id=' . $task->getId() . ', cluster_id=' . $task->getClusterId() . ')');
			exit(-1);
		}*/

		/*if ($task->getStatus() != $t_status['running']) {
			$logger->info('error: wrong task status (id=' . $task->getId() . ', status=' . $task->getStatus() . ')');
			exit(-1);
		}*/

		$logger->info( 'stoping task (id='.$task->getId().')' );
		$container->get('entity_task')->stop( $task );
		$logger->info( 'task stoped (id='.$task->getId().')' );


		exit( 0 );
	}
}
