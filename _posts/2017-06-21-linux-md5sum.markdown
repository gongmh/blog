---
layout: post
title:  "linux下字符串和文件的md5值计算"
date:  "2017-06-21 00:18:40 +0800"
category: linux
tags: md5
keywords: md5
description: ""
---

* TOC  
{:toc}  


### 1、计算指定文件的MD5值

```
md5sum a.rmvb

//输出结果类似如下：
8dab209d0b7c7fb1afb071f0855a8c37 a.rmvb
```

即计算出的md5值加上文件名

### 2、计算字符串MD5值

```
echo -n “password” | md5sum
//输出：
5f4dcc3b5aa765d61d8327deb882cf99 -
```

上面`echo`加`-n`的作用是不输出回车符，因为`echo`命令默认会添加一回车符。
像如果是:

```
echo “password” | md5sum
//那输出的将会是：
286755fad04869ca523320acce0dc6a4 -
```

因此在命令行下，计算字符串的MD5值一般是要加-n参数。