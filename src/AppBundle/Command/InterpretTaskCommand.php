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

use ArusEntityAlertBundle\Entity\ArusEntityAlert;
use ArusEntityCommentBundle\Entity\ArusEntityComment;
use ArusEntityTaskBundle\Entity\ArusEntityTask;
use ArusEntityTechnologyBundle\Entity\ArusEntityTechnology;
use ArusHostBundle\Entity\ArusHost;

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

        $t_status = array_flip( $container->getParameter('entity')['status'] );
        //$entity->setStatus( $t_status['todo'] );
        $em->persist( $entity );

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
		$container->get('entity_alert')->create( $task->getEntity(), $t_params['text'], $t_params['alert_level'] );

		return true;
	}


	/**
	 * Add a comment to an entity
	 *
	 * @param ArusEntityTask $task
	 * @param ArusTaskCallback $callback
	 * @return bool true
	 */
	private function addComment( $task, $callback )
	{
		$t_params = $callback->getParams();
		$container = $this->container;
		$container->get('entity_comment')->create( $task->getEntity(), $t_params['text'] );

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
			$container->get('entity_alert')->create($task->getEntity(), 'Directory listing found: ' . implode(', ', $t_dir) . '.', $t_alert_level['medium']);
		}

		return -1;
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
				$container->get('entity_alert')->create( $domain, $cnt.' new host added.', $t_alert_level['info'] );
			}
		}

		return $cnt;
	}


	/**
	 * Add discovered hosts
	 */
	private function host( $task )
	{
		$cnt = 0;
		$is_alias = 0;
		$t_host = [];
		$t_server = [];
		$t_link = [];

		$container = $this->container;
		$output = array_map( 'trim', explode("\n",$task->getOutput()) );
		$entity = $task->getEntity();
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
					} else {
						// external alias
						$is_alias = -1;
					}
				}
			} elseif( preg_match('#(.*) has address (.*)#', $l, $m) ) {
				if( $container->get('domain')->sameProject($domain,$m[1]) ) {
                    $t_host[] = $m[1];
					$t_server[] = $m[2];
					$t_link[] = [ 'host'=>$m[1], 'server'=>$m[2] ];
				}
			} elseif( preg_match('#(.*) mail is handled by [0-9]+ (.*)#', $l, $m) ) {
				if( $container->get('domain')->sameProject($domain,$m[1]) ) {
                    $t_host[] = $m[1];
				}
				if( $container->get('domain')->sameProject($domain,$m[2]) ) {
                    $t_host[] = $m[2];
				}
			}
		}

