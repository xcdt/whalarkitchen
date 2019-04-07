#!/usr/bin/env bash

if [ $# -eq 0 ]; then
   echo "missing environment (dev/test/stage/live)"
   exit 1
fi

if [ `echo "|dev|test|stage|live|" | grep "|${1}|" | wc -l` -eq 0 ]; then
   echo "Wrong environment (dev/test/stage/live)"
   exit 1
fi

if [ "$EUID" -ne 0 ]
  then echo "Please run as root"
  exit 1
fi


DIR=`dirname $0`

cd $DIR

   ### https://www.elastic.co/guide/en/elasticsearch/reference/current/vm-max-map-count.html
   sudo sysctl -w vm.max_map_count=262144
   docker-compose -f docker-compose.yml up -d

exit 0
