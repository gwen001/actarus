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


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;


class CronCommand extends ContainerAwareCommand
{
	protected $em;

	protected $container;

	protected $logger;


	protected function configure()
	{
		$this->setName('arus:cron:run')
			->setDescription('Run a cron')
			->setDefinition(
				new InputDefinition([
					new InputOption(
						'cron_name',
						'c',
						InputOption::VALUE_REQUIRED,
						'What cron do you want to run?'
					),
					new InputOption(
						'project_name',
						'p',
						InputOption::VALUE_REQUIRED,
						'What project is concerned?'
					)
				])
			);
	}


	protected function execute( InputInterface $input, OutputInterface $output )
	{
		$this->container = $this->getContainer();
		$this->em        = $this->container->get('doctrine')->getManager();
		$this->logger    = $this->container->get('logger');

		$logger = $this->logger;
		$cron_name = $input->getOption('cron_name');
		$logger->info( $cron_name );

		$project_name = $input->getOption('project_name');

		// call the all time callback
		$f = $cron_name;
		if( is_callable([$this,$f]) ) {
			$this->$f( $project_name );
		} else {
			$logger->info( 'error: action not found (func='.$f.')' );
		}

		$logger->info( 'cron ended (name='.$cron_name.')' );
		exit( 0 );
	}

	
	private function domainSurvey( $project_name )
	{
		$cnt = 0;
		$container = $this->container;

		// do not threat domain that have been disabled
		$t_domain = $container->get('domain')->search( ['survey'=>'1','status'=>[4,'!=']], null, null );

		foreach( $t_domain as $d ) {
			$cnt++;
			$container->get('entity_task')->create( $d, 'crtsh' );
			$container->get('entity_task')->create( $d, 'subthreat' );
		}

		return $cnt;
	}


	private function crtsh( $project_name )
	{
		$cnt = 0;
		$container = $this->container;

		// do not threat domain that have been disabled
		$t_domain = $container->get('domain')->search( ['status'=>[4,'!=']], null, null );

		foreach( $t_domain as $d ) {
			$cnt++;
			$container->get('entity_task')->create( $d, 'crtsh' );
		}

		return $cnt;
	}


	private function subthreat( $project_name )
	{
		$cnt = 0;
		$container = $this->container;

		// do not threat domain that have been disabled
		$t_domain = $container->get('domain')->search( ['status'=>[4,'!=']], null, null );

		foreach( $t_domain as $d ) {
			$cnt++;
			$container->get('entity_task')->create( $d, 'subthreat' );
		}

		return $cnt;
	}


	private function hackeroneProgramGrabber( $project_name )
	{
		$cnt = 0;
		$em = $this->em;
		$container = $this->getContainer();
		$t_search = ['ibb:no type:hackerone','ibb:no bounties:yes'];
			$t_search = ['type:hackerone','bounties:yes'];

		$client = new Client( ['base_uri'=>'https://hackerone.com'] );

		foreach( $t_search as $s )
		{
			$response = $client->request( 'GET',
										'/programs/search', [
										'query' => [
											'query' => $s,
											'sort' => 'published_at:descending',
											'page' => '1',
											'limit' => '500',
										],
										'headers' => [
											'Accept' => 'application/json, text/javascript; q=0.01',
											'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:48.0) Gecko/20100101 Firefox/48.0',
											'X-Requested-With' => 'XMLHttpRequest',
										],
								]);
			$t_json = json_decode( $response->getBody()->getContents() );
			$t_program = $t_json->results;
			// id, url, name, meta, about, stripped_policy, handle, profile_picture, internet_bug_bounty

			foreach( $t_program as $p )
			{
				$project = $em->getRepository('ArusProjectBundle:ArusProject')->findOneByName( $p->name );
				if( !$project ) {
					$cnt++;
					$project = $container->get('project')->create( $p->name );
				}
				$project->setHandle( $p->handle );
				echo $p->name."\n";
				$em->persist( $project );
			}
		}

		$em->flush();

		if( $cnt ) {
			$actarus = $container->get('app')->getActarus();
			$t_alert_level = $container->getParameter('alert')['level'];
			$container->get('entity_alert')->create( $actarus, $cnt.' new project added.', $t_alert_level['info'] );
		}

		return $cnt;
	}


