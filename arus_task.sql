
INSERT INTO `arus_task` (`id`, `name`, `command`, `default_options`, `created_at`, `updated_at`) VALUES
(1, 'nmap_full', 'nmap -A -T4 -sT -p 1-65535 --open __E_NAME__ -Pn', 'N;', '2016-08-01 22:28:44', '2016-09-02 19:54:32'),
(2, 'host', 'host __E_NAME__', 'N;', '2016-08-01 22:28:44', '2016-09-02 19:54:11'),
(3, 'whois', 'whois __E_NAME__', 'N;', '2016-08-01 22:28:44', '2016-09-02 19:55:59'),
(4, 'nikto', 'nikto -h http__O_SSL__://__E_NAME__:__O_PORT__', 'a:1:{s:4:"PORT";i:80;}', '2016-08-01 22:28:44', '2016-09-04 22:43:13'),
(5, 'whatweb', 'whatweb -v http__O_SSL__://__E_NAME__:__O_PORT__', 'a:1:{s:4:"PORT";i:80;}', '2016-08-01 22:28:44', '2016-09-07 16:30:49'),
(6, 'dirb', 'dirb http__O_SSL__://__E_NAME__:__O_PORT__ /opt/SecLists/dirb/big.txt -S', 'a:1:{s:4:"PORT";i:80;}', '2016-08-01 22:28:44', '2016-09-22 12:25:23'),
(7, 'theharvester', 'theharvester -d __E_NAME__ -b all -l 1000 -n', 'N;', '2016-08-01 22:28:44', '2016-09-02 19:55:27'),
(8, 'wappalyzer', 'wappalyzer http__O_SSL__://__E_NAME__:__O_PORT__', 'a:1:{s:4:"PORT";i:80;}', '2016-08-14 11:14:04', '2016-09-23 14:01:33'),
(9, 'testssl', 'testssl --color 0 -U __E_NAME__', 'N;', '2016-08-24 16:43:08', '2016-09-02 19:55:21'),
(10, 'wpscan', 'wpscan --url http__O_SSL__://__E_NAME__:__O_PORT__ --enumerate u -r --update --batch', 'a:1:{s:4:"PORT";i:80;}', '2016-09-23 15:18:17', '2016-10-10 15:05:25');
