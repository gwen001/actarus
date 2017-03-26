#!/bin/bash


function usage() {
    echo "Usage: "$0" <task_id> <pid> <realpid>"
    if [ -n "$1" ] ; then
		echo "Error: "$1"!"
    fi
    exit
}

if [ ! $# -eq 3 ] ; then
    usage
fi

task_id=$1
task_pid=$2
task_realpid=$3

pid=$(ps auxf | grep "arus:task:run \-t $task_id" | awk '{print $2}')

if [ -n "$pid" ] ; then
	if [ "$pid" = "$task_pid" ] ; then
		#pkill -TERM -P $pid
		#kill -9 $task_pid $task_realpid
		kill -TERM -- -$task_pid
		echo "Task killed: "$task_id" "$task_pid" "$task_realpid
	else
		echo "Task not found!"
	fi
else
	echo "Task not found!"
fi

exit;
