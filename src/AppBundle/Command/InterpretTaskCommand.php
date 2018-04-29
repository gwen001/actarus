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

use ArusHostBundle\Entity\ArusHost;
use ArusServerBundle\Entity\ArusServer;
use ArusServerServiceBundle\Entity\ArusServerService;
use ArusEntityAlertBundle\Entity\ArusEntityAlert;
use ArusEntityTaskBundle\Entity\ArusEntityTask;
use ArusEntityTechnologyBundle\Entity\ArusEntityTechnology;

use Actarus\Utils;


class InterpretTaskCommand extends ContainerAwareCommand
{
	/**
	 * Command configuration
	 */
	protected function configure()
	{
		$this->setName('arus:task:interpret')
			->setDescription('Interpret the result of a task')
			->setDefinition(
				new InputDefinition([
					new InputOption(
						'task_id',
						't',
						InputOption::VALUE_REQUIRED,
						'What task do you want to interpret?'
					),
					new InputOption(
						'force',
						'f'
					)
				])
			);
	}


	/**
	 * Run the command
	 *
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$container = $this->container = $this->getContainer();
		$logger = $this->logger = $container->get('logger');
		$em = $this->em = $container->get('doctrine')->getManager();
		$t_status = $container->getParameter('task')['status'];

		$task_id = (int)$input->getOption('task_id');
		$logger->info($task_id);

		$force = (int)$input->getOption('force');

		$task = $em->getRepository('ArusEntityTaskBundle:ArusEntityTask')->findOneById( $task_id );
		if( !$task ) {
			$logger->info( 'error: task id not found (id='.$task_id.')' );
			exit( -1 );
		}

		if( !$force ) {
			if ($task->getStatus() != $t_status['postreserved']) {
				$logger->info('error: wrong task status (id=' . $task->getId() . ', status=' . $task->getStatus() . ')');
				exit(-1);
			}
		}

        $t_entity_type = array_flip( $container->getParameter('entity')['type'] );
        $entity = $em->getRepository('ArusEntityTaskBundle:ArusEntityTask')->getRelatedEntity( $task, $t_entity_type );
        $task->setEntity( $entity );

        $logger->info( 'interpreting task (id='.$task->getId().')' );

		$task->setStatus( $t_status['interpreting'] );
		$em->persist($task);
		$em->flush();

		// run the interpreter himself
		//if(  trim($task->getOutput()) != '' ) {
			$this->interpret( $task );
		//}

		$task->setStatus( $t_status['interpreted'] );
		$em->persist( $task );

        //$t_status = array_flip( $container->getParameter('entity')['status'] );
        //$entity->setStatus( $t_status['todo'] );
        //$em->persist( $entity );

        $em->flush();

        $logger->info( 'task interpreted (id='.$task->getId().')' );
		exit( 0 );
	}


	/**
	 * Output task interpreter
	 * Match the regexp and call the concerned callback
	 *
	 * @param $task
	 * @return bool
	 */
	private function interpret( $task )
	{
		$run_callback = 0;
		$em = $this->em;
		$container = $this->container;
		$logger = $this->logger;

		$t = $task->getTask();

		// call the all time callback
		$f = $t->getName();
		if( is_callable([$this,$f]) ) {
			// perform the action!
			//$logger->info( 'calling callback (func='.$f.')' );
			$run_callback = (int)$this->$f( $task );
		} else {
			$logger->info( 'error: action not found (func='.$f.')' );
		}

		if( $run_callback >= 0 ) {
			// call all callback setted for the concerned task
			$t_callback = $em->getRepository('ArusTaskCallbackBundle:ArusTaskCallback')->findByTask($t, ['priority' => 'asc']);

			foreach ($t_callback as $c) {
				if (preg_match('#' . $c->getRegex() . '#i', $task->getOutput(), $m)) {
					$f = $c->getAction();
					if (is_callable([$this, $f])) {
						// perform the action!
						//$logger->info( 'calling callback (func='.$f.')' );
						$this->$f($task, $c);
					} else {
						$logger->info('error: action not found (func=' . $f . ')');
					}
				}
			}
		}

        return true;
	}


	/*****************************************************/
	/* callbacks                                         */
	/*****************************************************/
	/**
	 * Add an alert for an entity
	 *
	 * @param ArusEntityTask $task
	 * @param ArusTaskCallback $callback
	 * @return bool true
	 */
	private function addAlert( $task, $callback )
	{
		$t_params = $callback->getParams();
		$container = $this->container;
		$container->get('entity_alert')->create( $task->getEntity(), $t_params['text'], $t_params['alert_level'], $task );

		return true;
	}


	/**
	 * Add a new task for an entity
	 *
	 * @param ArusEntityTask $task
	 * @param ArusTaskCallback $callback
	 * @return bool true
	 */
	private function addTask( $task, $callback )
	{
		$container = $this->container;
		$t_entity_type = array_flip( $container->getParameter('entity')['type'] );
		$t_params = $callback->getParams();

		$em = $this->em;
		$entity = $em->getRepository('ArusEntityTaskBundle:ArusEntityTask')->getRelatedEntity( $task, $t_entity_type );
		$container->get('entity_task')->create( $entity, $t_params['task'] );

		return true;
	}


