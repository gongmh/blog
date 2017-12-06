---
layout: post
title:  "php数组json后是数组还是对象问题"
date:  "2017-06-20 00:18:40 +0800"
category: production
tags: php
keywords: php
description: ""
---

* TOC  
{:toc}  

在最近项目中，前后端联调的时候php接口返回数组json后的数据，有时候是数组，有的时候却是对象，导致前端解析的时候总是抱怨数据问题。今天就对这个问题进行梳理。（**已解决，json_encode时设置JSON_FORCE_OBJECT选项即可。**）

### 一、JSON介绍

#### 1. JSON概念
[json](https://zh.wikipedia.org/wiki/JSON)是一种轻量级的数据交换文本格式。

#### 2. JSON数据结构[rfc4627](http://www.ietf.org/rfc/rfc4627.txt)

2.1 对象（object）  
一个对象以`{`开始，并以`}`结束。一个对象包含一系列非排序的`名称／值`对，每个`名称／值`对之间使用`,`分区。例如：  

```
{name:value}
```

2.2 名称/值（collection）  
名称和值之间使用`：`隔开，一般的形式是`{name:value}`。

2.3 值的有序列表（Array）  
一个或者多个值用`,`分区后，使用`[`，`]`括起来就形成了列表，例如：

```
[collection, collection]
```

2.4 字符串  
以`"`、`"`括起来的一串字符.

2.5 数值  
一系列0-9的数字组合，可以为负数或者小数。还可以用e或者E表示为指数形式。

2.6 布尔值  
用`true`或者`false`表示。

### 二、php中json_encode的表示

> Note: When encoding an array, if the keys are not a continuous numeric sequence starting from 0, all keys are encoded as strings, and specified explicitly for each key-value pair.    --  via [php manual](http://www.php.net/manual/en/function.json-encode.php)

也就是说php的数组做json_encode的时候，只有数组的下标是从0开始的连续整数，才会json为列表形式；其他情况都是对象形式。	

因此，针对php数组json_encode时，需要数组形式的话就重新排序一下数组即可。


当对php的数组进行json_encode的时候，可以设置option为JSON_FORCE_OBJECT，即可强制设置编码为JSON的对象。

```php
string json_encode ( mixed $value [, int $options = 0 [, int $depth = 512 ]] )
```

>[JSON_FORCE_OBJECT (integer)](http://php.net/manual/en/json.constants.php)
Outputs an object rather than an array when a non-associative array is used. Especially useful when the recipient of the output is expecting an object and the array is empty. Available since PHP 5.3.0.


