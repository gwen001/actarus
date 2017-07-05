<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use ArusEntityTaskBundle\Entity\ArusEntityTask;


class RunTaskCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('arus:task:run')
			->setDescription('Run a task')
			->setDefinition(
				new InputDefinition([
					new InputOption(
						'task_id',
						't',
						InputOption::VALUE_REQUIRED,
						'What task do you want to run?'
					),
					new InputOption(
						'force',
						'f'
					)
				])
			);
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$container = $this->getContainer();
		$bin_path = $container->getParameter('bin_path');
		$bin_path = rtrim($bin_path,'/') . '/';
		$t_status = $container->getParameter('task')['status'];
		$logger = $container->get('logger');
		$em = $container->get('doctrine')->getManager();

		$task_id = (int)$input->getOption('task_id');
		$logger->info($task_id);

		$force = (int)$input->getOption('force');

		$task = $em->getRepository('ArusEntityTaskBundle:ArusEntityTask')->findOneById( $task_id );
		if( !$task ) {
			$logger->info( 'error: task id not found (id='.$task_id.')' );
			exit( -1 );
		}

		if( !$force ) {
			if ($task->getStatus() != $t_status['reserved']) {
				$logger->info('error: wrong task status (id=' . $task->getId() . ', status=' . $task->getStatus() . ')');
				exit(-1);
			}
		}

        $t_entity_type = array_flip( $container->getParameter('entity')['type'] );
        $entity = $em->getRepository('ArusEntityTaskBundle:ArusEntityTask')->getRelatedEntity( $task, $t_entity_type );
        $task->setEntity( $entity );

        $logger->info( 'running task (id='.$task->getId().')' );

		// old school method
		//ob_start();
		//system( escapeshellcmd($task->getCommand()) );
		//$result = ob_get_contents();
		//ob_end_clean();

		// Symfony method
		$process = new Process( $bin_path . $task->getCommand() );
		$process->start();

		$t = $task->getTask();
		
		$task->setPid( getmypid() );
		$task->setRealPid( $process->getPid() );
		$task->setClusterId( $container->getParameter('daemon_cluster_id') );
		$task->setStatus( $t_status['running'] );
		$task->setStartedAt( new \DateTime() );
		$task->setEndedAt( null );
		$k = new \DateTime();
		$k->add( date_interval_create_from_date_string($t->getTimeout().' minutes') );
		$task->setKillAt( $k );
		
		$end_status = $t_status['finished'];
		
		while( $process->isRunning() )
		{
			$task->setOutput( $process->getOutput() );
			$em->persist($task);
			$em->flush();
			$a = $em->refresh( $task );

			if( $task->getStatus() == $t_status['cancelled'] || time() >= $task->getKillAt()->format('U') ) {
				$end_status = $t_status['cancelled'];
				$container->get('entity_task')->kill( $task );
				break;
			}
			
			usleep( 2000000 ); // 2 secondes
		}

		$task->setOutput( $process->getOutput() );
		$task->setStatus( $end_status );
		$task->setEndedAt( new \Datetime() );
		$em->persist($task);
		$em->flush();

        $logger->info( 'task ended (id='.$task->getId().')' );
		exit( 0 );
	}
}