	/**
	 * Add a tehnology to an entity
	 *
	 * @param ArusEntityTask $task
	 * @param ArusTaskCallback $callback
	 * @return bool true
	 */
	private function addTechnology( $task, $callback )
	{
		$em = $this->em;
		$t_params = $callback->getParams();

		$techno = $em->getRepository('ArusTechnologyBundle:ArusTechnology')->findOneById( $t_params['technology'] );
		if( !$techno ) {
			return false;
		}

		$container = $this->container;
		$entity = $task->getEntity();
		$version = isset($t_params['text']) ? $t_params['text'] : '';

		return $container->get('entity_technology')->create( $techno, $entity, $version );
	}


	/*****************************************************/
	/* post functions                                    */
	/*****************************************************/
	private function altdns( $task )
	{
        $n = $this->subthreat( $task );
        
        $container = $this->container;
        $domain = $task->getEntity();
        $project = $domain->getProject();
        
		$altdns = $container->get('entity_task')->search( ['project'=>$project,'command'=>'altdns','status'=>['13','<']] );
		$s3bucket = $container->get('entity_task')->search( ['project'=>$project,'command'=>'bucket','status'=>['13','<']] );
		$subthreat = $container->get('entity_task')->search( ['project'=>$project,'command'=>'subthreat','status'=>['13','<']] );
		
		if( !$altdns && !$subthreat ) {
	        $container->get('entity_task')->create( $domain, 'act_aquatone_takeover' );
		}
		if( !$altdns && !$s3bucket && !$subthreat ) {
			$container->get('entity_task')->create( $project, 'altbucket' );
		}

        return $n;
	}

	
	private function aquatone_discover( $task )
	{
        $container = $this->container;
        $domain = $task->getEntity();
		
		$container->get('entity_task')->create( $domain, 'aquatone_scan' );

		return true;
	}
	private function aquatone_scan( $task )
	{
        $container = $this->container;
        $domain = $task->getEntity();
		
		$container->get('entity_task')->create( $domain, 'aquatone_gather' );

		return true;
	}
	private function aquatone_gather( $task )
	{
        $container = $this->container;
        $domain = $task->getEntity();
		
		$container->get('entity_task')->create( $domain, 'aquatone_takeover' );

		return true;
	}
	private function aquatone_takeover( $task )
	{
		return true;
	}

	
	private function crtsh( $task )
	{
        return $this->subthreat( $task );
	}
	
	
	private function dirb_forbidden( $task )
	{
		return $this->dirb( $task );
	}
	private function dirb_myhardw( $task )
	{
		return $this->dirb( $task );
	}
	private function dirb( $task )
	{
		$container = $this->container;
		$em = $this->em;
		$t = $em->getRepository('ArusTaskBundle:ArusTask')->findOneByName( 'dirb' );
		$t_callback = $em->getRepository('ArusTaskCallbackBundle:ArusTaskCallback')->findByTask( $t, ['priority'=>'asc'] );

		// manage special directories
		foreach( $t_callback as $c )
		{
			$cc = clone $c;

			if( ($a=preg_match( '#\+ (.*/('.$cc->getRegex().')) \(CODE:([0-9]+)\|SIZE:([0-9]+)\)#i',$task->getOutput(),$match)) ) {
				$code = (int)$match[3];
				$size = (int)$match[4];

				if( $code < 200 || $code > 299 || !$size ) {
					continue;
				}
			} elseif( ($b=preg_match( '#==> DIRECTORY: (.*/('.$cc->getRegex().'))/#i',$task->getOutput(),$match)) ) {
				$code = $size = '';
			}

			if( $a || $b ) {
				$url = $match[1];
				$term = $match[2];
				$t_params = $cc->getParams();

				if (isset($t_params['text']) && trim($t_params['text']) != '') {
					$text = $t_params['text'];
					$text = str_replace( '__URL__', $url, $text );
					$text = str_replace( '__TERM__', $term, $text );
					$text = str_replace( '__CODE__', $code, $text );
					$text = str_replace( '__SIZE__', $size, $text );
					$t_params['text'] = $text;
					$cc->setParams( $t_params );
				}

				$f = $cc->getAction();
				if (is_callable([$this, $f])) {
					$this->$f( $task, $cc );
				}
			}
		}

		// manage directory listing
		$t_dir = [];
		$output = array_map( 'trim', explode("\n",$task->getOutput()) );

		foreach( $output as $l )
		{
			if( preg_match('#Directory IS LISTABLE#i',$l) ) {
				$m = preg_match( '#---- Entering directory: (.*) ----#i', $save_l, $match );
				if( $m ) {
					$url = $match[1];
					$dir = basename( $url );
					$t_dir[] = '<a href="'.$url.'" target="_blank">'.$dir.'</a>';
				}
			}
			$save_l = $l;
		}

		if( count($t_dir) ) {
			$t_alert_level = $container->getParameter('alert')['level'];
			$container->get('entity_alert')->create( $task->getEntity(), 'Directory listing found: ' . implode(', ', $t_dir) . '.', $t_alert_level['medium'], $task );
		}

		return -1;
	}

	
	private function dirbsearch( $task )
	{
	}

	
	/**
	 * Add discovered hosts
	 */
	private function dnsrecon_axfr( $task )
	{
		return $this->dnsrecon_brute( $task );
	}
	/**
	 * Add discovered hosts
	 */
	private function dnsrecon_brute( $task )
	{
		$cnt = 0;
		$t_host = [];

		$em = $this->em;
		$container = $this->container;
		$domain = $task->getEntity();
		$domain_name = $domain->getName();
		$output = array_map( 'trim', explode("\n",$task->getOutput()) );

		foreach( $output as $l ) {
			if( preg_match( '#\[\*\]\s+(A|AAAA|CNAME|MX|NS|SOA) (.*) (.*)#',$l,$m) ) {
				$m = array_map( 'trim', $m );

                switch( $m[1] ) {
                	case 'A':
                		// ip pointer
                		if( $container->get('domain')->sameProject($domain,$m[2]) ) {
		                    $t_host[] = $m[2];
                		}
                		break;
                	case 'AAAA':
                		// ipv6
                		break;
                	case 'CNAME':
                		// host alias
                		if( $container->get('domain')->sameProject($domain,$m[2]) ) {
		                    $t_host[] = $m[2];
                		}
                		if( $container->get('domain')->sameProject($domain,$m[3]) ) {
		                    $t_host[] = $m[3];
                		}
                		break;
                	case 'MX':
                		// ??
                		break;
                	case 'NS':
                		// ??
                		break;
                	case 'SOA':
                		// ???
                		break;
                }
			}
		}

		if( count($t_host) ) {
			// add hosts found
			$t_host = array_unique( $t_host );
			$cnt = $container->get('host')->import( $domain->getProject(), $t_host );
			if( $cnt ) {
				$t_alert_level = $container->getParameter('alert')['level'];
				$container->get('entity_alert')->create( $domain, $cnt.' new host added.', $t_alert_level['info'], $task );
			}
		}

		return $cnt;
	}


