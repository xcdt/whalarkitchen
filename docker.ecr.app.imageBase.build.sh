#!/usr/bin/env bash

DATE_TIME=$(date '+%Y%m%d%H%M%S')
VERSION=1.1.0

docker build -f Dockerfile.app.imageBase -t whalar-kitchen-app-base:v$VERSION -t whalar-kitchen-app-base:v$DATE_TIME .


