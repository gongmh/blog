#!/bin/bash

pwd=`pwd`

echo $pwd/_config.yml

#1. replace baseurl
sed -i 's/baseurl/# baseurl/g' _config.yml

#2. generate static files
jekyll b

sleep 5

#2. copy to web root dir
rm -r /home/gongmh/.site/*
cp -rf _site/* /home/gongmh/.site
cp ads.txt /home/gongmh/.site

echo "\033[31m Deploy success \033[0m"