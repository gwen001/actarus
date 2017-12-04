<?php

namespace Actarus;

use Doctrine\Common\Util\Inflector as Inflector;


class Utils
{
	const T_TLD_1 = ['.abogado','.ac','.academy','.accountant','.accountants','.active','.actor','.ad','.adult','.ae','.aero','.af','.ag','.agency','.ai','.airforce','.al','.allfinanz','.alsace','.am','.amsterdam','.an','.android','.ao','.apartments','.app','.aq','.aquarelle','.ar','.archi','.army','.as','.asia','.associates','.at','.attorney','.au','.auction','.audio','.autos','.aw','.ax','.axa','.az','.ba','.band','.bank','.bar','.barcelona','.barclaycard','.barclays','.bargains','.bayern','.bb','.bd','.be','.beer','.berlin','.best','.bf','.bg','.bh','.bi','.bid','.bike','.bingo','.bio','.biz','.bj','.black','.blackfriday','.blog','.bloomberg','.blue','.bm','.bmw','.bn','.bnl','.bnpparibas','.bo','.boo','.boutique','.bq','.br','.brussels','.bs','.bt','.budapest','.build','.builders','.business','.buzz','.bv','.bw','.by','.bz','.bzh','.ca','.cab','.cafe','.cal','.camera','.camp','.cancerresearch','.capetown','.capital','.caravan','.cards','.care','.career','.careers','.casa','.cash','.casino','.cat','.catering','.cc','.cd','.center','.ceo','.cern','.cf','.cg','.ch','.channel','.chat','.cheap','.christmas','.chrome','.church','.ci','.citic','.city','.ck','.cl','.claims','.cleaning','.click','.clinic','.clothing','.cloud','.club','.cm','.cn','.co','.coach','.codes','.coffee','.college','.cologne','.com','.community','.company','.computer','.condos','.construction','.consulting','.contractors','.cooking','.cool','.coop','.corsica','.country','.coupons','.cr','.credit','.creditcard','.cricket','.crs','.cruises','.cu','.cuisinella','.cv','.cw','.cx','.cy','.cymru','.cz','.dad','.dance','.date','.dating','.day','.de','.deals','.degree','.delivery','.democrat','.dental','.dentist','.desi','.design','.diamonds','.diet','.digital','.direct','.directory','.discount','.dj','.dk','.dm','.dnp','.do','.dog','.domains','.download','.durban','.dvag','.dz','.eat','.ec','.edu','.education','.ee','.eg','.eh','.email','.emerck','.energy','.engineer','.engineering','.enterprises','.equipment','.er','.es','.esq','.estate','.et','.eu','.eus','.events','.everbank','.exchange','.expert','.exposed','.express','.fail','.faith','.family','.fans','.farm','.fashion','.feedback','.fi','.finance','.financial','.firmdale','.fish','.fishing','.fit','.fitness','.fj','.fk','.flights','.florist','.flowers','.flsmidth','.fly','.fm','.fo','.foo','.football','.forsale','.foundation','.fr','.frl','.frogans','.fund','.furniture','.futbol','.fyi','.ga','.gal','.gallery','.garden','.gb','.gbiz','.gd','.ge','.gent','.gf','.gg','.gh','.gi','.gift','.gifts','.gives','.gl','.glass','.gle','.global','.globo','.gm','.gmail','.gmo','.gmx','.gn','.gold','.golf','.google','.gop','.gov','.gp','.gq','.gr','.graphics','.gratis','.green','.gripe','.gs','.gt','.gu','.guide','.guitars','.guru','.gw','.gy','.hamburg','.haus','.healthcare','.help','.here','.hiphop','.hiv','.hk','.hm','.hn','.hockey','.holdings','.holiday','.homes','.horse','.host','.hosting','.house','.how','.hr','.hsbc','.ht','.hu','.ibm','.id','.ie','.il','.im','.immo','.immobilien','.in','.industries','.info','.ing','.ink','.institute','.insure','.int','.international','.investments','.io','.iq','.ir','.irish','.is','.ist','.istanbul','.it','.je','.jetzt','.jewelry','.jm','.jo','.jobs','.joburg','.jp','.juegos','.kaufen','.ke','.kg','.kh','.ki','.kim','.kitchen','.kiwi','.km','.kn','.koeln','.kp','.kr','.krd','.kred','.kw','.ky','.kz','.la','.lacaixa','.land','.lat','.latrobe','.lawyer','.lb','.lc','.lds','.lease','.legal','.lgbt','.li','.life','.lighting','.limited','.limo','.link','.lk','.loan','.loans','.lol','.london','.lotto','.love','.lr','.ls','.lt','.ltda','.lu','.luxe','.luxury','.lv','.ly','.ma','.madrid','.maison','.management','.mango','.market','.marketing','.markets','.mba','.mc','.md','.me','.media','.meet','.melbourne','.meme','.memorial','.men','.menu','.mg','.mh','.miami','.mil','.mini','.mk','.ml','.mm','.mn','.mo','.mobi','.moda','.moe','.monash','.money','.mormon','.mortgage','.moscow','.motorcycles','.mov','.movie','.mp','.mq','.mr','.ms','.mt','.mu','.museum','.mv','.mw','.mx','.my','.mz','.na','.nagoya','.name','.navy','.nc','.ne','.net','.network','.neustar','.new','.news','.nexus','.nf','.ng','.ngo','.nhk','.ni','.nico','.ninja','.nl','.no','.np','.nr','.nra','.nrw','.nu','.nyc','.nz','.okinawa','.om','.one','.ong','.onl','.online','.ooo','.org','.organic','.otsuka','.ovh','.oz','.pa','.paris','.partners','.parts','.party','.pe','.pf','.pg','.ph','.pharmacy','.photo','.photography','.photos','.physio','.piaget','.pics','.pictures','.pid','.pink','.pizza','.pk','.pl','.place','.plumbing','.plus','.pm','.pn','.pohl','.poker','.porn','.post','.pr','.praxi','.press','.pro','.prod','.productions','.prof','.properties','.property','.ps','.pt','.pub','.pw','.pwc','.py','.qa','.qpon','.quebec','.racing','.re','.realtor','.recipes','.red','.rehab','.reise','.reisen','.reit','.ren','.rent','.rentals','.repair','.report','.republican','.rest','.restaurant','.review','.reviews','.rich','.rio','.rip','.ro','.rocks','.rodeo','.rs','.rsvp','.ru','.ruhr','.run','.rw','.ryukyu','.sa','.saarland','.sale','.sandvikcoromant','.sarl','.sb','.sc','.sca','.scb','.schmidt','.school','.schule','.science','.scot','.sd','.se','.seek','.services','.sex','.sexy','.sg','.sh','.shiksha','.shoes','.show','.si','.singles','.site','.sj','.sk','.sl','.sm','.sn','.so','.soccer','.social','.software','.sohu','.solar','.solutions','.soy','.space','.spiegel','.sr','.ss','.st','.store','.studio','.style','.su','.sucks','.supplies','.supply','.support','.surf','.surgery','.suzuki','.sv','.sx','.sy','.sydney','.systems','.sz','.taipei','.tatar','.tattoo','.tax','.taxi','.tc','.td','.team','.tech','.technology','.tel','.tennis','.tf','.tg','.th','.theater','.tienda','.tips','.tires','.tirol','.tj','.tk','.tl','.tm','.tn','.to','.today','.tokyo','.tools','.top','.tours','.town','.toys','.tp','.tr','.trade','.training','.travel','.tt','.tui','.tv','.tw','.tz','.ua','.ug','.uk','.university','.uno','.uol','.us','.uy','.uz','.va','.vacations','.vc','.ve','.vegas','.ventures','.versicherung','.vet','.vg','.vi','.viajes','.video','.villas','.vision','.vlaanderen','.vn','.vodka','.vote','.voting','.voto','.voyage','.vu','.wales','.wang','.watch','.webcam','.website','.wed','.wedding','.wf','.whoswho','.wien','.wiki','.williamhill','.win','.wine','.wme','.work','.works','.world','.ws','.wtc','.wtf','.xxx','.xyz','.yandex','.ye','.yoga','.yokohama','.youtube','.yt','.yu','.za','.zm','.zone','.zuerich','.zw'];
	const T_TLD_2 = ['.ac.at','.ac.il','.ac.nz','.ac.ru','.act.au','.ac.uk','.ac.yu','.ac.za','.aeroport.fr','.alberta.ca','.asn.au','.asso.fr','.avocat.fr','.bc.ca','.cg.yu','.co.at','.co.hu','.co.il','.com.au','.com.br','.com.fr','.com.ru','.com.sg','.com.tr','.conf.au','.co.in','.co.nl','.co.nz','.co.tv','.co.uk','.co.yu','.cri.nz','.csiro.au','.edu.au','.edu.tr','.film.hu','.geek.nz','.gen.nz','.gob.es','.gov.au','.gov.il','.govt.nz','.gov.uk','.gov.za','.gv.at','.gw.au','.health.nz','.hotel.hu','.id.au','.idf.il','.info.au','.ingatlan.hu','.int.ru','.irkutsk.ru','.iwi.nz','.k12.il','.kiwi.nz','.lakas.hu','.law.za','.ltd.uk','.maori.nz','.mb.ca','.me.uk','.mil.nz','.mil.za','.mod.uk','.msk.ru','.muni.il','.name.tr','.nb.ca','.net.au','.net.il','.net.nz','.net.uk','.net.za','.nf.ca','.nhs.uk','.nic.tr','.nl.ca','.nom.za','.ns.ca','.nsw.au','.nt.au','.nt.ca','.nu.ca','.on.ca','.or.at','.org.au','.org.es','.org.il','.org.nz','.org.uk','.org.yu','.otc.au','.oz.au','.parliament.nz','.pe.ca','.plc.uk','.police.uk','.priv.at','.qc.ca','.qld.au','.sa.au','.school.nz','.school.za','.sch.uk','.sk.ca','.sport.hu','.tas.au','.telememo.au','.tm.fr','.veterinaire.fr','.vic.au','.volgograd.ru','.wa.au','.yk.ca'];

	
	public static function isCli()
	{
		return (php_sapi_name() === 'cli');
	}