//		var_dump($t_server);
//		var_dump($t_link);
//		var_dump($is_alias);
//		exit();

		if( count($t_host) ) {
			$t_host = array_diff( $t_host, [$entity->getName()] );
		}
		if( count($t_host) ) {
			// add hosts found
			$t_host = array_unique( $t_host );
			$cnt = $container->get('host')->import( $domain->getProject(), $t_host );
			if( $cnt ) {
				$t_alert_level = $container->getParameter('alert')['level'];
				$container->get('entity_alert')->create( $domain, $cnt.' new host added.', $t_alert_level['info'] );
			}
		}

		if( count($t_server) ) {
			// add servers found
			$t_server = array_unique( $t_server );
			$cnt += $container->get('server')->import( $domain->getProject(), $t_server );
		}

		if( count($t_link) ) {
			// add host/server links
			$container->get('host_server')->import( $t_link );
		}

		if( $is_alias == 0 ) {
			// the host is not an alias
			$container->get('entity_task')->create( $task->getEntity(), 'testhttp' );
		} else if( $is_alias < 0 ) {
			// the host is an alias of a domain not in the project, third party there
			$t_alert_level = $container->getParameter('alert')['level'];
			$container->get('entity_alert')->create( $task->getEntity(), 'This host is an alias, check all domains in the chain for possible takeover.', $t_alert_level['info'] );
			$container->get('entity_task')->create( $task->getEntity(), 'dnsexpire' );
		} else {
			// the host is an alias of a domain of the same project
			// nothing
		}

		return $cnt;
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
		$m = preg_match_all( '#([0-9]+)/tcp[\s]+open[\s]+([^\s]*)(.*)\n#', $output, $match );

		if( $m )
		{
			$i = 0;
			$t_port = [];
			$match[1] = array_map( 'trim', $match[1] ); // port
			$match[2] = array_map( 'trim', $match[2] ); // service
			$match[3] = array_map( 'trim', $match[3] ); // version

			foreach( $match[1] as $port )
			{
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

				if( !is_null($task) ) {
					$t = $container->get('entity_task')->create( $entity, $new_task, ['PORT'=>$port] );
				}

				$txt = $port;
				if( $match[2][$i] != '' ) {
					$txt .= ' ('.$match[2][$i].')';
				}
				$t_port[] = $txt;

				$i++;
			}

			$t = $container->get('entity_task')->create( $entity, 'testhttp', ['PORT'=>implode(',',$match[1])] );
			$t_alert_level = $container->getParameter('alert')['level'];
			$container->get('entity_alert')->create( $task->getEntity(), 'Open ports are: '.implode(', ',$t_port).'.', $t_alert_level['info'] );
		}

		return $m;
	}


	private function portscan_nc( $task )
	{
		$output = $task->getOutput();

		if( stristr($output,'succeeded') ) {
			$m = preg_match_all( '#Connection to .* ([0-9]+) port \[tcp/\*\] succeeded!#i', $output, $match );
		} else {
			$m = preg_match_all( '#\(UNKNOWN\) \[.*\] ([0-9]+) \(.*\) open#i', $output, $match );
		}

		if( $m ) {
			sort( $match[1], SORT_NUMERIC );
			$this->container->get('entity_task')->create( $task->getEntity(), 'nmap_custom', ['PORT'=>implode(',',$match[1])] );
		} else {
			$server = $task->getEntity();
			$server->setStatus( 2 ); // ko
			$this->em->persist( $server );
		}

		return $m;
	}


	private function altbucket( $task )
	{
		return $this->s3_buckets( $task );
	}
	private function s3_buckets( $task )
	{
		$b_name = null;
		$t_buckets = [];
		$t_vulnerable = [];
		$project = $task->getEntity();
		$container = $this->container;
		$output = array_map( 'trim', explode("\n",$task->getOutput()) );

		foreach( $output as $l )
		{
			$l = trim( $l );
			
			if( $l!='' && !strstr($l,'success') && !strstr($l,'failed') ) {
				$b_name = trim( $l );
				$t_buckets[] = $b_name;
			}

			if( strstr($l,'success') ) {
				$link = '<a href="https://'.$b_name.'.s3.amazonaws.com" target="_blank">'.$b_name.'</a>';
				$t_vulnerable[] = $link;
			}
		}

		$cnt = count( $t_buckets );
		
		if( $cnt )
		{
			$container->get('bucket')->import( $project, $t_buckets );
			
			$cnt_vuln = count( $t_vulnerable );
			if( $cnt_vuln ) {
				$t_alert_level = $container->getParameter('alert')['level'];
				$container->get('entity_alert')->create( $project, 'S3 buckets seems to be misconfigured: '.implode(', ',$t_vulnerable).'.', $t_alert_level['high'] );
			}
			
			$altbucket = $this->container->get('entity_task')->search( ['project'=>$project,'command'=>'altbucket'] );
			if( !$altbucket ) {
				$altbucket = $this->container->get('entity_task')->create( $project, 'altbucket' );
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

		$cnt = $container->get('host')->import( $domain->getProject(), $t_host );

		if( $cnt ) {
			$t_alert_level = $container->getParameter('alert')['level'];
			$container->get('entity_alert')->create($domain, $cnt.' new host added.', $t_alert_level['info']);
		}

		return $cnt;
	}


	private function altdns( $task )
	{
        return $this->subthreat( $task );
	}
	private function crtsh( $task )
	{
        return $this->subthreat( $task );
	}
	private function subthreat( $task )
	{
		$output = $task->getOutput();
		if( strstr($output,'Error:') ) {
			return false;
		}

		$t_host = [];
		$tmp_host = array_map( 'trim', explode("\n",$output) );
		$container = $this->container;
		$domain = $task->getEntity();
		$domain_name = $domain->getName();

		foreach( $tmp_host as $h ) {
			if( Utils::extractDomain($h) == $domain_name ) {
				$t_host[] = $h;
			}
		}

		$cnt = $container->get('host')->import( $domain->getProject(), $t_host );

		if( $cnt ) {
			$t_alert_level = $container->getParameter('alert')['level'];
			$container->get('entity_alert')->create($domain, $cnt.' new host added.', $t_alert_level['info']);
		}

		return $cnt;
	}


	private function testhttp( $task )
	{
		$t_host = [];

		$flag = false;
		$output = array_map( 'trim', explode("\n",$task->getOutput()) );
		$container = $this->container;
		$entity = $task->getEntity();

		foreach( $output as $l )
		{
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

				$flag = true;
				//$container->get('entity_task')->create( $entity, 'whatweb', $t_options );
				$container->get('entity_task')->create( $task->getEntity(), 'wappalyzer', $t_options );
				$container->get('entity_task')->create( $task->getEntity(), 'testcrlf', $t_options );
				$container->get('entity_task')->create( $task->getEntity(), 'testcors', $t_options );
				$container->get('entity_task')->create( $task->getEntity(), 'dirb_myhardw', $t_options );
				//$container->get('entity_task')->create( $task->getEntity(), 'open_redirect', $t_options );
				//$container->get('entity_task')->create( $task->getEntity(), 'nikto', $t_options );
				//$container->get('entity_task')->create( $task->getEntity(), 'dirb', $t_options );
			}
		}

		if( !$flag && $entity->getEntityType() == ArusHost::ENTITY_TYPE_ID ) {
			$entity->setStatus( 2 ); // ko
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
			$container->get('entity_alert')->create($domain, $cnt.' new host added.', $t_alert_level['info']);
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
}