	private function hackeroneMassScopeGrabber( $project_name )
	{
		$cnt = 0;
		$em = $this->em;
		$container = $this->getContainer();
		$t_search = ['ibb:no type:hackerone','ibb:no bounties:yes'];

		$client = new Client( ['base_uri'=>'https://hackerone.com'] );

		foreach( $t_search as $s )
		{
			$response = $client->request( 'GET',
										'/programs/search', [
										'query' => [
											'query' => $s,
											'sort' => 'published_at:descending',
											'page' => '1',
											'limit' => '500',
										],
										'headers' => [
											'Accept' => 'application/json, text/javascript; q=0.01',
											'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:48.0) Gecko/20100101 Firefox/48.0',
											'X-Requested-With' => 'XMLHttpRequest',
										],
								]);
			$t_json = json_decode( $response->getBody()->getContents() );
			// id, url, name, meta, about, stripped_policy, handle, profile_picture, internet_bug_bounty

			$t_program = $t_json->results;

			foreach( $t_program as $p )
			{
				echo '>>> '.$p->handle." <<<\n";
				$project = $em->getRepository('ArusProjectBundle:ArusProject')->findOneByName( $p->name );
				if( !$project ) {
					$cnt++;
					$project = $container->get('project')->create( $p->name );
				}
				$project->setHandle( $p->handle );
				$em->persist( $project );

				$this->hackeroneScopeGrabber( $project->getName() );
			}
		}

		$em->flush();

		if( $cnt ) {
			$actarus = $container->get('app')->getActarus();
			$t_alert_level = $container->getParameter('alert')['level'];
			$container->get('entity_alert')->create( $actarus, $cnt.' new project added.', $t_alert_level['info'] );
		}

		return $cnt;
	}

	
	private function hackeroneScopeGrabber( $project_name )
	{
		$cnt = 0;
		$em = $this->em;
		$container = $this->getContainer();
		
		$project = $em->getRepository('ArusProjectBundle:ArusProject')->findOneByName( $project_name );
		if( !$project ) {
			return false;
		}

		$client = new Client( ['base_uri'=>'https://hackerone.com'] );

		$response = $client->request( 'GET',
									'/'.$project->getHandle(), [
									'headers' => [
										'Accept' => 'application/json, text/javascript; q=0.01',
										'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:48.0) Gecko/20100101 Firefox/48.0',
										'X-Requested-With' => 'XMLHttpRequest',
									],
							]);
		$t_details = json_decode( $response->getBody()->getContents() );
		// id, handle, url, profile, scopes, cover_color, twitter_handle, ibb, has_cover_photo, cover_photo_url, has_cover_video, profile_picture_urls,
		// external_program, external_url, rejecting_submission, can_manage_team_member_groups, can_invite_team_member, report_templates_enabled

		if( isset($t_details->scopes) ) {
			foreach( $t_details->scopes as $s ) {
				$cnt++;
				$s = str_replace( '*.', 'www.', $s );
				$s = str_replace( 'http://', '', $s );
				$s = str_replace( 'https://', '', $s );
				$s = str_replace( '/', '', $s );
				if( strstr($s,'.') !== false ) {
					echo str_replace('*.','www.',$s)."\n";
				}
			}
		}

		echo "\n";
		
		/*if( $cnt ) {
			$actarus = $container->get('app')->getActarus();
			$t_alert_level = $container->getParameter('alert')['level'];
			$container->get('entity_alert')->create( $actarus, $cnt.' new project added.', $t_alert_level['info'] );
		}*/
		
		return $cnt;
	}
	
	
	/*public function importScope( $project_name )
	{
		$em = $this->em;
		$container = $this->getContainer();
		$content = file( '/tmp/bb_scopes2.txt' );
		//var_dump( $content );

		$t_host = [];
		$project = null;
		$cnt = $total = 0;

		foreach( $content as $l )
		{
			$l = trim( $l );
			//echo $l."\n";

			if( $l == '' ) {
				continue;
			}
			
			if( strstr($l,'>>>') ) {
				if( $project && count($t_host) ) {
					$cnt = $container->get('host')->import( $project, $t_host );
					$total += $cnt;
					echo "Project ".$project->getHandle().": ".$cnt." hosts added\n";
				}
				$t_host = [];
				$p = str_replace( ['>','<',' '], '', $l );
				//var_dump($p);
				$project = $em->getRepository('ArusProjectBundle:ArusProject')->findOneByHandle( $p );
				continue;
			}
			else {
				$t_host[] = $l;
			}
		}

		echo $total." hosts added\n";
		return $total;
	}*/
	
	
	/*public function getThirdPartyHost( $project_name )
	{
		$container = $this->getContainer();
		$t_task = $container->get('entity_task')->search( ['command'=>'host','output'=>$project_name] );
		$cnt = count( $t_task );
		$t_host = [];
		
		foreach( $t_task as $t )
		{
			$m = preg_match( '#(.*) is an alias for ((.*)\.(.*)'.$project_name.'(.*)\.(.*))\.#', $t->getOutput(), $match );
			if( $m ) {
				$t_host[] = $match[2];
			}
		}

		$cnt = count( $t_host );
		
		if( $cnt ) {
			$t_host = array_unique( $t_host );
			foreach( $t_host as $h ) {
				echo $h."\n";
			}
		}
		
		return $cnt;
	}*/
}