	/**
	 * Add discovered hosts
	 */
	private function host( $task )
	{
		$container = $this->container;
		$entity = $task->getEntity();
		$t_status = array_flip( $container->getParameter('host')['status'] );

		if( strstr($task->getOutput(),'not found: 3(NXDOMAIN)') ) {
			$entity->setStatus( $t_status['ko'] );
			$this->em->persist( $entity );
			return false;
		}

		$cnt = 0;
		$is_alias = 0;
		$t_host = [];
		$t_server = [];
		$t_link = [];
		
		$output = array_map( 'trim', explode("\n",strtolower($task->getOutput())) );
		$domain = $entity->getDomain();
		$domain_name = $domain->getName();
		
		foreach( $output as $k=>$l )
		{
			$l = trim( $l, '.' );

			if( preg_match('#(.*) is an alias for (.*)#', $l, $m) ) {
				if( $m[1] == $entity->getName() ) {
					if( $container->get('domain')->sameProject($domain,$m[2]) ) {
						// alias but in the same project
						$is_alias = 1;
                    	$t_host[] = $m[2];
					} elseif( $container->get('host')->isWhiteListed($m[2]) ) {
						$is_alias = -2;
					} else {
						$is_alias = -1;
					}
				}
			} elseif( preg_match('#(.*) has address (.*)#', $l, $m) ) {
				if( $container->get('domain')->sameProject($domain,$m[1]) ) {
                    $t_host[] = $m[1];
					$t_server[] = $m[2];
					$t_link[] = [ 'host'=>$m[1], 'server'=>$m[2] ];
					$t_link[] = [ 'host'=>$entity->getName(), 'server'=>$m[2] ];
				} elseif( $container->get('host')->isWhiteListed($m[1]) ) { // mmmmmm should I remove that ? not sure...
                    //$t_host[] = $m[1];
					$t_server[] = $m[2];
					$t_link[] = [ 'host'=>$entity->getName(), 'server'=>$m[2] ];
				}
			} elseif( preg_match('#(.*) mail is handled by [0-9]+ (.*)#', $l, $m) ) {
				if( !Utils::isDomain($m[1]) && $container->get('domain')->sameProject($domain,$m[1]) ) {
                    $t_host[] = $m[1];
				}
				if( !Utils::isDomain($m[2]) && $container->get('domain')->sameProject($domain,$m[2]) ) {
                    $t_host[] = $m[2];
				}
			}
		}

		//var_dump( $t_host );
		//var_dump( $t_server );
		//var_dump( $t_link );
		//var_dump( $is_alias );
		//exit();

		if( count($t_host) ) {
			$t_host = array_diff( $t_host, [$entity->getName()] );
		}
		if( count($t_host) ) {
			// add hosts found
			$t_host = array_unique( $t_host );
			$cnt = $container->get('host')->import( $domain->getProject(), $t_host );
			if( $cnt ) {
				$t_alert_level = $container->getParameter('alert')['level'];
				$container->get('entity_alert')->create( $domain, $cnt.' new host added.', $t_alert_level['info'], $task );
			}
		}

		if( count($t_server) ) {
			// add servers found
			$t_server = array_unique( $t_server );
			$cnt += $container->get('server')->import( $domain->getProject(), $t_server );
		}

		if( count($t_link) ) {
			// add host/server links
			$container->get('host_server')->import( $domain->getProject(), $t_link );
		}

		switch( $is_alias )
		{
			case 1:
				// the host is an alias of a domain of the same project
				// nothing
				break;
				
			case -1:
				// the host is an alias of a domain not in the project, third party service there
				$t_alert_level = $container->getParameter('alert')['level'];
				$container->get('entity_alert')->create( $task->getEntity(), 'This host is an alias, check all domains in the chain for possible takeover.', $t_alert_level['info'], $task );
				$container->get('entity_task')->create( $task->getEntity(), 'dnsexpire' );
				break;
				
			case -2:
				// the host is an alias of a domain not in the project (third party service) but whitelisted
				$t_alert_level = $container->getParameter('alert')['level'];
				$container->get('entity_alert')->create( $task->getEntity(), 'This host is an alias, check all domains in the chain for possible takeover.', $t_alert_level['info'], $task );
				$container->get('entity_task')->create( $task->getEntity(), 'dnsexpire' );
				$container->get('entity_task')->create( $task->getEntity(), 'testhttp' );
				break;
				
			case 0:
			default;
				// the host is not an alias
				$container->get('entity_task')->create( $task->getEntity(), 'testhttp' );
				break;
		}

		return $cnt;
	}

	
	private function masscan( $task )
	{
		$project = $task->getProject();
		$output = $task->getOutput();
		$container = $this->container;
		$entity = $task->getEntity();
		$command = $task->getCommand();
		$udp = (stristr($command,'U:')===false) ? false : true;
		$m = preg_match_all( '#Discovered open port ([0-9]+)/(tcp|udp) on (.*)#', $output, $matches );
		$t_ip = [];
		$cnt = 0;

		if( $m )
		{
			$cnt = count( $matches[0] );
			
			for( $i=0 ; $i<$m ; $i++ )
			{
				$port = (int)$matches[1][$i];
				$prot = $matches[2][$i];
				$ip = trim( $matches[3][$i] );

				if( !isset($t_ip[$ip]) ) {
					$t_ip[$ip] = [ 'tcp'=>[], 'udp'=>[] ];
				}
				
				$t_ip[$ip][ $prot ][] = $port;
				
			}
		}
		
		// useless?
		//$container->get('server')->import( $project, array_keys($t_ip), false );
		//var_dump( $t_ip );

		foreach( $t_ip as $ip=>$v )
		{
			$server = $container->get('server')->search( ['name'=>[$ip,'=']] );
			if( !$server || !is_array($server) || !count($server) ) {
				// impossible!
				continue;
			}
			$server = $server[0];

			foreach( $v as $prot=>$t_port ) {
				if( count($t_port) > 0 && count($t_port) < 50 ) {
					sort( $t_port, SORT_NUMERIC );
					$s_port = implode( ',', $t_port );
					$t_options = ['PORT'=>$s_port];
					if( $udp ) {
						$t_options['TYPE'] = '-sU';
					}
				}
			}
			//var_dump( $t_options );
			
			$container->get('entity_task')->create( $server, 'nmap_custom', $t_options );
		}

		return $cnt;
	}

	
	private function nmap_udp( $task )
	{
        return $this->nmap_full( $task );
	}
	private function nmap_top10( $task )
	{
        return $this->nmap_full( $task );
	}
	private function nmap_custom( $task )
	{
        return $this->nmap_full( $task );
	}
	private function nmap_full( $task )
	{
		$output = $task->getOutput();
		$container = $this->container;
		$entity = $task->getEntity();
		//$m = preg_match_all( '#([0-9]+)/tcp[\s]+open[\s]+([^\s]*)(.*)\n#', $output, $match );
		$m = preg_match_all( '#([0-9]+)/([^\s]*)[ ]+open[ ]+([^\s]*)([ ]+(.*))?#', $output, $matches );
		//var_dump( $matches );

		if( $m )
		{
			$t_port = [];
			$a_text = [];
			$cnt = count( $matches[1] );
			//var_dump($matches);

			for( $i=0 ; $i<$cnt ; $i++ )
			{
				$port = (int)trim( $matches[1][$i] );
				$type = trim( $matches[2][$i] );
				$service = trim( $matches[3][$i], ' ?' );
				$version = trim( $matches[4][$i] );

				$t_port[] = $port;
				/*
				$new_task = null;
				switch( $port ) {
					case 21:
						$new_task = 'hydra_ftp';
						break;
					case 22:
					case 2121:
					case 2222:
						$new_task = 'hydra_ssh';
						break;
					case 25:
						$new_task = 'smtp_user';
						break;
					case 3306:
						$new_task = 'patator_mysql';
						break;
					case 3389:
						$new_task = 'hydra_rdp';
						break;
				}

				if( !is_null($new_task) ) {
					$container->get('entity_task')->create( $entity, $new_task, ['PORT'=>$port] );
				}
				*/
				$txt = $port;
				if( $service != '' ) {
					$txt .= ' ('.$service.')';
				}
				$a_text[] = $txt;

				$container->get('server_service')->create( $entity, $port, $type, $service, $version );
			}

			$container->get('entity_task')->create( $entity, 'testhttp', ['PORT'=>implode(',',$t_port)] );
			
			$t_alert_level = $container->getParameter('alert')['level'];
			$container->get('entity_alert')->create( $task->getEntity(), 'Open ports are: '.implode(', ',$a_text).'.', $t_alert_level['info'], $task );
		}

		return $m;
	}


