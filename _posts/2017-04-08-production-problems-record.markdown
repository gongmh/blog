---
layout: post
title:  "生产环境中问题记录"
date:  "2017-04-08 07:18:40 +0800"
category: production
tags: case study
keywords: production problem
description: ""
---

* TOC  
{:toc}  

在实际的生产环境中，各个系统都独立部署。系统间通过RPC调用，与单机环境有很多不同的地方。本文记录一些常见的case，希望对以后的开发中规避这些问题。  

### 1. 数据库主从延时问题  
有些场景下，我们需要先更新数据库然后读出数据再进行其他操作，这种在单机环境中不会有任何问题。但是实际生产环境数据库的部署是分主从的，主库用来更新操作，从库主要承担读的压力。数据库请求到来的时候，数据库上层有一个proxy，根据sql语句分发请求分别到主、从库。但是数据从主库同步到从库是有一定的时间间隔的，这个间隔从毫秒级到秒级都有可能，因此业务逻辑中要规避这种情况。  
解决方案：  
    * 业务中规避先写再读的case，尽量通过程序控制先读再写。  
    * 对于必须要求先写再读的业务逻辑，可以联系dba，让这部分的读分发到主库。  

### 2. php curl的毫秒级超时问题  
php业务中对外请求时，我们会设置超时时间，如果是秒级的超时，我们这样设置是没问题的：
    ``` php  
        $timeout = 2; //2s超时
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); //连接超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);    //处理超时
    ```  
但是实际业务中常常需要设置毫秒级的超时时间，例如`$timeout = 0.1;//100ms超时`再用上面的语句就会有问题了，你会发现超时时间根本没起作用。原因见[ Curl的毫秒超时的一个”Bug”](http://www.laruence.com/2014/01/21/2939.html)。如果需要毫秒级超时，必须设置CURLOPT_NOSIGNAL参数。  
    ``` php
        $timeout = 0.1;//100ms超时
        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $timeout * 1000);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout * 1000);
    ```
