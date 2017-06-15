---
layout: post
title:  "java web在mac出现bogon问题"
date:  "2017-06-15 07:18:40 +0800"
category: production
tags: java
keywords: java
description: ""
---

* TOC  
{:toc}  

### 问题引出：  
在mac本机启动java web的时候，出现下面报错：  
	`错误: 代理抛出异常错误: java.net.MalformedURLException: Local host name unknown: java.net.UnknownHostException: bogon: bogon: nodename nor servname provided, or not known`  

### 原因：
mac的终端会先向DNS查询当前ip对应的反向域名解析的结果，如果查询不到再显示我们设置的计算机名。由于DNS服务错误地将保留地址反向的NS查询结果返回为bogon（虚拟、虚伪），而不是localhost，导致计算机名变成了bogon，又导致程序出错。

### 解决方法： 

#### 方法一：  
设置hostname  
```
	sudo hostname your-desired-host-name
	sudo scutil --set LocalHostName $(hostname)
	sudo scutil --set HostName $(hostname)
```  

#### 方法二：  
设置DNS为`8.8.8.8`。  


> [Mac 终端里神秘的 bogon 及解决方法](https://air20.com/archives/486.html)