	private function portscan_nc( $task )
	{
		$command = $task->getCommand();
		$udp = (stristr($command,'udp')===false) ? false : true;
		$output = $task->getOutput();
		$container = $this->container;
		$t_status = array_flip( $container->getParameter('server')['status'] );

		if( stristr($output,'Too much success') )
		{
			if( $udp ) {
				$container->get('entity_task')->create( $task->getEntity(), 'nmap_udp' );
			} else {
				$container->get('entity_task')->create( $task->getEntity(), 'nmap_full' );
			}
			
			return 0;
		}
		else
		{
			if( stristr($output,'succeeded') ) {
				$m = preg_match_all( '#Connection to .* ([0-9]+) port \[(tcp|udp)/\*\] succeeded!#i', $output, $match );
			} else {
				$m = preg_match_all( '#\(UNKNOWN\) \[.*\] ([0-9]+) \(.*\) open#i', $output, $match );
			}
	
			if( $m ) {
				sort( $match[1], SORT_NUMERIC );
				$t_port = $match[1];
				if( count($t_port) < 50 ) {
					$s_port = implode( ',', $t_port );
					$t_options = ['PORT'=>$s_port];
					if( $udp ) {
						$t_options['TYPE'] = '-sU';
					}
					$container->get('entity_task')->create( $task->getEntity(), 'nmap_custom', $t_options );
				}
			} else {
				$server = $task->getEntity();
				$server->setStatus( $t_status['ko'] );
				$this->em->persist( $server );
			}
			
			if( !$udp ) {
				$container->get('entity_task')->create( $task->getEntity(), 'portscan_nc', ['UDP'=>'udp'], null, -1 );
			}
			
			return $m;
		}
	}


