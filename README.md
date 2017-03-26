ACTARUS
===================

Purpose
==============
I started the development of Actarus to learn Symfony framework.

Description
==============
Actarus is a tool designed to help ~~hackers~~ bounty hunters in their task of performing information gathering about their ~~targets~~ clients.

Features
==============
* project managment - create, edit, delete  
* server managment - create, import, edit, delete, search  
* domain managment - create, edit, delete, search  
* host (subdomain) managment - create, import, edit, delete, search  
* task managment - create, edit, delete, search  
* task priority 
* task autokill - when a task is longer than x minutes. See `parameters/task/max_duration`
* automatic recon - when an entity is created some tasks are immediatly performed. See `parameters/entity/recon`
* result interpretation and callback - when a task is finished a callback is immediatly executed and performed some actions. See _"Settings/Manage Task and their Callback"_ and `src/AppBundle/Command/InterpretTaskCommand.php`
* alert managment - create, edit, delete, search
* alert generation - the callbacks can generate alert, 4 levels available: info, low, medium, high. See `parameters/alerts/level`
* technology managment - add, delete
* technology gathering - Actarus can link an entity with certain technology. See `db.actarus.arus_technology`
* multi processing - the three daemons fork to many childs. See `parameters/*_max_child`
* clustering - Actarus can be installed on many servers, while the gui frontend is enabled on only one, the daemon (task runner) can be launched on all of them, admitting that the servers can access the database
* HackerOne cron for project and scope grabbing - see `src/AppBundle/Command/CronCommand.php`

Required
==============
Mysql  
Apache  
PHP and php-dev  
libyaml and libyaml-dev  
pecl yaml  

Quick Install
==============
git clone https://github.com/gwen001/actarus.git  
cd actarus  
php composer.phar install  
php app/console doctrine:schema:update --dump-sql  
php app/console doctrine:schema:update --force  
chmod -R 500 .  
chmod -R 700 app/cache app/logs  

The tools configuration is up to you, depending what kind of recon you want to perform.

Create a user
==============
`php app/console fos:user:create <username> <email> <password> --super-admin`

Run daemons
==============
`[...]/actarus/vendor/actarus/custom/daemon_run_task.php`
`[...]/actarus/vendor/actarus/custom/daemon_interpret_task.php`
`[...]/actarus/vendor/actarus/custom/daemon_kill_task.php`

How to configure a new module/command
==============
1. Install the concerned program on your machine and test it by yourself  
2. Create a symblic link in the binary path configured in Actarus `parameters/bin_path` default is `/opt/bin`  
3. Configure the task in `Settings/Manage Task and their Callback`  
name: will be the name of the function in the PHP code so no space or strange symbols  
command: the full command to launch your program as you want (will probably be the name of the symbolic link you create in step 3 + some options)  
You can also use some special datas related to the concerned entity `__E_<entity_property__>__` (check the database to get the properties)  
You can also configure options by yourself `__O_<option_name>__`  
4. (optionnal) edit the task to add callback and configure alert  
5. (optionnal) if the callback is tricky you can write your own PHP code in `src/AppBundle/Command/InterpretTaskCommand.php`  

Faq
==============
What the purpose of the table `db.actarus.requete`?  
Supposed to be a feature to performed tasks on specific url, like sqlmap. It's not currently used, you can delete it.  

What the purpose of the table `db.actarus.arus_entity_loot`?  
Supposed to be the data collected, like credentials, I finally decided to merge those datas with alerts, so it's not used anymore.  

It doesn't work!  
Try to read the `full_install.txt`.  

It still doesn't work !  
I would be happy to answer any question about Actarus installation/configuration but for problem related to Apache configuration or Linux problem I'm definitly not your man.

What if I use a mac?  
I have no fucking idea!  

What about PHP7?  
It works with PHP7, I currently use it, you could meet deprecated errors but not a big deal.  

Can I delete the project Actarus?  
Never do that!

How to properly stop the daemons?
Simply create the file `daemon_run_task.php.stop`, `daemon_run_task.php.stop` or `daemon_run_task.php.stop` in the daemons folder and the corresponding daemon will immediatly stop
(note that the daemon will shutdown but not the current running task).

Bugs
==============
They are, yes.

Finally
==============
This tool help me a lot. It performs basics redundant actions and it keeps things classified. 
It's pretty interesting when you deal with many targets.
Unfortunately I don't like Symfony and I am to lazy now to continue this project.  

I don't believe in license.  
Feel free to do whatever you want with this program.  

Demo
==============
If you want to test Actarus but you can't (or don't want) install it, I created a "small" Debian virtual machine with VirtualBox where it's ready to use in a very basic configuration.
[Give it a try](http://10degres.net/assets/actarus.ova)

<br>

[![ScreenShot](http://10degres.net/images/actarus_video_preview.jpg)](https://www.youtube.com/watch?v=_u1-L0YjI7g)