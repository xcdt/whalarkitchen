#!/bin/bash

DIR=./recipes

for a in $(ls $DIR); do curl -H 'Content-Type: application/json' -XPOST 'http://localhost:9200/cookbook/recipe' -d @${DIR}/${a}; done