	private function act_mydirbbucket( $task )
	{
		return $this->s3_buckets( $task );
	}
	private function act_busterbucket( $task )
	{
		return $this->s3_buckets( $task );
	}
	private function act_fuzzbucket( $task )
	{
		return $this->s3_buckets( $task );
	}
	private function altbucket( $task )
	{
		return $this->s3_buckets( $task );
	}
	private function s3_buckets( $task )
	{
		$b_name = null;
		$t_perms = [];
		$t_buckets = [];
		$t_vulnerable = [];
		$project = $task->getEntity();
		$container = $this->container;
		$output = array_map( 'trim', explode("\n",$task->getOutput()) );

		foreach( $output as $l )
		{
			$l = trim( $l );
			
			if( preg_match('#Testing: (.*) FOUND!#',$l,$m) ) {
				//var_dump( $m );
				$b_name = trim( $m[1] );
				$t_buckets[] = $b_name;
			}

			if( strstr($l,'Testing permissions:') )
			{
				$r = preg_match( '#Testing permissions: put ACL (failed|success)(, get ACL (failed|success), list (failed|success), HTTP list (failed|success), write (failed|success))?#', $l, $res );
				//var_dump( $res );
				
				if( $r )
				{
					if( count($res) == 2 ) {
						$tmp = [ 1, 0, 0, 0, 0 ];
					} else {
						$tmp = [
							($res[1]=='success') ? 1: 0,
							($res[3]=='success') ? 1: 0,
							($res[4]=='success') ? 1: 0,
							($res[5]=='success') ? 1: 0,
							($res[6]=='success') ? 1: 0,
						];
					}
					
					$t_perms[ $b_name ] = $tmp;
					
					if( in_array(1,$tmp) ) {
						$link = '<a href="https://'.$b_name.'.s3.amazonaws.com" target="_blank">'.$b_name.'</a>';
						$t_vulnerable[] = $link;
					}
				}
			}
		}
		
		//var_dump($t_perms);
		$cnt = count( $t_buckets );

		if( $cnt )
		{
			$container->get('bucket')->doImport( $project, $t_buckets, $t_perms );
			
			$cnt_vuln = count( $t_vulnerable );
			if( $cnt_vuln ) {
				$t_alert_level = $container->getParameter('alert')['level'];
				$container->get('entity_alert')->create( $project, 'S3 buckets seems to be misconfigured: '.implode(', ',$t_vulnerable).'.', $t_alert_level['high'], $task );
			}
		}
		
		return $cnt;
	}


	private function sublist3r( $task )
	{
		$output = $task->getOutput();
		//var_dump($output);
		if( stristr($output,'Total Unique Subdomains Found') === false ) {
			return false;
		}

		$m = preg_match( '#Total Unique Subdomains Found: ([0-9]+)([^µ]*)#', $output, $match );
		if( !$m || !isset($match[1]) || !($n=(int)$match[1]) ) {
			return false;
		}
		
		$t_host = [];
		$tmp_host = array_map( 'trim', explode("\n",trim($match[2])) );
		$container = $this->container;
		$domain = $task->getEntity();
		$domain_name = $domain->getName();

		foreach( $tmp_host as $h ) {
			if( Utils::extractDomain($h) == $domain_name ) {
				$t_host[] = $h;
			}
		}
		
		if( count($t_host) ) {
			$container->get('entity_task')->create( $domain, 'altdns' );
			$cnt = $container->get('host')->import( $domain->getProject(), $t_host );
	
			if( $cnt ) {
				$t_alert_level = $container->getParameter('alert')['level'];
				$container->get('entity_alert')->create( $domain, $cnt.' new host added.', $t_alert_level['info'], $task );
			}
		}
		else {
			$cnt = 0;
		}

		return $cnt;
	}
	

	private function sqlmap_smart( $task )
	{
		$container = $this->container;
		$output = $task->getOutput();
		$output = str_replace( "\r", '', $output );
		$task->setOutput( $output );
		$t_status = array_flip( $container->getParameter('request')['status'] );
		
		$request = $task->getEntity();
		$request->setStatus( $t_status['tested'] );

		$em = $this->em;
		$em->persist( $request );
		$em->flush();
		
		return 1;
	}