	public static function dateFR2Time( $date )
	{
		list( $day, $month, $year ) = explode( '/', $date );
		$timestamp = mktime( 0, 0, 0, $month, $day, $year );
		return $timestamp;
	}


	public static function stripAccents( $str, $charset='utf-8' )
	{
		$str = htmlentities( $str, ENT_NOQUOTES, $charset );

		$str = preg_replace( '#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str );
		$str = preg_replace( '#&([A-za-z]{2})(?:lig);#', '\1', $str ); // pour les ligatures e.g. '&oelig;'
		$str = preg_replace( '#&[^;]+;#', '', $str ); // supprime les autres caractères

		return $str;
	}


	public static function urlize( $str )
	{
		$str = str_replace( (array)array("'",'"'), ' ', $str );

		$clean = self::stripAccents( $str );
		$clean = iconv( 'UTF-8', 'ASCII//TRANSLIT', $clean );
		$clean = preg_replace( "/[^a-zA-Z0-9\/_|+ -\.]/", '', $clean );
		$clean = strtolower( trim($clean,'-') );
		$clean = preg_replace( "/[\/_|+ -\.]+/", '-', $clean );

		return $clean;
	}


	public static function isIp( $str )
	{
		return preg_match( '#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z#', trim($str) );
	}


	public static function isDomain( $str )
	{
		$str = strtolower( $str );

		if( preg_match('/[^0-9a-z_\-\.]/',$str) || preg_match('/[^0-9a-z]/',$str[0]) || preg_match('/[^a-z]/',$str[strlen($str)-1]) || substr_count($str,'.')>2 || substr_count($str,'.')<=0 ) {
			return false;
		} else {
			return true;
		}
	}


	public static function isSubdomain( $str )
	{
		$str = strtolower( $str );

		if( preg_match('/[^0-9a-z_\-\.]/',$str) || preg_match('/[^0-9a-z]/',$str[0]) || preg_match('/[^a-z]/',$str[strlen($str)-1]) || substr_count($str,'.')<2 ) {
			return false;
		} else {
			return true;
		}
	}


	public static function cleanOutput( $str )
	{
		$str = preg_replace( '#\[[0-9;]{1,4}m#', '', $str );

		return $str;
	}


	public static function isAjax()
	{
		if( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ) {
			return true;
		} else {
			return false;
		}
	}

	public static function array2object( $data, $class )
	{
		if( is_array($data) && !is_object($data) ) {
			$o = new $class;
			foreach( $data as $k=>$v ) {
				$f = Inflector::camelize('set_'.$k);
				if( is_callable([$o,$f]) ) {
					$o->$f( $v );
				}
			}
			$data = $o;
		}

		return $data;
	}

	/*
	public static function extractDomain( $host )
	{
		$tmp = explode( '.', $host );
		$cnt = count( $tmp );

		$domain = $tmp[$cnt-1];

		for( $i=$cnt-2 ; $i>=0 ; $i-- ) {
			$domain = $tmp[$i].'.'.$domain;
			if( strlen($tmp[$i]) > 3 ) {
				break;
			}
		}

		return $domain;
	}
	*/
	
	public static function extractDomain( $host )
	{
		$t_host = explode( '.', strtolower($host) );
		//var_dump( $t_host );
		$cnt = count($t_host) - 1;
		//var_dump( $cnt );
		
		if( in_array('.'.$t_host[$cnt-1].'.'.$t_host[$cnt],self::T_TLD_2) ) {
		  $domain = $t_host[$cnt-2].'.'.$t_host[$cnt-1].'.'.$t_host[$cnt];
		} else if( in_array('.'.$t_host[$cnt],self::T_TLD_1) ) {
		  $domain = $t_host[$cnt-1].'.'.$t_host[$cnt];
		 } else {
		  $domain = false;
		 }
		
		//var_dump( $domain );
		return $domain;
	}


	public function getScore( $t_alerts )
	{
		$score = [];

		for( $i=-1 ; $i<=3 ; $i++ ) {
			$score[$i] = 0;
		}

		foreach( $t_alerts as $a ) {
			if( $a->getStatus() == 0 || $a->getStatus() == 1 ) {
				$score[ $a->getLevel() ]++;
				$s = $a->getLevel() * 10;
				if( $a->getStatus() == 0 ) {
					$s = $s/2;
				}
				$score[-1] += $s;
			}
		}

		return $score;
	}


	public function getMaxAlertLevel( $t_alerts )
	{
		$max = [-1,-1];

		foreach( $t_alerts as $a ) {
			if( $a->getStatus() == 0 || $a->getStatus() == 1 ) {
				$s = $a->getLevel();
				if( $s >= $max[0] ) {
					$max[0] = $s;
					if( $a->getStatus() > $max[1] ) {
						$max[1] = $a->getStatus();
					}
				}
			}
		}

		return $max;
	}


	public function isInt( $str )
	{
		return !preg_match( '#[^0-9]#', $str );
	}
	
	
	public function killProcess( $pid )
	{
		$ps = 'pstree -ap -n '.$pid;
		exec( $ps, $output );
		//var_dump( $output );
		
		if( !count($output) ) {
			return false;
		}
		
		$to_kill = [];
		
		foreach( $output as $k=>$line ) {
			$tmp = explode( ',', $line );
			$tmp2 = explode( ' ', $tmp[1] );
			$to_kill[] = preg_replace( '#[^0-9]#', '', $tmp2[0]);
		}
		
		$cmd = 'kill -9 '.implode( ' ', $to_kill ).' 2>/dev/null';
		//echo $cmd."\n";
		//exit();
		exec( $cmd );
		
		return true;
	}
}
