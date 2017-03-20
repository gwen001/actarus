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
* auto recon - when an entity is created some tasks are immediatly performed. See `parameters/entity/recon`
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

Faq
==============
What the purpose of the table `db.actarus.requete`?  
Supposed to be a feature to performed tasks on specific url, like sqlmap. It's not currently used, you can delete it.  

What the purpose of the table `db.actarus.arus_entity_loot`?  
Supposed to be the data collected, like credentials, I finally decided to merge those datas with alerts, so it's not used anymore.  

It doesn't work!  
Try to read the `full_install.txt`.  

It still doesn't work !  
Contact me :)  

What if I use a mac?  
I have no fucking idea!  

What about PHP7?  
It works with PHP7, I currently use it, you could meet some deprecated error but not a big deal.  

Finally
==============
This tool help me a lot. It performs basics redundant actions and it keeps things classified. 
It's pretty interesting when you deal with many targets.
I had the opportunity to test it on 3 dedicated servers at the same time, the result was awesome. 
Unfortunately I don't like Symfony and I am to lazy now to continue this project.  

I don't believe in license.  
Feel free to do whatever you want with this program.  

Demo
==============
[![ScreenShot](http://10degres.net/images/actarus_video_preview.jpg)](https://www.youtube.com/watch?v=_u1-L0YjI7g)