	private function subthreat( $task )
	{
		$output = trim( $task->getOutput() );
		if( $output == '' ) {
			return 0;
		}
		if( strstr($output,'Error:') ) {
			return false;
		}

		$t_host = [];
		$tmp_host = array_map( 'trim', explode("\n",$output) );
		$container = $this->container;
		$domain = $task->getEntity();
		$domain_name = $domain->getName();

		foreach( $tmp_host as $h ) {
			$h = trim( $h );
			if( $h == '' ) {
				continue;
			}
			if( Utils::extractDomain($h) == $domain_name ) {
				$t_host[] = $h;
			}
		}

		$cnt = $container->get('host')->import( $domain->getProject(), $t_host );

		if( $cnt ) {
			$t_alert_level = $container->getParameter('alert')['level'];
			$container->get('entity_alert')->create( $domain, $cnt.' new host added.', $t_alert_level['info'], $task );
		}

		return $cnt;
	}


	private function testhttp( $task )
	{
		$t_host = [];

		$cnt = 0;
		$flag = false;
		$output = array_map( 'trim', explode("\n",$task->getOutput()) );
		$container = $this->container;
		$entity = $task->getEntity();
		$t_status = array_flip( $container->getParameter('host')['status'] );

		foreach( $output as $l )
		{
			if( $l == '' ) {
				continue;
			}
			
			$tmp = explode( ':', $l );
			$cnt = count($tmp);
			$port = (int)trim( $tmp[0] );
			$http = trim( $tmp[1] );

			if( $http=='REDIR' && $entity->getEntityType() == ArusHost::ENTITY_TYPE_ID && $cnt>2 ) {
				// it's a redirection from a host
				for( $i=2 ; $i<$cnt ; $i++ ) {
					if( !Utils::isIp($tmp[$i]) && $container->get('domain')->sameProject($entity->getDomain(),$tmp[$i]) ) {
						// redirection to a host in the same project
						$t_host[] = $tmp[$i];
					}
				}
				if( $tmp[$cnt-1] == $entity->getName() ) {
					// should be the same
					$http = 'OK';
				}
			}
			
			if( $http == 'OK' ) {
				$t_options = ['PORT'=>$port];
				if( $port == 443 ) {
					$t_options['SSL'] = 's';
				}

				if( isset($t_options['PORT']) ) {
					if( $t_options['PORT'] == 80 || $t_options['PORT'] == 443 ) {
						unset( $t_options['PORT'] );
					} else {
						$t_options['PORT'] = ':'.$t_options['PORT'];
					}
				}

				$flag = true;
				$container->get('entity_task')->create( $entity, 'whatweb', $t_options );
				$container->get('entity_task')->create( $task->getEntity(), 'wappalyzer', $t_options );
				//$container->get('entity_task')->create( $task->getEntity(), 'testcrlf', $t_options );
				//$container->get('entity_task')->create( $task->getEntity(), 'testcors', $t_options );
				$container->get('entity_task')->create( $task->getEntity(), 'dirb_myhardw', $t_options, null, -2 );
				//$container->get('entity_task')->create( $task->getEntity(), 'dirb_forbidden', $t_options );
				$container->get('entity_task')->create( $task->getEntity(), 'httpscreenshot', $t_options );
				$container->get('entity_task')->create( $task->getEntity(), 'urlgrabber', $t_options );
				$container->get('entity_task')->create( $task->getEntity(), 'urlgrabber_wget', $t_options, null, -1 );
				//$container->get('entity_task')->create( $task->getEntity(), 'open_redirect', $t_options );
				//$container->get('entity_task')->create( $task->getEntity(), 'nikto', $t_options, null, -1 );
				//$container->get('entity_task')->create( $task->getEntity(), 'dirb', $t_options, null, -1 );
				//$container->get('entity_task')->create( $task->getEntity(), 'dirsearch', $t_options, null, -1 );
				//$container->get('entity_task')->create( $task->getEntity(), 'act_wfuzz', $t_options, null, -1 );
			}
		}

		if( !$flag && $entity->getEntityType() == ArusHost::ENTITY_TYPE_ID ) {
			$entity->setStatus( $t_status['ko'] );
			$this->em->persist( $entity );
		}

		if( count($t_host) ) {
			// add hosts found
			$t_host = array_unique( $t_host );
			$cnt = $container->get('host')->import( $entity->getProject(), $t_host );
		}

		return $cnt;
	}


	/**
	 * Add discovered hosts
	 */
	private function theharvester( $task )
	{
		$em = $this->em;
		$container = $this->container;
		$domain = $task->getEntity();
		$domain_name = $domain->getName();
		$output = array_map( 'trim', explode("\n",$task->getOutput()) );
		$t_host = [];

		foreach( $output as $l ) {
			$l = str_replace( '<<', '<', $l );
			$l = strip_tags( $l );
			if( preg_match('#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+[:|\s]+(.*)#',$l,$m) ) {
				if( Utils::isSubdomain($m[1]) && Utils::extractDomain($m[1]) == $domain_name ) {
					$t_host[] = trim( $m[1] );
				}
			}
		}

		$t_host = array_unique( $t_host );
		$cnt = $container->get('host')->import( $domain->getProject(), $t_host );

		if( $cnt ) {
			$t_alert_level = $container->getParameter('alert')['level'];
			$container->get('entity_alert')->create( $domain, $cnt.' new host added.', $t_alert_level['info'], $task );
		}

		return $cnt;
	}


