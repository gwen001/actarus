#!/bin/bash


bash_source=$(ls -ld ${BASH_SOURCE[0]})
source_dir=$(echo $bash_source | awk -F "-> " '{print $2}')
if [ ${#source_dir} -gt 0 ] ; then
  source_dir=$(dirname $source_dir)"/"
else
  source_dir=$(dirname $(echo $bash_source | awk -F " " '{print $(NF)}'))"/"
fi

cd $source_dir

touch daemon_run_task.php.stop
touch daemon_kill_task.php.stop
touch daemon_interpret_task.php.stop
