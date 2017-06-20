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

在最近项目中，前后端联调的时候php接口返回数组json后的数据，有时候是数组，有的时候却是对象，导致前端解析的时候总是抱怨数据问题。今天就对这个问题进行梳理，

### 一、JSON介绍

#### 1. JSON概念
[json](https://zh.wikipedia.org/wiki/JSON)是一种轻量级的数据交换文本格式。

#### 2. JSON数据结构[rfc4627](http://www.ietf.org/rfc/rfc4627.txt)

1. 对象（object）

一个对象以`{`开始，并以`}`结束。一个对象包含一系列非排序的`名称／值`对，每个`名称／值`对之间使用`,`分区。例如：  

```
{name:value}
```

2. 名称/值（collection）

名称和值之间使用`：`隔开，一般的形式是`{name:value}`。

3. 值的有序列表（Array）

一个或者多个值用`,`分区后，使用`[`，`]`括起来就形成了列表，例如：

```
[collection, collection]
```

4. 字符串

以`"`、`"`括起来的一串字符.

5. 数值

一系列0-9的数字组合，可以为负数或者小数。还可以用e或者E表示为指数形式。

6. 布尔值

用`true`或者`false`表示。

### 二、php中json_encode的表示

(php manual)[http://www.php.net/manual/en/function.json-encode.php]  > Note:
When encoding an array, if the keys are not a continuous numeric sequence starting from 0, all keys are encoded as strings, and specified explicitly for each key-value pair.

也就是说php的数组做json_encode的时候，只有数组的下标是从0开始的连续整数，才会json为列表形式；其他情况都是对象形式。	


