#!/bin/bash

pwd=`pwd`

echo $pwd/_config.yml

#1. replace baseurl
sed -i 's/baseurl/# baseurl/g' _config.yml

#2. generate static files
jekyll b

sleep 10

#2. copy to web root dir
cp -rf _site /home/gongmh/.site