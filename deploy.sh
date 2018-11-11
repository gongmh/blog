#!/bin/bash

pwd=`pwd`

echo $pwd/_config.yml

#1. replace baseurl
sed -i 's/baseurl/# baseurl/g' _config.yml

#2. generate static files
jekyll g

#3. copy to web root dir
mv _site /usr/share/nginx/html
