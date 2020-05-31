---
layout: post
title:  "shell解析yaml文件"
date:   2020-05-31 13:40:18 +0800
categories: linux
tags: shell yaml
author: gongmh
---

* TOC
{:toc}


在有些场景下，想要解析yaml文件，但是环境受限只能使用shell脚本，不能使用python等高级语言解析。调研后，本文记录下解析shell脚本，以备后用。

shell解析函数如下，

``` shell
function parse_yaml {
   local prefix=$2
   local s='[[:space:]]*' w='[a-zA-Z0-9_]*' fs=$(echo @|tr @ '\034')
   sed -ne "s|^\($s\):|\1|" \
        -e "s|^\($s\)\($w\)$s:$s[\"']\(.*\)[\"']$s\$|\1$fs\2$fs\3|p" \
        -e "s|^\($s\)\($w\)$s:$s\(.*\)$s\$|\1$fs\2$fs\3|p"  $1 |
   awk -F$fs '{
      indent = length($1)/2;
      vname[indent] = $2;
      for (i in vname) {if (i > indent) {delete vname[i]}}
      if (length($3) > 0) {
         vn=""; for (i=0; i<indent; i++) {vn=(vn)(vname[i])("_")}
         printf("%s%s%s=\"%s\"\n", "'$prefix'",vn, $2, $3);
      }
   }'
}
```

脚本中的解析函数调用格式为：`parse_yaml <path_to_conf.yaml> <prefix>`。调用后就会有prefix为前缀的配置变量。

例如，yaml文件内容如下：

``` yaml
#conf_path: ~/conf.yaml

## global definitions
global:
  debug: yes
  verbose: no
  debugging:
    detailed: no
    header: "debugging started"

## output
output:
   file: "yes"

```

执行解析配置文件

``` shell
eval $(parse_yaml "~/conf.yaml" "conf_")
```

输出解析后的一些配置值

``` shell
echo $conf_global_debug  #output: yes
echo $conf_global_debugging_header  #output: debugging started
echo $conf_output_file   #output: yes
```

如上，我们就解析出来对应的配置了~


原文[How can I parse a YAML file from a Linux shell script?](https://stackoverflow.com/questions/5014632/how-can-i-parse-a-yaml-file-from-a-linux-shell-script)