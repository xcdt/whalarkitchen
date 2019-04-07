#!/usr/bin/env bash

if [ $# -eq 0 ]; then
   echo "missing environment (dev/test/stage/live)"
   exit 1
fi

if [ `echo "|dev|test|stage|live|" | grep "|${1}|" | wc -l` -eq 0 ]; then
   echo "Wrong environment (dev/test/stage/live)"
   exit 1
fi

export ENV=$1
DATE_TIME=$(date '+%Y%m%d%H%M%S')

docker build -f Dockerfile.app.fullBuild --build-arg GLASSNET_CONFIG_ENV=${ENV} -t glassnet-app-${ENV}:v$DATE_TIME -t 851104204372.dkr.ecr.eu-west-1.amazonaws.com/glassnet-app-${ENV}:v$DATE_TIME .

#docker push 851104204372.dkr.ecr.eu-west-1.amazonaws.com/glassnet-app-${ENV}:v$DATE_TIME

