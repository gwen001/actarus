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


class CreateTaskCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('arus:task:create')
			->setDescription('Create a task')
			->setDefinition(
				new InputDefinition([
					new InputOption(
						'task_id',
						't',
						InputOption::VALUE_REQUIRED,
						'What task do you want to run?'
					),
					new InputOption(
						'entity_id',
						'i',
						InputOption::VALUE_REQUIRED,
						'What entity is concerned?'
					)
				])
			);
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$container = $this->getContainer();
		$logger = $container->get('logger');
		$em = $container->get('doctrine')->getManager();

		$task_id = (int)$input->getOption('task_id');
		$task = $em->getRepository('ArusTaskBundle:ArusTask')->findOneById( $task_id );
		if( !$task ) {
			$logger->info( 'error: task id not found (id='.$task_id.')' );
			exit( -1 );
		}

		$entity_id = $input->getOption('entity_id');
		$entity = $container->get('app')->getEntityById( $entity_id );
		if( !$entity ) {
			$logger->info( 'error: entity id not found (entity_id='.$entity_id.')' );
			exit( -1 );
		}
		
		$r = $container->get('entity_task')->create( $entity, $task->getName() );
		if( !$entity ) {
			$logger->info( 'error: cannot create task (task_id='.$task_id.', entity_id='.$entity_id.')' );
			exit( -1 );
		}
		
		exit( 0 );
	}
}
