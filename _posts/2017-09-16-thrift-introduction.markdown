---
layout: post
title:  "Thrift介绍"
date:  "2017-09-16 23:09:40 +800"
category: Thrift
tags: Thrift
keywords: Thrift
description: ""
---


* TOC  
{:toc} 



本文内容来自[Apache Thrift](http://Thrift.apache.org/)，主要目的是为了学习总结。

### 1.介绍

[Thrift](https://zh.wikipedia.org/wiki/Thrift)是一种接口描述语言和二进制通讯协议，它被用来定义和创建跨语言的服务。

### 2.Thrift结构

Thrift的网络栈如下所示：

```
  +-------------------------------------------+
  | Server                                    |
  | (single-threaded, event-driven etc)       |
  +-------------------------------------------+
  | Processor                                 |
  | (compiler generated)                      |
  +-------------------------------------------+
  | Protocol                                  |
  | (JSON, compact etc)                       |
  +-------------------------------------------+
  | Transport                                 |
  | (raw TCP, HTTP etc)                       |
  +-------------------------------------------+
```

#### 2.1 Transport

Transport提供了网络读写的简单抽象。传输层可以使得底层传输与系统的其余部分分离，例如序列化/反序列化。

Transport提供的方法有：

    open
    close
    read
    write
    flush

除了上面的传输接口，Thrift还使用ServerTransport接口来接收或者创建原始传输对象。顾名思义，ServerTransport主要用于服务端，为到来的connection创建传输层对象。ServerTransport提供的方法有：

    open
    listen
    accept
    close


#### 2.2 Protocol

Protocol定义内存数据结构到序列化映射机制。换句话说就是，Protocol规定如何对Transport的数据进行编解码。因此Protocol实现编解码方案，并且负责序列化/反序列化。常用的协议有JSON、XML、纯文本和compact binary等。

`Thrift Protocol`是面向流的设计，因此在开始序列化之前，不需要知道字符串的长度或列表中的项目数量。Thrift支持的协议如下所示：

    binary：将字段的长度和类型编码，然后是字段的value
    compact：见[Thrift-110](https://issues.apache.org/jira/browse/Thrift-110)
    json


#### 2.3 Processor

Processor负责从输入流读取数据，向输出流写入数据。输入输出流由Protocol objects来表示。Processor接口非常简单：


```
interface TProcessor {
    bool process(TProtocol in, TProtocol out) throws TException
}
```

Processor从输入流中读取数据，委托用户实现的处理函数处理，然后向输出流中写入响应。

#### 2.4 Server

Server将上述功能集中在一起：

    （1）创建transport
    （2）为transport创建输入/输出protocol
    （3）基于输入/输出protocol创建processor
    （4）等待到来的connections，然后将connection移交到相应的processor

### 3.Interface Description Language (IDL)

3.1 Thrift Types

**Base Types**

    bool: A boolean value (true or false)
    byte: An 8-bit signed integer
    i16: A 16-bit signed integer
    i32: A 32-bit signed integer
    i64: A 64-bit signed integer
    double: A 64-bit floating point number
    string: A text string encoded using UTF-8 encoding

**Structs**

类似于OOP中没有继承的类，struct是一组强类型字段组成，并且每个字段都有唯一的名称标识。

**Containers**

Thrift container是强类型的容器，与大多数的编程语言中的容器一致。Thrift定义了三种容器：

    list
    set
    map

容器中的元素可以是任意的合法Thrift type。另外，为了兼容各种语言，map和JSON中的key只能是base type。

**Exceptions**

exception功能上和struct类似，是为了和各种语言的异常处理机制无缝集成。

**Services**

Thrift使用type来定义service。service的定义在语义上等同于OOP中的接口定义。通过Thrift编译器实现接口，并且生成客户端和服务端的全部功能。

service由一组命名函数组成，每个函数都有一个参数列表和返回类型。除了Thrift定义的其他type，void也是合法的函数返回类型。另外，oneway关键字也可以修饰到void函数，oneway将会生成不需要等待响应的代码。void函数也将会给client返回一个响应，来保证request成功到达服务端。然而对于oneway的函数调用，仅仅保证在transport层request成功。同一个client的oneway函数调用，在服务端会并行或者无序执行。


### 3.2 Namespace

namespace用来声明，文件中定义的type应该在哪个namaspace/module/package等。

例如Facebook's [fb303.Thrift](https://git-wip-us.apache.org/repos/asf?p=Thrift.git;a=blob_plain;f=contrib/fb303/if/fb303.Thrift;hb=HEAD)namespace如下：

```
namespace java com.facebook.fb303
namespace cpp facebook.fb303
namespace perl Facebook.FB303
namespace netcore Facebook.FB303.Test
```

### 4.实践

我们使用Thrift的大体流程如下：

    （1）服务端定义接口，设计idl文件
    （2）服务端、客户端根据相应的语言，生成对应的类
    （3）服务端开发实现具体的服务并启动服务
    （4）客户端请求服务端

具体实现代码可以参考[learnThrift](https://github.com/gongmh/learnThrift)。

#### 4.1 安装

#### 4.2 IDL

```
//learn.Thrift
namespace java com.gongmh.gen_java
namespace php gen_php

enum enumType {
    FIRST,
    SECOND
}

struct struceType{
    1: required i32  one;
    2: required i64 two;
    3: optional string three;
}

struct requestType{
    1: required i32 one;
    2: required string two;
    3: optional list<string> three;
    4: optional enumType four = enumType.FIRST;
    5: optional map<string, string> five;
}

struct responseType{
    1: required i32   errno = 0;
    2: required string errmsg = "OK";
    3: required  map<string, struceType>  data;
}

service ThriftService {
    responseType learnThrift(1: requestType data)
}

```

#### 4.3 服务端（java）

(1)生成服务端文件

```
Thrift -r --gen java learn.Thrift
```

(2)项目中添加Thrift依赖(maven)

```
<dependency>
    <groupId>org.apache.Thrift</groupId>
    <artifactId>libThrift</artifactId>
    <version>0.10.0</version>
</dependency>
```

(3)实现具体服务

初始化服务端

``` java
try {
    TProcessor tprocessor = new ThriftService.Processor<ThriftService.Iface>(new LearnThriftImpl());

    TServerSocket serverTransport = new TServerSocket(9099);

    TServer.Args tArgs = new TServer.Args(serverTransport);
    tArgs.processor(tprocessor);
    tArgs.protocolFactory(new TBinaryProtocol.Factory());
    tArgs.transportFactory(new TFramedTransport.Factory());
    TServer server = new TSimpleServer(tArgs);
    System.out.println("Start server on port 9099...");
    server.serve();
} catch (Exception e) {
    e.printStackTrace();
}
```

实现具体服务

``` java
package com.gongmh;

import com.gongmh.gen_java.*;

import java.util.HashMap;
import java.util.Map;

class LearnThriftImpl implements ThriftService.Iface{

    public responseType learnThrift(requestType data){
        responseType rt = new responseType();
        System.out.println("hello");

        Map<String,struceType> responseData = new HashMap<String, struceType>();

        struceType struceTypeVar = new struceType();
        struceTypeVar.setOne(1);
        struceTypeVar.setTwo(2);
        struceTypeVar.setThree("three");
        responseData.put("1", struceTypeVar);

        rt.setData(responseData);
        return rt;
    }
}
```

#### 4.4 客户端

(1)生成服务端文件

```
Thrift -r --gen php learn.Thrift
```

(2)项目中添加Thrift依赖(composer)

```
{
    "require" : {
        "apache/Thrift" : ">= 0.10.0"    
    }    
}
```

(3)客户端发起请求

``` php
<?php

require_once('./vendor/autoload.php');
require_once('./gen_php/Types.php');
require_once('./gen_php/ThriftService.php');

require_once('./vendor/apache/Thrift/lib/php/lib/Thrift/ClassLoader/ThriftClassLoader.php');

use Thrift\ClassLoader\ThriftClassLoader;

$loader = new ThriftClassLoader();
$loader->registerNamespace('Thrift', __DIR__);
$loader->register();


use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TSocket;
use Thrift\Transport\THttpClient;
use Thrift\Transport\TFramedTransport;
use Thrift\Exception\TException;

$request = new \gen_php\RequestType();
$request->one = 1;
$request->two = "abc";

$socket = new TSocket('127.0.0.1', 9099);
$transport = new TFramedTransport($socket);
$protocol = new TBinaryProtocol($transport);
$client = new \gen_php\ThriftServiceClient($protocol);

$transport->open();
$response = $client->learnThrift($request);
$transport->close();

echo json_encode($response);

```

#### 4.5 通信

### 5.总结
