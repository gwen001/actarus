<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\File;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use ArusEntityTaskBundle\Entity\ArusEntityTask;


class ImportRequestCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('arus:request:import')
			->setDescription('Import request')
			->setDefinition(
				new InputDefinition([
					new InputOption(
						'project_id',
						'p',
						InputOption::VALUE_REQUIRED,
						'What project is concerned?'
					),
					new InputOption(
						'source_file',
						'f',
						InputOption::VALUE_REQUIRED,
						'Source file containing the datas'
					),
					new InputOption(
						'format',
						'o',
						InputArgument::VALUE_REQUIRED,
						'Format of the datas'
					),
					new InputOption(
						'recon',
						'r'
					)
				])
			);
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$container = $this->getContainer();
		$logger = $container->get('logger');
		$em = $container->get('doctrine')->getManager();

		$project_id = $input->getOption('project_id');
		$project = $em->getRepository('ArusProjectBundle:ArusProject')->findOneById( $project_id );
		if( !$project ) {
			$logger->info( 'error: project id not found (id='.$project_id.')' );
			exit( -1 );
		}
		
		$source_file = trim( $input->getOption('source_file') );
		if( !is_file($source_file) ) {
			$logger->info( 'error: source file not found (file='.$source_file.')' );
			exit( -1 );
		}
		
		$format = trim( $input->getOption('format') );
		$t_format = $container->getParameter('request')['import_format'];
		if( !$format || !isset($t_format[$format]) ) {
			$logger->info( 'error: format not valid (format='.$format.')' );
			exit( -1 );
		}
		
		$recon = trim( $input->getOption('recon') );
		if( $recon ) {
			$recon = true;
		}
		
		$r = $container->get('arequest')->import( $project, $source_file, $format, $recon );
		
		exit( 0 );
	}
}