	private function wappalyzer( $task )
	{
		$o = trim( $task->getOutput() );
		$container = $this->container;

		if( stristr($o,'cURL error') || stristr($o,'PHP Notice:') || $o == '' ) {
			return false;
		}

		//$cnt = $this->wappalyzer_php( $task );
		$cnt = $this->wappalyzer_ruby( $task );

		if( stristr($task->getOutput(),'wordpress') !== false ) {
			$t_options = [];
			$tmp = explode( ' ', $task->getCommand() );
			$parse_url = parse_url( $tmp[1] );
			if( $parse_url['scheme'] == 'https' ) {
				$t_options['SSL'] = 's';
				$t_options['PORT'] = '443';
			}
			if( isset($parse_url['port']) ) {
				$t_options['PORT'] = $parse_url['port'];
			}

			if( isset($t_options['PORT']) ) {
				if( $t_options['PORT'] == 80 || $t_options['PORT'] == 443 ) {
					unset( $t_options['PORT'] );
				} else {
					$t_options['PORT'] = ':'.$t_options['PORT'];
				}
			}

			$r = $container->get('entity_task')->create( $task->getEntity(), 'wpscan', $t_options );
		}

		return $cnt;
	}
	private function wappalyzer_ruby( $task )
	{
		$em = $this->em;
		$container = $this->container;
		$t_techno = json_decode( trim($task->getOutput()), true );
		$entity = $task->getEntity();
		$cnt = 0;

		foreach( $t_techno as $k=>$t ) {
			$techno = $em->getRepository('ArusTechnologyBundle:ArusTechnology')->findOneByName( $k );
			if( $techno ) {
				$r = $container->get('entity_technology')->create( $techno, $entity, trim($t['version']) );
				if( $r ) {
					$cnt++;
				}
			}
		}

		return $cnt;
	}
	private function wappalyzer_php( $task )
	{
		$em = $this->em;
		$container = $this->container;
		$t_techno = array_map( 'trim', explode("\n",$task->getOutput()) );
		$entity = $task->getEntity();
		$cnt = 0;

		foreach( $t_techno as $l ) {
			$t = explode( ',', $l );
			$techno = $em->getRepository('ArusTechnologyBundle:ArusTechnology')->findOneByName( $t[0] );
			if( $techno ) {
				$r = $container->get('entity_technology')->create( $techno, $entity, trim($t['1']) );
				if( $r ) {
					$cnt++;
				}
			}
		}

		return $cnt;
	}


	/**
	 * Manage https
	 */
	private function whatweb( $task )
	{
		/*$container = $this->container;
		$o = preg_replace( '#[[:blank:]]#', '', $task->getOutput() );
		$entity = $task->getEntity();

		if( !strlen($o) || strstr($o,'ERROR') ) {
			if( !stristr($task->getCommand(),'https://') ) {
				$container->get('entity_task')->create($task->getEntity(), 'whatweb', ['SSL' => 's']);
			}
		} else {
			$options = [];
			if( stristr($task->getCommand(),'https://') ) {
				$options['SSL'] = 's';
			}
			$container->get('entity_task')->create( $task->getEntity(), 'wappalyzer', $options );
			$container->get('entity_task')->create( $task->getEntity(), 'nikto', $options );
			$container->get('entity_task')->create( $task->getEntity(), 'dirb', $options );
		}
		*/
		return true;
	}
	
	
	private function act_wfuzz( $task )
	{
		$cnt = 0;
		$em = $this->em;
		$container = $this->container;
		$output = trim( $task->getOutput() );
		list($tmp, $origin, $tmp) = explode(  ' ', $task->getCommand() );
		//var_dump($origin);

		$t = $em->getRepository('ArusTaskBundle:ArusTask')->findOneByName( 'dirb' );
		$t_callback = $em->getRepository('ArusTaskCallbackBundle:ArusTaskCallback')->findByTask( $t, ['priority'=>'asc'] );

		foreach( $t_callback as $c )
		{
			$cc = clone $c;
			
			if( ($a=preg_match('#[0-9]+:[\s]+C=([0-9]+)[\s]+([0-9]+) L[\s]+([0-9]+) W[\s]+([0-9]+) Ch[\s]+"('.$cc->getRegex().')"#i',$output,$match)) )
			{
				//var_dump( $match );
				$code   = (int)$match[1];
				$size   = (int)$match[2];
				$words  = (int)$match[3];
				$chars  = (int)$match[4];
				$term   = trim( $match[5] );

				if( $code < 200 || $code > 299 || !$size ) {
					continue;
				}
				
				$cnt++;
				$term = trim( $match[5] );
				$url = $origin . '/' . $term;
				$t_params = $cc->getParams();
	
				if (isset($t_params['text']) && trim($t_params['text']) != '') {
					$text = $t_params['text'];
					$text = str_replace('__URL__', $url, $text);
					$text = str_replace('__TERM__', $term, $text);
					$text = str_replace('__CODE__', $code, $text);
					$text = str_replace('__SIZE__', $size, $text);
					$t_params['text'] = $text;
					$cc->setParams($t_params);
				}
	
				$f = $cc->getAction();
				if (is_callable([$this, $f])) {
					$this->$f($task, $cc);
				}
			}
		}
		
		return $cnt;
	}
	
	
	private function urlgrabber( $task )
	{
		$em = $this->em;
		$container = $this->container;
		$entity = $task->getEntity();
		$project = $entity->getProject();
		$output = explode( "\n", $task->getOutput() );
		$t_urls = [];
		
		foreach( $output as $line )
		{
			$line = trim( $line );
			if( $line == '' ) {
				continue;
			}
			
			$t_urls[] = $line;
		}
		
		$cnt = 0;
		if( count($t_urls) ) {
			$cnt = $container->get('arequest')->import( $project, $t_urls, 'array', true );
		}
		
		return $cnt;
	}
	
	
	private function urlgrabber_wget( $task )
	{
		$em = $this->em;
		$container = $this->container;
		$entity = $task->getEntity();
		
		$t_options = [];
		$t_options['DIR'] = '/tmp/'.$entity->getName();
		$new_task = $container->get('entity_task')->create( $entity, 'extract_datas', $t_options, null, 0, $task->getClusterId() );
		
		return true;
	}
	
	
	private function extract_datas( $task )
	{
		$em = $this->em;
		$container = $this->container;
		$entity = $task->getEntity();
		$project = $entity->getProject();
		$output = trim( $task->getOutput() );
		$t_alert_level = $container->getParameter('alert')['level'];
		
		//var_dump( $output );
		$m = preg_match_all( '|###########.*?######################|is', $output, $matches );
		//var_dump( $matches );
		
		// add new domains with the same extension
		$t_new_domains = [];
		$t_output = array_slice( explode("\n",$matches[0][0]), 1, -1 );
		//var_dump( $t_new_domains );
		foreach( $t_output as $d ) {
			if( stristr($d,'PHP Warning') ) {
				continue;
			}
			$t_new_domains[] = Utils::extractDomain( $d );
		}
		//var_dump( $t_new_domains );
		if( count($t_new_domains) ) {
			$cnt1 = $container->get('domain')->import( $project, $t_new_domains, true );
		}
		
		// add new hosts within the same domain, yes yes redondant
		$t_new_hosts = [];
		$t_output = array_slice( explode("\n",$matches[0][1]), 1, -1 );
		//var_dump( $t_new_domains );
		foreach( $t_output as $d ) {
			if( stristr($d,'PHP Warning') ) {
				continue;
			}
			$t_new_hosts[] = $d;
		}
		//var_dump( $t_new_hosts );
		if( count($t_new_hosts) ) {
			$cnt2 = $container->get('host')->import( $project, $t_new_hosts, true );
		}
		
		// add new urls within the same host
		$t_new_urls = [];
		$t_output = array_slice( explode("\n",$matches[0][2]), 1, -1 );
		//var_dump( $t_new_domains );
		foreach( $t_output as $d ) {
			if( stristr($d,'PHP Warning') ) {
				continue;
			}
			$t_new_urls[] = $d;
		}
		//var_dump( $t_new_urls );
		if( count($t_new_urls) ) {
			$cnt3 = $container->get('arequest')->import( $project, $t_new_urls, 'array', true );
		}

		// add new urls within the same host, yes yes this normal!
		$t_new_urls = [];
		$t_output = array_slice( explode("\n",$matches[0][3]), 1, -1 );
		//var_dump( $t_new_domains );
		foreach( $t_output as $d ) {
			if( stristr($d,'PHP Warning') ) {
				continue;
			}
			$t_new_urls[] = $d;
		}
		//var_dump( $t_new_urls );
		if( count($t_new_urls) ) {
			$cnt3 = $container->get('arequest')->import( $project, $t_new_urls, 'array', true );
		}

		// amazon s3 buckets
		$t_output = array_slice( explode("\n",$matches[0][4]), 1, -1 );
		$t_bucket_aws = array_unique( $t_output );
		$cnt4 = count( $t_bucket_aws );
		if( $cnt4 ) {
			$t_links = [];
			foreach( $t_bucket_aws as $b ) {
				if( stristr($b,'PHP Warning') ) {
					continue;
				}
				$t_links[] = '<a href="https://'.$b.'.s3.amazonaws.com" target="_blank">'.$b.'</a>';
			}
			if( count($t_links) ) {
				$container->get('entity_alert')->create( $project, 'Amazon S3 buckets found: '.implode(', ',$t_links).'.', $t_alert_level['low'], $task );
			}
		}
		
		// google cloud buckets
		$t_output = array_slice( explode("\n",$matches[0][5]), 1, -1 );
		$t_bucket_gc = array_unique( $t_output );
		$cnt5 = count( $t_bucket_gc );
		if( $cnt5 ) {
			$t_links = [];
			foreach( $t_bucket_gc as $b ) {
				if( stristr($b,'PHP Warning') ) {
					continue;
				}
				$t_links[] = '<a href="https://'.$b.'.storage.googleapis.com" target="_blank">'.$b.'</a>';
			}
			if( count($t_links) ) {
				$container->get('entity_alert')->create( $project, 'Google Cloud buckets found: '.implode(', ',$t_links).'.', $t_alert_level['low'], $task );
			}
		}
		
		return $cnt1+$cnt2+$cnt3+$cnt4+$cnt5;
	}
}
